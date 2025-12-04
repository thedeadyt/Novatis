<?php
/**
 * Configuration OAuth locale - Exemple
 *
 * Copiez ce fichier en oauth.local.php et remplissez vos clés API
 * Le fichier oauth.local.php ne sera pas commité dans git
 *
 * Pour obtenir vos clés:
 *
 * GOOGLE:
 * 1. Allez sur https://console.cloud.google.com/
 * 2. Créez un nouveau projet ou sélectionnez-en un
 * 3. Activez "Google+ API"
 * 4. Dans "Identifiants", créez des identifiants OAuth 2.0
 * 5. Ajoutez l'URI de redirection: http://localhost/Novatis/public/api/oauth/callback.php?provider=google
 *
 * MICROSOFT:
 * 1. Allez sur https://portal.azure.com/
 * 2. Recherchez "App registrations" (Inscriptions d'applications)
 * 3. Créez une nouvelle inscription
 * 4. Ajoutez l'URI de redirection: http://localhost/Novatis/public/api/oauth/callback.php?provider=microsoft
 * 5. Créez un secret client dans "Certificates & secrets"
 *
 * GITHUB:
 * 1. Allez sur https://github.com/settings/developers
 * 2. Cliquez sur "New OAuth App"
 * 3. Remplissez les informations:
 *    - Homepage URL: http://localhost/Novatis
 *    - Authorization callback URL: http://localhost/Novatis/public/api/oauth/callback.php?provider=github
 */

return [
    'google' => [
        'client_id' => 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com',
        'client_secret' => 'YOUR_GOOGLE_CLIENT_SECRET',
        'redirect_uri' => 'http://localhost/Novatis/public/api/oauth/callback.php?provider=google',
        'scopes' => [
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile'
        ],
        'authorize_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
        'token_url' => 'https://oauth2.googleapis.com/token',
        'userinfo_url' => 'https://www.googleapis.com/oauth2/v2/userinfo'
    ],

    'microsoft' => [
        'client_id' => 'YOUR_MICROSOFT_CLIENT_ID',
        'client_secret' => 'YOUR_MICROSOFT_CLIENT_SECRET',
        'redirect_uri' => 'http://localhost/Novatis/public/api/oauth/callback.php?provider=microsoft',
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
        'client_id' => 'YOUR_GITHUB_CLIENT_ID',
        'client_secret' => 'YOUR_GITHUB_CLIENT_SECRET',
        'redirect_uri' => 'http://localhost/Novatis/public/api/oauth/callback.php?provider=github',
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
