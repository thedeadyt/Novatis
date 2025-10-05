<?php
require_once __DIR__ . '/../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/pages/Autentification.php');
    exit;
}

$user = $_SESSION['user'];
$prestataire_id = $_GET['prestataire'] ?? null;

// Récupérer les informations du prestataire et ses services
$prestataire = null;
$services = [];
if ($prestataire_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$prestataire_id]);
    $prestataire = $stmt->fetch(PDO::FETCH_ASSOC);

    // Calculer le nom complet
    if ($prestataire) {
        $prestataire['name'] = trim($prestataire['firstname'] . ' ' . $prestataire['lastname']);
        if (empty($prestataire['name'])) {
            $prestataire['name'] = $prestataire['pseudo'];
        }

        // Récupérer les services du prestataire
        $stmt = $pdo->prepare("SELECT * FROM services WHERE user_id = ? AND status = 'active' ORDER BY price ASC");
        $stmt->execute([$prestataire_id]);
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!$prestataire) {
    header('Location: ' . BASE_URL . '/pages/Prestataires.php');
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

    <!-- React & ReactDOM -->
    <script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

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
                    <a href="<?= BASE_URL ?>/pages/Prestataires.php" class="btn-secondary px-4 py-2 rounded-lg">
                        ← Retour aux prestataires
                    </a>
                </div>
            </div>

            <!-- Info Prestataire -->
            <div class="bg-white rounded-lg p-6 mb-6 shadow-lg">
                <div class="flex items-center space-x-4">
                    <div id="prestataire-avatar" data-avatar="<?= htmlspecialchars($prestataire['avatar'] ?? '') ?>" data-name="<?= htmlspecialchars($prestataire['name']) ?>" class="w-16 h-16"></div>
                    <div>
                        <h2 class="text-xl font-bold"><?= htmlspecialchars($prestataire['name']) ?></h2>
                        <p class="text-gray-600"><?= htmlspecialchars($prestataire['email']) ?></p>
                        <?php if ($prestataire['rating']): ?>
                            <p class="text-sm">Note: <?= $prestataire['rating'] ?>/5 ⭐</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Services disponibles -->
            <div class="bg-white rounded-lg p-6 mb-6 shadow-lg">
                <h3 class="font-bold text-xl mb-4">Services disponibles</h3>

                <?php if (empty($services)): ?>
                    <p class="text-gray-600 text-center py-8">Ce prestataire n'a pas encore de services actifs.</p>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php foreach ($services as $service): ?>
                            <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                                <?php if ($service['image']): ?>
                                    <img src="<?= htmlspecialchars($service['image']) ?>"
                                         alt="<?= htmlspecialchars($service['title']) ?>"
                                         class="w-full h-32 object-cover rounded-lg mb-3">
                                <?php endif; ?>

                                <h4 class="font-bold text-lg mb-2"><?= htmlspecialchars($service['title']) ?></h4>
                                <p class="text-gray-600 text-sm mb-3"><?= htmlspecialchars($service['description']) ?></p>

                                <div class="flex items-center justify-between mb-4">
                                    <span class="font-bold text-xl text-red-600"><?= number_format($service['price'], 2, ',', ' ') ?> €</span>
                                    <span class="text-sm text-gray-500">Livraison: <?= $service['delivery_days'] ?> jour<?= $service['delivery_days'] > 1 ? 's' : '' ?></span>
                                </div>

                                <form action="<?= BASE_URL ?>/api/orders/orders.php" method="POST" class="order-form">
                                    <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
                                    <input type="hidden" name="seller_id" value="<?= $prestataire['id'] ?>">
                                    <input type="hidden" name="buyer_id" value="<?= $user['id'] ?>">
                                    <input type="hidden" name="price" value="<?= $service['price'] ?>">

                                    <div class="mb-3">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Détails de votre demande (optionnel)
                                        </label>
                                        <textarea name="description"
                                                  class="w-full p-2 border border-gray-300 rounded-lg text-sm"
                                                  rows="2"
                                                  placeholder="Précisez vos besoins particuliers..."></textarea>
                                    </div>

                                    <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-3">
                                        <label class="block text-sm font-bold text-blue-800 mb-2">
                                            💬 Message pour <?= htmlspecialchars($prestataire['name']) ?>
                                        </label>
                                        <textarea name="message"
                                                  class="w-full p-3 border border-blue-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                  rows="3"
                                                  placeholder="Bonjour <?= htmlspecialchars($prestataire['name']) ?>, je suis intéressé par votre service. Voici mes besoins spécifiques..."></textarea>
                                        <p class="text-xs text-blue-600 mt-2 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Ce message sera envoyé directement au prestataire dès que votre commande sera confirmée.
                                        </p>
                                    </div>

                                    <button type="submit" class="btn-primary w-full px-4 py-2 rounded-lg">
                                        Commander ce service
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Info sur la messagerie -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
                <h3 class="font-bold text-green-800 mb-2">💬 Après votre commande</h3>
                <p class="text-green-700">
                    Une fois votre commande passée, vous pourrez directement échanger avec
                    <strong><?= htmlspecialchars($prestataire['name']) ?></strong> via le système de messagerie
                    intégré dans votre dashboard.
                </p>
            </div>

            <!-- Navigation -->
            <div class="bg-white rounded-lg p-6 shadow-lg">
                <h3 class="font-bold mb-4">Navigation</h3>
                <div class="flex flex-wrap gap-3">
                    <a href="<?= BASE_URL ?>/pages/Dashboard.php" class="btn-secondary px-4 py-2 rounded-lg">
                        Mon Dashboard
                    </a>
                    <a href="<?= BASE_URL ?>/pages/Prestataires.php" class="btn-secondary px-4 py-2 rounded-lg">
                        Autres prestataires
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Gérer la soumission des commandes
        document.querySelectorAll('.order-form').forEach(form => {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const button = this.querySelector('button[type="submit"]');
                const originalText = button.textContent;
                button.textContent = 'Commande en cours...';
                button.disabled = true;

                try {
                    const formData = new FormData(this);
                    const response = await fetch(this.action, {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        alert('Commande créée avec succès ! Vous allez être redirigé vers votre dashboard.');
                        window.location.href = result.redirect || '<?= BASE_URL ?>/pages/Dashboard.php';
                    } else {
                        alert('Erreur: ' + result.error);
                        button.textContent = originalText;
                        button.disabled = false;
                    }
                } catch (error) {
                    alert('Erreur lors de la commande: ' + error.message);
                    button.textContent = originalText;
                    button.disabled = false;
                }
            });
        });
    </script>

    <!-- Script pour l'avatar -->
    <script type="text/babel">
        // SVG Avatar anonyme
        const AnonymousAvatar = ({ className = "", size = 64 }) => (
            React.createElement('div', {
                className: `${className} flex items-center justify-center bg-gray-300 rounded-full`,
                style: { width: size, height: size }
            },
                React.createElement('svg', {
                    width: size * 0.6,
                    height: size * 0.6,
                    viewBox: "0 0 24 24",
                    fill: "none",
                    xmlns: "http://www.w3.org/2000/svg"
                },
                    React.createElement('path', {
                        d: "M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z",
                        fill: "#9CA3AF"
                    })
                )
            )
        );

        // Initialiser l'avatar
        document.addEventListener('DOMContentLoaded', function() {
            const avatarContainer = document.getElementById('prestataire-avatar');
            if (avatarContainer) {
                const avatarUrl = avatarContainer.dataset.avatar;
                const userName = avatarContainer.dataset.name;

                if (avatarUrl && avatarUrl.trim() !== '') {
                    // Créer l'image avec fallback
                    const img = React.createElement('img', {
                        src: avatarUrl,
                        alt: `Avatar de ${userName}`,
                        className: "w-16 h-16 rounded-full object-cover",
                        onError: (e) => {
                            // Si l'image échoue, remplacer par le SVG
                            const svgElement = React.createElement(AnonymousAvatar, { size: 64 });
                            ReactDOM.render(svgElement, avatarContainer);
                        }
                    });
                    ReactDOM.render(img, avatarContainer);
                } else {
                    // Pas d'avatar, utiliser directement le SVG
                    const svgElement = React.createElement(AnonymousAvatar, { size: 64 });
                    ReactDOM.render(svgElement, avatarContainer);
                }
            }
        });
    </script>
</body>
</html>