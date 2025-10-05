<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/TwoFactorAuth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

try {
    // Récupérer les données JSON
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        throw new Exception('Données invalides');
    }

    // Valider les données requises
    $emailOrPseudo = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if (empty($emailOrPseudo) || empty($password)) {
        throw new Exception('Email/pseudo et mot de passe requis');
    }

    // Déterminer si c'est un email ou un pseudo
    $isEmail = filter_var($emailOrPseudo, FILTER_VALIDATE_EMAIL);

    if ($isEmail) {
        // Recherche par email
        $stmt = $pdo->prepare("SELECT id, firstname, lastname, pseudo, email, password, role, avatar, rating, is_verified FROM users WHERE email = ?");
    } else {
        // Recherche par pseudo
        $stmt = $pdo->prepare("SELECT id, firstname, lastname, pseudo, email, password, role, avatar, rating, is_verified FROM users WHERE pseudo = ?");
    }

    $stmt->execute([$emailOrPseudo]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('Utilisateur introuvable');
    }

    // Vérifier le mot de passe
    if (!password_verify($password, $user['password'])) {
        throw new Exception('Mot de passe incorrect');
    }

    // Vérifier si l'email est vérifié
    if (!$user['is_verified']) {
        throw new Exception('Veuillez vérifier votre email avant de vous connecter. Consultez votre boîte de réception.');
    }

    // Vérifier si l'A2F est activée
    $stmt = $pdo->prepare("SELECT two_factor_enabled, two_factor_secret, backup_codes FROM user_security WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $security = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($security && $security['two_factor_enabled']) {
        // A2F activée - vérifier si le code est fourni
        $twoFactorCode = $data['two_factor_code'] ?? '';

        if (empty($twoFactorCode)) {
            // Premier appel - demander le code A2F
            echo json_encode([
                'success' => false,
                'require_2fa' => true,
                'message' => 'Code d\'authentification requis',
                'user_id' => $user['id']
            ]);
            exit;
        }

        // Vérifier le code A2F
        $twoFA = new TwoFactorAuth();
        $isValid = $twoFA->verifyCode($security['two_factor_secret'], $twoFactorCode);

        // Si le code principal n'est pas valide, vérifier les codes de sauvegarde
        if (!$isValid && !empty($security['backup_codes'])) {
            $backupCodes = json_decode($security['backup_codes'], true);
            $remainingCodes = $twoFA->verifyBackupCode($backupCodes, $twoFactorCode);

            if ($remainingCodes !== false) {
                $isValid = true;

                // Mettre à jour les codes de sauvegarde restants
                $stmt = $pdo->prepare("UPDATE user_security SET backup_codes = ? WHERE user_id = ?");
                $stmt->execute([json_encode($remainingCodes), $user['id']]);
            }
        }

        if (!$isValid) {
            throw new Exception('Code d\'authentification incorrect');
        }
    }

    // Supprimer le mot de passe des données de session
    unset($user['password']);

    // Créer la session
    $_SESSION['user'] = $user;

    // Mettre à jour last_login
    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Connexion réussie',
        'user' => $user
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>