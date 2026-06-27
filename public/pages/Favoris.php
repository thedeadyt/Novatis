<?php
require_once __DIR__ . '/../../config/Config.php';
require_once __DIR__ . '/../../config/database.php';

// Vérifie si l'utilisateur est connecté
$user = getCurrentUser();
if (!$user) {
    header('Location: ' . BASE_URL . '/index.php?page=connexion');
    exit;
}

$pdo = getDBConnection();

// Récupérer tous les services favoris de l'utilisateur
$sql = "
    SELECT
        s.id AS service_id,
        s.title AS service_title,
        s.description AS service_description,
        s.price,
        s.delivery_days,
        s.image AS service_image,
        u.id AS user_id,
        u.firstname,
        u.lastname,
        u.pseudo,
        u.avatar,
        u.bio,
        u.rating,
        c.id AS category_id,
        c.name AS category_name,
        f.created_at as favorited_at
    FROM favorites f
    JOIN services s ON f.service_id = s.id
    JOIN users u ON s.user_id = u.id
    LEFT JOIN categories c ON s.category_id = c.id
    WHERE f.user_id = ? AND s.status = 'active'
    ORDER BY f.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user['id']]);
$favoriteServices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement des services pour affichage
foreach ($favoriteServices as &$service) {
    $service['provider_name'] = trim($service['firstname'] . ' ' . $service['lastname']);
    if (empty($service['provider_name'])) {
        $service['provider_name'] = $service['pseudo'];
    }
}
unset($service);

$currentPage = 'Favoris.php';
?>
<!DOCTYPE html>
<html lang="fr" data-user-lang="fr">
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Novatis | Mes Services Favoris</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/logos/Logo_Novatis.png">
    <meta name='viewport' content='width=device-width, initial-scale=1'>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/Variables.css">
    <link rel='stylesheet' type='text/css' media='screen' href='<?= BASE_URL ?>/assets/css/prestataires.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='<?= BASE_URL ?>/assets/css/Footer.css'>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <script src="<?= BASE_URL ?>/assets/js/theme.js"></script>

    <script crossorigin src="https://unpkg.com/react@18/umd/react.development.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

    <?php include __DIR__ . '/../../includes/i18n-head.php'; ?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .favorite-btn {
            transition: transform 0.2s, background-color 0.2s;
        }
        .favorite-btn:hover {
            transform: scale(1.1);
            background-color: #f3f4f6 !important;
        }
        .favorite-btn:active {
            transform: scale(0.95);
        }
        .service-card {
            transition: all 0.3s ease;
        }
        .service-card.hidden {
            display: none !important;
        }
    </style>
</head>

<body class="flex flex-col min-h-screen bg-gray-50">
    <?php include __DIR__ . '/../../includes/Header.php'; ?>

    <main class="flex-1">
        <div class="content" id="content">
            <!-- Header de la page -->
            <div class="bg-white border-b mt-16">
                <div class="max-w-7xl mx-auto px-4 py-8">
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">Mes Services Favoris</h1>
                    <p class="text-lg text-gray-600">Services sauvegardés pour plus tard</p>
                </div>
            </div>

            <!-- Zone principale -->
            <div class="max-w-7xl mx-auto px-4 py-6">
                <!-- Barre du haut avec compteur -->
                <div class="bg-white rounded-lg shadow-lg p-4 mb-6">
                    <div class="flex items-center gap-4">
                        <div class="text-sm text-gray-600">
                            <span id="resultsCount" class="font-semibold text-gray-900"><?= count($favoriteServices) ?></span>
                            <span>service(s) sauvegardé(s)</span>
                        </div>
                    </div>
                </div>

                <!-- Grid de cartes services -->
                <div id="services-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    <?php foreach ($favoriteServices as $service): ?>
                        <?php $rating = $service['rating'] ?? 0; ?>

                        <!-- Carte Service -->
                        <div class="service-card bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden relative">

                            <!-- Bouton retirer des favoris -->
                            <button class="favorite-btn absolute top-3 right-3 z-10 w-10 h-10 bg-red-500 rounded-full shadow-md flex items-center justify-center hover:bg-red-600 transition-colors"
                                    data-service-id="<?= $service['service_id'] ?>"
                                    title="Retirer des favoris">
                                <i class="fas fa-heart text-white text-xl"></i>
                            </button>

                            <!-- Image du service ou avatar -->
                            <?php if (!empty($service['service_image'])): ?>
                            <div class="h-48 bg-cover bg-center" style="background-image: url('<?= htmlspecialchars($service['service_image']) ?>')"></div>
                            <?php else: ?>
                            <!-- Header avec avatar du prestataire -->
                            <div class="prestataire-header bg-gray-100 p-6 text-center">
                                <div class="avatar-container inline-block mb-3" data-avatar="<?= htmlspecialchars($service['avatar'] ?? '') ?>" data-name="<?= htmlspecialchars($service['provider_name']) ?>"></div>
                                <h3 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($service['provider_name']) ?></h3>

                                <!-- Affichage de la notation -->
                                <?php if ($rating > 0): ?>
                                <div class="flex items-center justify-center gap-1 mt-2">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                        <svg class="w-4 h-4 <?= $i < round($rating) ? 'text-yellow-400 fill-current' : 'text-gray-300 fill-current' ?>" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    <?php endfor; ?>
                                    <span class="text-sm text-gray-600 ml-1">(<?= number_format($rating, 1) ?>)</span>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>

                            <!-- Informations du service -->
                            <div class="p-6">
                                <!-- Titre du service -->
                                <h2 class="text-xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($service['service_title']) ?></h2>

                                <!-- Nom du prestataire -->
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                    Par <a href="<?= BASE_URL ?>/profil?id=<?= $service['user_id'] ?>" class="text-red-600 dark:text-red-400 hover:underline"><?= htmlspecialchars($service['provider_name']) ?></a>
                                </p>

                                <!-- Description -->
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-3"><?= htmlspecialchars($service['service_description'] ?? 'Aucune description disponible.') ?></p>

                                <!-- Catégorie -->
                                <?php if (!empty($service['category_name'])): ?>
                                <div class="mb-4">
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                                        <?= htmlspecialchars($service['category_name']) ?>
                                    </span>
                                </div>
                                <?php endif; ?>

                                <!-- Prix et délai -->
                                <div class="mb-4 pb-4 border-b space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 text-sm">Prix:</span>
                                        <span class="text-2xl font-bold text-gray-900"><?= number_format($service['price'], 0, ',', ' ') ?> €</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 text-sm">Délai:</span>
                                        <span class="text-sm font-semibold text-gray-700"><?= $service['delivery_days'] ?> jour(s)</span>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="grid grid-cols-2 gap-2">
                                    <a href="<?= BASE_URL ?>/profil?id=<?= $service['user_id'] ?>"
                                       class="flex items-center justify-center gap-2 px-4 py-2 bg-red-600 dark:bg-red-700 text-white rounded-lg hover:bg-red-700 dark:hover:bg-red-600 transition-colors text-sm font-medium">
                                       <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                       </svg>
                                       <span>Profil</span>
                                    </a>
                                    <a href="<?= BASE_URL ?>/contact?prestataire=<?= $service['user_id'] ?>"
                                       class="flex items-center justify-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                                       <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                       </svg>
                                       <span>Commander</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Message si aucun service favoris -->
                <div id="no-results" class="<?= empty($favoriteServices) ? '' : 'hidden' ?> text-center py-16 bg-white rounded-lg shadow-lg">
                    <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="mt-4 text-xl font-medium text-gray-900 dark:text-white">Aucun service favori</h3>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">Commencez à ajouter vos services préférés !</p>
                    <a href="<?= BASE_URL ?>/pages/Prestataires" class="mt-4 inline-block px-6 py-2 bg-red-600 dark:bg-red-700 text-white rounded-lg hover:bg-red-700 dark:hover:bg-red-600 transition-colors">
                        Découvrir les services
                    </a>
                </div>

            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../../includes/Footer.php'; ?>

    <!-- ===== SCRIPT POUR LES AVATARS ===== -->
    <script type="text/babel">
        const AnonymousAvatar = ({ className = "", size = 80 }) => (
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

        document.addEventListener('DOMContentLoaded', function() {
            const avatarContainers = document.querySelectorAll('.avatar-container');

            avatarContainers.forEach(container => {
                const avatarUrl = container.dataset.avatar;
                const userName = container.dataset.name;

                if (avatarUrl && avatarUrl.trim() !== '') {
                    const img = React.createElement('img', {
                        src: avatarUrl,
                        alt: `Avatar de ${userName}`,
                        className: "w-20 h-20 rounded-full border-4 border-white shadow-lg object-cover",
                        onError: (e) => {
                            const svgElement = React.createElement(AnonymousAvatar, { size: 80 });
                            ReactDOM.render(svgElement, container);
                        }
                    });
                    ReactDOM.render(img, container);
                } else {
                    const svgElement = React.createElement(AnonymousAvatar, { size: 80 });
                    ReactDOM.render(svgElement, container);
                }
            });
        });
    </script>

    <!-- ===== SCRIPT DE GESTION DES FAVORIS ===== -->
    <script>
        let allCards = [];

        document.addEventListener('DOMContentLoaded', function() {
            allCards = Array.from(document.querySelectorAll('.service-card'));
            initFavoriteButtons();
        });

        async function toggleFavorite(serviceId, button) {
            try {
                const formData = new FormData();
                formData.append('action', 'toggle');
                formData.append('service_id', serviceId);

                const response = await fetch('<?= BASE_URL ?>/api/favorites.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                if (data.success) {
                    // Retirer la carte si service retiré des favoris
                    if (!data.is_favorite) {
                        const card = button.closest('.service-card');
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            card.remove();
                            allCards = allCards.filter(c => c !== card);
                            updateResultsCount();

                            // Afficher le message "aucun favori" si nécessaire
                            if (allCards.length === 0) {
                                document.getElementById('no-results').classList.remove('hidden');
                                document.getElementById('services-grid').classList.add('hidden');
                            }
                        }, 300);
                    }

                    button.classList.add('animate-bounce');
                    setTimeout(() => button.classList.remove('animate-bounce'), 500);
                }
            } catch (error) {
                console.error('Erreur lors de la gestion du favori:', error);
            }
        }

        function initFavoriteButtons() {
            document.querySelectorAll('.favorite-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const serviceId = parseInt(btn.dataset.serviceId);
                    toggleFavorite(serviceId, btn);
                });
            });
        }

        function updateResultsCount() {
            const visibleCount = allCards.filter(c => !c.classList.contains('hidden')).length;
            document.getElementById('resultsCount').textContent = visibleCount;
        }
    </script>
</body>
</html>
