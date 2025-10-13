<?php
// config/config.php

// Exemple : chemin relatif à la racine du serveur web (à adapter si ton projet n'est pas à la racine)
define('BASE_URL', '/Novatis/public');

// Mode développement (affiche les codes de vérification pour tester sans SMS)
// À mettre à false en production
define('DEVELOPMENT_MODE', true);


$host = 'mysql-alex2pro.alwaysdata.net';
$db   = 'alex2pro_movatis';
$user = 'alex2pro_alex';
$pass = 'Alex.2005';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Démarrer la session si nécessaire
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
