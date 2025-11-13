<?php
require_once __DIR__ . '/../../config/Config.php';

$pdo = getDBConnection();

// Récupérer toutes les catégories
$categoriesStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

// Requête pour récupérer TOUS les services avec leurs prestataires
// Une carte par service
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
        c.name AS category_name
    FROM services s
    JOIN users u ON s.user_id = u.id
    LEFT JOIN categories c ON s.category_id = c.id
    WHERE s.status = 'active'
    ORDER BY s.created_at DESC
";

$stmt = $pdo->query($sql);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculer les statistiques pour les filtres
$priceMin = PHP_INT_MAX;
$priceMax = 0;
$categoryCounts = [];

// Traitement des services
foreach ($services as &$service) {
    // Calculer le nom complet du prestataire
    $service['provider_name'] = trim($service['firstname'] . ' ' . $service['lastname']);
    if (empty($service['provider_name'])) {
        $service['provider_name'] = $service['pseudo'];
    }

    // Mettre à jour les statistiques de prix
    $priceMin = min($priceMin, $service['price']);
    $priceMax = max($priceMax, $service['price']);

    // Compter les catégories
    if ($service['category_id']) {
        if (!isset($categoryCounts[$service['category_id']])) {
            $categoryCounts[$service['category_id']] = 0;
        }
        $categoryCounts[$service['category_id']]++;
    }
}
unset($service); // Libérer la référence

// Arrondir les prix et gérer le cas où il n'y a pas de services
if ($priceMin === PHP_INT_MAX || $priceMax === 0) {
    $priceMin = 0;
    $priceMax = 1000;
} else {
    $priceMin = floor($priceMin);
    $priceMax = ceil($priceMax);
}
?>

<!DOCTYPE html>
<html lang="fr" data-user-lang="fr">
<head>
    <meta charset="UTF-8">
    <title data-i18n="prestataires.title" data-i18n-ns="pages">Novatis | Prestataires</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/logos/Logo_Novatis.png">
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

    <!-- i18next -->
    <?php include __DIR__ . '/../../includes/i18n-head.php'; ?>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Bouton favori */
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

        /* Custom styles pour le slider de prix */

        /* S'assurer que le conteneur parent n'a pas de fond bleu */
        .mb-6.pb-6.border-b:not(.price-range-container):not(.price-slider-track) {
            background: transparent !important;
            background-color: transparent !important;
        }

        .dark .mb-6.pb-6.border-b:not(.price-range-container):not(.price-slider-track) {
            background: transparent !important;
            background-color: transparent !important;
        }

        .price-range-container {
            position: relative;
            height: 40px;
            margin: 20px 0;
            background: transparent;
            padding: 0;
            box-shadow: none;
            overflow: visible;
            display: flex;
            align-items: center;
        }

        /* Tous les enfants input seulement */
        .price-range-container input {
            box-shadow: none !important;
        }

        .price-slider {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            width: 100%;
            height: 6px;
            border-radius: 3px;
            background: transparent;
            outline: none;
            position: relative;
            pointer-events: auto;
            z-index: 5;
            margin: 0;
            padding: 0;
            box-shadow: none;
            border: none;
        }

        /* Supprimer tout outline/focus ring */
        .price-slider:focus {
            outline: none;
            box-shadow: none;
        }

        .price-slider:focus-visible {
            outline: none;
            box-shadow: none;
        }

        .price-slider:active {
            outline: none;
            box-shadow: none;
        }

        /* Supprimer tous les tracks natifs du navigateur */
        .price-slider::-webkit-slider-runnable-track {
            background: transparent;
            border: none;
            height: 6px;
        }

        .price-slider::-moz-range-track {
            background: transparent;
            border: none;
            height: 6px;
        }

        /* Firefox progress bar - transparent */
        .price-slider::-moz-range-progress {
            background: transparent;
        }

        .price-slider::-ms-track {
            background: transparent;
            border: none;
            color: transparent;
            height: 6px;
        }

        .price-slider::-ms-fill-lower {
            background: transparent;
        }

        .price-slider::-ms-fill-upper {
            background: transparent;
        }

        /* Slider min en arrière-plan par défaut */
        #priceMin, #priceMinMobile {
            z-index: 10;
        }

        /* Slider max légèrement au-dessus */
        #priceMax, #priceMaxMobile {
            z-index: 11;
        }

        /* Quand le slider max est actif, le garder au-dessus */
        #priceMax:active, #priceMaxMobile:active {
            z-index: 12;
        }

        #priceMin:active, #priceMinMobile:active {
            z-index: 12;
        }

        /* Quand on interagit avec un slider, il passe au premier plan */
        .price-slider:active,
        .price-slider:focus {
            z-index: 12;
        }

        .price-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #B41200;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
            border: 2px solid white;
            pointer-events: auto;
            margin-top: -7px;
        }

        .price-slider::-moz-range-thumb {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #B41200;
            cursor: pointer;
            border: 2px solid white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
            pointer-events: auto;
            margin-top: -7px;
        }

        /* Mode sombre - ajuster la bordure des curseurs pour qu'ils soient visibles */
        .dark .price-slider::-webkit-slider-thumb {
            background: #DC2626;
            border: 3px solid #ffffff;
            box-shadow: 0 0 0 2px #B41200, 0 4px 12px rgba(0,0,0,0.8);
            margin-top: -7px;
        }

        .dark .price-slider::-moz-range-thumb {
            background: #DC2626;
            border: 3px solid #ffffff;
            box-shadow: 0 0 0 2px #B41200, 0 4px 12px rgba(0,0,0,0.8);
            margin-top: -7px;
        }

        /* Forcer la visibilité de la track au-dessus des thumbs lors de l'interaction */
        .price-slider:hover ~ .price-slider-track,
        .price-slider:active ~ .price-slider-track,
        #priceTrack:hover,
        #priceTrackMobile:hover,
        .price-range-container:has(.price-slider:active) #priceTrack,
        .price-range-container:has(.price-slider:active) #priceTrackMobile {
            z-index: 13 !important;
        }

        /* Track de fond (gris) */
        .price-range-container::before {
            content: '';
            position: absolute;
            height: 6px;
            width: 100%;
            background: #e5e7eb;
            border-radius: 3px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 0;
        }

        /* Track de fond en mode sombre */
        .dark .price-range-container::before {
            background: #4b5563;
        }

        /* Track de progression (rouge) */
        .price-slider-track {
            position: absolute !important;
            height: 6px !important;
            background: #B41200 !important;
            background-color: #B41200 !important;
            border-radius: 3px !important;
            top: 50%;
            transform: translateY(-50%);
            z-index: 9 !important;
            pointer-events: none !important;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        /* Mode sombre track */
        .dark .price-slider-track {
            background: #DC2626 !important;
            background-color: #DC2626 !important;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        /* Règles spécifiques par ID pour être absolument sûr */
        #priceTrack,
        #priceTrackMobile {
            background: #B41200;
            display: block;
            visibility: visible;
            opacity: 1;
            position: absolute;
            height: 6px;
            z-index: 9;
            top: 50%;
            transform: translateY(-50%);
            border-radius: 3px;
            pointer-events: none;
        }

        .dark #priceTrack,
        .dark #priceTrackMobile {
            background: #DC2626;
            display: block;
            visibility: visible;
            opacity: 1;
            position: absolute;
            height: 6px;
            z-index: 9;
            top: 50%;
            transform: translateY(-50%);
            border-radius: 3px;
            pointer-events: none;
        }

        /* Style pour les étoiles de notation */
        .rating-filter {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .rating-option {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid transparent;
        }

        .rating-option:hover {
            background-color: #f3f4f6;
        }

        .dark .rating-option:hover {
            background-color: #374151;
        }

        .rating-option.active {
            background-color: #FEE2E2;
            border-color: #B41200;
        }

        .dark .rating-option.active {
            background-color: #7F1D1D;
            border-color: #DC2626;
        }

        .rating-option input[type="radio"] {
            margin-right: 8px;
        }

        .stars {
            display: flex;
            gap: 2px;
            color: #fbbf24;
        }

        /* Sidebar sticky */
        .filters-sidebar {
            position: sticky;
            top: 100px;
            height: fit-content;
            max-height: calc(100vh - 120px);
            overflow-y: auto;
        }

        /* Scrollbar personnalisée */
        .filters-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .filters-sidebar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .dark .filters-sidebar::-webkit-scrollbar-track {
            background: #374151;
        }

        .filters-sidebar::-webkit-scrollbar-thumb {
            background: #B41200;
            border-radius: 10px;
        }

        .filters-sidebar::-webkit-scrollbar-thumb:hover {
            background: #7F0D00;
        }

        /* Mobile filter panel */
        .mobile-filter-panel {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: white;
            color: #111827;
            z-index: 1000;
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
            overflow-y: auto;
        }

        html.dark .mobile-filter-panel,
        [data-theme="dark"] .mobile-filter-panel,
        body.dark .mobile-filter-panel {
            background: #0f172a !important;
            color: #f1f5f9 !important;
        }

        html.dark .mobile-filter-panel * {
            color: inherit;
        }

        html.dark .mobile-filter-panel input,
        html.dark .mobile-filter-panel textarea,
        html.dark .mobile-filter-panel select {
            background: #1e293b;
            border-color: #334155;
            color: #f1f5f9;
        }

        html.dark .mobile-filter-panel input:focus,
        html.dark .mobile-filter-panel textarea:focus,
        html.dark .mobile-filter-panel select:focus {
            background: #293548;
            border-color: #475569;
        }

        .mobile-filter-panel.active {
            transform: translateX(0);
        }

        .filter-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }

        .filter-backdrop.active {
            display: block;
        }

        /* Animation pour les cartes */
        .prestataire-card {
            transition: all 0.3s ease;
        }

        .prestataire-card.hidden {
            display: none !important;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen bg-gray-50 dark:bg-slate-900">
    <?php include __DIR__ . '/../../includes/Header.php';?>

    <main class="flex-1">
        <div class="content" id="content">
            <!-- Header de la page -->
            <div class="bg-white dark:bg-slate-800 border-b dark:border-slate-700 mt-16">
                <div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 py-6 sm:py-8 md:py-10">
                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-2" data-i18n="prestataires.heading" data-i18n-ns="pages">Les Prestataires</h1>
                    <p class="text-sm sm:text-base md:text-lg text-gray-600 dark:text-gray-300" data-i18n="prestataires.description" data-i18n-ns="pages">Découvrez nos experts qualifiés et leurs services</p>
                </div>
            </div>

            <!-- Layout principal: Sidebar + Contenu -->
            <div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 py-4 sm:py-6 md:py-8">
                <div class="flex gap-3 sm:gap-4 md:gap-6">

                    <!-- ===== SIDEBAR DE FILTRES (Desktop) ===== -->
                    <aside class="hidden lg:block w-72 xl:w-80 flex-shrink-0">
                        <div class="filters-sidebar bg-white dark:bg-slate-800 rounded-lg shadow-lg dark:shadow-2xl p-6 border dark:border-slate-700">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white" data-i18n="prestataires.filters" data-i18n-ns="pages">Filtres</h2>
                                <button id="resetFiltersDesktop" class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 font-medium" data-i18n="prestataires.resetAll" data-i18n-ns="pages">Réinitialiser</button>
                            </div>

                            <!-- Filtre par catégories -->
                            <div class="mb-6 pb-6 border-b dark:border-slate-600">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3" data-i18n="prestataires.categories" data-i18n-ns="pages">Catégories</h3>
                                <div class="space-y-2 max-h-64 overflow-y-auto pr-2">
                                    <?php foreach ($categories as $category): ?>
                                        <?php
                                        $count = isset($categoryCounts[$category['id']]) ? $categoryCounts[$category['id']] : 0;
                                        if ($count > 0):
                                        ?>
                                        <label class="flex items-center justify-between p-2 rounded hover:bg-gray-50 dark:hover:bg-slate-700 cursor-pointer">
                                            <div class="flex items-center">
                                                <input type="checkbox"
                                                       class="category-checkbox w-4 h-4 text-red-600 rounded border-gray-300 focus:ring-red-500 dark:border-gray-600"
                                                       value="<?= $category['id'] ?>"
                                                       data-category-name="<?= htmlspecialchars($category['name']) ?>">
                                                <span class="ml-3 text-sm text-gray-700 dark:text-gray-300"><?= htmlspecialchars($category['name']) ?></span>
                                            </div>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">(<?= $count ?>)</span>
                                        </label>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Filtre par prix -->
                            <div class="mb-6 pb-6 border-b dark:border-slate-600">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3" data-i18n="prestataires.priceRange" data-i18n-ns="pages">Fourchette de prix</h3>
                                <div class="price-range-container">
                                    <div class="price-slider-track" id="priceTrack"></div>
                                    <input type="range"
                                           id="priceMin"
                                           class="price-slider"
                                           min="<?= $priceMin ?>"
                                           max="<?= $priceMax ?>"
                                           value="<?= $priceMin ?>"
                                           step="1">
                                    <input type="range"
                                           id="priceMax"
                                           class="price-slider"
                                           min="<?= $priceMin ?>"
                                           max="<?= $priceMax ?>"
                                           value="<?= $priceMax ?>"
                                           step="1">
                                </div>
                                <div class="flex justify-between items-center mt-4">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        <span data-i18n="prestataires.min" data-i18n-ns="pages">Min:</span>
                                        <span id="priceMinValue" class="font-semibold text-gray-900 dark:text-white"><?= $priceMin ?> €</span>
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        <span data-i18n="prestataires.max" data-i18n-ns="pages">Max:</span>
                                        <span id="priceMaxValue" class="font-semibold text-gray-900 dark:text-white"><?= $priceMax ?> €</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Filtre par notation -->
                            <div class="mb-6">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3" data-i18n="prestataires.rating" data-i18n-ns="pages">Notation minimum</h3>
                                <div class="rating-filter">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <label class="rating-option" data-rating="<?= $i ?>">
                                        <input type="radio" name="rating" value="<?= $i ?>" class="rating-radio">
                                        <div class="stars">
                                            <?php for ($j = 0; $j < 5; $j++): ?>
                                                <svg class="w-5 h-5 <?= $j < $i ? 'fill-current' : 'fill-gray-300' ?>" viewBox="0 0 20 20">
                                                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                                </svg>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400" data-i18n="prestataires.andMore" data-i18n-ns="pages">& plus</span>
                                    </label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <!-- ===== ZONE PRINCIPALE ===== -->
                    <div class="flex-1 min-w-0">

                        <!-- Barre du haut avec recherche, tri et compteur -->
                        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-lg dark:shadow-2xl p-3 sm:p-4 md:p-5 mb-4 sm:mb-6 border dark:border-slate-700">
                            <div class="flex flex-col gap-3 sm:gap-4 items-start justify-between">

                                <!-- Bouton filtres mobile + Compteur -->
                                <div class="flex items-center gap-3 sm:gap-4 w-full">
                                    <button id="mobileFiltersBtn" class="lg:hidden flex items-center gap-2 px-3 sm:px-4 py-2 sm:py-2.5 text-sm sm:text-base bg-red-600 text-white rounded-lg hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600 transition-colors font-medium">
                                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                        </svg>
                                        <span class="hidden sm:inline" data-i18n="prestataires.filters" data-i18n-ns="pages">Filtres</span>
                                    </button>

                                    <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                        <span id="resultsCount" class="font-semibold text-gray-900 dark:text-white"><?= count($services) ?></span>
                                        <span class="hidden sm:inline" data-i18n="prestataires.resultsFound" data-i18n-ns="pages">résultat(s)</span>
                                    </div>
                                </div>

                                <!-- Barre de recherche -->
                                <div class="relative w-full">
                                    <input type="text"
                                           id="searchInput"
                                           placeholder="Rechercher un prestataire..."
                                           data-i18n="prestataires.searchPlaceholder"
                                           data-i18n-ns="pages"
                                           data-i18n-attr="placeholder"
                                           class="w-full pl-9 sm:pl-10 pr-3 sm:pr-4 py-2 sm:py-2.5 text-sm sm:text-base border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                                    <svg class="absolute left-2.5 sm:left-3 top-2.5 sm:top-3 h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>

                                <!-- Tri -->
                                <div class="flex items-center gap-2 w-full">
                                    <label class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap" data-i18n="prestataires.sortBy" data-i18n-ns="pages">Trier:</label>
                                    <select id="sortFilter" class="flex-1 px-3 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-base border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                                        <option value="name-asc" data-i18n="prestataires.nameAZ" data-i18n-ns="pages">Nom (A-Z)</option>
                                        <option value="name-desc" data-i18n="prestataires.nameZA" data-i18n-ns="pages">Nom (Z-A)</option>
                                        <option value="price-asc" data-i18n="prestataires.priceAsc" data-i18n-ns="pages">Prix croissant</option>
                                        <option value="price-desc" data-i18n="prestataires.priceDesc" data-i18n-ns="pages">Prix décroissant</option>
                                        <option value="rating-desc" data-i18n="prestataires.ratingDesc" data-i18n-ns="pages">Meilleure note</option>
                                        <option value="delivery-asc" data-i18n="prestataires.deliveryAsc" data-i18n-ns="pages">Délai croissant</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Grid de cartes services -->
                        <!-- Debug: <?= count($services) ?> services trouvés -->
                        <div id="prestataires-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-3 sm:gap-4 md:gap-5 lg:gap-6">
                            <?php foreach ($services as $service): ?>
                                <?php
                                // Rating par défaut si null
                                $rating = $service['rating'] ?? 0;
                                ?>

                                <!-- Carte Service avec attributs data pour le filtrage -->
                                <div class="prestataire-card bg-white dark:bg-slate-800 rounded-lg shadow-md hover:shadow-xl dark:shadow-2xl transition-shadow duration-300 overflow-hidden relative border dark:border-slate-700"
                                     data-name="<?= htmlspecialchars(strtolower($service['provider_name'] . ' ' . $service['service_title'])) ?>"
                                     data-category-ids="<?= $service['category_id'] ?>"
                                     data-min-price="<?= $service['price'] ?>"
                                     data-max-price="<?= $service['price'] ?>"
                                     data-rating="<?= $rating ?>"
                                     data-min-delivery="<?= $service['delivery_days'] ?>">

                                    <!-- Bouton favori -->
                                    <button class="favorite-btn absolute top-3 right-3 z-10 w-10 h-10 bg-white dark:bg-slate-700 rounded-full shadow-md flex items-center justify-center hover:bg-gray-50 dark:hover:bg-slate-600 transition-colors"
                                            data-service-id="<?= $service['service_id'] ?>"
                                            title="Ajouter aux favoris">
                                        <i class="far fa-heart text-gray-400 text-xl"></i>
                                    </button>

                                    <!-- Image du service ou avatar -->
                                    <?php if (!empty($service['service_image'])): ?>
                                    <div class="h-32 sm:h-40 md:h-48 bg-cover bg-center" style="background-image: url('<?= htmlspecialchars($service['service_image']) ?>')"></div>
                                    <?php else: ?>
                                    <!-- Header avec avatar du prestataire -->
                                    <div class="prestataire-header bg-gray-100 dark:bg-slate-700 p-4 sm:p-5 md:p-6 text-center">
                                        <div class="avatar-container inline-block mb-3" data-avatar="<?= htmlspecialchars($service['avatar'] ?? '') ?>" data-name="<?= htmlspecialchars($service['provider_name']) ?>"></div>
                                        <h3 class="text-base sm:text-lg md:text-xl font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($service['provider_name']) ?></h3>

                                        <!-- Affichage de la notation -->
                                        <?php if ($rating > 0): ?>
                                        <div class="flex items-center justify-center gap-1 mt-2">
                                            <?php for ($i = 0; $i < 5; $i++): ?>
                                                <svg class="w-4 h-4 <?= $i < round($rating) ? 'text-yellow-400 fill-current' : 'text-gray-300 fill-current' ?>" viewBox="0 0 20 20">
                                                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                                </svg>
                                            <?php endfor; ?>
                                            <span class="text-sm text-gray-600 ml-1">(<?= number_format($rating, 1) ?>)</span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Informations du service -->
                                    <div class="p-3 sm:p-4 md:p-5 lg:p-6">
                                        <!-- Titre du service -->
                                        <h2 class="text-base sm:text-lg md:text-xl font-bold text-gray-900 dark:text-white mb-2"><?= htmlspecialchars($service['service_title']) ?></h2>

                                        <!-- Nom du prestataire -->
                                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mb-2 sm:mb-3">
                                            Par <a href="<?= BASE_URL ?>/profil?id=<?= $service['user_id'] ?>" class="text-red-600 dark:text-red-400 hover:underline"><?= htmlspecialchars($service['provider_name']) ?></a>
                                        </p>

                                        <!-- Description -->
                                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mb-3 sm:mb-4 line-clamp-3"><?= htmlspecialchars($service['service_description'] ?? 'Aucune description disponible.') ?></p>

                                        <!-- Catégorie -->
                                        <?php if (!empty($service['category_name'])): ?>
                                        <div class="mb-2 sm:mb-4">
                                            <span class="px-2 sm:px-3 py-1 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 text-xs font-medium rounded-full">
                                                <?= htmlspecialchars($service['category_name']) ?>
                                            </span>
                                        </div>
                                        <?php endif; ?>

                                        <!-- Prix et délai -->
                                        <div class="mb-3 sm:mb-4 pb-3 sm:pb-4 border-b dark:border-slate-600 space-y-1 sm:space-y-2">
                                            <div class="flex justify-between items-center">
                                                <span class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">Prix:</span>
                                                <span class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900 dark:text-white"><?= number_format($service['price'], 0, ',', ' ') ?> €</span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">Délai:</span>
                                                <span class="text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-300"><?= $service['delivery_days'] ?> jour(s)</span>
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="grid grid-cols-2 gap-2 sm:gap-3">
                                            <a href="<?= BASE_URL ?>/profil?id=<?= $service['user_id'] ?>"
                                               class="flex items-center justify-center gap-1 sm:gap-2 px-2 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm bg-red-600 dark:bg-red-700 text-white rounded-lg hover:bg-red-700 dark:hover:bg-red-600 transition-colors font-medium">
                                               <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                               </svg>
                                               <span class="hidden sm:inline" data-i18n="prestataires.viewProfile" data-i18n-ns="pages">Profil</span>
                                            </a>
                                            <a href="<?= BASE_URL ?>/contact?prestataire=<?= $service['user_id'] ?>"
                                               class="flex items-center justify-center gap-1 sm:gap-2 px-2 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
                                               <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                               </svg>
                                               <span class="hidden sm:inline" data-i18n="prestataires.contact" data-i18n-ns="pages">Commander</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Message si aucun prestataire trouvé -->
                        <div id="no-results" class="hidden text-center py-8 sm:py-12 md:py-16 bg-white dark:bg-slate-800 rounded-lg shadow-lg dark:shadow-2xl border dark:border-slate-700 px-4">
                            <svg class="mx-auto h-16 sm:h-20 md:h-24 w-16 sm:w-20 md:w-24 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h3 class="mt-3 sm:mt-4 text-lg sm:text-xl font-medium text-gray-900 dark:text-white" data-i18n="prestataires.noResults" data-i18n-ns="pages">Aucun prestataire trouvé</h3>
                            <p class="mt-1 sm:mt-2 text-sm sm:text-base text-gray-500 dark:text-gray-400" data-i18n="prestataires.modifySearch" data-i18n-ns="pages">Essayez de modifier vos critères de recherche.</p>
                        </div>

                    </div>
                </div>
            </div>

            <!-- ===== PANEL FILTRES MOBILE ===== -->
            <div class="filter-backdrop" id="filterBackdrop"></div>
            <div class="mobile-filter-panel" id="mobileFilterPanel">
                <div class="p-4 sm:p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white" data-i18n="prestataires.filters" data-i18n-ns="pages">Filtres</h2>
                        <button id="closeMobileFilters" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Copie des filtres desktop pour mobile -->
                    <div class="space-y-6">
                        <!-- Catégories -->
                        <div class="pb-6 border-b dark:border-slate-600">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3" data-i18n="prestataires.categories" data-i18n-ns="pages">Catégories</h3>
                            <div class="space-y-2 max-h-64 overflow-y-auto pr-2">
                                <?php foreach ($categories as $category): ?>
                                    <?php
                                    $count = isset($categoryCounts[$category['id']]) ? $categoryCounts[$category['id']] : 0;
                                    if ($count > 0):
                                    ?>
                                    <label class="flex items-center justify-between p-2 rounded hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                        <div class="flex items-center">
                                            <input type="checkbox"
                                                   class="category-checkbox-mobile w-4 h-4 text-red-600 rounded border-gray-300 focus:ring-red-500 dark:border-gray-600"
                                                   value="<?= $category['id'] ?>">
                                            <span class="ml-3 text-sm text-gray-700 dark:text-gray-300"><?= htmlspecialchars($category['name']) ?></span>
                                        </div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">(<?= $count ?>)</span>
                                    </label>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Prix -->
                        <div class="pb-6 border-b dark:border-slate-600">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3" data-i18n="prestataires.priceRange" data-i18n-ns="pages">Fourchette de prix</h3>
                            <div class="price-range-container">
                                <div class="price-slider-track" id="priceTrackMobile"></div>
                                <input type="range"
                                       id="priceMinMobile"
                                       class="price-slider"
                                       min="<?= $priceMin ?>"
                                       max="<?= $priceMax ?>"
                                       value="<?= $priceMin ?>"
                                       step="1">
                                <input type="range"
                                       id="priceMaxMobile"
                                       class="price-slider"
                                       min="<?= $priceMin ?>"
                                       max="<?= $priceMax ?>"
                                       value="<?= $priceMax ?>"
                                       step="1">
                            </div>
                            <div class="flex justify-between items-center mt-4">
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    <span data-i18n="prestataires.min" data-i18n-ns="pages">Min:</span>
                                    <span id="priceMinValueMobile" class="font-semibold text-gray-900 dark:text-white"><?= $priceMin ?> €</span>
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    <span data-i18n="prestataires.max" data-i18n-ns="pages">Max:</span>
                                    <span id="priceMaxValueMobile" class="font-semibold text-gray-900 dark:text-white"><?= $priceMax ?> €</span>
                                </div>
                            </div>
                        </div>

                        <!-- Notation -->
                        <div>
                            <h3 class="text-xs sm:text-sm font-semibold text-gray-900 dark:text-white mb-2 sm:mb-3" data-i18n="prestataires.rating" data-i18n-ns="pages">Notation minimum</h3>
                            <div class="rating-filter">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                <label class="rating-option" data-rating="<?= $i ?>">
                                    <input type="radio" name="rating-mobile" value="<?= $i ?>" class="rating-radio-mobile">
                                    <div class="stars">
                                        <?php for ($j = 0; $j < 5; $j++): ?>
                                            <svg class="w-5 h-5 <?= $j < $i ? 'fill-current' : 'fill-gray-300' ?>" viewBox="0 0 20 20">
                                                <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                            </svg>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="ml-2 text-sm text-gray-600" data-i18n="prestataires.andMore" data-i18n-ns="pages">& plus</span>
                                </label>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="mt-5 sm:mt-6 space-y-2 sticky bottom-0 bg-white dark:bg-slate-800 pt-3 sm:pt-4 border-t dark:border-slate-600">
                        <button id="resetFiltersMobile" class="w-full px-3 sm:px-4 py-2 sm:py-3 text-sm sm:text-base bg-gray-200 dark:bg-slate-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-slate-600 transition-colors font-medium">
                            <span data-i18n="prestataires.resetAll" data-i18n-ns="pages">Réinitialiser</span>
                        </button>
                        <button id="applyMobileFilters" class="w-full px-3 sm:px-4 py-2 sm:py-3 text-sm sm:text-base bg-red-600 dark:bg-red-700 text-white rounded-lg hover:bg-red-700 dark:hover:bg-red-600 transition-colors font-medium">
                            <span data-i18n="prestataires.applyFilters" data-i18n-ns="pages">Appliquer</span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <?php include __DIR__ . '/../../includes/Footer.php';?>

    <!-- ===== SCRIPT POUR LES AVATARS ===== -->
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

    <!-- ===== SCRIPT JAVASCRIPT DE FILTRAGE ===== -->
    <script>
        // ========================================
        // VARIABLES GLOBALES
        // ========================================
        const globalPriceMin = <?= $priceMin ?>;
        const globalPriceMax = <?= $priceMax ?>;
        let allCards = [];
        let searchDebounceTimer = null;

        // ========================================
        // INITIALISATION AU CHARGEMENT
        // ========================================
        document.addEventListener('DOMContentLoaded', function() {
            // Récupérer toutes les cartes
            allCards = Array.from(document.querySelectorAll('.prestataire-card'));

            // Initialiser tous les filtres
            initPriceSliders();
            initCategoryFilters();
            initRatingFilters();
            initSearchFilter();
            initSortFilter();
            initMobileFilters();
            initResetButtons();

            // Appliquer les paramètres GET (category et search)
            applyURLFilters();

            // Animations d'entrée
            animateCards();
        });

        // ========================================
        // APPLIQUER LES FILTRES DEPUIS L'URL
        // ========================================
        function applyURLFilters() {
            // Récupérer les paramètres de l'URL
            const urlParams = new URLSearchParams(window.location.search);
            const categoryId = urlParams.get('category');
            const searchTerm = urlParams.get('search');

            // Si paramètre category : cocher les catégories correspondantes
            if (categoryId) {
                const categoryCheckbox = document.querySelector(`.category-checkbox[value="${categoryId}"]`);
                const categoryCheckboxMobile = document.querySelector(`.category-checkbox-mobile[value="${categoryId}"]`);
                
                if (categoryCheckbox) {
                    categoryCheckbox.checked = true;
                }
                if (categoryCheckboxMobile) {
                    categoryCheckboxMobile.checked = true;
                }
            }

            // Si paramètre search : remplir le champ de recherche
            if (searchTerm) {
                document.getElementById('searchInput').value = decodeURIComponent(searchTerm);
            }

            // Appliquer les filtres
            applyFilters();
        }

        // ========================================
        // FONCTION PRINCIPALE DE FILTRAGE
        // ========================================
        function applyFilters() {
            // Récupérer les valeurs des filtres desktop (source de vérité)
            const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
            const selectedCategories = getSelectedCategories('.category-checkbox');
            const priceMin = parseFloat(document.getElementById('priceMin').value);
            const priceMax = parseFloat(document.getElementById('priceMax').value);
            const minRating = getSelectedRating('.rating-radio');
            const sortValue = document.getElementById('sortFilter').value;

            // Filtrer les cartes
            let visibleCards = allCards.filter(card => {
                // 1. Filtre par recherche textuelle (nom du service ou prestataire)
                const cardName = card.dataset.name || '';
                const matchesSearch = searchTerm === '' || cardName.includes(searchTerm);

                // 2. Filtre par catégories (checkboxes multiples)
                const cardCategoryId = card.dataset.categoryIds || '';
                const matchesCategory = selectedCategories.length === 0 ||
                    selectedCategories.includes(cardCategoryId);

                // 3. Filtre par prix (simple - chaque carte a UN prix)
                const cardPrice = Math.round(parseFloat(card.dataset.minPrice) || 0);
                const matchesPrice = cardPrice >= Math.floor(priceMin) && cardPrice <= Math.ceil(priceMax);

                // 4. Filtre par notation (radio buttons)
                const cardRating = parseFloat(card.dataset.rating) || 0;
                const matchesRating = minRating === null || cardRating >= minRating;

                // Retourner true si tous les critères sont satisfaits
                return matchesSearch && matchesCategory && matchesPrice && matchesRating;
            });

            // Trier les cartes filtrées
            visibleCards = sortCards(visibleCards, sortValue);

            // Afficher/masquer les cartes et mettre à jour l'UI
            displayCards(visibleCards);
        }

        // ========================================
        // RÉCUPÉRATION DES FILTRES ACTIFS
        // ========================================
        function getSelectedCategories(selector) {
            const checkboxes = document.querySelectorAll(selector + ':checked');
            return Array.from(checkboxes).map(cb => cb.value);
        }

        function getSelectedRating(selector) {
            const checkedRadio = document.querySelector(selector + ':checked');
            return checkedRadio ? parseFloat(checkedRadio.value) : null;
        }

        // ========================================
        // TRI DES CARTES
        // ========================================
        function sortCards(cards, sortValue) {
            return cards.sort((a, b) => {
                switch(sortValue) {
                    case 'name-asc':
                        return (a.dataset.name || '').localeCompare(b.dataset.name || '');
                    case 'name-desc':
                        return (b.dataset.name || '').localeCompare(a.dataset.name || '');
                    case 'price-asc':
                        return (parseFloat(a.dataset.minPrice) || 0) - (parseFloat(b.dataset.minPrice) || 0);
                    case 'price-desc':
                        return (parseFloat(b.dataset.minPrice) || 0) - (parseFloat(a.dataset.minPrice) || 0);
                    case 'rating-desc':
                        return (parseFloat(b.dataset.rating) || 0) - (parseFloat(a.dataset.rating) || 0);
                    case 'delivery-asc':
                        return (parseInt(a.dataset.minDelivery) || 0) - (parseInt(b.dataset.minDelivery) || 0);
                    default:
                        return 0;
                }
            });
        }

        // ========================================
        // AFFICHAGE DES CARTES
        // ========================================
        function displayCards(visibleCards) {
            const container = document.getElementById('prestataires-grid');
            const noResults = document.getElementById('no-results');
            const resultsCount = document.getElementById('resultsCount');

            // Masquer toutes les cartes
            allCards.forEach(card => {
                card.classList.add('hidden');
            });

            // Afficher les cartes visibles dans l'ordre
            visibleCards.forEach(card => {
                card.classList.remove('hidden');
                container.appendChild(card); // Réorganiser dans le DOM
            });

            // Mettre à jour le compteur de résultats
            resultsCount.textContent = visibleCards.length;

            // Afficher/masquer le message "aucun résultat"
            if (visibleCards.length === 0) {
                noResults.classList.remove('hidden');
                container.classList.add('hidden');
            } else {
                noResults.classList.add('hidden');
                container.classList.remove('hidden');
            }

            // Animation des cartes visibles
            animateVisibleCards(visibleCards);
        }

        // ========================================
        // INITIALISATION DES SLIDERS DE PRIX
        // ========================================
        function initPriceSliders() {
            console.log('🔧 initPriceSliders() appelé');
            console.trace('Stack trace de initPriceSliders:');

            // Éléments desktop
            const priceMinSlider = document.getElementById('priceMin');
            const priceMaxSlider = document.getElementById('priceMax');
            const priceMinValue = document.getElementById('priceMinValue');
            const priceMaxValue = document.getElementById('priceMaxValue');
            const priceTrack = document.getElementById('priceTrack');

            // Éléments mobile
            const priceMinSliderMobile = document.getElementById('priceMinMobile');
            const priceMaxSliderMobile = document.getElementById('priceMaxMobile');
            const priceMinValueMobile = document.getElementById('priceMinValueMobile');
            const priceMaxValueMobile = document.getElementById('priceMaxValueMobile');
            const priceTrackMobile = document.getElementById('priceTrackMobile');

            // Fonction de mise à jour des sliders avec prévention du croisement
            function updateSlider(minSlider, maxSlider, minValueDisplay, maxValueDisplay, track, applyFilter = true) {
                let minVal = parseFloat(minSlider.value);
                let maxVal = parseFloat(maxSlider.value);

                // Empêcher que min dépasse max
                if (minVal > maxVal) {
                    minVal = maxVal;
                    minSlider.value = minVal;
                }

                // Empêcher que max soit inférieur à min
                if (maxVal < minVal) {
                    maxVal = minVal;
                    maxSlider.value = maxVal;
                }

                // Mettre à jour l'affichage des valeurs
                minValueDisplay.textContent = Math.round(minVal) + ' €';
                maxValueDisplay.textContent = Math.round(maxVal) + ' €';

                // Mettre à jour la barre de progression visuelle
                const minPercent = ((minVal - globalPriceMin) / (globalPriceMax - globalPriceMin)) * 100;
                const maxPercent = ((maxVal - globalPriceMin) / (globalPriceMax - globalPriceMin)) * 100;

                console.log('updateSlider:', {
                    trackId: track.id,
                    minVal, maxVal, minPercent, maxPercent,
                    left: minPercent + '%',
                    width: (maxPercent - minPercent) + '%'
                });

                // Si les valeurs sont aux extrêmes globaux, afficher la stack trace
                if (minVal === globalPriceMin && maxVal === globalPriceMax) {
                    console.log('⚠️ RESET DÉTECTÉ - Stack trace:');
                    console.trace();
                }

                track.style.left = minPercent + '%';
                track.style.width = (maxPercent - minPercent) + '%';

                // Vérifier que les styles sont bien appliqués
                console.log('Track styles après update:', {
                    left: track.style.left,
                    width: track.style.width,
                    display: window.getComputedStyle(track).display,
                    background: window.getComputedStyle(track).background
                });

                // Appliquer les filtres
                if (applyFilter) {
                    applyFilters();
                }
            }

            // Event listeners pour les sliders desktop
            priceMinSlider.addEventListener('input', () => {
                console.log('📊 Desktop priceMin input event:', priceMinSlider.value);
                updateSlider(priceMinSlider, priceMaxSlider, priceMinValue, priceMaxValue, priceTrack, true);
                // Synchroniser avec mobile
                priceMinSliderMobile.value = priceMinSlider.value;
                updateSlider(priceMinSliderMobile, priceMaxSliderMobile, priceMinValueMobile, priceMaxValueMobile, priceTrackMobile, false);
            });

            priceMaxSlider.addEventListener('input', () => {
                console.log('📊 Desktop priceMax input event:', priceMaxSlider.value);
                updateSlider(priceMinSlider, priceMaxSlider, priceMinValue, priceMaxValue, priceTrack, true);
                // Synchroniser avec mobile
                priceMaxSliderMobile.value = priceMaxSlider.value;
                updateSlider(priceMinSliderMobile, priceMaxSliderMobile, priceMinValueMobile, priceMaxValueMobile, priceTrackMobile, false);
            });

            // Event listeners pour les sliders mobile
            priceMinSliderMobile.addEventListener('input', () => {
                updateSlider(priceMinSliderMobile, priceMaxSliderMobile, priceMinValueMobile, priceMaxValueMobile, priceTrackMobile, true);
                // Synchroniser avec desktop
                priceMinSlider.value = priceMinSliderMobile.value;
                updateSlider(priceMinSlider, priceMaxSlider, priceMinValue, priceMaxValue, priceTrack, false);
            });

            priceMaxSliderMobile.addEventListener('input', () => {
                updateSlider(priceMinSliderMobile, priceMaxSliderMobile, priceMinValueMobile, priceMaxValueMobile, priceTrackMobile, true);
                // Synchroniser avec desktop
                priceMaxSlider.value = priceMaxSliderMobile.value;
                updateSlider(priceMinSlider, priceMaxSlider, priceMinValue, priceMaxValue, priceTrack, false);
            });

            // Initialiser la position des barres de progression
            updateSlider(priceMinSlider, priceMaxSlider, priceMinValue, priceMaxValue, priceTrack, false);
            updateSlider(priceMinSliderMobile, priceMaxSliderMobile, priceMinValueMobile, priceMaxValueMobile, priceTrackMobile, false);
        }

        // ========================================
        // INITIALISATION DES FILTRES DE CATÉGORIES
        // ========================================
        function initCategoryFilters() {
            const categoryCheckboxes = document.querySelectorAll('.category-checkbox');
            const categoryCheckboxesMobile = document.querySelectorAll('.category-checkbox-mobile');

            // Desktop checkboxes
            categoryCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    // Synchroniser avec mobile
                    const mobileCheckbox = Array.from(categoryCheckboxesMobile).find(
                        cb => cb.value === checkbox.value
                    );
                    if (mobileCheckbox) {
                        mobileCheckbox.checked = checkbox.checked;
                    }
                    applyFilters();
                });
            });

            // Mobile checkboxes
            categoryCheckboxesMobile.forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    // Synchroniser avec desktop
                    const desktopCheckbox = Array.from(categoryCheckboxes).find(
                        cb => cb.value === checkbox.value
                    );
                    if (desktopCheckbox) {
                        desktopCheckbox.checked = checkbox.checked;
                    }
                    applyFilters();
                });
            });
        }

        // ========================================
        // INITIALISATION DES FILTRES DE NOTATION
        // ========================================
        function initRatingFilters() {
            const ratingOptions = document.querySelectorAll('.rating-option');

            ratingOptions.forEach(option => {
                option.addEventListener('click', function(e) {
                    // Empêcher le double déclenchement si on clique sur le radio
                    if (e.target.tagName === 'INPUT') return;

                    const radio = this.querySelector('input[type="radio"]');
                    const currentValue = radio.value;
                    const isDesktop = radio.classList.contains('rating-radio');

                    // Si déjà sélectionné, décocher (toggle behavior)
                    if (this.classList.contains('active')) {
                        radio.checked = false;
                        this.classList.remove('active');

                        // Synchroniser avec l'autre version (desktop/mobile)
                        const otherRadioClass = isDesktop ? '.rating-radio-mobile' : '.rating-radio';
                        const otherRadio = document.querySelector(`${otherRadioClass}[value="${currentValue}"]`);
                        if (otherRadio) {
                            otherRadio.checked = false;
                            otherRadio.closest('.rating-option').classList.remove('active');
                        }
                    } else {
                        // Désactiver tous les autres
                        const allOptions = isDesktop
                            ? document.querySelectorAll('.rating-option input.rating-radio')
                            : document.querySelectorAll('.rating-option input.rating-radio-mobile');

                        allOptions.forEach(r => {
                            r.closest('.rating-option').classList.remove('active');
                        });

                        // Activer celui-ci
                        this.classList.add('active');
                        radio.checked = true;

                        // Synchroniser avec l'autre version (desktop/mobile)
                        const otherRadioClass = isDesktop ? '.rating-radio-mobile' : '.rating-radio';
                        const allOtherOptions = document.querySelectorAll(otherRadioClass);

                        allOtherOptions.forEach(r => {
                            r.closest('.rating-option').classList.remove('active');
                        });

                        const otherRadio = document.querySelector(`${otherRadioClass}[value="${currentValue}"]`);
                        if (otherRadio) {
                            otherRadio.checked = true;
                            otherRadio.closest('.rating-option').classList.add('active');
                        }
                    }

                    applyFilters();
                });

                // Event listener sur le radio lui-même pour le comportement natif
                const radio = option.querySelector('input[type="radio"]');
                radio.addEventListener('change', function() {
                    if (this.checked) {
                        const isDesktop = this.classList.contains('rating-radio');
                        const currentValue = this.value;

                        // Activer visuellement
                        this.closest('.rating-option').classList.add('active');

                        // Synchroniser
                        const otherRadioClass = isDesktop ? '.rating-radio-mobile' : '.rating-radio';
                        const otherRadio = document.querySelector(`${otherRadioClass}[value="${currentValue}"]`);
                        if (otherRadio) {
                            otherRadio.checked = true;
                            otherRadio.closest('.rating-option').classList.add('active');
                        }

                        applyFilters();
                    }
                });
            });
        }

        // ========================================
        // INITIALISATION DU FILTRE DE RECHERCHE
        // ========================================
        function initSearchFilter() {
            const searchInput = document.getElementById('searchInput');

            searchInput.addEventListener('input', () => {
                // Debounce de 300ms pour éviter trop d'appels
                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(() => {
                    applyFilters();
                }, 300);
            });

            // Appliquer immédiatement sur Enter
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    clearTimeout(searchDebounceTimer);
                    applyFilters();
                }
            });
        }

        // ========================================
        // INITIALISATION DU TRI
        // ========================================
        function initSortFilter() {
            const sortFilter = document.getElementById('sortFilter');
            sortFilter.addEventListener('change', applyFilters);
        }

        // ========================================
        // INITIALISATION DES FILTRES MOBILE
        // ========================================
        function initMobileFilters() {
            const mobileFiltersBtn = document.getElementById('mobileFiltersBtn');
            const mobileFilterPanel = document.getElementById('mobileFilterPanel');
            const filterBackdrop = document.getElementById('filterBackdrop');
            const closeMobileFilters = document.getElementById('closeMobileFilters');
            const applyMobileFilters = document.getElementById('applyMobileFilters');

            // Fonction pour ouvrir le panel mobile
            function openPanel() {
                mobileFilterPanel.classList.add('active');
                filterBackdrop.classList.add('active');
                document.body.style.overflow = 'hidden'; // Bloquer le scroll du body
                
                // Appliquer le thème dark si nécessaire
                if (document.documentElement.classList.contains('dark')) {
                    mobileFilterPanel.style.backgroundColor = '#0f172a';
                    mobileFilterPanel.style.color = '#f1f5f9';
                } else {
                    mobileFilterPanel.style.backgroundColor = 'white';
                    mobileFilterPanel.style.color = '#111827';
                }
            }

            // Fonction pour fermer le panel mobile
            function closePanel() {
                mobileFilterPanel.classList.remove('active');
                filterBackdrop.classList.remove('active');
                document.body.style.overflow = ''; // Réactiver le scroll du body
                mobileFilterPanel.style.backgroundColor = '';
                mobileFilterPanel.style.color = '';
            }

            // Ouvrir le panel
            mobileFiltersBtn.addEventListener('click', openPanel);

            // Fermer le panel avec le bouton X
            closeMobileFilters.addEventListener('click', closePanel);

            // Fermer le panel en cliquant sur le backdrop
            filterBackdrop.addEventListener('click', closePanel);

            // Appliquer les filtres et fermer le panel
            applyMobileFilters.addEventListener('click', () => {
                applyFilters();
                closePanel();
            });

            // Fermer avec la touche Escape
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && mobileFilterPanel.classList.contains('active')) {
                    closePanel();
                }
            });
        }

        // ========================================
        // RÉINITIALISATION DES FILTRES
        // ========================================
        function resetFilters() {
            // 1. Reset recherche
            document.getElementById('searchInput').value = '';

            // 2. Reset catégories (desktop + mobile)
            document.querySelectorAll('.category-checkbox').forEach(cb => cb.checked = false);
            document.querySelectorAll('.category-checkbox-mobile').forEach(cb => cb.checked = false);

            // 3. Reset prix (desktop + mobile)
            document.getElementById('priceMin').value = globalPriceMin;
            document.getElementById('priceMax').value = globalPriceMax;
            document.getElementById('priceMinValue').textContent = globalPriceMin + ' €';
            document.getElementById('priceMaxValue').textContent = globalPriceMax + ' €';
            document.getElementById('priceMinMobile').value = globalPriceMin;
            document.getElementById('priceMaxMobile').value = globalPriceMax;
            document.getElementById('priceMinValueMobile').textContent = globalPriceMin + ' €';
            document.getElementById('priceMaxValueMobile').textContent = globalPriceMax + ' €';

            // Mettre à jour les barres de progression
            const priceTrack = document.getElementById('priceTrack');
            const priceTrackMobile = document.getElementById('priceTrackMobile');
            priceTrack.style.left = '0%';
            priceTrack.style.width = '100%';
            priceTrackMobile.style.left = '0%';
            priceTrackMobile.style.width = '100%';

            // 4. Reset notation (desktop + mobile)
            document.querySelectorAll('.rating-option').forEach(opt => opt.classList.remove('active'));
            document.querySelectorAll('.rating-radio').forEach(radio => radio.checked = false);
            document.querySelectorAll('.rating-radio-mobile').forEach(radio => radio.checked = false);

            // 5. Reset tri
            document.getElementById('sortFilter').value = 'name-asc';

            // 6. Appliquer les filtres (afficher tous les résultats)
            applyFilters();
        }

        function initResetButtons() {
            document.getElementById('resetFiltersDesktop').addEventListener('click', resetFilters);
            document.getElementById('resetFiltersMobile').addEventListener('click', resetFilters);
        }

        // ========================================
        // ANIMATIONS
        // ========================================
        function animateCards() {
            const cards = document.querySelectorAll('.prestataire-card');

            // Observer pour animer les cartes au scroll
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateY(0)';
                        }, index * 50);
                        observer.unobserve(entry.target); // Animer une seule fois
                    }
                });
            }, {
                threshold: 0.1
            });

            cards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                observer.observe(card);
            });
        }

        function animateVisibleCards(cards) {
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 30);
            });
        }

        // ========================================
        // SYSTÈME DE FAVORIS
        // ========================================
        let userFavorites = [];

        // Charger les favoris de l'utilisateur
        async function loadFavorites() {
            try {
                const response = await fetch('<?= BASE_URL ?>/api/favorites.php?action=list');
                const data = await response.json();
                if (data.success) {
                    userFavorites = data.favorites;
                    updateFavoriteButtons();
                }
            } catch (error) {
                console.error('Erreur lors du chargement des favoris:', error);
            }
        }

        // Mettre à jour l'apparence des boutons favoris
        function updateFavoriteButtons() {
            document.querySelectorAll('.favorite-btn').forEach(btn => {
                const serviceId = parseInt(btn.dataset.serviceId);
                const isFavorite = userFavorites.includes(serviceId);
                const icon = btn.querySelector('i');

                if (isFavorite) {
                    icon.classList.remove('far', 'text-gray-400');
                    icon.classList.add('fas', 'text-red-500');
                    btn.title = 'Retirer des favoris';
                } else {
                    icon.classList.remove('fas', 'text-red-500');
                    icon.classList.add('far', 'text-gray-400');
                    btn.title = 'Ajouter aux favoris';
                }
            });
        }

        // Toggle favori
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
                    if (data.is_favorite) {
                        userFavorites.push(serviceId);
                    } else {
                        userFavorites = userFavorites.filter(id => id !== serviceId);
                    }
                    updateFavoriteButtons();

                    // Animation du bouton
                    button.classList.add('animate-bounce');
                    setTimeout(() => button.classList.remove('animate-bounce'), 500);
                }
            } catch (error) {
                console.error('Erreur lors de la gestion du favori:', error);
            }
        }

        // Initialiser les boutons favoris
        function initFavorites() {
            document.querySelectorAll('.favorite-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const serviceId = parseInt(btn.dataset.serviceId);
                    toggleFavorite(serviceId, btn);
                });
            });

            // Charger les favoris existants
            loadFavorites();
        }

        // Ajouter l'initialisation des favoris au chargement
        document.addEventListener('DOMContentLoaded', function() {
            initFavorites();
        });
    </script>
</body>
</html>
