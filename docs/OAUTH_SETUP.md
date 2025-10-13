# Configuration OAuth pour Novatis

Ce guide vous explique comment configurer l'authentification OAuth avec Google, Microsoft et GitHub.

## Vue d'ensemble

L'authentification OAuth permet aux utilisateurs de se connecter ou de s'inscrire sur Novatis en utilisant leurs comptes Google, Microsoft ou GitHub. Cela simplifie le processus d'inscription et améliore la sécurité.

## Prérequis

- Compte Google Cloud Platform
- Compte Microsoft Azure
- Compte GitHub

## Configuration

### 1. Créer le fichier de configuration local

```bash
cd config
cp oauth.local.example.php oauth.local.php
```

### 2. Configurer Google OAuth

1. Allez sur [Google Cloud Console](https://console.cloud.google.com/)
2. Créez un nouveau projet ou sélectionnez un projet existant
3. Dans le menu, allez à "APIs & Services" > "Credentials"
4. Cliquez sur "Create Credentials" > "OAuth 2.0 Client ID"
5. Configurez l'écran de consentement si nécessaire
6. Sélectionnez "Web application" comme type
7. Ajoutez les URIs de redirection autorisées:
   - Développement: `http://localhost/Novatis/api/oauth/callback.php?provider=google`
   - Production: `https://votredomaine.com/api/oauth/callback.php?provider=google`
8. Copiez le **Client ID** et le **Client Secret**
9. Collez-les dans `config/oauth.local.php` pour Google

### 3. Configurer Microsoft OAuth

1. Allez sur [Azure Portal](https://portal.azure.com/)
2. Recherchez "App registrations" (Inscriptions d'applications)
3. Cliquez sur "New registration"
4. Remplissez les informations:
   - Name: Novatis OAuth
   - Supported account types: Accounts in any organizational directory and personal Microsoft accounts
5. Ajoutez l'URI de redirection:
   - Platform: Web
   - URI: `http://localhost/Novatis/api/oauth/callback.php?provider=microsoft`
6. Après la création, notez l'**Application (client) ID**
7. Allez dans "Certificates & secrets" > "New client secret"
8. Créez un nouveau secret et notez sa **Value** (elle ne sera affichée qu'une seule fois!)
9. Collez l'ID et le secret dans `config/oauth.local.php` pour Microsoft

### 4. Configurer GitHub OAuth

1. Allez sur [GitHub Developer Settings](https://github.com/settings/developers)
2. Cliquez sur "New OAuth App"
3. Remplissez les informations:
   - Application name: Novatis
   - Homepage URL: `http://localhost/Novatis`
   - Authorization callback URL: `http://localhost/Novatis/api/oauth/callback.php?provider=github`
4. Cliquez sur "Register application"
5. Notez le **Client ID**
6. Cliquez sur "Generate a new client secret"
7. Notez le **Client Secret**
8. Collez-les dans `config/oauth.local.php` pour GitHub

### 5. Exemple de configuration

Votre fichier `config/oauth.local.php` devrait ressembler à ceci:

```php
<?php
return [
    'google' => [
        'client_id' => '123456789-abcdefghijklmnop.apps.googleusercontent.com',
        'client_secret' => 'GOCSPX-abcdefghijklmnop',
        'redirect_uri' => 'http://localhost/Novatis/api/oauth/callback.php?provider=google',
        'scopes' => [
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile'
        ],
        'authorize_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
        'token_url' => 'https://oauth2.googleapis.com/token',
        'userinfo_url' => 'https://www.googleapis.com/oauth2/v2/userinfo'
    ],

    'microsoft' => [
        'client_id' => '12345678-1234-1234-1234-123456789012',
        'client_secret' => 'abc~123456789012345678901234567890',
        // ... reste de la configuration
    ],

    'github' => [
        'client_id' => 'Iv1.abcdefghijklmnop',
        'client_secret' => '1234567890abcdef1234567890abcdef12345678',
        // ... reste de la configuration
    ]
];
```

## Fonctionnalités

### Pour les utilisateurs

- **Connexion rapide**: Se connecter en un clic sans mémoriser de mot de passe
- **Inscription simplifiée**: Créer un compte automatiquement avec les informations du provider
- **Sécurité renforcée**: Utilise l'authentification OAuth2 sécurisée
- **Liaison de comptes**: Lier plusieurs providers OAuth au même compte Novatis

### Flux d'authentification

1. L'utilisateur clique sur un bouton OAuth (Google, Microsoft, GitHub)
2. Une popup s'ouvre avec la page d'authentification du provider
3. L'utilisateur autorise l'application
4. Le provider renvoie un code d'autorisation
5. Novatis échange le code contre un token d'accès
6. Les informations utilisateur sont récupérées
7. Si l'email existe déjà, le compte OAuth est lié
8. Sinon, un nouveau compte est créé automatiquement
9. L'utilisateur est connecté et redirigé vers le dashboard

## Données stockées

Les informations suivantes sont stockées dans la table `oauth_connections`:

- `provider`: Le nom du provider (google, microsoft, github)
- `provider_user_id`: L'ID de l'utilisateur chez le provider
- `access_token`: Token d'accès (crypté)
- `refresh_token`: Token de rafraîchissement (si disponible)
- `token_expires_at`: Date d'expiration du token
- `email`: Email de l'utilisateur
- `name`: Nom complet
- `avatar_url`: URL de l'avatar

## Utilisation dans le code

### Lier un compte OAuth depuis les paramètres

Les utilisateurs peuvent lier leurs comptes depuis la page Paramètres > Intégrations:

```php
// Dans Parametres.php
<button onclick="connectOAuth('google')">Connecter Google</button>
```

### Vérifier si un utilisateur a des connexions OAuth

```php
$stmt = $pdo->prepare("
    SELECT provider FROM oauth_connections
    WHERE user_id = ?
");
$stmt->execute([$userId]);
$providers = $stmt->fetchAll(PDO::FETCH_COLUMN);

// $providers contient ['google', 'github'] par exemple
```

## Sécurité

- Les tokens sont stockés de manière sécurisée dans la base de données
- Utilisation du paramètre `state` pour prévenir les attaques CSRF
- Vérification de l'origine des messages postMessage
- Les popups OAuth sont ouvertes dans une fenêtre séparée

## Dépannage

### "Les clés OAuth ne sont pas configurées"

- Vérifiez que le fichier `config/oauth.local.php` existe
- Vérifiez que les clés sont correctement renseignées

### "Erreur lors de l'échange du code"

- Vérifiez que l'URI de redirection est correctement configurée dans le provider
- Vérifiez que l'URI dans `oauth.local.php` correspond exactement

### "Aucun email vérifié trouvé" (GitHub)

- Sur GitHub, assurez-vous d'avoir au moins un email vérifié
- Allez dans Settings > Emails et vérifiez votre email

### La popup se ferme sans se connecter

- Vérifiez la console du navigateur pour les erreurs JavaScript
- Vérifiez que les domaines sont autorisés dans les CORS

## Production

Pour déployer en production:

1. Créez de nouvelles applications OAuth avec les URLs de production
2. Mettez à jour `oauth.local.php` avec les nouvelles clés
3. Mettez à jour les URIs de redirection:
   - Google: `https://votredomaine.com/api/oauth/callback.php?provider=google`
   - Microsoft: `https://votredomaine.com/api/oauth/callback.php?provider=microsoft`
   - GitHub: `https://votredomaine.com/api/oauth/callback.php?provider=github`
4. Activez HTTPS sur votre serveur (obligatoire pour OAuth)

## Support

Pour toute question ou problème, contactez l'équipe de développement.
