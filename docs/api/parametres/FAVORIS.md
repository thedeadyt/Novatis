# API Favoris

Documentation de l'API Favoris de Novatis.

---

## Vue d'ensemble

L'API Favoris permet aux utilisateurs de sauvegarder leurs prestataires préférés pour un accès rapide ultérieur. Les utilisateurs peuvent ajouter, retirer ou consulter leurs favoris.

**Base URL :** `/api/favorites/`

---

## Authentification

Tous les endpoints de cette API nécessitent une authentification utilisateur.

---

## Endpoints

### 1. Lister les favoris

**Méthode :** `GET`
**URL :** `/api/favorites/favorites.php?action=list`
**Authentification :** Requise

Récupère tous les prestataires ajoutés aux favoris par l'utilisateur connecté.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| action | string | Oui | Valeur : `list` |

**Exemple de requête :**

```javascript
const response = await fetch('/api/favorites/favorites.php?action=list', {
  method: 'GET'
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "favorites": [
    {
      "id": 1,
      "firstname": "Marie",
      "lastname": "Martin",
      "pseudo": "mariedesigner",
      "avatar": "https://...",
      "bio": "Designer graphique professionnel",
      "location": "Paris, France",
      "rating": 4.8,
      "favorited_at": "2024-01-15 10:30:00"
    },
    {
      "id": 2,
      "firstname": "Pierre",
      "lastname": "Durand",
      "pseudo": "pierredeveloper",
      "avatar": "https://...",
      "bio": "Développeur web spécialisé en React",
      "location": "Lyon, France",
      "rating": 4.5,
      "favorited_at": "2024-01-10 14:00:00"
    }
  ],
  "count": 2
}
```

---

### 2. Compter les favoris

**Méthode :** `GET`
**URL :** `/api/favorites/favorites.php?action=count`
**Authentification :** Requise

Récupère le nombre de favoris de l'utilisateur connecté.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| action | string | Oui | Valeur : `count` |

**Exemple de requête :**

```javascript
const response = await fetch('/api/favorites/favorites.php?action=count', {
  method: 'GET'
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "count": 5
}
```

---

### 3. Ajouter un favori

**Méthode :** `POST`
**URL :** `/api/favorites/favorites.php`
**Authentification :** Requise

Ajoute un prestataire aux favoris de l'utilisateur connecté.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| action | string | Oui | Valeur : `add` |
| favorited_user_id | integer | Oui | ID de l'utilisateur à ajouter aux favoris |

**Exemple de requête :**

```javascript
const response = await fetch('/api/favorites/favorites.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    action: 'add',
    favorited_user_id: 15
  })
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "message": "Ajouté aux favoris",
  "is_favorite": true
}
```

**Réponse (erreur) :**

```json
{
  "success": false,
  "message": "Vous ne pouvez pas vous ajouter vous-même en favori"
}
```

---

### 4. Retirer un favori

**Méthode :** `POST`
**URL :** `/api/favorites/favorites.php`
**Authentification :** Requise

Retire un prestataire des favoris de l'utilisateur connecté.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| action | string | Oui | Valeur : `remove` |
| favorited_user_id | integer | Oui | ID de l'utilisateur à retirer des favoris |

**Exemple de requête :**

```javascript
const response = await fetch('/api/favorites/favorites.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    action: 'remove',
    favorited_user_id: 15
  })
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "message": "Retiré des favoris",
  "is_favorite": false
}
```

---

### 5. Basculer favori (Toggle)

**Méthode :** `POST`
**URL :** `/api/favorites/favorites.php`
**Authentification :** Requise

Ajoute ou retire un prestataire des favoris selon son statut actuel.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| action | string | Oui | Valeur : `toggle` |
| favorited_user_id | integer | Oui | ID de l'utilisateur à basculer |

**Exemple de requête :**

```javascript
const response = await fetch('/api/favorites/favorites.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    action: 'toggle',
    favorited_user_id: 15
  })
});
const data = await response.json();
```

**Réponse (si ajout) :**

```json
{
  "success": true,
  "message": "Ajouté aux favoris",
  "is_favorite": true
}
```

**Réponse (si retrait) :**

```json
{
  "success": true,
  "message": "Retiré des favoris",
  "is_favorite": false
}
```

---

### 6. Vérifier le statut favori

**Méthode :** `POST`
**URL :** `/api/favorites/favorites.php`
**Authentification :** Requise

Vérifie si un prestataire est dans les favoris de l'utilisateur connecté.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| action | string | Oui | Valeur : `check` |
| favorited_user_id | integer | Oui | ID de l'utilisateur à vérifier |

**Exemple de requête :**

```javascript
const response = await fetch('/api/favorites/favorites.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    action: 'check',
    favorited_user_id: 15
  })
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "is_favorite": true
}
```

```json
{
  "success": true,
  "is_favorite": false
}
```

---

## Cas d'usage courants

### Consulter les favoris

```javascript
// Récupérer tous les favoris
const response = await fetch('/api/favorites/favorites.php?action=list');
const data = await response.json();

if (data.success) {
  console.log(`${data.count} favoris`);
  data.favorites.forEach(fav => {
    console.log(`${fav.pseudo} - ${fav.rating}/5`);
  });
}
```

### Ajouter un favori depuis un profil

```javascript
// Lorsque l'utilisateur clique sur "Ajouter aux favoris"
const userId = 15; // ID du prestataire visité

const response = await fetch('/api/favorites/favorites.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    action: 'toggle',
    favorited_user_id: userId
  })
});

const data = await response.json();
if (data.is_favorite) {
  // Afficher l'icone "favori" remplie
} else {
  // Afficher l'icone "favori" vide
}
```

### Vérifier le statut lors du chargement du profil

```javascript
// Vérifier si un utilisateur est déjà en favori
const response = await fetch('/api/favorites/favorites.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    action: 'check',
    favorited_user_id: userId
  })
});

const data = await response.json();
const heartButton = document.querySelector('.favorite-button');
if (data.is_favorite) {
  heartButton.classList.add('filled');
} else {
  heartButton.classList.remove('filled');
}
```

---

## Structure d'un favori

```json
{
  "id": 1,
  "firstname": "Marie",
  "lastname": "Martin",
  "pseudo": "mariedesigner",
  "avatar": "https://...",
  "bio": "Designer graphique",
  "location": "Paris",
  "rating": 4.8,
  "favorited_at": "2024-01-15 10:30:00"
}
```

---

## Limitations

- Un utilisateur ne peut pas s'ajouter lui-même en favori
- Les favoris sont personnels et non visibles aux autres utilisateurs
- Les favoris ne sont pas synchronisés sur plusieurs appareils
- Aucune limite sur le nombre de favoris

---

## Codes d'erreur HTTP

| Code | Message | Description |
|------|---------|-------------|
| 200 | OK | Requête réussie |
| 400 | Bad Request | Paramètres manquants ou invalides |
| 401 | Unauthorized | Authentification requise |
| 403 | Forbidden | Accès refusé |
| 404 | Not Found | Utilisateur introuvable |
| 405 | Method Not Allowed | Méthode HTTP non autorisée |
| 500 | Server Error | Erreur serveur |

---

## Sécurité

- Chaque utilisateur ne peut gérer que ses propres favoris
- Les favoris sont liés au profil utilisateur via une relation unique
- Les doublons sont gérés (impossible d'ajouter deux fois le même favori)

---

## Bonnes pratiques

1. **Affichage du statut** : Utiliser l'endpoint `check` pour afficher le bon icone
2. **Utiliser toggle** : Simplifier l'interface avec `toggle` au lieu de deux boutons
3. **Compteur** : Afficher le nombre de favoris avec `count`
4. **Tri** : Lister les favoris par date ou note
5. **Intégration** : Suggérer des favoris basés sur les services consultés

---

## Intégration UI recommandée

```html
<!-- Bouton dans le profil d'un prestataire -->
<button id="favorite-button" class="favorite-btn">
  <span class="heart-icon">♡</span>
  <span class="text">Ajouter aux favoris</span>
</button>

<script>
  const button = document.querySelector('#favorite-button');
  const userId = 15; // ID du prestataire

  // Vérifier le statut initial
  checkFavorite(userId);

  // Basculer au clic
  button.addEventListener('click', async () => {
    const response = await fetch('/api/favorites/favorites.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'toggle',
        favorited_user_id: userId
      })
    });

    const data = await response.json();
    updateButtonState(data.is_favorite);
  });

  async function checkFavorite(userId) {
    const response = await fetch('/api/favorites/favorites.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'check',
        favorited_user_id: userId
      })
    });

    const data = await response.json();
    updateButtonState(data.is_favorite);
  }

  function updateButtonState(isFavorite) {
    if (isFavorite) {
      button.classList.add('active');
      button.querySelector('.heart-icon').textContent = '♥';
    } else {
      button.classList.remove('active');
      button.querySelector('.heart-icon').textContent = '♡';
    }
  }
</script>
```

---

## Ressources

- [Documentation API](API.md)
- [Profils (PROFILS.md)](PROFILS.md)
- [Services (SERVICES.md)](SERVICES.md)
- [Documentation Complète](../DOCUMENTATION.md)

---

<div align="center">
[← Paramètres](PARAMETRES.md) • [API Générale →](API.md)
</div>
