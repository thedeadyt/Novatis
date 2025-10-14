<?php
require_once __DIR__ . '/../../config/config.php';

$pdo = getDBConnection();

// Requête pour récupérer les prestataires avec leurs services groupés
$sql = "
    SELECT
        u.id AS user_id,
        u.firstname,
        u.lastname,
        u.pseudo,
        u.avatar,
        u.bio,
        u.rating,
        GROUP_CONCAT(
            CONCAT(s.title, '|', s.price, '|', s.delivery_days)
            SEPARATOR ';;'
        ) AS services
    FROM users u
    JOIN services s ON u.id = s.user_id
    WHERE s.status = 'active'
    GROUP BY u.id, u.firstname, u.lastname, u.pseudo, u.avatar, u.bio, u.rating
    ORDER BY u.pseudo ASC
";

$stmt = $pdo->query($sql);
$prestataires = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement des services pour chaque prestataire
foreach ($prestataires as &$prestataire) {
    // Calculer le nom complet
    $prestataire['name'] = trim($prestataire['firstname'] . ' ' . $prestataire['lastname']);
    if (empty($prestataire['name'])) {
        $prestataire['name'] = $prestataire['pseudo'];
    }

    $services = [];
    if (!empty($prestataire['services'])) {
        $servicesList = explode(';;', $prestataire['services']);
        foreach ($servicesList as $service) {
            $parts = explode('|', $service);
            if (count($parts) === 3) {
                $services[] = [
                    'title' => $parts[0],
                    'price' => floatval($parts[1]),
                    'delivery_days' => intval($parts[2])
                ];
            }
        }
    }
    $prestataire['services_list'] = $services;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Prestataires</title>
    <link rel='stylesheet' type='text/css' media='screen' href='<?= BASE_URL ?>/assets/css/Variables.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='<?= BASE_URL ?>/assets/css/prestataires.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='<?= BASE_URL ?>/assets/css/Footer.css'>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <script src="<?= BASE_URL ?>/assets/js/theme.js"></script>

    <!-- React & ReactDOM CDN -->
    <script script crossorigin src="https://unpkg.com/react@18/umd/react.development.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>

    <!-- Babel CDN pour JSX -->
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
</head>
<body>
    <?php include __DIR__ . '/../../includes/Header.php';?>
    <div class="content" id="content">
<!-- Container Prestataires -->
<div class="Prestataires max-w-7xl mx-auto mt-32 px-4">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold mb-4">Les Prestataires</h1>
        <p class="text-lg text-gray-600">Découvrez nos experts qualifiés et leurs services</p>
    </div>

    <!-- Filtres et recherche -->
    <div class="filters-container bg-white rounded-xl shadow-lg p-6 mb-8">
        <div class="flex flex-wrap gap-4 items-center justify-between">
            <!-- Recherche -->
            <div class="relative flex-1 min-w-64">
                <input type="text"
                       id="searchInput"
                       placeholder="Rechercher un prestataire..."
                       class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <svg class="absolute left-3 top-3.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>

            <!-- Filtres par catégorie -->
            <div class="flex flex-wrap gap-2 items-center">
                <label class="text-sm font-medium text-gray-700">Catégorie:</label>
                <select id="categoryFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Toutes les catégories</option>
                    <?php
                    // Récupérer les catégories uniques des services
                    $categories = [];
                    foreach ($prestataires as $prestataire) {
                        foreach ($prestataire['services_list'] as $service) {
                            $categories[] = $service['title'];
                        }
                    }
                    $categories = array_unique($categories);
                    sort($categories);
                    foreach ($categories as $category):
                    ?>
                        <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Tri par prix -->
            <div class="flex flex-wrap gap-2 items-center">
                <label class="text-sm font-medium text-gray-700">Trier par:</label>
                <select id="sortFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="name">Nom (A-Z)</option>
                    <option value="name-desc">Nom (Z-A)</option>
                    <option value="price-asc">Prix croissant</option>
                    <option value="price-desc">Prix décroissant</option>
                    <option value="delivery-asc">Délai croissant</option>
                    <option value="delivery-desc">Délai décroissant</option>
                </select>
            </div>

            <!-- Bouton reset -->
            <button id="resetFilters" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Réinitialiser
            </button>
        </div>

        <!-- Compteur de résultats -->
        <div class="mt-4 text-sm text-gray-600">
            <span id="resultsCount"><?= count($prestataires) ?></span> prestataire(s) trouvé(s)
        </div>
    </div>

    <div class="prestataires" id="prestataires-grid">
        <?php foreach ($prestataires as $prestataire): ?>
            <!-- Carte Prestataire -->
            <div class="prestataire-card"
                 data-name="<?= htmlspecialchars(strtolower($prestataire['name'])) ?>"
                 data-services="<?= htmlspecialchars(strtolower(implode('|', array_column($prestataire['services_list'], 'title')))) ?>"
                 data-min-price="<?= min(array_column($prestataire['services_list'], 'price')) ?>"
                 data-min-delivery="<?= min(array_column($prestataire['services_list'], 'delivery_days')) ?>">
                <div class="prestataire-header">
                    <div class="avatar-container" data-avatar="<?= htmlspecialchars($prestataire['avatar'] ?? '') ?>" data-name="<?= htmlspecialchars($prestataire['name']) ?>"></div>
                </div>

                <div class="prestataire-info">
                    <h2><?= htmlspecialchars($prestataire['name']) ?></h2>
                    <p class="bio"><?= htmlspecialchars($prestataire['bio'] ?? 'Aucune description disponible.') ?></p>
                </div>

                <div class="services-section">
                    <h3>Services proposés</h3>
                    <ul class="services-list">
                        <?php foreach ($prestataire['services_list'] as $service): ?>
                            <li class="service-item">
                                <div class="service-info">
                                    <span class="service-title"><?= htmlspecialchars($service['title']) ?></span>
                                    <div class="service-details">
                                        <span class="service-price"><?= number_format($service['price'], 2, ',', ' ') ?> €</span>
                                        <span class="service-delivery"><?= $service['delivery_days'] ?> jour<?= $service['delivery_days'] > 1 ? 's' : '' ?></span>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="card-actions">
                    <a href="<?= BASE_URL ?>/profil?id=<?= $prestataire['user_id'] ?>"
                       class="btn-profile">
                       <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                       </svg>
                       Voir profil
                    </a>
                    <a href="<?= BASE_URL ?>/contact?prestataire=<?= $prestataire['user_id'] ?>"
                       class="btn-contact">
                       <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                       </svg>
                       Contacter
                    </a>
                    <button class="btn-favorite" onclick="toggleFavorite(<?= $prestataire['user_id'] ?>)">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Message si aucun prestataire trouvé -->
    <div id="no-results" class="text-center py-12 hidden">
        <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.516-.798-6.235-2.145a9.974 9.974 0 01-1.761-2.145A9.974 9.974 0 014.004 8.854a2.014 2.014 0 00.562-.84c.08-.151.185-.281.315-.389a.994.994 0 011.319-.042.996.996 0 01.042 1.639zM15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">Aucun prestataire trouvé</h3>
        <p class="mt-2 text-gray-500">Essayez de modifier votre recherche.</p>
    </div>
</div>

    </div>
    <?php include __DIR__ . '/../../includes/Footer.php';?>

    <!-- Script pour les avatars -->
    <script type="text/babel">
        // SVG Avatar anonyme
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

        // Initialiser les avatars
        document.addEventListener('DOMContentLoaded', function() {
            const avatarContainers = document.querySelectorAll('.avatar-container');

            avatarContainers.forEach(container => {
                const avatarUrl = container.dataset.avatar;
                const userName = container.dataset.name;

                if (avatarUrl && avatarUrl.trim() !== '') {
                    // Créer l'image avec fallback
                    const img = React.createElement('img', {
                        src: avatarUrl,
                        alt: `Avatar de ${userName}`,
                        className: "avatar",
                        onError: (e) => {
                            // Si l'image échoue, remplacer par le SVG
                            const svgElement = React.createElement(AnonymousAvatar, { size: 80 });
                            ReactDOM.render(svgElement, container);
                        }
                    });
                    ReactDOM.render(img, container);
                } else {
                    // Pas d'avatar, utiliser directement le SVG
                    const svgElement = React.createElement(AnonymousAvatar, { size: 80 });
                    ReactDOM.render(svgElement, container);
                }
            });
        });
    </script>

    <!-- Script JavaScript pour les fonctionnalités interactives -->
    <script type="text/babel">
        let allCards = [];

        // Fonction de recherche et filtrage
        function initFilters() {
            const searchInput = document.getElementById('searchInput');
            const categoryFilter = document.getElementById('categoryFilter');
            const sortFilter = document.getElementById('sortFilter');
            const resetButton = document.getElementById('resetFilters');
            const prestataireCards = document.querySelectorAll('.prestataire-card');
            const noResults = document.getElementById('no-results');
            const resultsCount = document.getElementById('resultsCount');

            // Stocker toutes les cartes
            allCards = Array.from(prestataireCards);

            // Fonction de filtrage et tri
            function filterAndSort() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                const selectedCategory = categoryFilter.value.toLowerCase();
                const sortValue = sortFilter.value;

                let visibleCards = allCards.filter(card => {
                    const name = card.dataset.name;
                    const services = card.dataset.services;

                    // Filtrage par recherche
                    const matchesSearch = searchTerm === '' ||
                        name.includes(searchTerm) ||
                        services.includes(searchTerm);

                    // Filtrage par catégorie
                    const matchesCategory = selectedCategory === '' ||
                        services.includes(selectedCategory);

                    return matchesSearch && matchesCategory;
                });

                // Tri
                visibleCards.sort((a, b) => {
                    switch(sortValue) {
                        case 'name':
                            return a.dataset.name.localeCompare(b.dataset.name);
                        case 'name-desc':
                            return b.dataset.name.localeCompare(a.dataset.name);
                        case 'price-asc':
                            return parseFloat(a.dataset.minPrice) - parseFloat(b.dataset.minPrice);
                        case 'price-desc':
                            return parseFloat(b.dataset.minPrice) - parseFloat(a.dataset.minPrice);
                        case 'delivery-asc':
                            return parseInt(a.dataset.minDelivery) - parseInt(b.dataset.minDelivery);
                        case 'delivery-desc':
                            return parseInt(b.dataset.minDelivery) - parseInt(a.dataset.minDelivery);
                        default:
                            return 0;
                    }
                });

                // Affichage des cartes
                allCards.forEach(card => {
                    card.style.display = 'none';
                });

                const container = document.getElementById('prestataires-grid');
                visibleCards.forEach((card, index) => {
                    card.style.display = 'block';
                    container.appendChild(card);
                });

                // Mettre à jour le compteur
                resultsCount.textContent = visibleCards.length;

                // Afficher/cacher le message "aucun résultat"
                if (visibleCards.length === 0) {
                    noResults.classList.remove('hidden');
                } else {
                    noResults.classList.add('hidden');
                }

                // Ré-animer les cartes visibles
                animateVisibleCards(visibleCards);
            }

            // Event listeners
            searchInput.addEventListener('input', filterAndSort);
            categoryFilter.addEventListener('change', filterAndSort);
            sortFilter.addEventListener('change', filterAndSort);

            // Reset tous les filtres
            resetButton.addEventListener('click', function() {
                searchInput.value = '';
                categoryFilter.value = '';
                sortFilter.value = 'name';
                filterAndSort();
            });
        }

        // Fonction pour les favoris
        function toggleFavorite(userId) {
            const button = event.target.closest('.btn-favorite');
            const svg = button.querySelector('svg');

            // Toggle entre rempli et vide
            if (svg.getAttribute('fill') === 'currentColor') {
                svg.setAttribute('fill', 'none');
                button.classList.remove('favorited');
            } else {
                svg.setAttribute('fill', 'currentColor');
                button.classList.add('favorited');
            }

            // Ici vous pouvez ajouter une requête AJAX pour sauvegarder en BDD
            console.log('Toggle favorite for user:', userId);
        }

        // Animation d'apparition des cartes
        function animateCards() {
            const cards = document.querySelectorAll('.prestataire-card');

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateY(0)';
                        }, index * 100);
                    }
                });
            }, {
                threshold: 0.1
            });

            cards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(card);
            });
        }

        // Animation pour les cartes visibles après filtrage
        function animateVisibleCards(cards) {
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 50);
            });
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            initFilters();
            animateCards();
        });
    </script>
</body>
</html>
