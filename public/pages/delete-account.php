<?php
require_once __DIR__ . '/../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/pages/Autentification.php');
    exit;
}

$user = $_SESSION['user'];

// Connexion à la base de données
try {
    $host = 'mysql-alex2pro.alwaysdata.net';
    $db   = 'alex2pro_movatis';
    $user_db = 'alex2pro_alex';
    $pass = 'Alex.2005';
    $charset = 'utf8mb4';

    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user_db, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Erreur de connexion à la base de données';
    header('Location: ' . BASE_URL . '/pages/Parametres?section=account');
    exit;
}

try {
    // Supprimer toutes les données liées à l'utilisateur
    $pdo->beginTransaction();

    // Supprimer les préférences
    $stmt = $pdo->prepare("DELETE FROM user_preferences WHERE user_id = ?");
    $stmt->execute([$user['id']]);

    // Supprimer les commandes (si table existe)
    $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("DELETE FROM orders WHERE user_id = ?");
        $stmt->execute([$user['id']]);
    }

    // Supprimer les avis (si table existe)
    $stmt = $pdo->query("SHOW TABLES LIKE 'reviews'");
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE user_id = ?");
        $stmt->execute([$user['id']]);
    }

    // Supprimer les notifications (si table existe)
    $stmt = $pdo->query("SHOW TABLES LIKE 'notifications'");
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
        $stmt->execute([$user['id']]);
    }

    // Supprimer l'utilisateur
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);

    $pdo->commit();

    // Détruire la session
    session_destroy();

    // Rediriger vers la page d'accueil avec un message
    header('Location: ' . BASE_URL . '/?message=account_deleted');

} catch (Exception $e) {
    $pdo->rollback();
    $_SESSION['error_message'] = 'Erreur lors de la suppression du compte : ' . $e->getMessage();
    header('Location: ' . BASE_URL . '/pages/Parametres?section=account');
}
?>