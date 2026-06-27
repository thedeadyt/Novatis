<?php
/**
 * Script de débogage OAuth
 * À utiliser pour diagnostiquer les problèmes de connexion OAuth
 */

require_once __DIR__ . '/config/config.php';

echo "<!DOCTYPE html>";
echo "<html><head><title>Débogage OAuth</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .section { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    h2 { color: #B41200; margin-top: 0; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background: #f9f9f9; font-weight: bold; }
    .success { color: #22c55e; }
    .error { color: #ef4444; }
    .warning { color: #f59e0b; }
    .btn { background: #B41200; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
    .btn:hover { background: #8B0E00; }
</style>";
echo "</head><body>";

echo "<h1>🔍 Débogage OAuth - Novatis</h1>";

// Section 1 : État de la session
echo "<div class='section'>";
echo "<h2>📋 État de la session</h2>";
if (isset($_SESSION['user_id'])) {
    echo "<p class='success'>✅ Session active - User ID: " . htmlspecialchars($_SESSION['user_id']) . "</p>";
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
} else {
    echo "<p class='warning'>⚠️ Aucune session active</p>";
}
echo "</div>";

// Section 2 : Connexion BDD et données
try {
    $pdo = getDBConnection();
    echo "<div class='section'>";
    echo "<h2>💾 État de la base de données</h2>";
    echo "<p class='success'>✅ Connexion à la base de données réussie</p>";

    // Compter les utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<p>Nombre total d'utilisateurs: <strong>{$userCount}</strong></p>";

    // Compter les connexions OAuth
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM oauth_connections");
    $oauthCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<p>Nombre total de connexions OAuth: <strong>{$oauthCount}</strong></p>";

    // Liste des connexions OAuth
    echo "<h3>Connexions OAuth existantes</h3>";
    $stmt = $pdo->query("
        SELECT oc.id, oc.user_id, oc.provider, oc.email, u.pseudo, u.email as user_email, oc.created_at
        FROM oauth_connections oc
        LEFT JOIN users u ON oc.user_id = u.id
        ORDER BY oc.created_at DESC
        LIMIT 20
    ");
    $connections = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($connections) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>User ID</th><th>Pseudo</th><th>Provider</th><th>OAuth Email</th><th>User Email</th><th>Date</th></tr>";
        foreach ($connections as $conn) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($conn['id']) . "</td>";
            echo "<td>" . htmlspecialchars($conn['user_id']) . "</td>";
            echo "<td>" . htmlspecialchars($conn['pseudo']) . "</td>";
            echo "<td><strong>" . htmlspecialchars($conn['provider']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($conn['email']) . "</td>";
            echo "<td>" . htmlspecialchars($conn['user_email']) . "</td>";
            echo "<td>" . htmlspecialchars($conn['created_at']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ Aucune connexion OAuth trouvée</p>";
    }

    // Derniers utilisateurs créés
    echo "<h3>Derniers utilisateurs créés</h3>";
    $stmt = $pdo->query("
        SELECT id, firstname, lastname, pseudo, email, created_at
        FROM users
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($users) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Prénom</th><th>Nom</th><th>Pseudo</th><th>Email</th><th>Date</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
            echo "<td>" . htmlspecialchars($user['firstname']) . "</td>";
            echo "<td>" . htmlspecialchars($user['lastname']) . "</td>";
            echo "<td>" . htmlspecialchars($user['pseudo']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['created_at']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    echo "</div>";

} catch (Exception $e) {
    echo "<div class='section'>";
    echo "<h2>💾 État de la base de données</h2>";
    echo "<p class='error'>❌ Erreur de connexion: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

// Section 3 : Actions
echo "<div class='section'>";
echo "<h2>🔧 Actions de débogage</h2>";
echo "<p><a href='?action=clear_session' class='btn'>Nettoyer la session</a></p>";
echo "<p><a href='?action=test_oauth' class='btn'>Tester OAuth Google</a></p>";
echo "</div>";

// Actions
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'clear_session':
            session_destroy();
            session_start();
            echo "<script>alert('Session nettoyée !'); window.location.href = 'debug_oauth.php';</script>";
            break;
        case 'test_oauth':
            header('Location: ' . BASE_URL . '/api/oauth/authorize.php?provider=google');
            exit;
            break;
    }
}

echo "</body></html>";
?>
