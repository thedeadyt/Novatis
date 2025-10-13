<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/EmailService.php';

// Note: Ne pas utiliser requireAuth() ici car c'est la page d'inscription
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = getDBConnection();

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
    $firstname = trim($data['firstname'] ?? '');
    $lastname = trim($data['lastname'] ?? '');
    $pseudo = trim($data['pseudo'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if (empty($firstname) || empty($lastname) || empty($pseudo) || empty($email) || empty($password)) {
        throw new Exception('Tous les champs sont requis');
    }

    // Valider le format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Format d\'email invalide');
    }

    // Valider la longueur du mot de passe
    if (strlen($password) < 6) {
        throw new Exception('Le mot de passe doit contenir au moins 6 caractères');
    }

    // Valider la longueur du pseudo
    if (strlen($pseudo) < 3) {
        throw new Exception('Le pseudo doit contenir au moins 3 caractères');
    }

    // Vérifier si l'email existe déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        throw new Exception('Cet email est déjà utilisé');
    }

    // Vérifier si le pseudo existe déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE pseudo = ?");
    $stmt->execute([$pseudo]);
    if ($stmt->fetch()) {
        throw new Exception('Ce pseudo est déjà utilisé');
    }

    // Hasher le mot de passe
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insérer l'utilisateur
    $stmt = $pdo->prepare("
        INSERT INTO users (firstname, lastname, pseudo, email, password, role, created_at)
        VALUES (?, ?, ?, ?, ?, 'user', NOW())
    ");

    $stmt->execute([$firstname, $lastname, $pseudo, $email, $hashedPassword]);
    $userId = $pdo->lastInsertId();

    // Générer un token de vérification unique
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

    // Insérer le token dans la table de vérification
    $stmt = $pdo->prepare("
        INSERT INTO email_verification_tokens (user_id, token, expires_at)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$userId, $token, $expiresAt]);

    // Envoyer l'email de vérification
    $emailSent = EmailService::sendVerificationEmail($email, $firstname, $lastname, $token);

    if (!$emailSent) {
        error_log("Échec d'envoi de l'email de vérification pour l'utilisateur $userId");
    }

    // Récupérer les données de l'utilisateur créé
    $stmt = $pdo->prepare("SELECT id, firstname, lastname, pseudo, email, role, avatar, rating, is_verified FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Ne PAS créer de session - l'utilisateur doit d'abord vérifier son email
    // $_SESSION['user'] = $user;

    echo json_encode([
        'success' => true,
        'message' => 'Compte créé avec succès. Un email de vérification a été envoyé à ' . $email,
        'email_sent' => $emailSent,
        'verification_required' => true
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>