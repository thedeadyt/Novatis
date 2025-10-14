<?php
require_once __DIR__ . '/../../config/config.php';

// Récupérer l'ID du prestataire depuis l'URL
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$userId) {
    header('Location: ' . BASE_URL . '/Prestataires');
    exit;
}

// Récupérer les informations du prestataire
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
        u.website,
        u.rating,
        u.created_at,
        (SELECT COUNT(*) FROM orders o
         JOIN services s ON s.id = o.service_id
         WHERE s.user_id = u.id AND o.status = 'completed') as completed_orders
    FROM users u
    WHERE u.id = ?
");
$stmt->execute([$userId]);
$prestataire = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prestataire) {
    header('Location: ' . BASE_URL . '/Prestataires');
    exit;
}

// Récupérer les paramètres de confidentialité
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM user_privacy WHERE user_id = ?");
$stmt->execute([$userId]);
$privacy = $stmt->fetch(PDO::FETCH_ASSOC);

// Si pas de paramètres, utiliser les valeurs par défaut
if (!$privacy) {
    $privacy = [
        'profile_visibility' => 'public',
        'show_email' => false,
        'show_phone' => false,
        'allow_search_engines' => true
    ];
}

// Vérifier si l'utilisateur connecté peut voir ce profil
$currentUserId = $_SESSION['user']['id'] ?? null;
$canViewProfile = true;

// Seuls les modes "public" et "private" sont supportés
if ($privacy['profile_visibility'] === 'private' && $currentUserId != $userId) {
    // Profil privé : seul le propriétaire peut le voir
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Novatis | Profil privé</title>
        <link rel='stylesheet' type='text/css' href='<?= BASE_URL ?>/assets/css/Variables.css'>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body class="bg-gray-100">
        <?php include __DIR__ . '/../../includes/Header.php'; ?>
        <div class="min-h-screen flex items-center justify-center" style="margin-top: 6rem;">
            <div class="bg-white rounded-lg shadow-lg p-8 max-w-md text-center">
                <i class="fas fa-lock text-6xl text-gray-400 mb-4"></i>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Profil privé</h1>
                <p class="text-gray-600 mb-6">Ce profil est configuré en mode privé. Seul le propriétaire peut le consulter.</p>
                <a href="<?= BASE_URL ?>/Prestataires" class="inline-block bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Retour aux prestataires
                </a>
            </div>
        </div>
        <?php include __DIR__ . '/../../includes/Footer.php'; ?>
    </body>
    </html>
    <?php
    exit;
}

// Calculer le nom complet
$prestataire['name'] = trim($prestataire['firstname'] . ' ' . $prestataire['lastname']);
if (empty($prestataire['name'])) {
    $prestataire['name'] = $prestataire['pseudo'];
}

// Récupérer les services du prestataire
$stmt = $pdo->prepare("
    SELECT s.*, c.name as category_name
    FROM services s
    LEFT JOIN categories c ON c.id = s.category_id
    WHERE s.user_id = ? AND s.status = 'active'
    ORDER BY s.created_at DESC
");
$stmt->execute([$userId]);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les avis
$stmt = $pdo->prepare("
    SELECT r.*,
           u.firstname, u.lastname, u.pseudo, u.avatar,
           o.title as order_title
    FROM reviews r
    JOIN orders o ON o.id = r.order_id
    JOIN users u ON u.id = r.reviewer_id
    WHERE r.reviewee_id = ?
    ORDER BY r.created_at DESC
    LIMIT 10
");
$stmt->execute([$userId]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculer la note moyenne
$avgRating = count($reviews) > 0 ? array_sum(array_column($reviews, 'rating')) / count($reviews) : 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($prestataire['name']) ?> - Profil | Novatis</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/logos/Logo_Novatis.png">
    <?php if (!$privacy['allow_search_engines']): ?>
    <meta name="robots" content="noindex, nofollow">
    <?php endif; ?>
    <link rel='stylesheet' type='text/css' href='<?= BASE_URL ?>/assets/css/Variables.css'>
    <link rel='stylesheet' type='text/css' href='<?= BASE_URL ?>/assets/css/Footer.css'>
    <script src="https://cdn.tailwindcss.com"></script>
    <script crossorigin src="https://unpkg.com/react@18/umd/react.development.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
            margin-top: 6rem;
        }

        .profile-header {
            background: linear-gradient(135deg, var(--color-red) 0%, var(--color-accent-2) 100%);
            border-radius: 20px;
            padding: 3rem 2rem;
            margin-bottom: 2rem;
            color: white;
            box-shadow: var(--shadow-lg);
        }

        .rating-stars {
            color: #fbbf24;
        }

        .service-card, .review-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            transition: all 0.3s;
        }

        .service-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: var(--shadow-sm);
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../../includes/Header.php'; ?>

    <div class="profile-container">
        <!-- Header du profil -->
        <div class="profile-header">
            <div class="flex flex-col md:flex-row items-center gap-6">
                <div id="avatar-container" class="flex-shrink-0"></div>
                <div class="flex-1 text-center md:text-left">
                    <h1 class="text-3xl md:text-4xl font-bold mb-2"><?= htmlspecialchars($prestataire['name']) ?></h1>
                    <?php if ($prestataire['location']): ?>
                        <p class="text-white/90 mb-2">
                            <i class="fas fa-map-marker-alt mr-2"></i><?= htmlspecialchars($prestataire['location']) ?>
                        </p>
                    <?php endif; ?>
                    <div class="flex items-center justify-center md:justify-start gap-4 mb-3">
                        <div class="rating-stars">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star<?= $i <= $avgRating ? '' : ' opacity-30' ?>"></i>
                            <?php endfor; ?>
                            <span class="ml-2 text-white"><?= number_format($avgRating, 1) ?>/5</span>
                        </div>
                        <span class="text-white/90">(<?= count($reviews) ?> avis)</span>
                    </div>
                    <?php if ($prestataire['bio']): ?>
                        <p class="text-white/90 max-w-2xl"><?= nl2br(htmlspecialchars($prestataire['bio'])) ?></p>
                    <?php endif; ?>
                </div>
                <div class="flex flex-col gap-3">
                    <a href="<?= BASE_URL ?>/contact?prestataire=<?= $userId ?>"
                       class="bg-white text-red-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors text-center">
                        <i class="fas fa-envelope mr-2"></i>Contacter
                    </a>
                    <?php if ($prestataire['website']): ?>
                        <a href="<?= htmlspecialchars($prestataire['website']) ?>" target="_blank" rel="noopener noreferrer"
                           class="bg-white/20 backdrop-blur text-white px-6 py-3 rounded-lg font-semibold hover:bg-white/30 transition-colors text-center">
                            <i class="fas fa-globe mr-2"></i>Site web
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="stat-card">
                <div class="text-3xl font-bold text-red-600"><?= count($services) ?></div>
                <div class="text-gray-600 text-sm">Services actifs</div>
            </div>
            <div class="stat-card">
                <div class="text-3xl font-bold text-red-600"><?= $prestataire['completed_orders'] ?></div>
                <div class="text-gray-600 text-sm">Commandes complétées</div>
            </div>
            <div class="stat-card">
                <div class="text-3xl font-bold text-red-600"><?= count($reviews) ?></div>
                <div class="text-gray-600 text-sm">Avis reçus</div>
            </div>
            <div class="stat-card">
                <div class="text-3xl font-bold text-red-600"><?= number_format($avgRating, 1) ?></div>
                <div class="text-gray-600 text-sm">Note moyenne</div>
            </div>
        </div>

        <!-- Informations de contact -->
        <?php if ($privacy['show_email'] || $privacy['show_phone']): ?>
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-2xl font-bold mb-4">
                    <i class="fas fa-address-card mr-2 text-red-600"></i>Informations de contact
                </h2>
                <div class="space-y-3">
                    <?php if ($privacy['show_email'] && $prestataire['email']): ?>
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <i class="fas fa-envelope text-red-600"></i>
                            <a href="mailto:<?= htmlspecialchars($prestataire['email']) ?>" class="text-gray-700 hover:text-red-600 transition-colors">
                                <?= htmlspecialchars($prestataire['email']) ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php if ($privacy['show_phone'] && $prestataire['phone']): ?>
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <i class="fas fa-phone text-red-600"></i>
                            <a href="tel:<?= htmlspecialchars($prestataire['phone']) ?>" class="text-gray-700 hover:text-red-600 transition-colors">
                                <?= htmlspecialchars($prestataire['phone']) ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Services -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold mb-4">Services proposés</h2>
            <?php if (count($services) > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($services as $service): ?>
                        <div class="service-card">
                            <?php if ($service['image']): ?>
                                <img src="<?= htmlspecialchars($service['image']) ?>"
                                     alt="<?= htmlspecialchars($service['title']) ?>"
                                     class="w-full h-40 object-cover rounded-lg mb-4">
                            <?php endif; ?>
                            <h3 class="font-bold text-lg mb-2"><?= htmlspecialchars($service['title']) ?></h3>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3"><?= htmlspecialchars($service['description']) ?></p>
                            <?php if ($service['category_name']): ?>
                                <span class="inline-block bg-gray-100 px-3 py-1 rounded-full text-xs text-gray-600 mb-3">
                                    <?= htmlspecialchars($service['category_name']) ?>
                                </span>
                            <?php endif; ?>
                            <div class="flex items-center justify-between">
                                <span class="text-2xl font-bold text-red-600"><?= number_format($service['price'], 2) ?> €</span>
                                <span class="text-sm text-gray-600">
                                    <i class="far fa-clock mr-1"></i><?= $service['delivery_days'] ?> jour<?= $service['delivery_days'] > 1 ? 's' : '' ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center py-8">Aucun service disponible pour le moment.</p>
            <?php endif; ?>
        </div>

        <!-- Avis -->
        <?php if (count($reviews) > 0): ?>
            <div>
                <h2 class="text-2xl font-bold mb-4">Avis clients</h2>
                <div class="space-y-4">
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-card">
                            <div class="flex items-start gap-4">
                                <div id="review-avatar-<?= $review['reviewer_id'] ?>" class="flex-shrink-0"></div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <div>
                                            <h4 class="font-semibold">
                                                <?= htmlspecialchars(trim($review['firstname'] . ' ' . $review['lastname']) ?: $review['pseudo']) ?>
                                            </h4>
                                            <p class="text-sm text-gray-500">Commande: <?= htmlspecialchars($review['order_title']) ?></p>
                                        </div>
                                        <div class="rating-stars text-sm">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star<?= $i <= $review['rating'] ? '' : ' opacity-30' ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <?php if ($review['comment']): ?>
                                        <p class="text-gray-700"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                                    <?php endif; ?>
                                    <p class="text-xs text-gray-400 mt-2">
                                        <?= date('d/m/Y', strtotime($review['created_at'])) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/../../includes/Footer.php'; ?>

    <script type="text/babel">
        // Avatar component
        const AnonymousAvatar = ({ size = 80 }) => (
            React.createElement('div', {
                className: 'flex items-center justify-center bg-gray-300 rounded-full',
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
                        d: "M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2",
                        stroke: "#666",
                        strokeWidth: "2",
                        strokeLinecap: "round",
                        strokeLinejoin: "round"
                    }),
                    React.createElement('circle', {
                        cx: "12",
                        cy: "7",
                        r: "4",
                        stroke: "#666",
                        strokeWidth: "2",
                        strokeLinecap: "round",
                        strokeLinejoin: "round"
                    })
                )
            )
        );

        // Main avatar
        const avatar = <?= json_encode($prestataire['avatar']) ?>;
        const name = <?= json_encode($prestataire['name']) ?>;

        const avatarContainer = document.getElementById('avatar-container');
        if (avatar) {
            const img = document.createElement('img');
            img.src = avatar;
            img.alt = name;
            img.className = 'w-24 h-24 md:w-32 md:h-32 rounded-full border-4 border-white shadow-lg object-cover';
            img.onerror = () => {
                ReactDOM.render(React.createElement(AnonymousAvatar, { size: 128 }), avatarContainer);
            };
            avatarContainer.appendChild(img);
        } else {
            ReactDOM.render(React.createElement(AnonymousAvatar, { size: 128 }), avatarContainer);
        }

        // Review avatars
        <?php foreach ($reviews as $review): ?>
            const reviewAvatar<?= $review['reviewer_id'] ?> = <?= json_encode($review['avatar']) ?>;
            const reviewName<?= $review['reviewer_id'] ?> = <?= json_encode(trim($review['firstname'] . ' ' . $review['lastname']) ?: $review['pseudo']) ?>;
            const reviewContainer<?= $review['reviewer_id'] ?> = document.getElementById('review-avatar-<?= $review['reviewer_id'] ?>');

            if (reviewAvatar<?= $review['reviewer_id'] ?>) {
                const img = document.createElement('img');
                img.src = reviewAvatar<?= $review['reviewer_id'] ?>;
                img.alt = reviewName<?= $review['reviewer_id'] ?>;
                img.className = 'w-12 h-12 rounded-full object-cover';
                img.onerror = () => {
                    ReactDOM.render(React.createElement(AnonymousAvatar, { size: 48 }), reviewContainer<?= $review['reviewer_id'] ?>);
                };
                reviewContainer<?= $review['reviewer_id'] ?>.appendChild(img);
            } else {
                ReactDOM.render(React.createElement(AnonymousAvatar, { size: 48 }), reviewContainer<?= $review['reviewer_id'] ?>);
            }
        <?php endforeach; ?>
    </script>
</body>
</html>
