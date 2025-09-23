<?php
require_once __DIR__ . '/../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

$user = $_SESSION['user'];
$prestataire_id = $_GET['prestataire'] ?? null;

// Récupérer les informations du prestataire
$prestataire = null;
if ($prestataire_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$prestataire_id]);
    $prestataire = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$prestataire) {
    header('Location: ' . BASE_URL . '/prestataires');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Novatis | Contacter <?= htmlspecialchars($prestataire['name']) ?></title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>

    <!-- Variables CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/variables.css">

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

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
    </style>
</head>

<body>
    <div class="min-h-screen py-8">
        <div class="max-w-4xl mx-auto px-4">
            <!-- Header -->
            <div class="bg-white rounded-lg p-6 mb-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold">Contacter un prestataire</h1>
                    <a href="<?= BASE_URL ?>/prestataires" class="btn-secondary px-4 py-2 rounded-lg">
                        ← Retour aux prestataires
                    </a>
                </div>
            </div>

            <!-- Info Prestataire -->
            <div class="bg-white rounded-lg p-6 mb-6 shadow-lg">
                <div class="flex items-center space-x-4">
                    <img src="<?= htmlspecialchars($prestataire['avatar'] ?? 'default.png') ?>"
                         alt="Avatar"
                         class="w-16 h-16 rounded-full object-cover">
                    <div>
                        <h2 class="text-xl font-bold"><?= htmlspecialchars($prestataire['name']) ?></h2>
                        <p class="text-gray-600"><?= htmlspecialchars($prestataire['email']) ?></p>
                        <?php if ($prestataire['rating']): ?>
                            <p class="text-sm">Note: <?= $prestataire['rating'] ?>/5 ⭐</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Message d'information -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                <h3 class="font-bold text-blue-800 mb-2">Comment contacter ce prestataire ?</h3>
                <p class="text-blue-700 mb-4">
                    Pour contacter ce prestataire, vous devez d'abord commander un de ses services.
                    Une fois la commande passée, vous pourrez échanger via le système de messagerie intégré.
                </p>
                <a href="<?= BASE_URL ?>/prestataires" class="btn-primary px-4 py-2 rounded-lg inline-block">
                    Voir ses services
                </a>
            </div>

            <!-- Alternative: Redirection vers Dashboard -->
            <div class="bg-white rounded-lg p-6 shadow-lg">
                <h3 class="font-bold mb-4">Accès rapide</h3>
                <div class="flex space-x-4">
                    <a href="<?= BASE_URL ?>/dashboard" class="btn-secondary px-4 py-2 rounded-lg">
                        Mon Dashboard
                    </a>
                    <a href="<?= BASE_URL ?>/services" class="btn-secondary px-4 py-2 rounded-lg">
                        Parcourir les services
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>