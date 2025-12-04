<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=novatis;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Catégories à ajouter
    $categories = [
        ['name' => 'Développement Web', 'description' => 'Création de sites web, applications web et solutions digitales sur mesure'],
        ['name' => 'Design & UX/UI', 'description' => 'Conception d\'interfaces utilisateur, design graphique et expérience utilisateur'],
        ['name' => 'Applications Mobile', 'description' => 'Développement d\'applications mobiles iOS, Android et cross-platform'],
        ['name' => 'SEO & Marketing Digital', 'description' => 'Référencement naturel, publicité en ligne et stratégie marketing digital'],
        ['name' => 'API & Intégrations', 'description' => 'Développement d\'APIs, intégrations de services tiers et webhooks'],
        ['name' => 'Maintenance & Support', 'description' => 'Maintenance technique, support client et optimisation de performances'],
        ['name' => 'E-commerce', 'description' => 'Solutions de vente en ligne, boutiques et marketplaces'],
        ['name' => 'Cloud & DevOps', 'description' => 'Déploiement cloud, containerisation et automatisation CI/CD'],
        ['name' => 'Intelligence Artificielle', 'description' => 'Solutions IA, machine learning, chatbots et analyse de données'],
        ['name' => 'Blockchain & Web3', 'description' => 'Smart contracts, NFT, DeFi et applications décentralisées']
    ];

    $added = 0;
    $existing = 0;

    foreach ($categories as $category) {
        // Vérifier si la catégorie existe déjà
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt->execute([$category['name']]);

        if ($stmt->fetch()) {
            echo "⚠️  Catégorie '{$category['name']}' existe déjà\n";
            $existing++;
        } else {
            // Insérer la nouvelle catégorie
            $stmt = $pdo->prepare("INSERT INTO categories (name, description, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$category['name'], $category['description']]);
            echo "✅ Catégorie '{$category['name']}' ajoutée (ID: {$pdo->lastInsertId()})\n";
            $added++;
        }
    }

    echo "\n📊 Résumé:\n";
    echo "  - Catégories ajoutées: $added\n";
    echo "  - Catégories existantes: $existing\n";

    // Afficher toutes les catégories
    echo "\n📋 Liste complète des catégories:\n";
    $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY id");
    $allCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($allCategories as $cat) {
        echo "  {$cat['id']}. {$cat['name']}\n";
    }

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
?>
