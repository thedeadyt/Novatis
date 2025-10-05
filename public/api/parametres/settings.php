<?php
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

// Connexion aux bases de données
try {
    $host = 'mysql-alex2pro.alwaysdata.net';
    $db   = 'alex2pro_movatis';
    $user_db = 'alex2pro_alex';
    $pass = 'Alex.2005';
    $charset = 'utf8mb4';

    // Base principale
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user_db, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Base des paramètres (même base pour l'instant)
    $pdo_settings = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user_db, $pass);
    $pdo_settings->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Créer les tables si elles n'existent pas
    createTablesIfNotExist($pdo_settings);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de connexion à la base de données']);
    exit;
}

$user = $_SESSION['user'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'update_profile':
            updateProfile($pdo, $pdo_settings, $user);
            break;

        case 'change_password':
            changePassword($pdo, $user);
            break;

        case 'toggle_2fa':
            toggle2FA($pdo_settings, $user);
            break;

        case 'update_notifications':
            updateNotifications($pdo_settings, $user);
            break;

        case 'update_privacy':
            updatePrivacy($pdo_settings, $user);
            break;

        case 'update_display':
            updateDisplay($pdo_settings, $user);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Action non reconnue']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
}

function createTablesIfNotExist($pdo) {
    // Table des préférences
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_preferences (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            email_notifications BOOLEAN DEFAULT TRUE,
            push_notifications BOOLEAN DEFAULT FALSE,
            sms_notifications BOOLEAN DEFAULT FALSE,
            dark_mode BOOLEAN DEFAULT FALSE,
            language VARCHAR(10) DEFAULT 'fr',
            timezone VARCHAR(50) DEFAULT 'Europe/Paris',
            currency VARCHAR(3) DEFAULT 'EUR',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user (user_id)
        )
    ");

    // Table de sécurité
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_security (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL UNIQUE,
            two_factor_enabled BOOLEAN DEFAULT FALSE,
            two_factor_secret VARCHAR(255) DEFAULT NULL,
            backup_codes JSON DEFAULT NULL,
            last_password_change TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            password_expires_at TIMESTAMP NULL,
            login_attempts INT DEFAULT 0,
            locked_until TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");

    // Table de confidentialité
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_privacy (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL UNIQUE,
            profile_visibility VARCHAR(20) DEFAULT 'public',
            show_email BOOLEAN DEFAULT FALSE,
            show_phone BOOLEAN DEFAULT FALSE,
            allow_search_engines BOOLEAN DEFAULT TRUE,
            data_sharing BOOLEAN DEFAULT FALSE,
            analytics_tracking BOOLEAN DEFAULT TRUE,
            marketing_emails BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
}

function updateProfile($pdo, $pdo_settings, $user) {
    try {
        $firstname = trim($_POST['firstname'] ?? '');
        $lastname = trim($_POST['lastname'] ?? '');
        $pseudo = trim($_POST['pseudo'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $website = trim($_POST['website'] ?? '');
        $language = $_POST['language'] ?? 'fr';
        $timezone = $_POST['timezone'] ?? 'Europe/Paris';

        // Validation
        if (empty($firstname) || empty($lastname) || empty($pseudo)) {
            $_SESSION['error_message'] = 'Le prénom, le nom et le pseudo sont requis';
            header('Location: ' . BASE_URL . '/Parametres?section=profile');
            return;
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_message'] = 'Email invalide';
            header('Location: ' . BASE_URL . '/Parametres?section=profile');
            return;
        }

        // Vérifier si l'email existe déjà (sauf pour l'utilisateur actuel)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user['id']]);
        if ($stmt->fetch()) {
            $_SESSION['error_message'] = 'Cette adresse email est déjà utilisée';
            header('Location: ' . BASE_URL . '/Parametres?section=profile');
            return;
        }

        // Vérifier si le pseudo existe déjà (sauf pour l'utilisateur actuel)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE pseudo = ? AND id != ?");
        $stmt->execute([$pseudo, $user['id']]);
        if ($stmt->fetch()) {
            $_SESSION['error_message'] = 'Ce pseudo est déjà utilisé';
            header('Location: ' . BASE_URL . '/Parametres?section=profile');
            return;
        }

        // Mise à jour dans la table users
        $stmt = $pdo->prepare("UPDATE users SET firstname = ?, lastname = ?, pseudo = ?, email = ?, phone = ?, bio = ?, location = ?, website = ? WHERE id = ?");
        $stmt->execute([$firstname, $lastname, $pseudo, $email, $phone, $bio, $location, $website, $user['id']]);

        // Mise à jour des préférences
        $stmt = $pdo_settings->prepare("
            INSERT INTO user_preferences (user_id, language, timezone)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE
            language = VALUES(language),
            timezone = VALUES(timezone)
        ");
        $stmt->execute([$user['id'], $language, $timezone]);

        // Mettre à jour la session
        // Mettre à jour la session avec toutes les nouvelles données
        $_SESSION['user']['firstname'] = $firstname;
        $_SESSION['user']['lastname'] = $lastname;
        $_SESSION['user']['pseudo'] = $pseudo;
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['phone'] = $phone;
        $_SESSION['user']['bio'] = $bio;
        $_SESSION['user']['location'] = $location;
        $_SESSION['user']['website'] = $website;

        $_SESSION['success_message'] = 'Profil mis à jour avec succès';
        header('Location: ' . BASE_URL . '/Parametres?section=profile');

    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Erreur lors de la mise à jour du profil : ' . $e->getMessage();
        header('Location: ' . BASE_URL . '/Parametres?section=profile');
    }
}

function changePassword($pdo, $user) {
    try {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['error_message'] = 'Tous les champs sont requis';
            header('Location: ' . BASE_URL . '/Parametres?section=security');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['error_message'] = 'Les mots de passe ne correspondent pas';
            header('Location: ' . BASE_URL . '/Parametres?section=security');
            return;
        }

        if (strlen($newPassword) < 8) {
            $_SESSION['error_message'] = 'Le mot de passe doit contenir au moins 8 caractères';
            header('Location: ' . BASE_URL . '/Parametres?section=security');
            return;
        }

        // Vérifier le mot de passe actuel
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $userData = $stmt->fetch();

        if (!$userData || !password_verify($currentPassword, $userData['password'])) {
            $_SESSION['error_message'] = 'Mot de passe actuel incorrect';
            header('Location: ' . BASE_URL . '/Parametres?section=security');
            return;
        }

        // Hasher le nouveau mot de passe
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Mise à jour
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $user['id']]);

        $_SESSION['success_message'] = 'Mot de passe modifié avec succès';
        header('Location: ' . BASE_URL . '/Parametres?section=security');

    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Erreur lors du changement de mot de passe : ' . $e->getMessage();
        header('Location: ' . BASE_URL . '/Parametres?section=security');
    }
}

function toggle2FA($pdo, $user) {
    try {
        // Récupérer l'état actuel
        $stmt = $pdo->prepare("SELECT two_factor_enabled FROM user_security WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $security = $stmt->fetch();

        $currentStatus = $security['two_factor_enabled'] ?? false;
        $newStatus = !$currentStatus;

        if ($newStatus) {
            // Activation de l'A2F - Générer un secret
            $secret = generateRandomSecret();
            $stmt = $pdo->prepare("
                INSERT INTO user_security (user_id, two_factor_enabled, two_factor_secret)
                VALUES (?, TRUE, ?)
                ON DUPLICATE KEY UPDATE
                two_factor_enabled = TRUE,
                two_factor_secret = VALUES(two_factor_secret)
            ");
            $stmt->execute([$user['id'], $secret]);
            $_SESSION['success_message'] = 'Authentification à deux facteurs activée';
        } else {
            // Désactivation de l'A2F
            $stmt = $pdo->prepare("
                INSERT INTO user_security (user_id, two_factor_enabled, two_factor_secret)
                VALUES (?, FALSE, NULL)
                ON DUPLICATE KEY UPDATE
                two_factor_enabled = FALSE,
                two_factor_secret = NULL
            ");
            $stmt->execute([$user['id']]);
            $_SESSION['success_message'] = 'Authentification à deux facteurs désactivée';
        }

        $_SESSION['user']['two_factor_enabled'] = $newStatus;
        header('Location: ' . BASE_URL . '/Parametres?section=security');

    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Erreur lors de la modification de l\'A2F : ' . $e->getMessage();
        header('Location: ' . BASE_URL . '/Parametres?section=security');
    }
}

function updateNotifications($pdo, $user) {
    try {
        $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;
        $pushNotifications = isset($_POST['push_notifications']) ? 1 : 0;

        $stmt = $pdo->prepare("
            INSERT INTO user_preferences (user_id, email_notifications, push_notifications)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE
            email_notifications = VALUES(email_notifications),
            push_notifications = VALUES(push_notifications)
        ");
        $stmt->execute([$user['id'], $emailNotifications, $pushNotifications]);

        $_SESSION['success_message'] = 'Préférences de notifications mises à jour';
        header('Location: ' . BASE_URL . '/Parametres?section=notifications');

    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Erreur lors de la mise à jour des notifications : ' . $e->getMessage();
        header('Location: ' . BASE_URL . '/Parametres?section=notifications');
    }
}

function updatePrivacy($pdo, $user) {
    try {
        $profileVisibility = $_POST['profile_visibility'] ?? 'public';
        $showEmail = isset($_POST['show_email']) ? 1 : 0;
        $showPhone = isset($_POST['show_phone']) ? 1 : 0;
        $allowSearchEngines = isset($_POST['allow_search_engines']) ? 1 : 0;
        $dataSharing = isset($_POST['data_sharing']) ? 1 : 0;

        $stmt = $pdo->prepare("
            INSERT INTO user_privacy (user_id, profile_visibility, show_email, show_phone, allow_search_engines, data_sharing)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            profile_visibility = VALUES(profile_visibility),
            show_email = VALUES(show_email),
            show_phone = VALUES(show_phone),
            allow_search_engines = VALUES(allow_search_engines),
            data_sharing = VALUES(data_sharing)
        ");
        $stmt->execute([$user['id'], $profileVisibility, $showEmail, $showPhone, $allowSearchEngines, $dataSharing]);

        $_SESSION['success_message'] = 'Paramètres de confidentialité mis à jour';
        header('Location: ' . BASE_URL . '/Parametres?section=privacy');

    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Erreur lors de la mise à jour de la confidentialité : ' . $e->getMessage();
        header('Location: ' . BASE_URL . '/Parametres?section=privacy');
    }
}

function updateDisplay($pdo, $user) {
    try {
        $darkMode = isset($_POST['dark_mode']) ? 1 : 0;
        $currency = $_POST['currency'] ?? 'EUR';
        $language = $_POST['language'] ?? 'fr';

        // Valider la langue
        $validLanguages = ['fr', 'en', 'es'];
        if (!in_array($language, $validLanguages)) {
            $language = 'fr';
        }

        $stmt = $pdo->prepare("
            INSERT INTO user_preferences (user_id, dark_mode, currency, language)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            dark_mode = VALUES(dark_mode),
            currency = VALUES(currency),
            language = VALUES(language)
        ");
        $stmt->execute([$user['id'], $darkMode, $currency, $language]);

        // Mettre à jour la langue dans la session
        require_once __DIR__ . '/../../../includes/Language.php';
        Language::setLanguage($language);

        $_SESSION['success_message'] = 'Préférences d\'affichage mises à jour';
        header('Location: ' . BASE_URL . '/Parametres?section=display');

    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Erreur lors de la mise à jour de l\'affichage : ' . $e->getMessage();
        header('Location: ' . BASE_URL . '/Parametres?section=display');
    }
}

function generateRandomSecret($length = 32) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $secret = '';
    for ($i = 0; $i < $length; $i++) {
        $secret .= $characters[random_int(0, strlen($characters) - 1)];
    }
    return $secret;
}
?>