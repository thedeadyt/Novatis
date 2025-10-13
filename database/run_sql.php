<?php
echo "🚀 Installation de Novatis - Catégories et Services\n";
echo "==================================================\n\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=novatis;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "📂 Lecture du fichier SQL...\n";
    $sql = file_get_contents(__DIR__ . '/setup_complete.sql');

    echo "⚙️  Exécution du script SQL...\n";
    $pdo->exec($sql);

    echo "\n✅ Script exécuté avec succès!\n\n";

    // Compter les catégories
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
    $categoriesCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "📊 Total de catégories: {$categoriesCount['count']}\n";

    // Compter les services
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM predefined_services");
    $servicesCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "📊 Total de services prédéfinis: {$servicesCount['count']}\n\n";

    // Afficher les catégories avec le nombre de services
    echo "📋 Répartition des services par catégorie:\n";
    echo "-------------------------------------------\n";
    $stmt = $pdo->query("
        SELECT c.name, c.icon, COUNT(ps.id) as nb_services
        FROM categories c
        LEFT JOIN predefined_services ps ON ps.category_id = c.id
        GROUP BY c.id
        ORDER BY c.id
    ");
    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($stats as $stat) {
        $icon = $stat['icon'] ?? '📦';
        echo sprintf("  %s %-30s : %2d services\n", $icon, $stat['name'], $stat['nb_services']);
    }

    echo "\n✨ Installation terminée avec succès!\n";
    echo "Vous pouvez maintenant utiliser les services prédéfinis dans le Dashboard.\n";

} catch (Exception $e) {
    echo "\n❌ Erreur: " . $e->getMessage() . "\n";
    echo "📍 Assurez-vous que MySQL est démarré et que la base 'novatis' existe.\n";
}
?>
