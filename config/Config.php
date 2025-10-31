<?php
/**
 * Config.php
 * Main configuration file for backward compatibility
 * Uses modern configuration system with .env support
 */

// Bootstrap the application
require_once __DIR__ . '/../bootstrap/app.php';

use App\Database\Connection;

// Legacy constants for backward compatibility
// These are now loaded from .env but defined here for old code
if (!defined('BASE_URL')) {
    define('BASE_URL', rtrim(env('APP_URL'), '/') . '/public');
}

if (!defined('DEVELOPMENT_MODE')) {
    define('DEVELOPMENT_MODE', env('APP_DEBUG', false));
}

if (!defined('DB_HOST')) {
    define('DB_HOST', env('DB_HOST'));
    define('DB_NAME', env('DB_NAME'));
    define('DB_USER', env('DB_USER'));
    define('DB_PASS', env('DB_PASS'));
    define('DB_CHARSET', env('DB_CHARSET', 'utf8mb4'));
}

/**
 * Get database connection (legacy function)
 * Uses new Connection class internally
 *
 * @return PDO
 */
function getDBConnection(): PDO {
    return Connection::getInstance();
}

/**
 * Vérifier si l'utilisateur est connecté
 * @param bool $redirect Si true, redirige vers la page de connexion
 * @return bool
 */
function isUserLoggedIn($redirect = false) {
    $isLoggedIn = isset($_SESSION['user']);

    if (!$isLoggedIn && $redirect) {
        header('Location: ' . BASE_URL . '/pages/Autentification.php');
        exit;
    }

    return $isLoggedIn;
}

/**
 * Vérifier si l'utilisateur est connecté (pour les API)
 * @return void Termine avec une erreur 401 si non connecté
 */
function requireAuth() {
    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Non autorisé']);
        exit;
    }
}

/**
 * Obtenir l'utilisateur connecté
 * @return array|null
 */
function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

/**
 * Vérifier si l'utilisateur est admin
 * @return bool
 */
function isAdmin() {
    $user = getCurrentUser();
    return $user && isset($user['role']) && $user['role'] === 'admin';
}

// Pour la rétrocompatibilité, la variable $pdo sera créée à la demande
// via getDBConnection() plutôt qu'au chargement du fichier
// Cela évite les erreurs 500 si la DB distante n'est pas accessible
// $pdo = getDBConnection(); // Removed: causes 500 errors when DB is unreachable
?>
