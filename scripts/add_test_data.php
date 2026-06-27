<?php
require_once __DIR__ . '/../config/Config.php';

$pdo = getDBConnection();

// 1. Créer des utilisateurs (prestataires) fictifs
$users = [
    ['Marie', 'Dupont', 'marie_design', 'marie@test.com', 'Graphiste passionnée', 'https://i.pravatar.cc/150?img=1', 4.8],
    ['Jean', 'Martin', 'jean_dev', 'jean@test.com', 'Développeur web React', 'https://i.pravatar.cc/150?img=2', 4.9],
    ['Sophie', 'Bernard', 'sophie_photo', 'sophie@test.com', 'Photographe pro', 'https://i.pravatar.cc/150?img=3', 4.7],
    ['Pierre', 'Rousseau', 'pierre_marketing', 'pierre@test.com', 'Expert marketing digital', 'https://i.pravatar.cc/150?img=4', 4.6],
    ['Isabelle', 'Laurent', 'isabelle_video', 'isabelle@test.com', 'Réalisatrice vidéo', 'https://i.pravatar.cc/150?img=5', 4.5],
    ['Thomas', 'Leclerc', 'thomas_ux', 'thomas@test.com', 'Designer UX/UI', 'https://i.pravatar.cc/150?img=6', 4.8],
    ['Amélie', 'Girard', 'amelie_copy', 'amelie@test.com', 'Copywriter e-commerce', 'https://i.pravatar.cc/150?img=7', 4.9],
];

$userMap = [];
foreach ($users as [$fname, $lname, $pseudo, $email, $bio, $avatar, $rating]) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO users (firstname, lastname, pseudo, email, password, bio, avatar, rating, phone, account_status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, '0612345678', 'active', NOW(), NOW())
            ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)
        ");
        $stmt->execute([$fname, $lname, $pseudo, $email, password_hash('password123', PASSWORD_BCRYPT), $bio, $avatar, $rating]);
        $id = $pdo->lastInsertId();
        if ($id) {
            $userMap[$pseudo] = $id;
            echo "✅ $pseudo créé (ID: $id)\n";
        } else {
            // Récupérer l'ID de l'utilisateur existant
            $s = $pdo->prepare("SELECT id FROM users WHERE pseudo = ?");
            $s->execute([$pseudo]);
            $r = $s->fetch();
            if ($r) {
                $userMap[$pseudo] = $r['id'];
                echo "⚠️ $pseudo existe déjà (ID: " . $r['id'] . ")\n";
            }
        }
    } catch (PDOException $e) {
        echo "❌ Erreur $pseudo: " . $e->getMessage() . "\n";
    }
}

// 2. Récupérer les catégories
$stmt = $pdo->query("SELECT id, name FROM categories LIMIT 10");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($categories)) {
    echo "❌ Aucune catégorie!\n";
    exit;
}

// 3. Services à créer
$servicesData = [
    ['marie_design', 'Logo Minimal Design', 'Logo épuré et moderne en 48h', 200, 5],
    ['marie_design', 'Branding Complet', 'Identité visuelle complète', 800, 14],
    ['marie_design', 'Flyer Design Premium', 'Création de flyer A4/A5', 150, 3],
    ['marie_design', 'Packaging Design', 'Design d\'emballage produit', 600, 10],
    
    ['jean_dev', 'Site Vitrine React', 'Site moderne et responsive', 1000, 12],
    ['jean_dev', 'App Web Full Stack', 'Application complète', 2500, 21],
    ['jean_dev', 'API REST Custom', 'API personnalisée Node.js', 800, 8],
    ['jean_dev', 'Migration CMS', 'Migration site WordPress', 500, 7],
    ['jean_dev', 'Optimization Performance', 'Optimiser vitesse site', 400, 5],
    
    ['sophie_photo', 'Séance Portrait', 'Photos de profil professionnel', 300, 2],
    ['sophie_photo', 'Mariage Complet', 'Couverture mariage complète', 2800, 14],
    ['sophie_photo', 'Photoshoot Produits', 'Photos e-commerce 30 images', 400, 3],
    ['sophie_photo', 'Événement Corporatif', 'Couverture événement', 900, 5],
    ['sophie_photo', 'Séance Famille', 'Photos famille 45min', 350, 2],
    
    ['pierre_marketing', 'Audit SEO Complet', 'Analyse SEO site complet', 700, 7],
    ['pierre_marketing', 'Google Ads Expert', 'Gestion campagnes PPC', 350, 1],
    ['pierre_marketing', 'Social Media Plan', 'Stratégie réseaux sociaux', 500, 5],
    ['pierre_marketing', 'Email Marketing', 'Campagne email professionnelle', 250, 3],
    ['pierre_marketing', 'Content Strategy', 'Stratégie contenu marketing', 600, 7],
    
    ['isabelle_video', 'Montage Vidéo Pro', 'Montage haute qualité 4K', 500, 5],
    ['isabelle_video', 'Pub Vidéo', 'Production publicité 30s-1min', 1800, 14],
    ['isabelle_video', 'Animation 2D', 'Animation explainer vidéo', 1000, 10],
    ['isabelle_video', 'Drone Aérien', 'Filmage aérien avec drone', 700, 6],
    
    ['thomas_ux', 'Wireframe & Prototype', 'Design UX app mobile/web', 600, 8],
    ['thomas_ux', 'Design System', 'Création design system complet', 1200, 12],
    ['thomas_ux', 'Mobile App Design', 'Design interface app mobile', 800, 9],
    
    ['amelie_copy', 'Copy Landing Page', 'Copywriting page de vente', 300, 4],
    ['amelie_copy', 'Product Description', 'Descriptions produits 20 items', 250, 3],
    ['amelie_copy', 'Email Sequences', 'Séquence email marketing', 400, 5],
];

$createdCount = 0;
foreach ($servicesData as [$pseudo, $title, $desc, $price, $days]) {
    if (!isset($userMap[$pseudo])) continue;
    
    $user_id = $userMap[$pseudo];
    $category_id = $categories[rand(0, count($categories)-1)]['id'];
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO services (user_id, category_id, title, description, price, delivery_days, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())
        ");
        $stmt->execute([$user_id, $category_id, $title, $desc, $price, $days]);
        $createdCount++;
        echo "  ✅ [$pseudo] $title ({$price}€)\n";
    } catch (PDOException $e) {
        echo "  ❌ Erreur: " . $e->getMessage() . "\n";
    }
}

// Statistiques finales
echo "\n" . str_repeat("=", 70) . "\n";
echo "🎉 DONNÉES DE TEST AJOUTÉES!\n";
echo str_repeat("=", 70) . "\n";
echo "✅ Utilisateurs: " . count($userMap) . "\n";
echo "✅ Services créés: " . $createdCount . "\n";
echo "\n📝 Comptes de test:\n";
foreach ($userMap as $pseudo => $id) {
    echo "  • $pseudo / password123\n";
}
echo "\n✨ Accédez à: /pages/Prestataires\n";
?>
