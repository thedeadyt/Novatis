# API Authentification

Documentation de l'API d'authentification de Novatis.

---

## Vue d'ensemble

L'API d'authentification gère l'inscription et la connexion des utilisateurs sur la plateforme Novatis.

**Base URL :** `/api/auth/`

---

## Authentification

Les endpoints de cette API ne nécessitent pas d'authentification préalable (ils permettent de s'authentifier).

---

## Endpoints

### 1. Inscription

**Méthode :** `POST`
**URL :** `/api/auth/register.php`
**Authentification :** Non requise

Crée un nouveau compte utilisateur sur la plateforme. Un email de vérification est envoyé automatiquement.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| firstname | string | Oui | Prénom de l'utilisateur |
| lastname | string | Oui | Nom de l'utilisateur |
| pseudo | string | Oui | Pseudo unique (minimum 3 caractères) |
| email | string | Oui | Adresse email (doit être unique et valide) |
| password | string | Oui | Mot de passe (minimum 6 caractères) |

**Exemple de requête :**

```javascript
const response = await fetch('/api/auth/register.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    firstname: 'Jean',
    lastname: 'Dupont',
    pseudo: 'jeandupont',
    email: 'jean@example.com',
    password: 'SecurePassword123'
  })
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "message": "Compte créé avec succès. Un email de vérification a été envoyé à jean@example.com",
  "email_sent": true,
  "verification_required": true
}
```

**Réponse (erreur) :**

```json
{
  "success": false,
  "error": "Cet email est déjà utilisé"
}
```

**Codes d'erreur possibles :**
- Tous les champs sont requis
- Format d'email invalide
- Le mot de passe doit contenir au moins 6 caractères
- Le pseudo doit contenir au moins 3 caractères
- Cet email est déjà utilisé
- Ce pseudo est déjà utilisé

---

### 2. Connexion

**Méthode :** `POST`
**URL :** `/api/auth/login.php`
**Authentification :** Non requise

Authentifie un utilisateur et crée une session.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| email | string | Oui | Email ou pseudo de l'utilisateur |
| password | string | Oui | Mot de passe |
| two_factor_code | string | Optionnel | Code d'authentification à 2 facteurs si activé |

**Exemple de requête :**

```javascript
const response = await fetch('/api/auth/login.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    email: 'jean@example.com',
    password: 'SecurePassword123'
  })
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "message": "Connexion réussie",
  "user": {
    "id": 1,
    "firstname": "Jean",
    "lastname": "Dupont",
    "pseudo": "jeandupont",
    "email": "jean@example.com",
    "role": "user",
    "avatar": "https://...",
    "rating": 4.5,
    "is_verified": true
  }
}
```

**Réponse (A2F requise) :**

```json
{
  "success": false,
  "require_2fa": true,
  "message": "Code d'authentification requis",
  "user_id": 1
}
```

**Réponse (erreur) :**

```json
{
  "success": false,
  "error": "Utilisateur introuvable"
}
```

**Codes d'erreur possibles :**
- Email/pseudo et mot de passe requis
- Utilisateur introuvable
- Mot de passe incorrect
- Veuillez vérifier votre email avant de vous connecter
- Code d'authentification incorrect

---

### 3. Déconnexion

**Méthode :** `POST`
**URL :** `/api/auth/logout.php`
**Authentification :** Requise

Terminer la session de l'utilisateur actuel.

**Paramètres :** Aucun

**Exemple de requête :**

```javascript
const response = await fetch('/api/auth/logout.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  }
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "message": "Déconnexion réussie"
}
```

---

### 4. Mot de passe oublié

**Méthode :** `POST`
**URL :** `/api/auth/forgot-password.php`
**Authentification :** Non requise

Envoie un email de réinitialisation de mot de passe à l'utilisateur.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| email | string | Oui | Adresse email du compte |

**Exemple de requête :**

```javascript
const response = await fetch('/api/auth/forgot-password.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    email: 'jean@example.com'
  })
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "message": "Un email de réinitialisation a été envoyé si le compte existe"
}
```

---

### 5. Réinitialisation de mot de passe

**Méthode :** `POST`
**URL :** `/api/auth/reset-password.php`
**Authentification :** Non requise

Réinitialise le mot de passe avec le token reçu par email.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| token | string | Oui | Token de réinitialisation reçu par email |
| password | string | Oui | Nouveau mot de passe |
| password_confirm | string | Oui | Confirmation du nouveau mot de passe |

**Exemple de requête :**

```javascript
const response = await fetch('/api/auth/reset-password.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    token: 'abc123xyz...',
    password: 'NewPassword123',
    password_confirm: 'NewPassword123'
  })
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "message": "Mot de passe réinitialisé avec succès"
}
```

**Réponse (erreur) :**

```json
{
  "success": false,
  "error": "Token invalide ou expiré"
}
```

---

## Codes d'erreur HTTP

| Code | Message | Description |
|------|---------|-------------|
| 200 | OK | Requête réussie |
| 400 | Bad Request | Paramètres manquants ou invalides |
| 405 | Method Not Allowed | Seule la méthode POST est acceptée |
| 500 | Server Error | Erreur serveur lors du traitement |

---

## Sécurité

- Les mots de passe sont hachés avec `PASSWORD_DEFAULT` (bcrypt)
- Les emails doivent être vérifiés avant de pouvoir se connecter
- L'authentification à 2 facteurs est optionnelle
- Les codes de réinitialisation de mot de passe expirent après 24 heures

---

## Points importants

1. **Vérification d'email** : Après l'inscription, l'utilisateur doit vérifier son email avant de pouvoir se connecter
2. **A2F optionnel** : Si l'utilisateur a activé l'authentification à 2 facteurs, le code doit être fourni lors de la connexion
3. **Pseudo ou email** : Pour la connexion, l'utilisateur peut utiliser soit son email, soit son pseudo
4. **Sessions** : Les sessions sont gérées côté serveur via PHP

---

## Ressources

- [Documentation API](API.md)
- [OAuth (OAUTH.md)](OAUTH.md)
- [Documentation Complète](../DOCUMENTATION.md)

---

<div align="center">
[← API Générale](API.md) • [OAuth →](OAUTH.md)
</div>
