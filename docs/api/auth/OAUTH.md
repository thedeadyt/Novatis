# API OAuth

Documentation de l'API OAuth de Novatis.

---

## Vue d'ensemble

L'API OAuth permet aux utilisateurs de s'authentifier et de lier leurs comptes via des fournisseurs OAuth externes (Google, Microsoft, GitHub).

**Base URL :** `/api/oauth/`

**Fournisseurs supportés :** Google, Microsoft, GitHub

---

## Authentification

Les endpoints de cette API ne nécessitent pas d'authentification préalable pour l'autorisation. Le callback peut fonctionner avec ou sans utilisateur connecté.

---

## Endpoints

### 1. Autorisation OAuth

**Méthode :** `GET`
**URL :** `/api/oauth/authorize.php`
**Authentification :** Non requise

Redirige l'utilisateur vers la page d'authentification du fournisseur OAuth.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| provider | string | Oui | Fournisseur OAuth (`google`, `microsoft`, ou `github`) |

**Exemple de requête :**

```javascript
// Rediriger vers la page d'authentification Google
window.location.href = '/api/oauth/authorize.php?provider=google';

// Ou ouvrir dans un popup
window.open('/api/oauth/authorize.php?provider=google', 'oauth_popup', 'width=500,height=600');
```

**Fournisseurs disponibles :**

```javascript
const providers = [
  'google',
  'microsoft',
  'github'
];
```

**Réponse :**

L'utilisateur est redirigé vers le fournisseur OAuth pour autoriser l'accès.

---

### 2. Callback OAuth

**Méthode :** `GET`
**URL :** `/api/oauth/callback.php`
**Authentification :** Optionnelle

Traite le callback du fournisseur OAuth et authentifie ou lie le compte.

**Paramètres :** (Fournis automatiquement par le fournisseur)

| Paramètre | Type | Description |
|-----------|------|-------------|
| code | string | Code d'autorisation du fournisseur |
| state | string | État pour la sécurité CSRF |
| error | string | Message d'erreur si l'autorisation a échoué |

**Flux d'utilisation :**

1. L'utilisateur clique sur "Se connecter avec Google"
2. Il est redirigé vers `/api/oauth/authorize.php?provider=google`
3. Google l'authentifie et le redirige vers `/api/oauth/callback.php?code=...&state=...`
4. Notre API traite le callback et gère les cas suivants :

**Cas 1 : Utilisateur NON connecté + connexion OAuth existe**
→ Connexion directe avec le compte lié

**Cas 2 : Utilisateur NON connecté + email existe mais pas de OAuth**
→ Liaison du compte + connexion

**Cas 3 : Utilisateur NON connecté + nouveau contact**
→ Création du compte + liaison OAuth + connexion

**Cas 4 : Utilisateur connecté + OAuth n'existe pas**
→ Liaison du nouveau provider au compte actuel

**Cas 5 : Utilisateur connecté + OAuth appartient à ce compte**
→ Mise à jour des tokens + message de succès

**Cas 6 : Utilisateur connecté + OAuth appartient à un autre compte**
→ Erreur : le compte OAuth est déjà lié à un autre compte

**Réponse (succès) :**

```html
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
        message: 'Connexion réussie avec Google !'
      }, window.location.origin);
      window.close();
    } else {
      alert('Connexion réussie !');
      window.location.href = '/dashboard';
    }
  </script>
</body>
</html>
```

**Réponse (erreur) :**

```html
<!DOCTYPE html>
<html>
<head>
  <title>Erreur OAuth</title>
</head>
<body>
  <div class="error-box">
    <h2>Erreur d'authentification</h2>
    <p>Erreur lors de l'authentification: [message d'erreur]</p>
    <button onclick="closeWindow()">Fermer</button>
  </div>
</body>
</html>
```

---

### 3. Déconnexion OAuth (Dissociation)

**Méthode :** `POST`
**URL :** `/api/oauth/disconnect.php`
**Authentification :** Requise

Dissocie un compte OAuth du compte utilisateur actuel.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| provider | string | Oui | Fournisseur à dissocier (`google`, `microsoft`, ou `github`) |

**Exemple de requête :**

```javascript
const response = await fetch('/api/oauth/disconnect.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    provider: 'google'
  })
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "message": "Compte Google dissocié avec succès"
}
```

**Réponse (erreur) :**

```json
{
  "success": false,
  "error": "Cette connexion n'existe pas ou ne peut pas être supprimée"
}
```

---

## Configuration OAuth

Pour que les endpoints OAuth fonctionnent, vous devez configurer les clés API des fournisseurs.

**Fichier de configuration :** `config/oauth.php`

```php
return [
    'google' => [
        'client_id' => 'YOUR_GOOGLE_CLIENT_ID',
        'client_secret' => 'YOUR_GOOGLE_CLIENT_SECRET',
        'redirect_uri' => 'https://votre-domaine.com/api/oauth/callback.php',
        'authorize_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
        'token_url' => 'https://www.googleapis.com/oauth2/v4/token',
        'userinfo_url' => 'https://www.googleapis.com/oauth2/v2/userinfo',
        'scopes' => ['https://www.googleapis.com/auth/userinfo.email', 'https://www.googleapis.com/auth/userinfo.profile']
    ],
    'microsoft' => [
        'client_id' => 'YOUR_MICROSOFT_CLIENT_ID',
        'client_secret' => 'YOUR_MICROSOFT_CLIENT_SECRET',
        'redirect_uri' => 'https://votre-domaine.com/api/oauth/callback.php',
        'authorize_url' => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
        'token_url' => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
        'userinfo_url' => 'https://graph.microsoft.com/v1.0/me',
        'scopes' => ['openid', 'profile', 'email']
    ],
    'github' => [
        'client_id' => 'YOUR_GITHUB_CLIENT_ID',
        'client_secret' => 'YOUR_GITHUB_CLIENT_SECRET',
        'redirect_uri' => 'https://votre-domaine.com/api/oauth/callback.php',
        'authorize_url' => 'https://github.com/login/oauth/authorize',
        'token_url' => 'https://github.com/login/oauth/access_token',
        'userinfo_url' => 'https://api.github.com/user',
        'emails_url' => 'https://api.github.com/user/emails',
        'scopes' => ['user:email']
    ]
];
```

---

## Codes d'erreur HTTP

| Code | Message | Description |
|------|---------|-------------|
| 200 | OK | Requête réussie |
| 302 | Found | Redirection vers fournisseur OAuth |
| 400 | Bad Request | Paramètres manquants ou invalides |
| 401 | Unauthorized | Authentification requise |
| 403 | Forbidden | Accès refusé |
| 500 | Server Error | Erreur serveur |

---

## Sécurité

- **State CSRF** : Un token aléatoire est généré pour prévenir les attaques CSRF
- **Token d'accès** : Stocké de manière sécurisée en base de données
- **Refresh token** : Utilisé pour renouveler l'authentification
- **Isolation des comptes** : Chaque connexion OAuth ne peut être liée qu'à un seul compte Novatis

---

## Informations utilisateur récupérées

### Google
- ID
- Email
- Prénom
- Nom
- Photo de profil

### Microsoft
- ID
- Email principal
- Prénom
- Nom

### GitHub
- ID
- Email (demande API supplémentaire si privé)
- Nom
- Avatar

---

## Ressources

- [Documentation API](API.md)
- [Authentification (AUTH.md)](AUTH.md)
- [Documentation Complète](../DOCUMENTATION.md)

---

<div align="center">
[← Authentification](AUTH.md) • [Profils →](PROFILS.md)
</div>
