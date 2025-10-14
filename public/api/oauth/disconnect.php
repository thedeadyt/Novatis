<?php
/**
 * API pour déconnecter un compte OAuth d'un utilisateur
 * Permet de délier un provider (Google, Microsoft, GitHub) du compte utilisateur
 */

require_once __DIR__ . '/../../../config/config.php';

// Vérifier que l'utilisateur est connecté
isUserLoggedIn(true);

// Récupérer l'utilisateur actuel
$user = getCurrentUser();
$userId = $user['id'];

// Récupérer le provider à déconnecter
$provider = $_POST['provider'] ?? $_GET['provider'] ?? '';

// Providers autorisés
$allowedProviders = ['google', 'microsoft', 'github'];

// Validation
if (empty($provider) || !in_array($provider, $allowedProviders)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Provider invalide ou non spécifié'
    ]);
    exit;
}

try {
    $pdo = getDBConnection();

    // Vérifier que l'utilisateur a bien cette connexion OAuth
    $stmt = $pdo->prepare("
        SELECT id FROM oauth_connections
        WHERE user_id = ? AND provider = ?
    ");
    $stmt->execute([$userId, $provider]);
    $connection = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$connection) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Aucune connexion ' . ucfirst($provider) . ' trouvée pour votre compte'
        ]);
        exit;
    }

    // Vérifier que l'utilisateur a un mot de passe ou au moins une autre connexion OAuth
    // pour éviter qu'il se retrouve bloqué sans moyen de se connecter
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userPassword = $stmt->fetch(PDO::FETCH_ASSOC)['password'];

    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM oauth_connections WHERE user_id = ?");
    $stmt->execute([$userId]);
    $oauthCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Si pas de mot de passe et qu'une seule connexion OAuth, refuser la déconnexion
    $hasPassword = !empty($userPassword) && $userPassword !== 'oauth_user';

    if (!$hasPassword && $oauthCount <= 1) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Impossible de supprimer cette connexion. Vous devez définir un mot de passe ou avoir au moins une autre méthode de connexion pour éviter de perdre l\'accès à votre compte.'
        ]);
        exit;
    }

    // Supprimer la connexion OAuth
    $stmt = $pdo->prepare("
        DELETE FROM oauth_connections
        WHERE user_id = ? AND provider = ?
    ");
    $stmt->execute([$userId, $provider]);

    // Succès
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Connexion ' . ucfirst($provider) . ' déconnectée avec succès'
    ]);

} catch (PDOException $e) {
    error_log("OAuth Disconnect Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la déconnexion du compte OAuth'
    ]);
}
