# API Commandes

Documentation de l'API Commandes de Novatis.

---

## Vue d'ensemble

L'API Commandes gère le cycle de vie complet des commandes sur la plateforme Novatis. Elle permet de créer, consulter et modifier le statut des commandes entre acheteurs et vendeurs.

**Base URL :** `/api/orders/`

---

## Authentification

Tous les endpoints de cette API nécessitent une authentification utilisateur.

---

## Endpoints

### 1. Lister les commandes

**Méthode :** `GET`
**URL :** `/api/orders/orders.php`
**Authentification :** Requise

Récupère les commandes de l'utilisateur connecté (en tant qu'acheteur ou vendeur). Les admins voient toutes les commandes.

**Paramètres :** Aucun

**Exemple de requête :**

```javascript
const response = await fetch('/api/orders/orders.php', {
  method: 'GET'
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "orders": [
    {
      "id": 1,
      "service_id": 5,
      "buyer_id": 10,
      "seller_id": 15,
      "price": 50,
      "description": "Détails de la commande",
      "deadline": "2024-02-15 00:00:00",
      "status": "in_progress",
      "created_at": "2024-01-15 10:30:00",
      "service_title": "Design de logo",
      "buyer_name": "Jean Dupont",
      "seller_name": "Marie Martin",
      "buyer_avatar": "https://...",
      "seller_avatar": "https://...",
      "user_role": "buyer"
    }
  ]
}
```

**Statuts possibles :**
- `pending` - En attente d'acceptation par le vendeur
- `in_progress` - En cours de traitement
- `delivered` - Livrée
- `completed` - Complétée
- `cancelled` - Annulée

---

### 2. Créer une commande

**Méthode :** `POST`
**URL :** `/api/orders/orders.php`
**Authentification :** Requise

Crée une nouvelle commande pour un service.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| service_id | integer | Oui | ID du service à commander |
| seller_id | integer | Oui | ID du vendeur (propriétaire du service) |
| buyer_id | integer | Oui | ID de l'acheteur (l'utilisateur connecté) |
| price | float | Oui | Prix de la commande |
| description | string | Non | Description détaillée des besoins |
| message | string | Non | Message initial au vendeur |

**Exemple de requête :**

```javascript
const response = await fetch('/api/orders/orders.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded'
  },
  body: new URLSearchParams({
    service_id: 5,
    seller_id: 15,
    buyer_id: 10,
    price: 50,
    description: 'Je voudrais un logo pour mon entreprise...',
    message: 'Bonjour, pouvez-vous commencer rapidement ?'
  })
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "message": "Commande créée avec succès et message envoyé",
  "order_id": 42,
  "redirect": "/dashboard?tab=messages"
}
```

**Réponse (erreur) :**

```json
{
  "success": false,
  "error": "Service non trouvé ou inactif"
}
```

---

### 3. Modifier le statut d'une commande

**Méthode :** `PUT`
**URL :** `/api/orders/orders.php`
**Authentification :** Requise

Modifie le statut d'une commande. Seul le vendeur (ou un admin) peut modifier.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| order_id | integer | Oui | ID de la commande |
| status | string | Oui | Nouveau statut |

**Statuts acceptés :**
- `in_progress` - Commencer à travailler
- `delivered` - Marquer comme livrée
- `completed` - Marquer comme complétée
- `cancelled` - Annuler

**Exemple de requête :**

```javascript
const response = await fetch('/api/orders/orders.php', {
  method: 'PUT',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    order_id: 42,
    status: 'delivered'
  })
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "message": "Statut mis à jour"
}
```

---

### 4. Annuler une commande

**Méthode :** `PUT`
**URL :** `/api/orders/orders.php`
**Authentification :** Requise

Annule une commande. Le vendeur peut annuler une commande `pending` ou `in_progress`. L'acheteur peut aussi annuler.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| order_id | integer | Oui | ID de la commande à annuler |
| status | string | Oui | Valeur : `cancelled` |

**Exemple de requête :**

```javascript
const response = await fetch('/api/orders/orders.php', {
  method: 'PUT',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    order_id: 42,
    status: 'cancelled'
  })
});
const data = await response.json();
```

---

### 5. Lister les avis (Reviews)

**Méthode :** `GET`
**URL :** `/api/orders/reviews.php`
**Authentification :** Requise

Récupère les avis laissés par ou pour l'utilisateur connecté.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| order_id | integer | Non | Pour récupérer l'avis d'une commande spécifique |

**Exemple de requête :**

```javascript
const response = await fetch('/api/orders/reviews.php', {
  method: 'GET'
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "reviews": [
    {
      "id": 1,
      "order_id": 42,
      "reviewer_id": 10,
      "reviewee_id": 15,
      "rating": 5,
      "comment": "Excellent travail ! Très professionnel.",
      "created_at": "2024-01-20 15:30:00",
      "reviewer_name": "Jean Dupont",
      "reviewee_name": "Marie Martin"
    }
  ]
}
```

---

### 6. Créer un avis

**Méthode :** `POST`
**URL :** `/api/orders/reviews.php`
**Authentification :** Requise

Crée un nouvel avis pour une commande complétée.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| order_id | integer | Oui | ID de la commande à évaluer |
| rating | integer | Oui | Note de 1 à 5 étoiles |
| comment | string | Non | Commentaire détaillé |

**Exemple de requête :**

```javascript
const response = await fetch('/api/orders/reviews.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    order_id: 42,
    rating: 5,
    comment: 'Excellent travail ! Très professionnel et rapide.'
  })
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "message": "Évaluation ajoutée avec succès"
}
```

**Réponse (erreur) :**

```json
{
  "success": false,
  "error": "Vous avez déjà évalué cette commande"
}
```

---

## Flux de commande typique

1. **Création** : L'acheteur crée une commande (`pending`)
2. **Acceptation** : Le vendeur accepte et commence (`in_progress`)
3. **Livraison** : Le vendeur livre le service (`delivered`)
4. **Notification** : L'acheteur reçoit une demande d'évaluation
5. **Évaluation** : L'acheteur laisse un avis
6. **Complétion** : La commande est marquée comme complétée (`completed`)

---

## Notifications

- Nouvelle commande : notification au vendeur
- Changement de statut : notifications aux deux parties
- Livraison : message automatique demandant l'évaluation
- Avis : notification à la personne évaluée

---

## Codes d'erreur HTTP

| Code | Message | Description |
|------|---------|-------------|
| 200 | OK | Requête réussie |
| 400 | Bad Request | Paramètres manquants ou invalides |
| 401 | Unauthorized | Authentification requise |
| 403 | Forbidden | Accès refusé (pas propriétaire) |
| 404 | Not Found | Commande introuvable |
| 405 | Method Not Allowed | Méthode HTTP non autorisée |
| 500 | Server Error | Erreur serveur |

---

## Validations

- **Order ID** : Doit exister et appartenir à l'utilisateur
- **Statut** : Doit être un statut valide
- **Rating** : Doit être entre 1 et 5
- **Service** : Doit être actif et disponible

---

## Ressources

- [Documentation API](API.md)
- [Services (SERVICES.md)](SERVICES.md)
- [Messages (MESSAGES.md)](MESSAGES.md)
- [Documentation Complète](../DOCUMENTATION.md)

---

<div align="center">
[← Services](SERVICES.md) • [Messages →](MESSAGES.md)
</div>
