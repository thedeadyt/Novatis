# API Messagerie

Documentation de l'API Messagerie de Novatis.

---

## Vue d'ensemble

L'API Messagerie permet aux acheteurs et vendeurs de communiquer directement concernant leurs commandes. Les messages sont organisés par commande.

**Base URL :** `/api/messaging/`

---

## Authentification

Tous les endpoints de cette API nécessitent une authentification utilisateur.

---

## Endpoints

### 1. Lister les conversations

**Méthode :** `GET`
**URL :** `/api/messaging/messages.php?conversations`
**Authentification :** Requise

Récupère toutes les conversations (groupes de messages par commande) de l'utilisateur connecté.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| conversations | boolean | Oui | Doit être présent pour récupérer les conversations |

**Exemple de requête :**

```javascript
const response = await fetch('/api/messaging/messages.php?conversations', {
  method: 'GET'
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "conversations": [
    {
      "contact_id": 15,
      "contact_name": "Marie Martin",
      "contact_avatar": "https://...",
      "order_id": 42,
      "order_title": "Service Title",
      "last_message": "Merci pour votre commande !",
      "last_message_time": "2024-01-20 15:30:00",
      "unread_count": 2
    }
  ]
}
```

---

### 2. Récupérer les messages d'une conversation

**Méthode :** `GET`
**URL :** `/api/messaging/messages.php?order_id=ID`
**Authentification :** Requise

Récupère tous les messages d'une commande spécifique.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| order_id | integer | Oui | ID de la commande |

**Exemple de requête :**

```javascript
const response = await fetch('/api/messaging/messages.php?order_id=42', {
  method: 'GET'
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "messages": [
    {
      "id": 1,
      "order_id": 42,
      "sender_id": 15,
      "sender_name": "Marie Martin",
      "sender_avatar": "https://...",
      "content": "Bonjour ! J'ai bien reçu votre commande.",
      "is_read": 1,
      "created_at": "2024-01-15 10:30:00"
    },
    {
      "id": 2,
      "order_id": 42,
      "sender_id": 10,
      "sender_name": "Jean Dupont",
      "sender_avatar": "https://...",
      "content": "Merci ! Quand pouvez-vous démarrer ?",
      "is_read": 1,
      "created_at": "2024-01-15 11:00:00"
    }
  ],
  "order": {
    "id": 42,
    "service_id": 5,
    "buyer_id": 10,
    "seller_id": 15,
    "status": "in_progress",
    "other_user_id": 15
  }
}
```

**Note :** Les messages sont automatiquement marqués comme lus lors de la récupération.

---

### 3. Envoyer un message

**Méthode :** `POST`
**URL :** `/api/messaging/messages.php`
**Authentification :** Requise

Envoie un nouveau message dans une conversation.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| order_id | integer | Oui | ID de la commande |
| content | string | Oui | Contenu du message (non vide) |

**Exemple de requête :**

```javascript
const response = await fetch('/api/messaging/messages.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    order_id: 42,
    content: 'Je voudrais apporter quelques modifications au design.'
  })
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "message": "Message envoyé"
}
```

**Réponse (erreur) :**

```json
{
  "success": false,
  "error": "Message vide"
}
```

---

### 4. Marquer les messages comme lus

**Méthode :** `GET`
**URL :** `/api/messaging/messages.php?order_id=ID`
**Authentification :** Requise

Marque automatiquement tous les messages d'une conversation comme lus lors de la récupération.

**Note :** Cette action est automatique lors de l'appel de l'endpoint 2.

---

## Structure d'un message

```json
{
  "id": 1,
  "order_id": 42,
  "sender_id": 15,
  "sender_name": "Marie Martin",
  "sender_avatar": "https://avatar.jpg",
  "content": "Contenu du message",
  "is_read": 1,
  "created_at": "2024-01-15 10:30:00"
}
```

---

## Règles de messagerie

1. **Accès** : Seuls les deux parties de la commande (acheteur et vendeur) peuvent voir les messages
2. **Création** : N'importe lequel peut envoyer un message
3. **Lecture** : Les messages sont marqués comme lus automatiquement
4. **Notifications** : Le destinataire reçoit une notification de nouveau message
5. **Synchronisation** : Les messages non lus sont synchronisés avec les notifications

---

## Cas d'usage courants

### Initialiser une commande avec message

```javascript
// 1. Créer la commande
const orderResponse = await fetch('/api/orders/orders.php', {
  method: 'POST',
  body: new URLSearchParams({
    service_id: 5,
    seller_id: 15,
    buyer_id: 10,
    price: 50,
    message: 'Pouvez-vous commencer cette semaine ?'
  })
});
const orderData = await orderResponse.json();
```

### Chat en temps réel

```javascript
// Polling - Récupérer les messages toutes les 3 secondes
setInterval(async () => {
  const response = await fetch('/api/messaging/messages.php?order_id=42');
  const data = await response.json();
  displayMessages(data.messages);
}, 3000);
```

---

## Codes d'erreur HTTP

| Code | Message | Description |
|------|---------|-------------|
| 200 | OK | Requête réussie |
| 400 | Bad Request | Paramètres manquants ou invalides |
| 401 | Unauthorized | Authentification requise |
| 403 | Forbidden | Accès refusé (pas partie de la commande) |
| 404 | Not Found | Commande ou message introuvable |
| 405 | Method Not Allowed | Méthode HTTP non autorisée |
| 500 | Server Error | Erreur serveur |

---

## Limitations

- **Longueur du message** : Pas de limite stricte (texte)
- **Format** : Texte brut uniquement (pas de HTML, markdown limité)
- **Attachements** : Non supportés actuellement
- **Suppression** : Les messages ne peuvent pas être supprimés

---

## Notifications associées

Lorsqu'un message est reçu :
1. Une notification est créée pour le destinataire
2. Un email peut être envoyé (selon les préférences)
3. La notification est marquée comme lue lors de la visualisation

---

## Ressources

- [Documentation API](API.md)
- [Commandes (COMMANDES.md)](COMMANDES.md)
- [Notifications (NOTIFICATIONS.md)](NOTIFICATIONS.md)
- [Documentation Complète](../DOCUMENTATION.md)

---

<div align="center">
[← Commandes](COMMANDES.md) • [Notifications →](NOTIFICATIONS.md)
</div>
