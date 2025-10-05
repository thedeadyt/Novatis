<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/TwoFactorAuth.php';
require_once __DIR__ . '/../../../includes/NotificationService.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$user = $_SESSION['user'];
$twoFA = new TwoFactorAuth();

// Connexion à la base de données
try {
    $host = 'mysql-alex2pro.alwaysdata.net';
    $db   = 'alex2pro_movatis';
    $user_db = 'alex2pro_alex';
    $pass = 'Alex.2005';
    $charset = 'utf8mb4';

    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user_db, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données']);
    exit;
}

// Récupérer les données JSON
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {
    case 'generate_secret':
        generateSecret($twoFA, $user);
        break;

    case 'enable_2fa':
        enableTwoFactor($twoFA, $pdo, $user, $input);
        break;

    case 'disable_2fa':
        disableTwoFactor($pdo, $user, $input);
        break;

    case 'get_backup_codes':
        getBackupCodes($pdo, $user);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
}

/**
 * Génère un nouveau secret et le QR code
 */
function generateSecret($twoFA, $user) {
    try {
        // Générer un nouveau secret
        $secret = $twoFA->generateSecret();

        // Générer l'URL du QR code
        $qrCodeUrl = $twoFA->getQRCodeUrl($user, $secret);

        // Stocker temporairement le secret en session
        $_SESSION['temp_2fa_secret'] = $secret;

        echo json_encode([
            'success' => true,
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la génération du secret']);
    }
}

/**
 * Active l'A2F après vérification du code
 */
function enableTwoFactor($twoFA, $pdo, $user, $input) {
    $secret = $input['secret'] ?? '';
    $code = $input['code'] ?? '';

    if (empty($secret) || empty($code)) {
        echo json_encode(['success' => false, 'message' => 'Secret et code requis']);
        return;
    }

    // Vérifier le code
    if (!$twoFA->verifyCode($secret, $code)) {
        echo json_encode(['success' => false, 'message' => 'Code incorrect']);
        return;
    }

    // Générer les codes de sauvegarde
    $backupCodes = $twoFA->generateBackupCodes(10);
    $backupCodesJson = json_encode($backupCodes);

    try {
        // Mettre à jour la base de données
        $stmt = $pdo->prepare("
            INSERT INTO user_security (user_id, two_factor_enabled, two_factor_secret, backup_codes)
            VALUES (?, 1, ?, ?)
            ON DUPLICATE KEY UPDATE
                two_factor_enabled = 1,
                two_factor_secret = ?,
                backup_codes = ?,
                updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([$user['id'], $secret, $backupCodesJson, $secret, $backupCodesJson]);

        // Mettre à jour la session
        $_SESSION['user']['two_factor_enabled'] = 1;

        // Supprimer le secret temporaire
        unset($_SESSION['temp_2fa_secret']);

        // Envoyer une notification de sécurité
        try {
            $notificationService = new NotificationService($pdo);
            $notificationService->notifySecurityAlert(
                $user['id'],
                "L'authentification à deux facteurs (A2F) a été activée sur votre compte. Votre compte est maintenant plus sécurisé."
            );
        } catch (Exception $e) {
            error_log("Erreur notification A2F activée: " . $e->getMessage());
        }

        echo json_encode([
            'success' => true,
            'message' => 'A2F activée avec succès',
            'backup_codes' => $backupCodes
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'activation de l\'A2F']);
    }
}

/**
 * Désactive l'A2F après vérification du mot de passe
 */
function disableTwoFactor($pdo, $user, $input) {
    $password = $input['password'] ?? '';

    if (empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Mot de passe requis']);
        return;
    }

    try {
        // Vérifier le mot de passe
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userData || !password_verify($password, $userData['password'])) {
            echo json_encode(['success' => false, 'message' => 'Mot de passe incorrect']);
            return;
        }

        // Désactiver l'A2F
        $stmt = $pdo->prepare("
            UPDATE user_security
            SET two_factor_enabled = 0,
                two_factor_secret = NULL,
                backup_codes = NULL,
                updated_at = CURRENT_TIMESTAMP
            WHERE user_id = ?
        ");
        $stmt->execute([$user['id']]);

        // Mettre à jour la session
        $_SESSION['user']['two_factor_enabled'] = 0;

        // Envoyer une notification de sécurité
        try {
            $notificationService = new NotificationService($pdo);
            $notificationService->notifySecurityAlert(
                $user['id'],
                "⚠️ L'authentification à deux facteurs (A2F) a été désactivée sur votre compte. Si ce n'était pas vous, réactivez-la immédiatement et changez votre mot de passe."
            );
        } catch (Exception $e) {
            error_log("Erreur notification A2F désactivée: " . $e->getMessage());
        }

        echo json_encode(['success' => true, 'message' => 'A2F désactivée avec succès']);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la désactivation de l\'A2F']);
    }
}

/**
 * Récupère les codes de sauvegarde
 */
function getBackupCodes($pdo, $user) {
    try {
        $stmt = $pdo->prepare("SELECT backup_codes FROM user_security WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $security = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($security && $security['backup_codes']) {
            $backupCodes = json_decode($security['backup_codes'], true);

            if (is_array($backupCodes) && count($backupCodes) > 0) {
                echo json_encode(['success' => true, 'backup_codes' => $backupCodes]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Aucun code de sauvegarde disponible']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'A2F non activée ou aucun code trouvé']);
        }

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des codes']);
    }
}
?>
