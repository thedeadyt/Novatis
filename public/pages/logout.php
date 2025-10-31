<?php
require_once __DIR__ . '/../../config/Config.php';

// Supprime uniquement les informations de l'utilisateur
unset($_SESSION['user']);

// Redirection vers la page d'accueil après 2 secondes
$redirectUrl = BASE_URL . '/index.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Novatis | Déconnexion</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/logos/Logo_Novatis.png">
    <link rel='stylesheet' type='text/css' media='screen' href='<?= BASE_URL ?>/assets/css/Variables.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='<?= BASE_URL ?>/assets/css/logout.css'>
    <meta http-equiv="refresh" content="2;url=<?= $redirectUrl ?>">
</head>
<body>
    <div class="login-container">
        <h2>Déconnexion</h2>
        <p class="success" style="text-align:center; margin-top:20px;">
            Vous avez été déconnecté avec succès. Redirection vers la page d'Accueil'...
        </p>
    </div>
</body>
</html>
