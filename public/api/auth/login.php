<?php
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

try {
    // Récupérer les données JSON
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        throw new Exception('Données invalides');
    }

    // Valider les données requises
    $emailOrPseudo = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if (empty($emailOrPseudo) || empty($password)) {
        throw new Exception('Email/pseudo et mot de passe requis');
    }

    // Déterminer si c'est un email ou un pseudo
    $isEmail = filter_var($emailOrPseudo, FILTER_VALIDATE_EMAIL);

    if ($isEmail) {
        // Recherche par email
        $stmt = $pdo->prepare("SELECT id, name, pseudo, email, password, role, avatar, rating FROM users WHERE email = ?");
    } else {
        // Recherche par pseudo
        $stmt = $pdo->prepare("SELECT id, name, pseudo, email, password, role, avatar, rating FROM users WHERE pseudo = ?");
    }

    $stmt->execute([$emailOrPseudo]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('Utilisateur introuvable');
    }

    // Vérifier le mot de passe
    if (!password_verify($password, $user['password'])) {
        throw new Exception('Mot de passe incorrect');
    }

    // Supprimer le mot de passe des données de session
    unset($user['password']);

    // Créer la session
    $_SESSION['user'] = $user;

    echo json_encode([
        'success' => true,
        'message' => 'Connexion réussie',
        'user' => $user
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>