<?php
/**
 * Callback OAuth - Traite la réponse du fournisseur OAuth
 * Échange le code d'autorisation contre un token d'accès
 * Récupère les informations utilisateur et crée/connecte le compte
 */

require_once __DIR__ . '/../../../config/config.php';

// Récupérer les paramètres de callback
$code = $_GET['code'] ?? '';
$state = $_GET['state'] ?? '';
$error = $_GET['error'] ?? '';

// Vérifier s'il y a une erreur
if ($error) {
    showError("Erreur d'autorisation: " . htmlspecialchars($error));
}

// Vérifier le state pour la sécurité CSRF
if (empty($state) || empty($_SESSION['oauth_state']) || $state !== $_SESSION['oauth_state']) {
    showError('État OAuth invalide. Tentative de connexion annulée pour des raisons de sécurité.');
}

// Récupérer le provider
$provider = $_SESSION['oauth_provider'] ?? '';
if (empty($provider)) {
    showError('Provider OAuth non trouvé dans la session');
}

// Charger la configuration OAuth
$oauthConfig = require __DIR__ . '/../../../config/oauth.php';
$config = $oauthConfig[$provider];

// Échanger le code contre un token d'accès
try {
    $tokenData = exchangeCodeForToken($code, $config);
    $accessToken = $tokenData['access_token'];

    // Récupérer les informations utilisateur
    $userInfo = getUserInfo($accessToken, $config, $provider);

    // Connexion à la base de données
    $pdo = getDBConnection();

    // Vérifier si l'utilisateur existe déjà avec cet email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$userInfo['email']]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        // L'utilisateur existe déjà - on lie le compte OAuth
        $userId = $existingUser['id'];
        linkOAuthAccount($pdo, $userId, $provider, $userInfo, $tokenData);

        // Connecter l'utilisateur
        $_SESSION['user'] = $existingUser;
        $_SESSION['user_id'] = $userId;

        showSuccess("Connexion réussie avec " . ucfirst($provider) . " !");
    } else {
        // Créer un nouveau compte utilisateur
        $userId = createUserFromOAuth($pdo, $userInfo, $provider);

        // Lier le compte OAuth
        linkOAuthAccount($pdo, $userId, $provider, $userInfo, $tokenData);

        // Récupérer les infos complètes de l'utilisateur
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $newUser = $stmt->fetch(PDO::FETCH_ASSOC);

        // Connecter l'utilisateur
        $_SESSION['user'] = $newUser;
        $_SESSION['user_id'] = $userId;

        showSuccess("Compte créé avec succès via " . ucfirst($provider) . " !");
    }

} catch (Exception $e) {
    error_log("OAuth Error: " . $e->getMessage());
    showError("Erreur lors de l'authentification: " . $e->getMessage());
}

// Nettoyer la session OAuth
unset($_SESSION['oauth_state']);
unset($_SESSION['oauth_provider']);

/**
 * Échange le code d'autorisation contre un token d'accès
 */
function exchangeCodeForToken($code, $config) {
    $postData = [
        'client_id' => $config['client_id'],
        'client_secret' => $config['client_secret'],
        'code' => $code,
        'redirect_uri' => $config['redirect_uri'],
        'grant_type' => 'authorization_code'
    ];

    $ch = curl_init($config['token_url']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/x-www-form-urlencoded'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception("Erreur lors de l'échange du code: HTTP $httpCode - $response");
    }

    $data = json_decode($response, true);

    if (!isset($data['access_token'])) {
        throw new Exception("Token d'accès non reçu");
    }

    return $data;
}

/**
 * Récupère les informations utilisateur du provider OAuth
 */
function getUserInfo($accessToken, $config, $provider) {
    $ch = curl_init($config['userinfo_url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Accept: application/json'
    ]);

    // User-Agent requis pour GitHub
    if ($provider === 'github') {
        curl_setopt($ch, CURLOPT_USERAGENT, 'Novatis-OAuth-App');
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception("Erreur lors de la récupération des infos utilisateur: HTTP $httpCode");
    }

    $data = json_decode($response, true);

    // Normaliser les données selon le provider
    $userInfo = [];

    switch ($provider) {
        case 'google':
            $userInfo = [
                'provider_user_id' => $data['id'],
                'email' => $data['email'],
                'name' => $data['name'] ?? '',
                'firstname' => $data['given_name'] ?? '',
                'lastname' => $data['family_name'] ?? '',
                'avatar_url' => $data['picture'] ?? ''
            ];
            break;

        case 'microsoft':
            $userInfo = [
                'provider_user_id' => $data['id'],
                'email' => $data['mail'] ?? $data['userPrincipalName'] ?? '',
                'name' => $data['displayName'] ?? '',
                'firstname' => $data['givenName'] ?? '',
                'lastname' => $data['surname'] ?? '',
                'avatar_url' => ''
            ];
            break;

        case 'github':
            // GitHub peut ne pas retourner l'email public, il faut faire une requête supplémentaire
            $email = $data['email'];
            if (empty($email)) {
                $email = getGitHubEmail($accessToken, $config);
            }

            $nameParts = explode(' ', $data['name'] ?? '', 2);
            $userInfo = [
                'provider_user_id' => (string)$data['id'],
                'email' => $email,
                'name' => $data['name'] ?? $data['login'],
                'firstname' => $nameParts[0] ?? $data['login'],
                'lastname' => $nameParts[1] ?? '',
                'avatar_url' => $data['avatar_url'] ?? ''
            ];
            break;
    }

    return $userInfo;
}

/**
 * Récupère l'email principal de GitHub (si non public)
 */
function getGitHubEmail($accessToken, $config) {
    $ch = curl_init($config['emails_url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Novatis-OAuth-App');

    $response = curl_exec($ch);
    curl_close($ch);

    $emails = json_decode($response, true);

    // Trouver l'email principal et vérifié
    foreach ($emails as $emailData) {
        if ($emailData['primary'] && $emailData['verified']) {
            return $emailData['email'];
        }
    }

    // Si pas d'email principal, prendre le premier vérifié
    foreach ($emails as $emailData) {
        if ($emailData['verified']) {
            return $emailData['email'];
        }
    }

    throw new Exception("Aucun email vérifié trouvé sur le compte GitHub");
}

/**
 * Crée un nouvel utilisateur à partir des infos OAuth
 */
function createUserFromOAuth($pdo, $userInfo, $provider) {
    // Générer un pseudo unique
    $basePseudo = strtolower(str_replace(' ', '', $userInfo['firstname']));
    $pseudo = $basePseudo;
    $counter = 1;

    while (true) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE pseudo = ?");
        $stmt->execute([$pseudo]);
        if (!$stmt->fetch()) {
            break;
        }
        $pseudo = $basePseudo . $counter;
        $counter++;
    }

    // Créer l'utilisateur
    $stmt = $pdo->prepare("
        INSERT INTO users (firstname, lastname, pseudo, email, password, avatar, is_verified, role, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 1, 'user', NOW())
    ");

    // Pas de mot de passe pour les comptes OAuth (on met un hash aléatoire)
    $randomPassword = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);

    $stmt->execute([
        $userInfo['firstname'],
        $userInfo['lastname'],
        $pseudo,
        $userInfo['email'],
        $randomPassword,
        $userInfo['avatar_url']
    ]);

    return $pdo->lastInsertId();
}

/**
 * Lie un compte OAuth à un utilisateur existant
 */
function linkOAuthAccount($pdo, $userId, $provider, $userInfo, $tokenData) {
    // Vérifier si la connexion OAuth existe déjà
    $stmt = $pdo->prepare("
        SELECT id FROM oauth_connections
        WHERE user_id = ? AND provider = ?
    ");
    $stmt->execute([$userId, $provider]);

    $expiresAt = null;
    if (isset($tokenData['expires_in'])) {
        $expiresAt = date('Y-m-d H:i:s', time() + $tokenData['expires_in']);
    }

    if ($stmt->fetch()) {
        // Mettre à jour
        $stmt = $pdo->prepare("
            UPDATE oauth_connections
            SET provider_user_id = ?,
                access_token = ?,
                refresh_token = ?,
                token_expires_at = ?,
                email = ?,
                name = ?,
                avatar_url = ?,
                updated_at = NOW()
            WHERE user_id = ? AND provider = ?
        ");

        $stmt->execute([
            $userInfo['provider_user_id'],
            $tokenData['access_token'],
            $tokenData['refresh_token'] ?? null,
            $expiresAt,
            $userInfo['email'],
            $userInfo['name'],
            $userInfo['avatar_url'],
            $userId,
            $provider
        ]);
    } else {
        // Créer
        $stmt = $pdo->prepare("
            INSERT INTO oauth_connections
            (user_id, provider, provider_user_id, access_token, refresh_token, token_expires_at, email, name, avatar_url)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $userId,
            $provider,
            $userInfo['provider_user_id'],
            $tokenData['access_token'],
            $tokenData['refresh_token'] ?? null,
            $expiresAt,
            $userInfo['email'],
            $userInfo['name'],
            $userInfo['avatar_url']
        ]);
    }
}

/**
 * Affiche un message de succès et ferme le popup
 */
function showSuccess($message) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Connexion réussie</title>
    </head>
    <body>
        <script>
            if (window.opener) {
                window.opener.postMessage({
                    type: 'oauth_success',
                    message: '<?= addslashes($message) ?>'
                }, window.location.origin);
                window.close();
            } else {
                alert('<?= addslashes($message) ?>');
                window.location.href = '<?= BASE_URL ?>/dashboard';
            }
        </script>
    </body>
    </html>
    <?php
    exit;
}

/**
 * Affiche un message d'erreur
 */
function showError($message) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Erreur OAuth</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                padding: 20px;
                background: #f5f5f5;
            }
            .error-box {
                background: white;
                padding: 20px;
                border-radius: 8px;
                border-left: 4px solid #ef4444;
                max-width: 500px;
                margin: 50px auto;
            }
            h2 { color: #ef4444; margin-top: 0; }
            button {
                background: #ef4444;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 4px;
                cursor: pointer;
                margin-top: 15px;
            }
            button:hover { background: #dc2626; }
        </style>
    </head>
    <body>
        <div class="error-box">
            <h2>Erreur d'authentification</h2>
            <p><?= htmlspecialchars($message) ?></p>
            <button onclick="closeWindow()">Fermer</button>
        </div>
        <script>
            function closeWindow() {
                if (window.opener) {
                    window.opener.postMessage({
                        type: 'oauth_error',
                        message: '<?= addslashes($message) ?>'
                    }, window.location.origin);
                    window.close();
                } else {
                    window.location.href = '<?= BASE_URL ?>/pages/Autentification.php';
                }
            }
        </script>
    </body>
    </html>
    <?php
    exit;
}
