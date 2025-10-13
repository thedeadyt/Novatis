<?php
/**
 * Script pour exécuter la migration de la table oauth_connections
 * À exécuter une seule fois
 */

require_once __DIR__ . '/config/config.php';

try {
    $pdo = getDBConnection();

    echo "Connexion à la base de données réussie.\n\n";

    // Lire le fichier SQL
    $sql = file_get_contents(__DIR__ . '/migrations/003_create_oauth_connections.sql');

    if ($sql === false) {
        die("Erreur: Impossible de lire le fichier de migration.\n");
    }

    echo "Exécution de la migration oauth_connections...\n";

    // Exécuter la migration
    $pdo->exec($sql);

    echo "✓ Migration exécutée avec succès!\n";
    echo "La table 'oauth_connections' a été créée.\n\n";

    // Vérifier que la table existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'oauth_connections'");
    $result = $stmt->fetch();

    if ($result) {
        echo "✓ Vérification: La table oauth_connections existe bien dans la base de données.\n";

        // Afficher la structure de la table
        echo "\nStructure de la table:\n";
        $stmt = $pdo->query("DESCRIBE oauth_connections");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($columns as $column) {
            echo "  - {$column['Field']} ({$column['Type']})\n";
        }
    } else {
        echo "⚠ Attention: La table oauth_connections n'a pas été trouvée.\n";
    }

} catch (PDOException $e) {
    echo "❌ Erreur lors de la migration: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✅ Migration terminée avec succès!\n";
echo "\nVous pouvez maintenant utiliser l'authentification OAuth avec Google, Microsoft et GitHub.\n";
echo "\nN'oubliez pas de:\n";
echo "1. Copier config/oauth.local.example.php en config/oauth.local.php\n";
echo "2. Configurer vos clés API dans config/oauth.local.php\n";
echo "3. Créer les applications OAuth sur Google, Microsoft et GitHub\n";
