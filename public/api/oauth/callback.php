<?php
/**
 * Callback OAuth - Traite la réponse du fournisseur OAuth
 * Échange le code d'autorisation contre un token d'accès
 * Récupère les informations utilisateur et crée/connecte le compte
 */

require_once __DIR__ . '/../../../config/Config.php';

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

    // PRIORITÉ 1 : Vérifier si l'utilisateur est DÉJÀ CONNECTÉ ET VALIDE
    // Si oui, on lie simplement le nouveau provider au compte existant
    $isUserLoggedIn = false;
    $currentUserId = null;

    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        // Vérifier que l'utilisateur existe vraiment en BDD
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $validUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($validUser) {
            $isUserLoggedIn = true;
            $currentUserId = $validUser['id'];
        }
    }

    // ÉTAPE 1 : Vérifier si cette connexion OAuth existe déjà en BDD
    $stmt = $pdo->prepare("SELECT user_id FROM oauth_connections WHERE provider = ? AND provider_user_id = ?");
    $stmt->execute([$provider, $userInfo['provider_user_id']]);
    $existingOAuth = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingOAuth) {
        // Cette connexion OAuth existe déjà
        $userId = $existingOAuth['user_id'];

        // CAS A : L'utilisateur est connecté ET c'est le même compte
        if ($isUserLoggedIn && $currentUserId == $userId) {
            // Déjà lié au bon compte, juste mettre à jour les tokens
            linkOAuthAccount($pdo, $userId, $provider, $userInfo, $tokenData);

            // Créer un token temporaire
            $loginToken = bin2hex(random_bytes(32));
            $_SESSION['login_token'] = $loginToken;
            $_SESSION['login_token_user_id'] = $userId;
            $_SESSION['login_token_expires'] = time() + 60;
            session_write_close();
            session_start();

            showSuccess("Connexion réussie avec " . ucfirst($provider) . " !", $loginToken);
        }
        // CAS B : L'utilisateur est connecté MAIS c'est un compte différent
        else if ($isUserLoggedIn && $currentUserId != $userId) {
            // Le compte OAuth est lié à un autre compte Novatis
            showError("Ce compte " . ucfirst($provider) . " est déjà lié à un autre compte Novatis. Déconnectez-vous d'abord pour vous connecter avec ce compte.");
        }
        // CAS C : L'utilisateur N'est PAS connecté → Connexion normale
        else {
            // Mettre à jour les tokens
            linkOAuthAccount($pdo, $userId, $provider, $userInfo, $tokenData);

            // Récupérer les infos de l'utilisateur
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Connecter l'utilisateur dans la session de la popup
            $_SESSION['user'] = $user;
            $_SESSION['user_id'] = $userId;

            // Créer un token temporaire pour transférer la session à la fenêtre principale
            $loginToken = bin2hex(random_bytes(32));
            $_SESSION['login_token'] = $loginToken;
            $_SESSION['login_token_user_id'] = $userId;
            $_SESSION['login_token_expires'] = time() + 60; // 60 secondes

            // Forcer l'écriture de la session
            session_write_close();
            session_start();

            showSuccess("Connexion réussie avec " . ucfirst($provider) . " !", $loginToken);
        }
    }
    // ÉTAPE 2 : La connexion OAuth n'existe pas encore
    else {
        // CAS A : L'utilisateur est déjà connecté → Liaison de compte
        if ($isUserLoggedIn) {
            // Lier le nouveau provider au compte actuel
            $userId = $currentUserId;
            linkOAuthAccount($pdo, $userId, $provider, $userInfo, $tokenData);

            showSuccess("Compte " . ucfirst($provider) . " lié avec succès à votre profil !");
        }
        // CAS B : Vérifier si un utilisateur existe avec cet email
        else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$userInfo['email']]);
            $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingUser) {
                // L'utilisateur existe avec cet email - on lie le compte OAuth et on connecte
                $userId = $existingUser['id'];
                linkOAuthAccount($pdo, $userId, $provider, $userInfo, $tokenData);

                // Connecter l'utilisateur
                $_SESSION['user'] = $existingUser;
                $_SESSION['user_id'] = $userId;

                // Créer un token temporaire
                $loginToken = bin2hex(random_bytes(32));
                $_SESSION['login_token'] = $loginToken;
                $_SESSION['login_token_user_id'] = $userId;
                $_SESSION['login_token_expires'] = time() + 60;

                // Forcer l'écriture de la session
                session_write_close();
                session_start();

                showSuccess("Connexion réussie avec " . ucfirst($provider) . " !", $loginToken);
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

                // Créer un token temporaire
                $loginToken = bin2hex(random_bytes(32));
                $_SESSION['login_token'] = $loginToken;
                $_SESSION['login_token_user_id'] = $userId;
                $_SESSION['login_token_expires'] = time() + 60;

                // Forcer l'écriture de la session
                session_write_close();
                session_start();

                showSuccess("Compte créé avec succès via " . ucfirst($provider) . " !", $loginToken);
            }
        }
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
function showSuccess($message, $loginToken = null) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Connexion réussie</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }
            .success-box {
                text-align: center;
                background: rgba(255, 255, 255, 0.1);
                padding: 40px;
                border-radius: 20px;
                backdrop-filter: blur(10px);
            }
            .checkmark {
                font-size: 60px;
                margin-bottom: 20px;
            }
            h2 { margin: 0 0 10px 0; }
            p { opacity: 0.9; }
        </style>
    </head>
    <body>
        <div class="success-box">
            <div class="checkmark">✓</div>
            <h2>Connexion réussie !</h2>
            <p><?= htmlspecialchars($message) ?></p>
            <p><small>Fermeture automatique...</small></p>
        </div>
        <script>
            console.log('=== OAUTH CALLBACK - Envoi postMessage ===');
            console.log('Login Token: <?= $loginToken ?>');

            if (window.opener && !window.opener.closed) {
                console.log('Envoi de postMessage avec token à la fenêtre parente...');

                // Envoyer le message plusieurs fois pour être sûr
                const sendMessage = function() {
                    window.opener.postMessage({
                        type: 'oauth_success',
                        loginToken: '<?= $loginToken ?>',
                        message: '<?= addslashes($message) ?>'
                    }, '*');
                    console.log('Message envoyé avec token');
                };

                // Envoyer immédiatement
                sendMessage();
                // Envoyer après 100ms
                setTimeout(sendMessage, 100);
                // Envoyer après 300ms
                setTimeout(sendMessage, 300);

                console.log('Messages envoyés, attente de redirection par le parent...');

                // Fermer la popup après 2 secondes
                setTimeout(function() {
                    console.log('Fermeture de la popup...');
                    window.close();
                }, 2000);
            } else {
                console.log('Pas de window.opener, redirection dans cette fenêtre');
                window.location.replace('<?= BASE_URL ?>/pages/Dashboard.php<?= $loginToken ? "?token=" . $loginToken : "" ?>');
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
