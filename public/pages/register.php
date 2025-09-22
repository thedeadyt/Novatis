<?php
require_once __DIR__ . '/../../config/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si l'utilisateur est déjà connecté, on le redirige vers le dashboard
if (isset($_SESSION['user'])) {
    header('Location: /dashboard');
    exit;
}

$name = $email = $password = $passwordConfirm = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $passwordConfirm = trim($_POST['password_confirm'] ?? '');

    if ($name === '' || $email === '' || $password === '' || $passwordConfirm === '') {
        $error = 'Veuillez remplir tous les champs.';
    } elseif ($password !== $passwordConfirm) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $error = 'Cet email est déjà utilisé.';
        } else {
            // Ajouter l'utilisateur
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'password' => $hash
            ]);

            // Rediriger vers login
            header('Location: login');
            exit;

        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Novatis | Inscription</title>
    <link rel='stylesheet' type='text/css' media='screen' href='<?= BASE_URL ?>/assets/css/Variables.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='<?= BASE_URL ?>/assets/css/register.css'>
</head>
<body>
    <div class="login-container">
        <h2>Inscription</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <label for="name">Nom</label>
            <input type="text" name="name" id="name" value="<?= htmlspecialchars($name) ?>" required>

            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>" required>

            <label for="password">Mot de passe</label>
            <input type="password" name="password" id="password" required>

            <label for="password_confirm">Confirmer le mot de passe</label>
            <input type="password" name="password_confirm" id="password_confirm" required>

            <button type="submit">S'inscrire</button>
        </form>

        <div style="margin-top: 12px; text-align:center;">
            <span>Déjà un compte ? </span><a href="login">Se connecter</a>
        </div>
    </div>
</body>
</html>
