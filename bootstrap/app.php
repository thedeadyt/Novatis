<?php
/**
 * Bootstrap Application
 * Initialize the application and load dependencies
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load helper functions
require_once __DIR__ . '/helpers.php';

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Set error reporting based on environment
if (env('APP_DEBUG', false)) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', storage_path('logs/php-errors.log'));
}

// Set timezone
date_default_timezone_set('Europe/Paris');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => env('SESSION_LIFETIME', 120) * 60,
        'cookie_secure' => env('SESSION_SECURE', false),
        'cookie_httponly' => env('SESSION_HTTP_ONLY', true),
        'cookie_samesite' => 'Lax'
    ]);
}

// Define constants for backward compatibility
define('BASE_URL', rtrim(env('APP_URL'), '/'));
define('DB_HOST', env('DB_HOST'));
define('DB_NAME', env('DB_NAME'));
define('DB_USER', env('DB_USER'));
define('DB_PASS', env('DB_PASS'));

return true;
