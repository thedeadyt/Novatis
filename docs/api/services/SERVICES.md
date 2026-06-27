# API Services

Documentation complète de l'API Services de Novatis.

---

## Vue d'ensemble

L'API Services permet de gérer les services offerts par les prestataires sur la plateforme. Les services sont des offres que les prestataires créent pour vendre leurs compétences.

**Base URL :** `/api/services/`

---

## Authentification

La plupart des endpoints de cette API nécessitent une authentification utilisateur.

- GET `/categories.php` - Non requise
- POST/PUT/DELETE `/services.php` - Requise
- GET `/services.php` - Requise

---

## Endpoints

### 1. Lister les services

**Méthode :** `GET`
**URL :** `/api/services/services.php`
**Authentification :** Requise

Récupère les services de l'utilisateur connecté (ou tous les services pour les admins).

**Paramètres :** Aucun

**Exemple de requête :**

```javascript
const response = await fetch('/api/services/services.php', {
  method: 'GET'
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "services": [
    {
      "id": 1,
      "user_id": 5,
      "title": "Design de logo",
      "description": "Je crée des logos professionnels et modernes",
      "category_id": 3,
      "category_name": "Design",
      "price": 50,
      "delivery_days": 3,
      "image": "https://...",
      "status": "active",
      "created_at": "2024-01-15 10:30:00",
      "user_name": "Jean Dupont",
      "pseudo": "jeandupont"
    }
  ]
}
```

---

### 2. Créer un service

**Méthode :** `POST`
**URL :** `/api/services/services.php`
**Authentification :** Requise

Crée un nouveau service pour l'utilisateur connecté.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| title | string | Oui | Titre du service |
| description | string | Oui | Description détaillée |
| category_id | integer | Non | ID de la catégorie |
| price | float | Oui | Prix du service |
| delivery_days | integer | Oui | Délai de livraison en jours |
| image | string | Non | URL de l'image du service |

**Exemple de requête :**

```javascript
const response = await fetch('/api/services/services.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    title: 'Design de logo',
    description: 'Je crée des logos professionnels et modernes pour votre entreprise',
    category_id: 3,
    price: 50,
    delivery_days: 3,
    image: 'https://example.com/image.jpg'
  })
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "message": "Service créé avec succès",
  "service_id": 42
}
```

**Réponse (erreur) :**

```json
{
  "success": false,
  "error": "Le prix doit être supérieur à 0"
}
```

---

### 3. Modifier un service

**Méthode :** `PUT`
**URL :** `/api/services/services.php`
**Authentification :** Requise

Modifie un service existant. Seul le propriétaire ou un admin peut modifier.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| id | integer | Oui | ID du service à modifier |
| title | string | Non | Nouveau titre |
| description | string | Non | Nouvelle description |
| category_id | integer | Non | Nouvelle catégorie |
| price | float | Non | Nouveau prix |
| delivery_days | integer | Non | Nouveau délai |
| image | string | Non | Nouvelle image |
| status | string | Non | Nouveau statut (`active`, `inactive`) |

**Exemple de requête :**

```javascript
const response = await fetch('/api/services/services.php', {
  method: 'PUT',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    id: 42,
    price: 75,
    delivery_days: 5
  })
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "message": "Service mis à jour"
}
```

---

### 4. Supprimer un service

**Méthode :** `DELETE`
**URL :** `/api/services/services.php`
**Authentification :** Requise

Supprime un service. Seul le propriétaire ou un admin peut supprimer.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| id | integer | Oui | ID du service à supprimer |

**Exemple de requête :**

```javascript
const response = await fetch('/api/services/services.php', {
  method: 'DELETE',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    id: 42
  })
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "message": "Service supprimé"
}
```

---

### 5. Lister les catégories

**Méthode :** `GET`
**URL :** `/api/services/categories.php`
**Authentification :** Non requise

Récupère toutes les catégories de services disponibles.

**Paramètres :** Aucun

**Exemple de requête :**

```javascript
const response = await fetch('/api/services/categories.php', {
  method: 'GET'
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "categories": [
    {
      "id": 1,
      "name": "Design",
      "slug": "design",
      "icon": "design-icon",
      "created_at": "2024-01-01 00:00:00"
    },
    {
      "id": 2,
      "name": "Programmation",
      "slug": "programmation",
      "icon": "code-icon",
      "created_at": "2024-01-01 00:00:00"
    }
  ]
}
```

---

### 6. Créer une catégorie (Admin)

**Méthode :** `POST`
**URL :** `/api/services/categories.php`
**Authentification :** Requise (Admin)

Crée une nouvelle catégorie. Seuls les admins peuvent créer.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| name | string | Oui | Nom de la catégorie |
| icon | string | Non | Icône de la catégorie |

**Exemple de requête :**

```javascript
const response = await fetch('/api/services/categories.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    name: 'Vidéographie',
    icon: 'video-icon'
  })
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "message": "Catégorie créée avec succès",
  "category_id": 10
}
```

---

### 7. Modifier une catégorie (Admin)

**Méthode :** `PUT`
**URL :** `/api/services/categories.php`
**Authentification :** Requise (Admin)

Modifie une catégorie existante. Seuls les admins peuvent modifier.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| id | integer | Oui | ID de la catégorie |
| name | string | Oui | Nouveau nom |
| icon | string | Non | Nouvelle icône |

**Exemple de requête :**

```javascript
const response = await fetch('/api/services/categories.php', {
  method: 'PUT',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    id: 10,
    name: 'Production Vidéo',
    icon: 'video-production-icon'
  })
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "message": "Catégorie mise à jour avec succès"
}
```

---

### 8. Supprimer une catégorie (Admin)

**Méthode :** `DELETE`
**URL :** `/api/services/categories.php`
**Authentification :** Requise (Admin)

Supprime une catégorie. Seuls les admins peuvent supprimer. La catégorie ne doit pas être utilisée.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| id | integer | Oui | ID de la catégorie |

**Exemple de requête :**

```javascript
const response = await fetch('/api/services/categories.php', {
  method: 'DELETE',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    id: 10
  })
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "message": "Catégorie supprimée avec succès"
}
```

**Réponse (erreur) :**

```json
{
  "success": false,
  "error": "Impossible de supprimer cette catégorie car elle est utilisée par des services ou projets"
}
```

---

### 9. Services prédéfinis

**Méthode :** `GET`
**URL :** `/api/services/predefined_services.php`
**Authentification :** Non requise

Récupère les services prédéfinis du système.

**Réponse (succès) :**

```json
{
  "success": true,
  "services": [
    {
      "id": 1,
      "name": "Service Standard",
      "description": "Description du service",
      "icon": "icon-name"
    }
  ]
}
```

---

### 10. Portfolio

**Méthode :** `GET/POST/PUT/DELETE`
**URL :** `/api/services/portfolio.php`
**Authentification :** Requise

Gère le portfolio (projets réalisés) des utilisateurs.

---

## Codes d'erreur HTTP

| Code | Message | Description |
|------|---------|-------------|
| 200 | OK | Requête réussie |
| 400 | Bad Request | Paramètres manquants ou invalides |
| 401 | Unauthorized | Authentification requise |
| 403 | Forbidden | Accès refusé (permissions insuffisantes) |
| 404 | Not Found | Ressource introuvable |
| 405 | Method Not Allowed | Méthode HTTP non autorisée |
| 500 | Server Error | Erreur serveur |

---

## Validations

- **Prix** : Doit être supérieur à 0
- **Délai de livraison** : Doit être supérieur à 0
- **Catégorie** : L'ID doit correspondre à une catégorie existante
- **Titre** : Requis et non vide
- **Description** : Requise et non vide

---

## Notifications

Lorsqu'un service est modifié, les clients ayant des commandes actives pour ce service reçoivent une notification.

---

## Ressources

- [Documentation API](API.md)
- [Commandes (COMMANDES.md)](COMMANDES.md)
- [Documentation Complète](../DOCUMENTATION.md)

---

<div align="center">
[← OAuth](OAUTH.md) • [Commandes →](COMMANDES.md)
</div>
