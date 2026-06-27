<?php
require_once __DIR__ . '/../config/Config.php';

$pdo = getDBConnection();

// 1. Créer des utilisateurs (prestataires) fictifs
$users = [
    [
        'firstname' => 'Marie',
        'lastname' => 'Dupont',
        'pseudo' => 'marie_design_' . time(),
        'email' => 'marie' . time() . '@example.com',
        'password' => password_hash('password123', PASSWORD_BCRYPT),
        'bio' => 'Graphiste passionnée par le design moderne et épuré',
        'avatar' => 'https://i.pravatar.cc/150?img=1',
        'rating' => 4.8,
        'phone' => '0612345678'
    ],
    [
        'firstname' => 'Jean',
        'lastname' => 'Martin',
        'pseudo' => 'jean_dev_' . time(),
        'email' => 'jean' . time() . '@example.com',
        'password' => password_hash('password123', PASSWORD_BCRYPT),
        'bio' => 'Développeur web spécialisé en React et Node.js',
        'avatar' => 'https://i.pravatar.cc/150?img=2',
        'rating' => 4.9,
        'phone' => '0623456789'
    ],
    [
        'firstname' => 'Sophie',
        'lastname' => 'Bernard',
        'pseudo' => 'sophie_photo_' . time(),
        'email' => 'sophie' . time() . '@example.com',
        'password' => password_hash('password123', PASSWORD_BCRYPT),
        'bio' => 'Photographe professionnelle - Mariage, Événements, Portraits',
        'avatar' => 'https://i.pravatar.cc/150?img=3',
        'rating' => 4.7,
        'phone' => '0634567890'
    ],
    [
        'firstname' => 'Pierre',
        'lastname' => 'Rousseau',
        'pseudo' => 'pierre_marketing_' . time(),
        'email' => 'pierre' . time() . '@example.com',
        'password' => password_hash('password123', PASSWORD_BCRYPT),
        'bio' => 'Consultant marketing digital - SEO, SEM, Réseaux sociaux',
        'avatar' => 'https://i.pravatar.cc/150?img=4',
        'rating' => 4.6,
        'phone' => '0645678901'
    ],
    [
        'firstname' => 'Isabelle',
        'lastname' => 'Laurent',
        'pseudo' => 'isabelle_video_' . time(),
        'email' => 'isabelle' . time() . '@example.com',
        'password' => password_hash('password123', PASSWORD_BCRYPT),
        'bio' => 'Réalisatrice vidéo - Publicités, Tutoriels, Montage professionnel',
        'avatar' => 'https://i.pravatar.cc/150?img=5',
        'rating' => 4.5,
        'phone' => '0656789012'
    ],
    [
        'firstname' => 'Thomas',
        'lastname' => 'Leclerc',
        'pseudo' => 'thomas_ux_' . time(),
        'email' => 'thomas' . time() . '@example.com',
        'password' => password_hash('password123', PASSWORD_BCRYPT),
        'bio' => 'Designer UX/UI avec 8 ans d\'expérience',
        'avatar' => 'https://i.pravatar.cc/150?img=6',
        'rating' => 4.8,
        'phone' => '0667890123'
    ],
    [
        'firstname' => 'Amélie',
        'lastname' => 'Girard',
        'pseudo' => 'amelie_copywriter_' . time(),
        'email' => 'amelie' . time() . '@example.com',
        'password' => password_hash('password123', PASSWORD_BCRYPT),
        'bio' => 'Copywriter spécialisée en e-commerce',
        'avatar' => 'https://i.pravatar.cc/150?img=7',
        'rating' => 4.9,
        'phone' => '0678901234'
    ],
];

// Insérer les utilisateurs
$userMap = [];
foreach ($users as $user) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO users (firstname, lastname, pseudo, email, password, bio, avatar, rating, phone, email_verified_at, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $user['firstname'],
            $user['lastname'],
            $user['pseudo'],
            $user['email'],
            $user['password'],
            $user['bio'],
            $user['avatar'],
            $user['rating'],
            $user['phone']
        ]);
        $userMap[$user['pseudo']] = $pdo->lastInsertId();
        echo "✅ Utilisateur créé: {$user['pseudo']}\n";
    } catch (PDOException $e) {
        echo "❌ Erreur: " . $e->getMessage() . "\n";
    }
}

// 2. Récupérer les catégories
$stmt = $pdo->query("SELECT id, name FROM categories LIMIT 10");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($categories)) {
    echo "❌ Aucune catégorie trouvée. Exiting.\n";
    exit;
}

// 3. Créer beaucoup de services
$services = [
    // Design
    ['marie_design_' . time(), 0, 'Logo Minimal Design', 'Logo épuré et moderne', 200, 5],
    ['marie_design_' . time(), 0, 'Branding Complet', 'Identité visuelle complète', 800, 14],
    ['marie_design_' . time(), 0, 'Flyer Design Premium', 'Création de flyer professionnel', 150, 3],
    ['marie_design_' . time(), 0, 'Packaging Design', 'Design d\'emballage produit', 600, 10],
    
    // Dev Web
    ['jean_dev_' . time(), 1, 'Site Vitrine React', 'Site moderne en React', 1000, 12],
    ['jean_dev_' . time(), 1, 'App Web Full Stack', 'Application complète', 2500, 21],
    ['jean_dev_' . time(), 1, 'API REST Custom', 'API personnalisée', 800, 8],
    ['jean_dev_' . time(), 1, 'Migration CMS', 'Migration site WordPress', 500, 7],
    
    // Photo
    ['sophie_photo_' . time(), 2, 'Séance Portrait', 'Photos de profil professionnel', 300, 2],
    ['sophie_photo_' . time(), 2, 'Mariage Complet', 'Couverture mariage', 2800, 14],
    ['sophie_photo_' . time(), 2, 'Photoshoot Produits', 'Photos de produits e-commerce', 400, 3],
    ['sophie_photo_' . time(), 2, 'Événement Corporatif', 'Couverture événement', 900, 5],
    
    // Marketing
    ['pierre_marketing_' . time(), 3, 'Audit SEO Complet', 'Analyse SEO site', 700, 7],
    ['pierre_marketing_' . time(), 3, 'Google Ads Expert', 'Gestion campagnes', 350, 1],
    ['pierre_marketing_' . time(), 3, 'Social Media Plan', 'Stratégie réseaux', 500, 5],
    ['pierre_marketing_' . time(), 3, 'Email Marketing', 'Campagne email', 250, 3],
    
    // Vidéo
    ['isabelle_video_' . time(), 4, 'Montage Vidéo Pro', 'Montage haute qualité', 500, 5],
    ['isabelle_video_' . time(), 4, 'Pub Vidéo', 'Production publicité', 1800, 14],
    ['isabelle_video_' . time(), 4, 'Animation 2D', 'Animation explainer', 1000, 10],
    ['isabelle_video_' . time(), 4, 'Drone Aérien', 'Filmage aérien', 700, 6],
    
    // UX/UI
    ['thomas_ux_' . time(), 0, 'Wireframe & Prototype', 'Design UX app mobile', 600, 8],
    ['thomas_ux_' . time(), 0, 'Design System', 'Création design system', 1200, 12],
    
    // Copywriting
    ['amelie_copywriter_' . time(), 3, 'Copy Landing Page', 'Copywriting page de vente', 300, 4],
    ['amelie_copywriter_' . time(), 3, 'Product Description', 'Descriptions produits', 250, 3],
];

// Insérer les services
$createdCount = 0;
foreach ($services as [$pseudo_key, $cat_idx, $title, $desc, $price, $days]) {
    // Trouver le vrai pseudo qui a été créé
    $pseudo_match = null;
    foreach (array_keys($userMap) as $p) {
        if (strpos($p, str_replace('_' . time(), '', $pseudo_key)) === 0) {
            $pseudo_match = $p;
            break;
        }
    }
    
    if (!$pseudo_match) continue;
    
    $user_id = $userMap[$pseudo_match];
    $category_id = $categories[$cat_idx % count($categories)]['id'];
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO services (user_id, category_id, title, description, price, delivery_days, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())
        ");
        $stmt->execute([$user_id, $category_id, $title, $desc, $price, $days]);
        $createdCount++;
        echo "✅ Service: $title ($price€)\n";
    } catch (PDOException $e) {
        echo "❌ Erreur: " . $e->getMessage() . "\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🎉 DONNÉES DE TEST AJOUTÉES AVEC SUCCÈS!\n";
echo str_repeat("=", 60) . "\n";
echo "✅ Utilisateurs créés: " . count($userMap) . "\n";
echo "✅ Services créés: " . $createdCount . "\n";
echo "\n📝 Comptes de test créés:\n";
foreach ($userMap as $pseudo => $id) {
    echo "  • Pseudo: $pseudo\n";
    echo "    Email: voir la BDD (ID: $id)\n";
    echo "    Password: password123\n\n";
}
echo "\n✨ La page Prestataires affichera tous ces services!\n";
?>
