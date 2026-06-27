<?php
/**
 * Configuration OAuth pour Google, Microsoft et GitHub
 *
 * IMPORTANT: Ne commitez jamais ce fichier avec de vraies clés!
 * Créez un fichier oauth.local.php pour vos clés de développement.
 */

// Charger les configurations locales si elles existent
$localConfig = __DIR__ . '/oauth.local.php';
if (file_exists($localConfig)) {
    return require $localConfig;
}

return [
    'google' => [
        'client_id' => getenv('GOOGLE_CLIENT_ID') ?: '',
        'client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: '',
        'redirect_uri' => 'http://localhost' . BASE_URL . '/api/oauth/callback.php?provider=google',
        'scopes' => [
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile'
        ],
        'authorize_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
        'token_url' => 'https://oauth2.googleapis.com/token',
        'userinfo_url' => 'https://www.googleapis.com/oauth2/v2/userinfo'
    ],

    'microsoft' => [
        'client_id' => getenv('MICROSOFT_CLIENT_ID') ?: '',
        'client_secret' => getenv('MICROSOFT_CLIENT_SECRET') ?: '',
        'redirect_uri' => 'http://localhost' . BASE_URL . '/api/oauth/callback.php?provider=microsoft',
        'scopes' => [
            'openid',
            'profile',
            'email',
            'User.Read'
        ],
        'authorize_url' => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
        'token_url' => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
        'userinfo_url' => 'https://graph.microsoft.com/v1.0/me'
    ],

    'github' => [
        'client_id' => getenv('GITHUB_CLIENT_ID') ?: '',
        'client_secret' => getenv('GITHUB_CLIENT_SECRET') ?: '',
        'redirect_uri' => 'http://localhost' . BASE_URL . '/api/oauth/callback.php?provider=github',
        'scopes' => [
            'user:email',
            'read:user'
        ],
        'authorize_url' => 'https://github.com/login/oauth/authorize',
        'token_url' => 'https://github.com/login/oauth/access_token',
        'userinfo_url' => 'https://api.github.com/user',
        'emails_url' => 'https://api.github.com/user/emails'
    ]
];
