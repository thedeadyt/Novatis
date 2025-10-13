<?php
/**
 * Point d'entrée pour l'autorisation OAuth
 * Redirige l'utilisateur vers le fournisseur OAuth (Google, Microsoft, GitHub)
 */

require_once __DIR__ . '/../../../config/config.php';

// Récupérer le provider demandé
$provider = $_GET['provider'] ?? '';

// Providers autorisés
$allowedProviders = ['google', 'microsoft', 'github'];

if (!in_array($provider, $allowedProviders)) {
    die('Provider non supporté');
}

// Charger la configuration OAuth
$oauthConfig = require __DIR__ . '/../../../config/oauth.php';

if (!isset($oauthConfig[$provider])) {
    die('Configuration OAuth non trouvée pour ' . $provider);
}

$config = $oauthConfig[$provider];

// Vérifier que les clés sont configurées
if (empty($config['client_id']) || empty($config['client_secret'])) {
    die('Les clés OAuth ne sont pas configurées. Veuillez créer le fichier config/oauth.local.php avec vos clés API.');
}

// Générer un state pour la sécurité CSRF
$state = bin2hex(random_bytes(32));
$_SESSION['oauth_state'] = $state;
$_SESSION['oauth_provider'] = $provider;

// Construire l'URL d'autorisation
$params = [
    'client_id' => $config['client_id'],
    'redirect_uri' => $config['redirect_uri'],
    'response_type' => 'code',
    'state' => $state,
    'scope' => implode(' ', $config['scopes'])
];

// Paramètres spécifiques à Microsoft
if ($provider === 'microsoft') {
    $params['response_mode'] = 'query';
}

$authorizeUrl = $config['authorize_url'] . '?' . http_build_query($params);

// Rediriger vers la page d'autorisation du fournisseur
header('Location: ' . $authorizeUrl);
exit;
