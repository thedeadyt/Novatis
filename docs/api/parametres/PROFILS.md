# API Profils

Documentation de l'API Profils de Novatis.

---

## Vue d'ensemble

L'API Profils permet de consulter et modifier les informations des profils utilisateurs, y compris la gestion des photos de profil et du portfolio.

**Base URL :** `/api/profils/` ou selon les endpoints existants

---

## Authentification

- GET endpoints : Optionnelle (certains profils publics)
- POST/PUT endpoints : Requise

---

## Endpoints

### 1. Consulter le profil utilisateur

**Méthode :** `GET`
**URL :** `/profil.php?id=USER_ID` ou via API
**Authentification :** Optionnelle

Récupère les informations publiques du profil d'un utilisateur.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| id | integer | Oui | ID de l'utilisateur |

**Exemple de requête :**

```javascript
const response = await fetch('/api/profils/?id=15', {
  method: 'GET'
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "profile": {
    "id": 15,
    "firstname": "Marie",
    "lastname": "Martin",
    "pseudo": "mariedesigner",
    "email": "marie@example.com",
    "avatar": "https://...",
    "bio": "Designer graphique professionnel avec 5 ans d'expérience",
    "location": "Paris, France",
    "website": "https://mariedesign.com",
    "phone": "+33612345678",
    "rating": 4.8,
    "is_verified": true,
    "role": "user",
    "created_at": "2023-01-15 10:30:00",
    "last_login": "2024-01-20 15:00:00"
  }
}
```

---

### 2. Consulter le profil personnel (Session)

**Méthode :** `GET`
**URL :** `/api/profils/profile.php`
**Authentification :** Requise

Récupère les informations complètes du profil de l'utilisateur connecté.

**Paramètres :** Aucun

**Exemple de requête :**

```javascript
const response = await fetch('/api/profils/profile.php', {
  method: 'GET'
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "profile": {
    "id": 10,
    "firstname": "Jean",
    "lastname": "Dupont",
    "pseudo": "jeandupont",
    "email": "jean@example.com",
    "avatar": "https://...",
    "bio": "Développeur web React",
    "location": "Lyon, France",
    "website": "https://jean-dev.com",
    "phone": "+33698765432",
    "rating": 4.5,
    "is_verified": true,
    "role": "user",
    "created_at": "2023-06-10 14:20:00",
    "last_login": "2024-01-20 16:00:00",
    "total_orders": 25,
    "completed_orders": 23,
    "total_earnings": 1250.00
  }
}
```

---

### 3. Modifier le profil

**Méthode :** `PUT/POST`
**URL :** `/api/parametres/settings.php` avec action `update_profile`
**Authentification :** Requise

Modifie les informations du profil de l'utilisateur connecté.

Voir le détail complet dans la documentation [PARAMETRES.md](PARAMETRES.md).

---

### 4. Upload de photo de profil

**Méthode :** `POST`
**URL :** `/api/profils/upload-photo.php` ou `/api/parametres/settings.php`
**Authentification :** Requise

Télécharge une nouvelle photo de profil.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| avatar | file | Oui | Fichier image (JPG, PNG, GIF) |

**Formats acceptés :**
- JPEG/JPG (max 5MB)
- PNG (max 5MB)
- GIF (max 5MB)

**Exemple de requête :**

```javascript
const formData = new FormData();
const fileInput = document.querySelector('input[type="file"]');
formData.append('avatar', fileInput.files[0]);

const response = await fetch('/api/profils/upload-photo.php', {
  method: 'POST',
  body: formData
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "message": "Photo de profil mise à jour avec succès",
  "avatar_url": "https://...",
  "avatar": "https://..."
}
```

**Réponse (erreur) :**

```json
{
  "success": false,
  "error": "Le fichier doit être une image (JPG, PNG, GIF)"
}
```

---

### 5. Consulter le portfolio

**Méthode :** `GET`
**URL :** `/api/services/portfolio.php`
**Authentification :** Optionnelle

Récupère le portfolio (projets réalisés) d'un utilisateur.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| user_id | integer | Non | ID de l'utilisateur (défaut: utilisateur connecté) |

**Exemple de requête :**

```javascript
const response = await fetch('/api/services/portfolio.php?user_id=15', {
  method: 'GET'
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "portfolio": [
    {
      "id": 1,
      "user_id": 15,
      "title": "Logo Design - Startup TechFlow",
      "description": "Logo moderne et épuré pour une startup en tech",
      "category_id": 3,
      "category_name": "Design",
      "image": "https://...",
      "image_url": "https://...",
      "created_at": "2023-11-20 10:30:00",
      "updated_at": "2024-01-15 14:00:00"
    }
  ]
}
```

---

### 6. Ajouter un projet au portfolio

**Méthode :** `POST`
**URL :** `/api/services/portfolio.php`
**Authentification :** Requise

Ajoute un nouveau projet au portfolio de l'utilisateur connecté.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| title | string | Oui | Titre du projet |
| description | string | Oui | Description détaillée |
| category_id | integer | Non | ID de la catégorie |
| image | string/file | Non | Image du projet |

**Exemple de requête :**

```javascript
const formData = new FormData();
formData.append('title', 'Rebranding - Restaurant Gourmet');
formData.append('description', 'Identité visuelle complète pour un restaurant haut de gamme');
formData.append('category_id', 3);
formData.append('image', fileInput.files[0]);

const response = await fetch('/api/services/portfolio.php', {
  method: 'POST',
  body: formData
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "message": "Projet ajouté au portfolio avec succès",
  "portfolio_id": 42
}
```

---

### 7. Modifier un projet du portfolio

**Méthode :** `PUT`
**URL :** `/api/services/portfolio.php`
**Authentification :** Requise

Modifie un projet existant du portfolio.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| id | integer | Oui | ID du projet |
| title | string | Non | Nouveau titre |
| description | string | Non | Nouvelle description |
| category_id | integer | Non | Nouvelle catégorie |
| image | file | Non | Nouvelle image |

**Exemple de requête :**

```javascript
const formData = new FormData();
formData.append('id', 1);
formData.append('title', 'Logo Design - Startup TechFlow (v2)');
formData.append('description', 'Logo redesigné avec amélioration des détails');

const response = await fetch('/api/services/portfolio.php', {
  method: 'PUT',
  body: formData
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "message": "Projet mis à jour avec succès"
}
```

---

### 8. Supprimer un projet du portfolio

**Méthode :** `DELETE`
**URL :** `/api/services/portfolio.php`
**Authentification :** Requise

Supprime un projet du portfolio.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| id | integer | Oui | ID du projet à supprimer |

**Exemple de requête :**

```javascript
const response = await fetch('/api/services/portfolio.php', {
  method: 'DELETE',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    id: 1
  })
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "message": "Projet supprimé du portfolio"
}
```

---

## Structure d'un profil

```json
{
  "id": 15,
  "firstname": "Marie",
  "lastname": "Martin",
  "pseudo": "mariedesigner",
  "email": "marie@example.com",
  "avatar": "https://...",
  "bio": "Designer graphique",
  "location": "Paris, France",
  "website": "https://...",
  "phone": "+33612345678",
  "rating": 4.8,
  "is_verified": true,
  "role": "user",
  "created_at": "2023-01-15 10:30:00",
  "last_login": "2024-01-20 15:00:00"
}
```

---

## Structure du portfolio

```json
{
  "id": 1,
  "user_id": 15,
  "title": "Logo Design - Startup",
  "description": "Description du projet",
  "category_id": 3,
  "category_name": "Design",
  "image": "https://...",
  "created_at": "2023-11-20 10:30:00",
  "updated_at": "2024-01-15 14:00:00"
}
```

---

## Visibilité du profil

### Profil public
- Pseudo, avatar, bio
- Localisation, site web
- Note moyenne et nombre d'avis
- Services et portfolio
- Statut de vérification

### Profil privé (si applicable)
- Email, téléphone (masqués sauf configuration)
- Informations personnelles sensibles

### Profil de l'utilisateur connecté
- Toutes les informations
- Email, téléphone
- Statistiques privées
- Paramètres de compte

---

## Codes d'erreur HTTP

| Code | Message | Description |
|------|---------|-------------|
| 200 | OK | Requête réussie |
| 400 | Bad Request | Paramètres manquants ou invalides |
| 401 | Unauthorized | Authentification requise |
| 403 | Forbidden | Accès refusé (pas le propriétaire) |
| 404 | Not Found | Profil ou projet introuvable |
| 405 | Method Not Allowed | Méthode HTTP non autorisée |
| 413 | Payload Too Large | Fichier trop volumineux |
| 415 | Unsupported Media Type | Type de fichier non supporté |
| 500 | Server Error | Erreur serveur |

---

## Validations

- **Prénom/Nom** : Non vides
- **Pseudo** : Unique, au moins 3 caractères
- **Email** : Unique, format valide
- **Avatar** : JPG, PNG, GIF, max 5MB
- **Bio** : Maximum 500 caractères
- **Site web** : URL valide

---

## Sécurité

- Les informations personnelles sont protégées selon la confidentialité
- Les uploads de fichiers sont vérifiés (type et taille)
- Les images sont compressées et optimisées
- Les mots de passe ne sont jamais retournés

---

## Cas d'usage courants

### Afficher le profil d'un prestataire

```javascript
const userId = 15;
const response = await fetch(`/api/profils/?id=${userId}`);
const data = await response.json();

if (data.success) {
  displayProfile(data.profile);
  loadPortfolio(userId);
}
```

### Modifier son propre profil

```javascript
// Voir PARAMETRES.md pour les détails
const response = await fetch('/api/parametres/settings.php', {
  method: 'POST',
  body: new URLSearchParams({
    action: 'update_profile',
    firstname: 'Jean',
    lastname: 'Dupont',
    bio: 'Nouvelle bio'
  })
});
```

### Upload de photo de profil

```javascript
const file = document.querySelector('input[type="file"]').files[0];
const formData = new FormData();
formData.append('avatar', file);

const response = await fetch('/api/profils/upload-photo.php', {
  method: 'POST',
  body: formData
});

const data = await response.json();
if (data.success) {
  updateAvatarDisplay(data.avatar_url);
}
```

---

## Ressources

- [Documentation API](API.md)
- [Services (SERVICES.md)](SERVICES.md)
- [Favoris (FAVORIS.md)](FAVORIS.md)
- [Paramètres (PARAMETRES.md)](PARAMETRES.md)
- [Documentation Complète](../DOCUMENTATION.md)

---

<div align="center">
[← OAuth](OAUTH.md) • [Services →](SERVICES.md)
</div>
