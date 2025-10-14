<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/EmailService.php';

$pdo = getDBConnection();
$error = null;
$success = false;
$message = '';

// Récupérer le token depuis l'URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error = 'Token de vérification manquant';
} else {
    try {
        // Vérifier si le token existe et est valide
        $stmt = $pdo->prepare("
            SELECT evt.*, u.id as user_id, u.firstname, u.lastname, u.email, u.is_verified
            FROM email_verification_tokens evt
            JOIN users u ON evt.user_id = u.id
            WHERE evt.token = ? AND evt.expires_at > NOW()
        ");
        $stmt->execute([$token]);
        $verification = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$verification) {
            $error = 'Token invalide ou expiré. Veuillez demander un nouveau lien de vérification.';
        } elseif ($verification['is_verified']) {
            $success = true;
            $message = 'Votre email est déjà vérifié. Vous pouvez vous connecter.';
        } else {
            // Marquer l'utilisateur comme vérifié
            $stmt = $pdo->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
            $stmt->execute([$verification['user_id']]);

            // Supprimer le token utilisé
            $stmt = $pdo->prepare("DELETE FROM email_verification_tokens WHERE token = ?");
            $stmt->execute([$token]);

            // Envoyer l'email de bienvenue
            EmailService::sendWelcomeEmail(
                $verification['email'],
                $verification['firstname'],
                $verification['lastname']
            );

            $success = true;
            $message = 'Votre email a été vérifié avec succès ! Vous pouvez maintenant vous connecter.';
        }
    } catch (Exception $e) {
        error_log("Erreur lors de la vérification d'email : " . $e->getMessage());
        $error = 'Une erreur est survenue lors de la vérification. Veuillez réessayer.';
    }
}

require_once __DIR__ . '/../../includes/Header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novatis | Vérification d'email</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/logos/Logo_Novatis.png">
    <link href="<?= BASE_URL ?>/assets/css/Variables.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--color-bg);
            min-height: 100vh;
            font-family: var(--font-base);
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .card {
            background: white;
            border-radius: 1rem;
            padding: 3rem 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .icon-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
        }

        .icon-circle.success {
            background: #10b981;
        }

        .icon-circle.error {
            background: #ef4444;
        }

        .icon-circle svg {
            width: 60px;
            height: 60px;
            color: white;
        }

        h1 {
            text-align: center;
            font-family: var(--font-heading);
            color: var(--color-black);
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .message {
            text-align: center;
            color: #666;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--color-red) 0%, var(--color-hover-2) 100%);
            color: white;
        }

        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(180, 18, 0, 0.3);
        }

        .btn-secondary {
            background: #f3f4f6;
            color: var(--color-black);
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .btn-container {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .features {
            background: #f9fafb;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .features h3 {
            font-size: 1.1rem;
            color: var(--color-black);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        .features h3 svg {
            width: 24px;
            height: 24px;
            margin-right: 0.5rem;
            color: var(--color-red);
        }

        .features ul {
            list-style: none;
        }

        .features li {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
            color: #4b5563;
        }

        .features li svg {
            width: 20px;
            height: 20px;
            margin-right: 0.75rem;
            flex-shrink: 0;
            color: #10b981;
        }

        .help-box {
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .help-box h3 {
            font-size: 1.1rem;
            color: var(--color-black);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        .help-box h3 svg {
            width: 24px;
            height: 24px;
            margin-right: 0.5rem;
            color: #f59e0b;
        }

        .help-box ul {
            list-style: none;
        }

        .help-box li {
            display: flex;
            align-items: flex-start;
            margin-bottom: 0.75rem;
            color: #4b5563;
        }

        .help-box li svg {
            width: 20px;
            height: 20px;
            margin-right: 0.75rem;
            margin-top: 0.15rem;
            flex-shrink: 0;
            color: #f59e0b;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }

        .animate-fadeIn {
            animation: fadeIn 0.6s ease-out;
        }

        .animate-scaleIn {
            animation: scaleIn 0.5s ease-out;
        }

        @media (max-width: 640px) {
            .card {
                padding: 2rem 1.5rem;
            }

            h1 {
                font-size: 1.5rem;
            }

            .btn-container {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container animate-fadeIn">
        <?php if ($success): ?>
            <!-- Message de succès -->
            <div class="card">
                <!-- Icône de succès -->
                <div class="icon-circle success animate-scaleIn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <h1>Email vérifié !</h1>
                <p class="message"><?= htmlspecialchars($message) ?></p>

                <!-- Bouton de connexion -->
                <div class="btn-container">
                    <a href="<?= BASE_URL ?>/Autentification" class="btn btn-primary">
                        Se connecter
                    </a>
                </div>

                <!-- Fonctionnalités disponibles -->
                <div class="features">
                    <h3>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                        </svg>
                        Vous pouvez maintenant :
                    </h3>
                    <ul>
                        <li>
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Créer et publier vos services freelance</span>
                        </li>
                        <li>
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Commander des services auprès d'autres étudiants</span>
                        </li>
                        <li>
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Gérer votre portfolio et vos projets</span>
                        </li>
                        <li>
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Échanger avec la communauté</span>
                        </li>
                    </ul>
                </div>
            </div>

        <?php else: ?>
            <!-- Message d'erreur -->
            <div class="card">
                <!-- Icône d'erreur -->
                <div class="icon-circle error animate-scaleIn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>

                <h1>Erreur de vérification</h1>
                <p class="message"><?= htmlspecialchars($error) ?></p>

                <!-- Boutons d'action -->
                <div class="btn-container">
                    <a href="<?= BASE_URL ?>/Autentification" class="btn btn-primary">
                        Retour à l'inscription
                    </a>
                    <a href="<?= BASE_URL ?>/Contact" class="btn btn-secondary">
                        Contacter le support
                    </a>
                </div>

                <!-- Aide -->
                <div class="help-box">
                    <h3>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Que faire ?
                    </h3>
                    <ul>
                        <li>
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Vérifiez votre boîte email et cliquez sur le bon lien</span>
                        </li>
                        <li>
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Les liens de vérification expirent après 24 heures</span>
                        </li>
                        <li>
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Contactez notre support si le problème persiste</span>
                        </li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
