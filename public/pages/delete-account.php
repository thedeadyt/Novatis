<?php
require_once __DIR__ . '/../../config/Config.php';

// Vérifie si l'utilisateur est connecté
isUserLoggedIn(true);

$user = getCurrentUser();
$pdo = getDBConnection();

try {
    // Supprimer toutes les données liées à l'utilisateur
    $pdo->beginTransaction();

    // Supprimer les préférences
    $stmt = $pdo->prepare("DELETE FROM user_preferences WHERE user_id = ?");
    $stmt->execute([$user['id']]);

    // Supprimer les paramètres de sécurité
    $stmt = $pdo->prepare("DELETE FROM user_security WHERE user_id = ?");
    $stmt->execute([$user['id']]);

    // Supprimer les paramètres de confidentialité
    $stmt = $pdo->prepare("DELETE FROM user_privacy WHERE user_id = ?");
    $stmt->execute([$user['id']]);

    // Supprimer les services de l'utilisateur (cascade supprimera les commandes liées)
    $stmt = $pdo->prepare("DELETE FROM services WHERE user_id = ?");
    $stmt->execute([$user['id']]);

    // Supprimer les commandes comme acheteur
    $stmt = $pdo->prepare("DELETE FROM orders WHERE buyer_id = ?");
    $stmt->execute([$user['id']]);

    // Supprimer les messages
    $stmt = $pdo->prepare("DELETE FROM messages WHERE sender_id = ?");
    $stmt->execute([$user['id']]);

    // Supprimer les notifications
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
    $stmt->execute([$user['id']]);

    // Supprimer le portfolio
    $stmt = $pdo->prepare("DELETE FROM portfolio WHERE user_id = ?");
    $stmt->execute([$user['id']]);

    // Supprimer l'utilisateur (cela déclenchera les cascades pour les autres tables)
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);

    $pdo->commit();

    // Détruire complètement la session
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();

    // Rediriger vers la page d'accueil
    header('Location: ' . BASE_URL . '/index.php');
    exit;

} catch (Exception $e) {
    $pdo->rollback();
    $_SESSION['error_message'] = 'Erreur lors de la suppression du compte : ' . $e->getMessage();
    header('Location: ' . BASE_URL . '/Parametres?section=account');
}
?>