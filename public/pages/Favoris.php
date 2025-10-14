<?php
require_once __DIR__ . '/../../config/config.php';

// Vérifie si l'utilisateur est connecté
isUserLoggedIn(true);

$user = getCurrentUser();
$pdo = getDBConnection();

// Créer la table favorites si elle n'existe pas
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS favorites (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            favorited_user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_favorite (user_id, favorited_user_id),
            INDEX idx_user_id (user_id),
            INDEX idx_favorited_user_id (favorited_user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} catch (PDOException $e) {
    // Table existe déjà
}

// Récupérer les favoris de l'utilisateur avec les informations des prestataires
try {
    $stmt = $pdo->prepare("
        SELECT
            u.id,
            u.firstname,
            u.lastname,
            u.pseudo,
            u.email,
            u.phone,
            u.avatar,
            u.bio,
            u.location,
            u.rating,
            u.created_at,
            f.created_at as favorited_at,
            (SELECT COUNT(*) FROM services WHERE user_id = u.id AND status = 'active') as service_count,
            (SELECT COUNT(*) FROM orders o
             JOIN services s ON s.id = o.service_id
             WHERE s.user_id = u.id AND o.status = 'completed') as completed_orders
        FROM favorites f
        JOIN users u ON f.favorited_user_id = u.id
        WHERE f.user_id = ?
        ORDER BY f.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $favorites = [];
}

$currentPage = 'Favoris.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Novatis | Mes Favoris</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/logos/Logo_Novatis.png">
    <meta name='viewport' content='width=device-width, initial-scale=1'>

    <!-- Variables CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/variables.css">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- React -->
    <script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

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

        .favorite-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .favorite-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .btn-remove {
            transition: all 0.3s ease;
        }

        .btn-remove:hover {
            background-color: #dc2626;
            transform: scale(1.05);
        }
    </style>
</head>

<body class="bg-custom-bg min-h-screen">
    <!-- Header -->
    <?php include __DIR__ . '/../../includes/Header.php'; ?>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8 mt-20">
        <div class="max-w-7xl mx-auto">
            <!-- En-tête -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-custom-black mb-2">
                    <i class="fas fa-heart text-red-500 mr-3"></i>
                    Mes Favoris
                </h1>
                <p class="text-gray-600">
                    <?= count($favorites) ?> prestataire<?= count($favorites) > 1 ? 's' : '' ?> favori<?= count($favorites) > 1 ? 's' : '' ?>
                </p>
            </div>

            <?php if (empty($favorites)): ?>
                <!-- Message si aucun favori -->
                <div class="bg-white rounded-lg shadow-md p-12 text-center">
                    <i class="fas fa-heart-broken text-gray-300 text-6xl mb-4"></i>
                    <h2 class="text-2xl font-semibold text-gray-700 mb-2">Aucun favori pour le moment</h2>
                    <p class="text-gray-500 mb-6">
                        Explorez nos prestataires et ajoutez-les à vos favoris pour les retrouver facilement !
                    </p>
                    <a href="<?= BASE_URL ?>/Prestataires"
                       class="inline-block bg-custom-red text-white px-6 py-3 rounded-lg hover:bg-hover-2 transition">
                        <i class="fas fa-search mr-2"></i>
                        Découvrir les prestataires
                    </a>
                </div>
            <?php else: ?>
                <!-- Grille des favoris -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($favorites as $favorite): ?>
                        <div class="favorite-card bg-white rounded-lg shadow-md overflow-hidden">
                            <!-- Image de profil -->
                            <div class="relative h-48 bg-gradient-to-br from-custom-red to-accent-2">
                                <?php if ($favorite['avatar']): ?>
                                    <img src="<?= htmlspecialchars($favorite['avatar']) ?>"
                                         alt="Avatar"
                                         class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i class="fas fa-user text-white text-6xl opacity-50"></i>
                                    </div>
                                <?php endif; ?>

                                <!-- Bouton retirer des favoris -->
                                <button onclick="removeFavorite(<?= $favorite['id'] ?>)"
                                        class="btn-remove absolute top-3 right-3 bg-red-500 text-white w-10 h-10 rounded-full shadow-lg flex items-center justify-center">
                                    <i class="fas fa-heart-broken"></i>
                                </button>
                            </div>

                            <!-- Informations -->
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-xl font-bold text-custom-black">
                                        <?= htmlspecialchars($favorite['pseudo']) ?>
                                    </h3>
                                    <div class="flex items-center">
                                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                                        <span class="font-semibold"><?= number_format($favorite['rating'], 1) ?></span>
                                    </div>
                                </div>

                                <p class="text-sm text-gray-600 mb-4">
                                    <?= htmlspecialchars($favorite['firstname'] . ' ' . $favorite['lastname']) ?>
                                </p>

                                <?php if ($favorite['bio']): ?>
                                    <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                                        <?= htmlspecialchars($favorite['bio']) ?>
                                    </p>
                                <?php endif; ?>

                                <?php if ($favorite['location']): ?>
                                    <div class="flex items-center text-gray-500 text-sm mb-2">
                                        <i class="fas fa-map-marker-alt mr-2"></i>
                                        <span><?= htmlspecialchars($favorite['location']) ?></span>
                                    </div>
                                <?php endif; ?>

                                <div class="flex items-center text-gray-500 text-sm mb-4">
                                    <i class="fas fa-briefcase mr-2"></i>
                                    <span><?= $favorite['service_count'] ?> service<?= $favorite['service_count'] > 1 ? 's' : '' ?></span>
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <span><?= $favorite['completed_orders'] ?> commande<?= $favorite['completed_orders'] > 1 ? 's' : '' ?></span>
                                </div>

                                <div class="text-xs text-gray-400 mb-4">
                                    <i class="fas fa-heart mr-1"></i>
                                    Ajouté le <?= date('d/m/Y', strtotime($favorite['favorited_at'])) ?>
                                </div>

                                <!-- Boutons d'action -->
                                <div class="flex gap-2">
                                    <a href="<?= BASE_URL ?>/profil?id=<?= $favorite['id'] ?>"
                                       class="flex-1 bg-custom-red text-white py-2 px-4 rounded-lg text-center hover:bg-hover-2 transition text-sm">
                                        <i class="fas fa-eye mr-2"></i>
                                        Voir le profil
                                    </a>
                                    <a href="<?= BASE_URL ?>/Contact?prestataire=<?= $favorite['id'] ?>"
                                       class="flex-1 bg-gray-200 text-gray-700 py-2 px-4 rounded-lg text-center hover:bg-gray-300 transition text-sm">
                                        <i class="fas fa-envelope mr-2"></i>
                                        Contacter
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . '/../../includes/Footer.php'; ?>

    <script>
        function removeFavorite(userId) {
            if (!confirm('Êtes-vous sûr de vouloir retirer ce prestataire de vos favoris ?')) {
                return;
            }

            fetch('<?= BASE_URL ?>/api/favorites/favorites.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'remove',
                    favorited_user_id: userId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erreur : ' + (data.message || 'Impossible de retirer des favoris'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erreur lors de la suppression');
            });
        }
    </script>
</body>
</html>
