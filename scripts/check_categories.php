<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=novatis;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query('SELECT * FROM categories ORDER BY name');
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Catégories existantes:\n";
    foreach ($categories as $cat) {
        echo "ID: {$cat['id']}, Nom: {$cat['name']}\n";
    }
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
?>
