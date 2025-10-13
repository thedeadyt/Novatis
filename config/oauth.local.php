<?php
/**
 * Configuration OAuth locale
 *
 * REMPLACEZ LES VALEURS CI-DESSOUS PAR VOS VRAIES CLÉS API
 *
 * Pour obtenir vos clés:
 *
 * GOOGLE:
 * 1. Allez sur https://console.cloud.google.com/
 * 2. Créez un nouveau projet
 * 3. Activez "Google+ API"
 * 4. Créez des identifiants OAuth 2.0
 * 5. Ajoutez l'URI: http://localhost/Novatis/public/api/oauth/callback.php?provider=google
 *
 * MICROSOFT:
 * 1. Allez sur https://portal.azure.com/
 * 2. Créez une "App registration"
 * 3. Ajoutez l'URI: http://localhost/Novatis/public/api/oauth/callback.php?provider=microsoft
 * 4. Créez un secret client
 *
 * GITHUB:
 * 1. Allez sur https://github.com/settings/developers
 * 2. Créez une "New OAuth App"
 * 3. Homepage: http://localhost/Novatis
 * 4. Callback: http://localhost/Novatis/public/api/oauth/callback.php?provider=github
 */

return [
    'google' => [
        'client_id' => '378413768163-18h1j2mmvkf9b5ll1v4nc8omuqhcnbs4.apps.googleusercontent.com',
        'client_secret' => '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',
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
        'client_id' => '4fce303c-54f4-4227-aec5-9a1f03d8a52d',
        'client_secret' => '.xA8Q~sKG_SSpi6QD4.FbNNbCfnRKr-~pGrjpdtp',
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
        'client_id' => 'Ov23liWt1MZec2E0aSd7',
        'client_secret' => '2aebd70e58d6b591189d4023702d3affe0079732',
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
