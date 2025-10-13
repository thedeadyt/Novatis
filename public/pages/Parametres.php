<?php
require_once __DIR__ . '/../../config/config.php';

// Vérifie si l'utilisateur est connecté
isUserLoggedIn(true);

// Récupération des données utilisateur
$user = getCurrentUser();
$isAdmin = isAdmin();

// Section active (par défaut: profile)
$activeSection = $_GET['section'] ?? 'profile';

// Connexion à la base de données
try {
    $pdo = getDBConnection();
    $pdo_settings = $pdo; // Même connexion pour les paramètres

    // Créer les tables si elles n'existent pas
    $pdo_settings->exec("
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

    // Ajouter la colonne language si elle n'existe pas (pour les tables existantes)
    try {
        $pdo_settings->exec("ALTER TABLE user_preferences ADD COLUMN language VARCHAR(10) DEFAULT 'fr' AFTER dark_mode");
    } catch (PDOException $e) {
        // Colonne existe déjà, ignorer l'erreur
    }

    $pdo_settings->exec("
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

    $pdo_settings->exec("
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

    // Récupérer les préférences utilisateur
    $stmt = $pdo_settings->prepare("SELECT * FROM user_preferences WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $preferences = $stmt->fetch(PDO::FETCH_ASSOC);

    // Récupérer la sécurité utilisateur
    $stmt = $pdo_settings->prepare("SELECT * FROM user_security WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $security = $stmt->fetch(PDO::FETCH_ASSOC);

    // Récupérer la confidentialité utilisateur
    $stmt = $pdo_settings->prepare("SELECT * FROM user_privacy WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $privacy = $stmt->fetch(PDO::FETCH_ASSOC);

    // Récupérer les informations complètes de l'utilisateur depuis la table users
    $stmt = $pdo->prepare("
        SELECT id, firstname, lastname, pseudo, email, phone, avatar, rating, bio, location, website,
               is_verified, created_at, role
        FROM users
        WHERE id = ?
    ");
    $stmt->execute([$user['id']]);
    $userDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    // Mettre à jour les infos utilisateur en session avec les dernières données
    if ($userDetails) {
        // Fusionner en écrasant avec les nouvelles valeurs de la BDD
        foreach ($userDetails as $key => $value) {
            $user[$key] = $value;
        }
        $_SESSION['user'] = $user;
    }

    // Valeurs par défaut si pas de données
    if (!$preferences) {
        $preferences = [
            'email_notifications' => 1,
            'push_notifications' => 0,
            'sms_notifications' => 0,
            'dark_mode' => 0,
            'language' => 'fr',
            'timezone' => 'Europe/Paris',
            'currency' => 'EUR'
        ];
    }

    if (!$security) {
        $security = [
            'two_factor_enabled' => 0,
            'two_factor_secret' => null,
            'last_password_change' => date('Y-m-d H:i:s')
        ];
    }

    if (!$privacy) {
        $privacy = [
            'profile_visibility' => 'public',
            'show_email' => 0,
            'show_phone' => 0,
            'allow_search_engines' => 1,
            'data_sharing' => 0,
            'analytics_tracking' => 1,
            'marketing_emails' => 1
        ];
    }

} catch (PDOException $e) {
    // En cas d'erreur, utiliser les valeurs par défaut
    $preferences = [
        'email_notifications' => 1,
        'push_notifications' => 0,
        'sms_notifications' => 0,
        'dark_mode' => 0,
        'language' => 'fr',
        'timezone' => 'Europe/Paris',
        'currency' => 'EUR'
    ];
    $security = [
        'two_factor_enabled' => 0,
        'two_factor_secret' => null,
        'last_password_change' => date('Y-m-d H:i:s')
    ];
    $privacy = [
        'profile_visibility' => 'public',
        'show_email' => 0,
        'show_phone' => 0,
        'allow_search_engines' => 1,
        'data_sharing' => 0,
        'analytics_tracking' => 1,
        'marketing_emails' => 1
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Novatis | Paramètres</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>

    <!-- Variables CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/variables.css">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'custom-bg': '#e8e8e8',
                        'custom-white': '#e8e8e8',
                        'custom-black': '#1f2020',
                        'custom-red': '#B41200',
                        'accent-1': '#1f2020',
                        'accent-2': '#7F0D00',
                        'hover-1': '#464646',
                        'hover-2': '#E04830'
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: var(--font-tinos);
            background-color: var(--color-bg);
            color: var(--color-black);
        }

        .btn-primary {
            background: var(--color-red);
            color: var(--color-white);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--color-hover-2);
        }

        .btn-secondary {
            background: var(--color-white);
            color: var(--color-red);
            border: 1px solid var(--color-red);
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: var(--color-red);
            color: var(--color-white);
        }

        .sidebar-item {
            transition: all 0.3s ease;
            border-radius: 8px;
            margin-bottom: 4px;
        }

        .sidebar-item:hover {
            background: rgba(180, 18, 0, 0.1);
            transform: translateX(4px);
        }

        .sidebar-item.active {
            background: var(--color-red);
            color: white;
        }

        .sidebar-item.active:hover {
            background: var(--color-hover-2);
            transform: translateX(0);
        }

        .settings-card {
            background: var(--color-white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            margin-bottom: 1.5rem;
        }

        .settings-header {
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--color-black);
        }

        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #f9fafb;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--color-red);
            background: white;
            box-shadow: 0 0 0 3px rgba(180, 18, 0, 0.1);
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: var(--color-red);
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        .section-content {
            display: none;
        }

        .section-content.active {
            display: block;
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideOutRight {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(100%);
            }
        }

        .danger-zone {
            border: 1px solid #ef4444;
            border-radius: 8px;
            padding: 1rem;
            background-color: #fef2f2;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <!-- Script A2F -->
    <script src="<?= BASE_URL ?>/assets/js/2fa.js"></script>
    <script>
        // Initialiser le module A2F
        document.addEventListener('DOMContentLoaded', function() {
            TwoFactorAuth.init('<?= BASE_URL ?>');
        });
    </script>
</head>

<body class="bg-custom-bg min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="<?= BASE_URL ?>/dashboard" class="text-xl font-bold text-custom-black">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Novatis
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">
                        Connecté en tant que <strong><?= htmlspecialchars($user['email']) ?></strong>
                    </span>
                    <a href="<?= BASE_URL ?>/logout" class="btn-secondary px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-sign-out-alt mr-1"></i>
                        Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg">
            <div class="p-6">
                <h1 class="text-2xl font-bold text-custom-black mb-6">
                    <i class="fas fa-cog mr-2"></i>
                    Paramètres
                </h1>

                <nav class="space-y-2">
                    <a href="?section=profile"
                       class="sidebar-item flex items-center px-4 py-3 text-sm font-medium <?= $activeSection === 'profile' ? 'active' : 'text-gray-700' ?>">
                        <i class="fas fa-user mr-3"></i>
                        Profil
                    </a>

                    <a href="?section=security"
                       class="sidebar-item flex items-center px-4 py-3 text-sm font-medium <?= $activeSection === 'security' ? 'active' : 'text-gray-700' ?>">
                        <i class="fas fa-shield-alt mr-3"></i>
                        Sécurité
                    </a>

                    <a href="?section=notifications"
                       class="sidebar-item flex items-center px-4 py-3 text-sm font-medium <?= $activeSection === 'notifications' ? 'active' : 'text-gray-700' ?>">
                        <i class="fas fa-bell mr-3"></i>
                        Notifications
                    </a>

                    <a href="?section=privacy"
                       class="sidebar-item flex items-center px-4 py-3 text-sm font-medium <?= $activeSection === 'privacy' ? 'active' : 'text-gray-700' ?>">
                        <i class="fas fa-user-shield mr-3"></i>
                        Confidentialité
                    </a>

                    <a href="?section=display"
                       class="sidebar-item flex items-center px-4 py-3 text-sm font-medium <?= $activeSection === 'display' ? 'active' : 'text-gray-700' ?>">
                        <i class="fas fa-paint-brush mr-3"></i>
                        Affichage
                    </a>

                    <a href="?section=integrations"
                       class="sidebar-item flex items-center px-4 py-3 text-sm font-medium <?= $activeSection === 'integrations' ? 'active' : 'text-gray-700' ?>">
                        <i class="fas fa-plug mr-3"></i>
                        Intégrations
                    </a>

                    <a href="?section=support"
                       class="sidebar-item flex items-center px-4 py-3 text-sm font-medium <?= $activeSection === 'support' ? 'active' : 'text-gray-700' ?>">
                        <i class="fas fa-headset mr-3"></i>
                        Contact Support
                    </a>

                    <div class="border-t border-gray-200 my-4"></div>

                    <a href="?section=danger"
                       class="sidebar-item flex items-center px-4 py-3 text-sm font-medium <?= $activeSection === 'danger' ? 'active' : 'text-red-600' ?>">
                        <i class="fas fa-exclamation-triangle mr-3"></i>
                        Zone de danger
                    </a>
                </nav>
            </div>
        </div>

        <!-- Content Area -->
        <div class="flex-1 p-8">
            <!-- Section Profil -->
            <div id="profile-section" class="section-content <?= $activeSection === 'profile' ? 'active' : '' ?>">
                <div class="settings-card">
                    <div class="settings-header">
                        <h2 class="text-xl font-semibold text-custom-black">
                            <i class="fas fa-user mr-2"></i>
                            Informations du profil
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">Modifiez vos informations personnelles</p>
                    </div>

                    <form method="POST" action="<?= BASE_URL ?>/api/parametres/settings.php">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label">Prénom</label>
                                <input type="text" name="firstname" class="form-input"
                                       value="<?= htmlspecialchars($user['firstname'] ?? '') ?>"
                                       placeholder="Votre prénom" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Nom</label>
                                <input type="text" name="lastname" class="form-input"
                                       value="<?= htmlspecialchars($user['lastname'] ?? '') ?>"
                                       placeholder="Votre nom" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Pseudo</label>
                            <input type="text" name="pseudo" class="form-input"
                                   value="<?= htmlspecialchars($user['pseudo'] ?? '') ?>"
                                   placeholder="Votre pseudo" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-input"
                                   value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                   placeholder="votre@email.com" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Téléphone</label>
                            <input type="tel" name="phone" class="form-input"
                                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                   placeholder="+33 1 23 45 67 89">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Bio</label>
                            <textarea name="bio" class="form-input" rows="4"
                                      placeholder="Parlez-nous de vous..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label">Localisation</label>
                                <input type="text" name="location" class="form-input"
                                       value="<?= htmlspecialchars($user['location'] ?? '') ?>"
                                       placeholder="Paris, France">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Site web</label>
                                <input type="url" name="website" class="form-input"
                                       value="<?= htmlspecialchars($user['website'] ?? '') ?>"
                                       placeholder="https://votre-site.com">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Fuseau horaire</label>
                            <select name="timezone" class="form-input">
                                <option value="Europe/Paris" <?= $preferences['timezone'] === 'Europe/Paris' ? 'selected' : '' ?>>Paris (UTC+1)</option>
                                <option value="Europe/London" <?= $preferences['timezone'] === 'Europe/London' ? 'selected' : '' ?>>Londres (UTC+0)</option>
                                <option value="America/New_York" <?= $preferences['timezone'] === 'America/New_York' ? 'selected' : '' ?>>New York (UTC-5)</option>
                            </select>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn-primary px-6 py-2 rounded-lg">
                                <i class="fas fa-save mr-2"></i>
                                Sauvegarder
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Section Sécurité -->
            <div id="security-section" class="section-content <?= $activeSection === 'security' ? 'active' : '' ?>">
                <div class="settings-card">
                    <div class="settings-header">
                        <h2 class="text-xl font-semibold text-custom-black">
                            <i class="fas fa-shield-alt mr-2"></i>
                            Sécurité
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">Gérez vos paramètres de sécurité</p>
                    </div>

                    <!-- Changement de mot de passe -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-custom-black mb-3">Changer le mot de passe</h3>
                        <form method="POST" action="<?= BASE_URL ?>/api/parametres/settings.php">
                            <input type="hidden" name="action" value="change_password">

                            <div class="form-group">
                                <label class="form-label">Mot de passe actuel</label>
                                <input type="password" name="current_password" class="form-input"
                                       placeholder="Votre mot de passe actuel" required>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="form-group">
                                    <label class="form-label">Nouveau mot de passe</label>
                                    <input type="password" name="new_password" class="form-input"
                                           placeholder="Nouveau mot de passe" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Confirmer le mot de passe</label>
                                    <input type="password" name="confirm_password" class="form-input"
                                           placeholder="Confirmer le mot de passe" required>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="btn-primary px-6 py-2 rounded-lg">
                                    <i class="fas fa-key mr-2"></i>
                                    Changer le mot de passe
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Authentification à deux facteurs -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-medium text-custom-black mb-3">
                            <i class="fas fa-mobile-alt mr-2"></i>
                            Authentification à deux facteurs (A2F)
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Ajoutez une couche de sécurité supplémentaire à votre compte en utilisant une application d'authentification
                        </p>

                        <?php if (!$security['two_factor_enabled']): ?>
                            <!-- A2F désactivée - Afficher le bouton d'activation -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-info-circle text-blue-500 text-xl"></i>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <h4 class="text-sm font-medium text-blue-800 mb-2">
                                            Pourquoi activer l'A2F ?
                                        </h4>
                                        <ul class="text-sm text-blue-700 space-y-1 mb-3">
                                            <li>• Protégez votre compte contre les accès non autorisés</li>
                                            <li>• Recevez une alerte en cas de tentative de connexion suspecte</li>
                                            <li>• Ajoutez un code unique à 6 chiffres à votre connexion</li>
                                        </ul>
                                        <button type="button" onclick="TwoFactorAuth.showEnableModal()" class="btn-primary px-4 py-2 rounded-lg text-sm">
                                            <i class="fas fa-shield-alt mr-2"></i>
                                            Activer l'A2F
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- A2F activée - Afficher les informations -->
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                                        </div>
                                        <div class="ml-3">
                                            <h4 class="text-sm font-medium text-green-800">
                                                L'authentification à deux facteurs est activée
                                            </h4>
                                            <p class="text-sm text-green-700">
                                                Votre compte est protégé par un code de sécurité supplémentaire
                                            </p>
                                        </div>
                                    </div>
                                    <button type="button" onclick="TwoFactorAuth.showDisableModal()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm">
                                        <i class="fas fa-times mr-2"></i>
                                        Désactiver
                                    </button>
                                </div>
                            </div>

                            <!-- Codes de sauvegarde -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-custom-black mb-2 flex items-center">
                                    <i class="fas fa-key mr-2 text-gray-600"></i>
                                    Codes de sauvegarde
                                </h4>
                                <p class="text-sm text-gray-600 mb-3">
                                    Utilisez ces codes si vous perdez l'accès à votre application d'authentification
                                </p>
                                <button type="button" onclick="TwoFactorAuth.showBackupCodes()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm">
                                    <i class="fas fa-eye mr-2"></i>
                                    Voir les codes de sauvegarde
                                </button>
                            </div>
                        <?php endif; ?>

                        <div class="mt-4 text-sm text-gray-600">
                            <p><strong>Dernier changement de mot de passe :</strong>
                            <?= date('d/m/Y à H:i', strtotime($security['last_password_change'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Notifications -->
            <div id="notifications-section" class="section-content <?= $activeSection === 'notifications' ? 'active' : '' ?>">
                <div class="settings-card">
                    <div class="settings-header">
                        <h2 class="text-xl font-semibold text-custom-black">
                            <i class="fas fa-bell mr-2"></i>
                            Préférences de notifications
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">Choisissez comment vous souhaitez être notifié</p>
                    </div>

                    <form method="POST" action="<?= BASE_URL ?>/api/parametres/settings.php" id="notifications-form">
                        <input type="hidden" name="action" value="update_notifications">

                        <div class="space-y-4">
                            <!-- Notifications par email -->
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex-1">
                                    <div class="font-medium text-custom-black flex items-center">
                                        <i class="fas fa-envelope text-blue-600 mr-2"></i>
                                        Notifications par email
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        Recevoir des notifications importantes par email
                                        <?php if ($user['is_verified']): ?>
                                            <span class="ml-2 text-green-600">
                                                <i class="fas fa-check-circle"></i> Vérifié
                                            </span>
                                        <?php else: ?>
                                            <span class="ml-2 text-orange-600">
                                                <i class="fas fa-exclamation-triangle"></i> Non vérifié
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="email_notifications"
                                           <?= $preferences['email_notifications'] ? 'checked' : '' ?>
                                           onchange="this.form.submit()">
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <!-- Notifications push -->
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex-1">
                                    <div class="font-medium text-custom-black flex items-center">
                                        <i class="fas fa-desktop text-purple-600 mr-2"></i>
                                        Notifications push
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        Recevoir des notifications dans le navigateur
                                    </div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="push_notifications"
                                           <?= $preferences['push_notifications'] ? 'checked' : '' ?>
                                           onchange="this.form.submit()">
                                    <span class="slider"></span>
                                </label>
                            </div>

                        </div>
                    </form>

                    <!-- Informations supplémentaires -->
                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <h4 class="font-medium text-blue-900 mb-2 flex items-center">
                            <i class="fas fa-info-circle mr-2"></i>
                            Types de notifications
                        </h4>
                        <ul class="text-sm text-blue-800 space-y-1">
                            <li>• Nouvelles commandes et messages</li>
                            <li>• Mises à jour de vos services</li>
                            <li>• Alertes de sécurité importantes</li>
                            <li>• Rappels de paiements</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Section Confidentialité -->
            <div id="privacy-section" class="section-content <?= $activeSection === 'privacy' ? 'active' : '' ?>">
                <div class="settings-card">
                    <div class="settings-header">
                        <h2 class="text-xl font-semibold text-custom-black">
                            <i class="fas fa-user-shield mr-2"></i>
                            Paramètres de confidentialité
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">Contrôlez la visibilité de vos informations</p>
                    </div>

                    <form method="POST" action="<?= BASE_URL ?>/api/parametres/settings.php">
                        <input type="hidden" name="action" value="update_privacy">

                        <div class="space-y-6">
                            <div>
                                <label class="form-label">Visibilité du profil</label>
                                <select name="profile_visibility" class="form-input">
                                    <option value="public" <?= $privacy['profile_visibility'] === 'public' ? 'selected' : '' ?>>Public - Tout le monde peut voir votre profil</option>
                                    <option value="private" <?= $privacy['profile_visibility'] === 'private' ? 'selected' : '' ?>>Privé - Seul vous pouvez voir votre profil</option>
                                </select>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <div class="font-medium text-custom-black">Afficher l'email</div>
                                        <div class="text-sm text-gray-600">Permettre aux autres de voir votre adresse email</div>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="show_email"
                                               <?= $privacy['show_email'] ? 'checked' : '' ?>>
                                        <span class="slider"></span>
                                    </label>
                                </div>

                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <div class="font-medium text-custom-black">Afficher le téléphone</div>
                                        <div class="text-sm text-gray-600">Permettre aux autres de voir votre numéro de téléphone</div>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="show_phone"
                                               <?= $privacy['show_phone'] ? 'checked' : '' ?>>
                                        <span class="slider"></span>
                                    </label>
                                </div>

                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <div class="font-medium text-custom-black">Indexation par les moteurs de recherche</div>
                                        <div class="text-sm text-gray-600">Permettre aux moteurs de recherche d'indexer votre profil</div>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="allow_search_engines"
                                               <?= $privacy['allow_search_engines'] ? 'checked' : '' ?>>
                                        <span class="slider"></span>
                                    </label>
                                </div>

                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <div class="font-medium text-custom-black">Partage de données</div>
                                        <div class="text-sm text-gray-600">Autoriser le partage de données anonymisées pour améliorer nos services</div>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="data_sharing"
                                               <?= $privacy['data_sharing'] ? 'checked' : '' ?>>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="btn-primary px-6 py-2 rounded-lg">
                                    <i class="fas fa-save mr-2"></i>
                                    Sauvegarder
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Section Affichage -->
            <div id="display-section" class="section-content <?= $activeSection === 'display' ? 'active' : '' ?>">
                <div class="settings-card">
                    <div class="settings-header">
                        <h2 class="text-xl font-semibold text-custom-black">
                            <i class="fas fa-paint-brush mr-2"></i>
                            Préférences d'affichage
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">Personnalisez l'apparence de l'application</p>
                    </div>

                    <form method="POST" action="<?= BASE_URL ?>/api/parametres/settings.php">
                        <input type="hidden" name="action" value="update_display">

                        <div class="space-y-4">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-language mr-2"></i>
                                    Langue / Language / Idioma
                                </label>
                                <select name="language" class="form-input">
                                    <option value="fr" <?= ($preferences['language'] ?? 'fr') === 'fr' ? 'selected' : '' ?>>🇫🇷 Français</option>
                                    <option value="en" <?= ($preferences['language'] ?? 'fr') === 'en' ? 'selected' : '' ?>>🇬🇧 English</option>
                                    <option value="es" <?= ($preferences['language'] ?? 'fr') === 'es' ? 'selected' : '' ?>>🇪🇸 Español</option>
                                </select>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <div class="font-medium text-custom-black">Mode sombre</div>
                                    <div class="text-sm text-gray-600">Utiliser le thème sombre</div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="dark_mode"
                                           <?= $preferences['dark_mode'] ? 'checked' : '' ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Monnaie par défaut</label>
                                <select name="currency" class="form-input">
                                    <option value="EUR" <?= $preferences['currency'] === 'EUR' ? 'selected' : '' ?>>Euro (€)</option>
                                    <option value="USD" <?= $preferences['currency'] === 'USD' ? 'selected' : '' ?>>Dollar US ($)</option>
                                    <option value="GBP" <?= $preferences['currency'] === 'GBP' ? 'selected' : '' ?>>Livre Sterling (£)</option>
                                </select>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="btn-primary px-6 py-2 rounded-lg">
                                    <i class="fas fa-save mr-2"></i>
                                    Sauvegarder
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Section Intégrations -->
            <div id="integrations-section" class="section-content <?= $activeSection === 'integrations' ? 'active' : '' ?>">
                <div class="settings-card">
                    <div class="settings-header">
                        <h2 class="text-xl font-semibold text-custom-black">
                            <i class="fas fa-plug mr-2"></i>
                            Intégrations
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">Connectez vos services tiers</p>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fab fa-google text-2xl mr-4 text-red-500"></i>
                                <div>
                                    <div class="font-medium text-custom-black">Google</div>
                                    <div class="text-sm text-gray-600">Synchroniser avec Google Drive et Gmail</div>
                                </div>
                            </div>
                            <button class="btn-secondary px-4 py-2 rounded-lg text-sm">
                                Connecter
                            </button>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fab fa-microsoft text-2xl mr-4 text-blue-500"></i>
                                <div>
                                    <div class="font-medium text-custom-black">Microsoft</div>
                                    <div class="text-sm text-gray-600">Synchroniser avec OneDrive et Outlook</div>
                                </div>
                            </div>
                            <button class="btn-secondary px-4 py-2 rounded-lg text-sm">
                                Connecter
                            </button>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fab fa-slack text-2xl mr-4 text-purple-500"></i>
                                <div>
                                    <div class="font-medium text-custom-black">Slack</div>
                                    <div class="text-sm text-gray-600">Recevoir des notifications dans Slack</div>
                                </div>
                            </div>
                            <button class="btn-secondary px-4 py-2 rounded-lg text-sm">
                                Connecter
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Contact Support -->
            <div id="support-section" class="section-content <?= $activeSection === 'support' ? 'active' : '' ?>">
                <div class="settings-card">
                    <div class="settings-header">
                        <h2 class="text-xl font-semibold text-custom-black">
                            <i class="fas fa-headset mr-2"></i>
                            Contact Support
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">Besoin d'aide ? Contactez notre équipe</p>
                    </div>

                    <div class="space-y-6">
                        <!-- Informations de contact -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-envelope text-custom-red text-xl mr-3"></i>
                                    <h3 class="font-medium text-custom-black">Email</h3>
                                </div>
                                <a href="mailto:support@novatis.com" class="text-sm text-custom-red hover:underline">
                                    support@novatis.com
                                </a>
                                <p class="text-xs text-gray-600 mt-1">Réponse sous 24h</p>
                            </div>

                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-phone text-custom-red text-xl mr-3"></i>
                                    <h3 class="font-medium text-custom-black">Téléphone</h3>
                                </div>
                                <a href="tel:+33123456789" class="text-sm text-custom-red hover:underline">
                                    +33 1 23 45 67 89
                                </a>
                                <p class="text-xs text-gray-600 mt-1">Lun-Ven 9h-18h</p>
                            </div>
                        </div>

                        <!-- Formulaire de contact -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-medium text-custom-black mb-4">Envoyer un message</h3>
                            <form method="POST" action="<?= BASE_URL ?>/api/parametres/settings.php">
                                <input type="hidden" name="action" value="contact_support">

                                <div class="form-group">
                                    <label class="form-label">Sujet</label>
                                    <select name="subject" class="form-input" required>
                                        <option value="">Sélectionnez un sujet</option>
                                        <option value="technique">Problème technique</option>
                                        <option value="facturation">Question de facturation</option>
                                        <option value="fonctionnalite">Demande de fonctionnalité</option>
                                        <option value="autre">Autre</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Message</label>
                                    <textarea name="message" class="form-input" rows="6"
                                              placeholder="Décrivez votre problème ou votre question..." required></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Priorité</label>
                                    <select name="priority" class="form-input">
                                        <option value="low">Basse</option>
                                        <option value="normal" selected>Normale</option>
                                        <option value="high">Haute</option>
                                        <option value="urgent">Urgente</option>
                                    </select>
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" class="btn-primary px-6 py-2 rounded-lg">
                                        <i class="fas fa-paper-plane mr-2"></i>
                                        Envoyer
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- FAQ rapide -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-medium text-custom-black mb-4">Questions fréquentes</h3>
                            <div class="space-y-3">
                                <details class="bg-gray-50 rounded-lg p-4">
                                    <summary class="cursor-pointer font-medium text-custom-black">
                                        Comment réinitialiser mon mot de passe ?
                                    </summary>
                                    <p class="text-sm text-gray-600 mt-2">
                                        Rendez-vous dans la section "Sécurité" de vos paramètres pour modifier votre mot de passe.
                                    </p>
                                </details>

                                <details class="bg-gray-50 rounded-lg p-4">
                                    <summary class="cursor-pointer font-medium text-custom-black">
                                        Comment mettre à jour mes informations de facturation ?
                                    </summary>
                                    <p class="text-sm text-gray-600 mt-2">
                                        Contactez le support par email avec vos nouvelles informations de facturation.
                                    </p>
                                </details>

                                <details class="bg-gray-50 rounded-lg p-4">
                                    <summary class="cursor-pointer font-medium text-custom-black">
                                        Puis-je exporter mes données ?
                                    </summary>
                                    <p class="text-sm text-gray-600 mt-2">
                                        Oui, contactez le support pour demander une exportation complète de vos données.
                                    </p>
                                </details>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Zone de danger -->
            <div id="danger-section" class="section-content <?= $activeSection === 'danger' ? 'active' : '' ?>">
                <div class="settings-card">
                    <div class="settings-header">
                        <h2 class="text-xl font-semibold text-red-600">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Zone de danger
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">Actions irréversibles</p>
                    </div>

                    <div class="danger-zone">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-medium text-red-800">Supprimer le compte</h3>
                                <p class="text-sm text-red-600 mt-1">
                                    Cette action est irréversible. Toutes vos données seront perdues.
                                </p>
                            </div>
                            <button class="btn-danger px-4 py-2 rounded-lg" onclick="confirmDeleteAccount()">
                                <i class="fas fa-trash mr-2"></i>
                                Supprimer le compte
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        function confirmDeleteAccount() {
            if (confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.')) {
                if (confirm('Dernière confirmation : toutes vos données seront définitivement perdues. Continuer ?')) {
                    window.location.href = '<?= BASE_URL ?>/pages/delete-account.php';
                }
            }
        }

        // Validation du formulaire de changement de mot de passe
        document.addEventListener('DOMContentLoaded', function() {
            const passwordForms = document.querySelectorAll('form[action*="parametres/settings.php"]');
            passwordForms.forEach(form => {
                if (form.querySelector('input[name="action"][value="change_password"]')) {
                    form.addEventListener('submit', function(e) {
                        const newPassword = this.querySelector('input[name="new_password"]').value;
                        const confirmPassword = this.querySelector('input[name="confirm_password"]').value;

                        if (newPassword !== confirmPassword) {
                            e.preventDefault();
                            alert('Les mots de passe ne correspondent pas.');
                            return false;
                        }

                        if (newPassword.length < 8) {
                            e.preventDefault();
                            alert('Le mot de passe doit contenir au moins 8 caractères.');
                            return false;
                        }
                    });
                }
            });
        });

        // Affichage des messages de succès/erreur
        <?php if (isset($_SESSION['success_message'])): ?>
            alert('<?= addslashes($_SESSION['success_message']) ?>');
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            alert('<?= addslashes($_SESSION['error_message']) ?>');
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>


    </script>
</body>
</html>
