<?php
// config/config.php

// Exemple : chemin relatif à la racine du serveur web (à adapter si ton projet n'est pas à la racine)
define('BASE_URL', '/Novatis/public');

// Mode développement (affiche les codes de vérification pour tester sans SMS)
// À mettre à false en production
define('DEVELOPMENT_MODE', true);

// Configuration de la base de données
define('DB_HOST', 'mysql-alex2pro.alwaysdata.net');
define('DB_NAME', 'alex2pro_movatis');
define('DB_USER', 'alex2pro_alex');
define('DB_PASS', 'Alex.2005');
define('DB_CHARSET', 'utf8mb4');

// Initialiser la session si nécessaire
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Obtenir une connexion PDO à la base de données
 * @return PDO
 */
function getDBConnection() {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }

    return $pdo;
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

// Pour la rétrocompatibilité, créer la connexion globale $pdo
$pdo = getDBConnection();
?>
