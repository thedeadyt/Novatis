<?php
require_once __DIR__ . '/../config/Config.php';

$pdo = getDBConnection();

// 1. Créer des utilisateurs (prestataires) fictifs
$users = [
    [
        'firstname' => 'Marie',
        'lastname' => 'Dupont',
        'pseudo' => 'marie_design',
        'email' => 'marie@example.com',
        'password' => password_hash('password123', PASSWORD_BCRYPT),
        'bio' => 'Graphiste passionnée par le design moderne et épuré',
        'avatar' => 'https://i.pravatar.cc/150?img=1',
        'rating' => 4.8,
        'phone' => '0612345678'
    ],
    [
        'firstname' => 'Jean',
        'lastname' => 'Martin',
        'pseudo' => 'jean_dev',
        'email' => 'jean@example.com',
        'password' => password_hash('password123', PASSWORD_BCRYPT),
        'bio' => 'Développeur web spécialisé en React et Node.js',
        'avatar' => 'https://i.pravatar.cc/150?img=2',
        'rating' => 4.9,
        'phone' => '0623456789'
    ],
    [
        'firstname' => 'Sophie',
        'lastname' => 'Bernard',
        'pseudo' => 'sophie_photo',
        'email' => 'sophie@example.com',
        'password' => password_hash('password123', PASSWORD_BCRYPT),
        'bio' => 'Photographe professionnelle - Mariage, Événements, Portraits',
        'avatar' => 'https://i.pravatar.cc/150?img=3',
        'rating' => 4.7,
        'phone' => '0634567890'
    ],
    [
        'firstname' => 'Pierre',
        'lastname' => 'Rousseau',
        'pseudo' => 'pierre_marketing',
        'email' => 'pierre@example.com',
        'password' => password_hash('password123', PASSWORD_BCRYPT),
        'bio' => 'Consultant marketing digital - SEO, SEM, Réseaux sociaux',
        'avatar' => 'https://i.pravatar.cc/150?img=4',
        'rating' => 4.6,
        'phone' => '0645678901'
    ],
    [
        'firstname' => 'Isabelle',
        'lastname' => 'Laurent',
        'pseudo' => 'isabelle_video',
        'email' => 'isabelle@example.com',
        'password' => password_hash('password123', PASSWORD_BCRYPT),
        'bio' => 'Réalisatrice vidéo - Publicités, Tutoriels, Montage professionnel',
        'avatar' => 'https://i.pravatar.cc/150?img=5',
        'rating' => 4.5,
        'phone' => '0656789012'
    ],
];

// Insérer les utilisateurs
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
        echo "✅ Utilisateur créé: {$user['pseudo']}\n";
    } catch (PDOException $e) {
        echo "⚠️ Utilisateur {$user['pseudo']} existe déjà\n";
    }
}

// 2. Récupérer les IDs des utilisateurs créés
$stmt = $pdo->query("SELECT id, pseudo FROM users WHERE pseudo IN ('marie_design', 'jean_dev', 'sophie_photo', 'pierre_marketing', 'isabelle_video')");
$createdUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$userMap = [];
foreach ($createdUsers as $user) {
    $userMap[$user['pseudo']] = $user['id'];
}

// 3. Récupérer les catégories existantes
$stmt = $pdo->query("SELECT id, name FROM categories LIMIT 10");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($categories)) {
    echo "❌ Aucune catégorie trouvée. Veuillez d'abord créer des catégories.\n";
    exit;
}

// 4. Créer des services fictifs
$services = [
    // Services de Marie (Design)
    [
        'user_id' => $userMap['marie_design'] ?? null,
        'category_id' => $categories[0]['id'] ?? 1,
        'title' => 'Design de Logo Professionnel',
        'description' => 'Création d\'un logo unique et mémorable pour votre entreprise. Livraison en 3 fichiers (PNG, SVG, PDF)',
        'price' => 250,
        'delivery_days' => 5,
        'status' => 'active'
    ],
    [
        'user_id' => $userMap['marie_design'] ?? null,
        'category_id' => $categories[0]['id'] ?? 1,
        'title' => 'Charte Graphique Complète',
        'description' => 'Développement d\'une identité visuelle cohérente pour votre marque',
        'price' => 500,
        'delivery_days' => 10,
        'status' => 'active'
    ],
    
    // Services de Jean (Développement)
    [
        'user_id' => $userMap['jean_dev'] ?? null,
        'category_id' => $categories[1]['id'] ?? 2,
        'title' => 'Développement Site Web React',
        'description' => 'Création d\'un site web moderne et responsive avec React. Responsive design, optimisé SEO',
        'price' => 1200,
        'delivery_days' => 15,
        'status' => 'active'
    ],
    [
        'user_id' => $userMap['jean_dev'] ?? null,
        'category_id' => $categories[1]['id'] ?? 2,
        'title' => 'Application Web Full Stack',
        'description' => 'Application complète avec frontend React et backend Node.js/Express',
        'price' => 2000,
        'delivery_days' => 21,
        'status' => 'active'
    ],
    [
        'user_id' => $userMap['jean_dev'] ?? null,
        'category_id' => $categories[1]['id'] ?? 2,
        'title' => 'Optimisation Performance Web',
        'description' => 'Audit et optimisation des performances de votre site. Réduction du temps de chargement',
        'price' => 400,
        'delivery_days' => 7,
        'status' => 'active'
    ],
    
    // Services de Sophie (Photographie)
    [
        'user_id' => $userMap['sophie_photo'] ?? null,
        'category_id' => $categories[2]['id'] ?? 3,
        'title' => 'Séance Photo Portait Professionnel',
        'description' => 'Séance photo de 1h pour vos portraits professionnels. 20-30 photos retouchées',
        'price' => 350,
        'delivery_days' => 3,
        'status' => 'active'
    ],
    [
        'user_id' => $userMap['sophie_photo'] ?? null,
        'category_id' => $categories[2]['id'] ?? 3,
        'title' => 'Couverture Photographique Événement',
        'description' => 'Couverture complète de votre événement (4-8h). 300-500 photos professionnel retouchées',
        'price' => 800,
        'delivery_days' => 5,
        'status' => 'active'
    ],
    [
        'user_id' => $userMap['sophie_photo'] ?? null,
        'category_id' => $categories[2]['id'] ?? 3,
        'title' => 'Package Mariage Complet',
        'description' => 'Couverture intégrale du mariage + séance engagement + album luxe',
        'price' => 2500,
        'delivery_days' => 14,
        'status' => 'active'
    ],
    
    // Services de Pierre (Marketing)
    [
        'user_id' => $userMap['pierre_marketing'] ?? null,
        'category_id' => $categories[3]['id'] ?? 4,
        'title' => 'Audit SEO et Stratégie',
        'description' => 'Analyse complète de votre site + plan d\'action SEO détaillé',
        'price' => 600,
        'delivery_days' => 7,
        'status' => 'active'
    ],
    [
        'user_id' => $userMap['pierre_marketing'] ?? null,
        'category_id' => $categories[3]['id'] ?? 4,
        'title' => 'Gestion Campagne Google Ads',
        'description' => 'Gestion professionnelle de vos campagnes Google Ads (par mois)',
        'price' => 300,
        'delivery_days' => 1,
        'status' => 'active'
    ],
    [
        'user_id' => $userMap['pierre_marketing'] ?? null,
        'category_id' => $categories[3]['id'] ?? 4,
        'title' => 'Social Media Management',
        'description' => 'Gestion complète de vos réseaux sociaux (contenu, publication, engagement)',
        'price' => 400,
        'delivery_days' => 1,
        'status' => 'active'
    ],
    
    // Services d'Isabelle (Vidéo)
    [
        'user_id' => $userMap['isabelle_video'] ?? null,
        'category_id' => $categories[4]['id'] ?? 5,
        'title' => 'Montage Vidéo Professionnel',
        'description' => 'Montage haute qualité de votre vidéo brute avec effets, musique, transitions',
        'price' => 450,
        'delivery_days' => 5,
        'status' => 'active'
    ],
    [
        'user_id' => $userMap['isabelle_video'] ?? null,
        'category_id' => $categories[4]['id'] ?? 5,
        'title' => 'Création Publicité Vidéo',
        'description' => 'Production complète d\'une vidéo publicitaire (concept, tournage, montage)',
        'price' => 1500,
        'delivery_days' => 14,
        'status' => 'active'
    ],
    [
        'user_id' => $userMap['isabelle_video'] ?? null,
        'category_id' => $categories[4]['id'] ?? 5,
        'title' => 'Motion Design - Animation 2D',
        'description' => 'Animation d\'un concept pour vidéo explicative ou marketing',
        'price' => 800,
        'delivery_days' => 10,
        'status' => 'active'
    ],
];

// Insérer les services
$createdServiceIds = [];
foreach ($services as $service) {
    if ($service['user_id'] === null) {
        continue;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO services (user_id, category_id, title, description, price, delivery_days, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $service['user_id'],
            $service['category_id'],
            $service['title'],
            $service['description'],
            $service['price'],
            $service['delivery_days'],
            $service['status']
        ]);
        $createdServiceIds[] = $pdo->lastInsertId();
        echo "✅ Service créé: {$service['title']}\n";
    } catch (PDOException $e) {
        echo "❌ Erreur lors de la création du service: " . $e->getMessage() . "\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 RÉSUMÉ DES DONNÉES DE TEST\n";
echo str_repeat("=", 50) . "\n";
echo "✅ Utilisateurs créés: " . count($userMap) . "\n";
echo "✅ Services créés: " . count($createdServiceIds) . "\n";
echo "\n📝 Comptes de test:\n";
foreach ($userMap as $pseudo => $id) {
    echo "  - $pseudo / password123\n";
}
echo "\n✅ Les données de test ont été ajoutées avec succès!\n";
?>
