# API Notifications

Documentation de l'API Notifications de Novatis.

---

## Vue d'ensemble

L'API Notifications gère les notifications système, les alertes de commandes, les messages et les mises à jour importantes envoyées aux utilisateurs.

**Base URL :** `/api/notifications/`

---

## Authentification

Tous les endpoints de cette API nécessitent une authentification utilisateur.

---

## Endpoints

### 1. Lister les notifications

**Méthode :** `GET`
**URL :** `/api/notifications/notifications.php`
**Authentification :** Requise

Récupère les notifications de l'utilisateur connecté.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| limit | integer | Non | Nombre de notifications à récupérer (défaut: 20) |
| unread_only | boolean | Non | Si true, retourne seulement les non lues |

**Exemple de requête :**

```javascript
const response = await fetch('/api/notifications/notifications.php?limit=10&unread_only=true', {
  method: 'GET'
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "notifications": [
    {
      "id": 1,
      "user_id": 10,
      "type": "order",
      "title": "Nouvelle commande",
      "message": "Vous avez reçu une nouvelle commande pour 'Design de logo'",
      "action_url": "/dashboard?tab=orders",
      "metadata": {
        "order_id": 42,
        "service_title": "Design de logo"
      },
      "is_read": 0,
      "created_at": "2024-01-20 15:30:00"
    },
    {
      "id": 2,
      "user_id": 10,
      "type": "message",
      "title": "Nouveau message",
      "message": "Vous avez reçu un message de Jean Dupont concernant 'Service X'",
      "action_url": "/dashboard?tab=messages",
      "metadata": {
        "order_id": 42,
        "sender_id": 15,
        "unread_count": 2
      },
      "is_read": 0,
      "created_at": "2024-01-20 14:00:00"
    }
  ],
  "unread_count": 2
}
```

**Types de notifications :**
- `order` - Nouvelle commande, changement de statut
- `message` - Nouveau message
- `system` - Notification système
- `payment` - Paiement, remboursement
- `service` - Mise à jour de service
- `review` - Nouvel avis

---

### 2. Récupérer une notification spécifique

**Méthode :** `GET`
**URL :** `/api/notifications/get.php?id=ID`
**Authentification :** Requise

Récupère les détails d'une notification spécifique.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| id | integer | Oui | ID de la notification |

**Exemple de requête :**

```javascript
const response = await fetch('/api/notifications/get.php?id=1', {
  method: 'GET'
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "notification": {
    "id": 1,
    "user_id": 10,
    "type": "order",
    "title": "Nouvelle commande",
    "message": "Vous avez reçu une nouvelle commande",
    "action_url": "/dashboard?tab=orders",
    "metadata": { ... },
    "is_read": 0,
    "created_at": "2024-01-20 15:30:00"
  }
}
```

---

### 3. Marquer une notification comme lue

**Méthode :** `PUT`
**URL :** `/api/notifications/notifications.php`
**Authentification :** Requise

Marque une notification comme lue.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| notification_id | integer | Oui | ID de la notification |

**Exemple de requête :**

```javascript
const response = await fetch('/api/notifications/notifications.php', {
  method: 'PUT',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    notification_id: 1
  })
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "message": "Notification marquée comme lue"
}
```

---

### 4. Marquer toutes les notifications comme lues

**Méthode :** `PUT`
**URL :** `/api/notifications/notifications.php`
**Authentification :** Requise

Marque toutes les notifications non lues comme lues.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| mark_all_read | boolean | Oui | Doit être true |

**Exemple de requête :**

```javascript
const response = await fetch('/api/notifications/notifications.php', {
  method: 'PUT',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    mark_all_read: true
  })
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "message": "Toutes les notifications marquées comme lues"
}
```

---

### 5. Supprimer une notification

**Méthode :** `DELETE`
**URL :** `/api/notifications/notifications.php`
**Authentification :** Requise

Supprime une notification.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| notification_id | integer | Oui | ID de la notification |

**Exemple de requête :**

```javascript
const response = await fetch('/api/notifications/notifications.php', {
  method: 'DELETE',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    notification_id: 1
  })
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "message": "Notification supprimée"
}
```

---

### 6. Créer une notification (Interne)

**Méthode :** `POST`
**URL :** `/api/notifications/create_notification.php`
**Authentification :** Requise (Admin)

Crée une notification. Endpoint interne utilisé par le système.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| user_id | integer | Oui | ID du destinataire |
| type | string | Oui | Type de notification |
| title | string | Oui | Titre |
| message | string | Oui | Corps du message |
| action_url | string | Non | URL pour l'action |
| metadata | object | Non | Données supplémentaires en JSON |

---

### 7. Mettre à jour une notification (Interne)

**Méthode :** `PUT`
**URL :** `/api/notifications/update.php`
**Authentification :** Requise

Met à jour une notification (principalement pour marquer comme lue).

---

## Structure d'une notification

```json
{
  "id": 1,
  "user_id": 10,
  "type": "order",
  "title": "Titre court",
  "message": "Message détaillé",
  "action_url": "/dashboard?tab=orders",
  "metadata": {
    "order_id": 42,
    "additional_data": "value"
  },
  "is_read": 0,
  "created_at": "2024-01-20 15:30:00"
}
```

---

## Types de notifications et exemples

### Ordre (order)
```json
{
  "type": "order",
  "title": "Nouvelle commande",
  "message": "Vous avez reçu une nouvelle commande pour 'Design de logo'",
  "metadata": {
    "order_id": 42,
    "price": 50,
    "service_title": "Design de logo"
  }
}
```

### Message (message)
```json
{
  "type": "message",
  "title": "Nouveau message",
  "message": "Vous avez reçu un message de Jean Dupont",
  "metadata": {
    "order_id": 42,
    "sender_id": 15,
    "unread_count": 2
  }
}
```

### Avis (review)
```json
{
  "type": "review",
  "title": "Nouvelle évaluation",
  "message": "Jean Dupont vous a laissé une évaluation de 5/5 étoiles !",
  "metadata": {
    "order_id": 42,
    "rating": 5,
    "reviewer_id": 15
  }
}
```

### Service (service)
```json
{
  "type": "service",
  "title": "Service mis à jour",
  "message": "Le service 'Design de logo' a été mis à jour",
  "metadata": {
    "service_id": 5,
    "service_title": "Design de logo"
  }
}
```

### Système (system)
```json
{
  "type": "system",
  "title": "Notification système",
  "message": "Votre compte a été activé",
  "metadata": {}
}
```

---

## Flux de notification typique

1. **Événement système** : Une action se produit (nouvelle commande, message, etc.)
2. **Création** : Une notification est créée dans la base de données
3. **Affichage** : L'utilisateur voit la notification dans son interface
4. **Lecture** : L'utilisateur clique ou lit la notification
5. **Action** : La notification peut contenir un lien vers l'action correspondante

---

## Codes d'erreur HTTP

| Code | Message | Description |
|------|---------|-------------|
| 200 | OK | Requête réussie |
| 400 | Bad Request | Paramètres manquants ou invalides |
| 401 | Unauthorized | Authentification requise |
| 403 | Forbidden | Accès refusé |
| 404 | Not Found | Notification introuvable |
| 405 | Method Not Allowed | Méthode HTTP non autorisée |
| 500 | Server Error | Erreur serveur |

---

## Synchronisation des messages

L'API synchronise automatiquement les messages non lus avec les notifications :
- Lorsqu'une notification est appelée, les messages non lus sont vérifiés
- Une notification est créée pour chaque conversation avec des messages non lus
- Les notifications sont liées aux commandes via les métadonnées

---

## Ressources

- [Documentation API](API.md)
- [Messages (MESSAGES.md)](MESSAGES.md)
- [Commandes (COMMANDES.md)](COMMANDES.md)
- [Documentation Complète](../DOCUMENTATION.md)

---

<div align="center">
[← Messages](MESSAGES.md) • [Avis →](AVIS.md)
</div>
