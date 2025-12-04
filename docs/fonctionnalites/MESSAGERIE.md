# 💬 Messagerie

Documentation complète du système de messagerie entre clients et prestataires.

---

## 📋 Vue d'ensemble

Le système de messagerie de Novatis permet une communication directe et fluide entre les clients et les prestataires. Les utilisateurs peuvent envoyer et recevoir des messages instantanés, avec un historique complet des conversations. Le système inclut les notifications en temps réel, la lecture des messages et une interface intuitive pour gérer plusieurs conversations simultanées.

---

## ✨ Fonctionnalités

### 1. Conversations

**Fonctionnement :**
- Création automatique d'une conversation lors du premier message
- Liste de toutes les conversations actives
- Tri par date du dernier message
- Indicateur de messages non lus
- Avatar et nom du contact visible
- Aperçu du dernier message

**Code exemple :**
```javascript
// Récupérer les conversations
const getConversations = async () => {
  const response = await fetch(`${BASE_URL}/api/messages/conversations.php`, {
    method: 'GET',
    headers: { 'Content-Type': 'application/json' }
  });
  return await response.json();
};
```

### 2. Envoi de Messages

**Page :** `/messages?conversation_id=XXX`

**Fonctionnement :**
- Rédacteur de message avec validation
- Support des messages texte
- Limite de caractères adaptée
- Envoi par bouton ou raccourci clavier (Ctrl+Enter)
- Indication d'envoi en cours
- Confirmation de succès/erreur

**Validations :**
- Message non vide
- Longueur minimale respectée
- Longueur maximale respectée
- Utilisateur authentifié
- Conversation valide

### 3. Réception de Messages

**Notifications :**
- Badge sur l'icône messagerie
- Notification toast en bas de page
- Notification navigateur (optionnel)
- Email de notification (optionnel)

**Fonctionnement :**
- Chargement automatique des nouveaux messages
- Défilement automatique vers le dernier message
- Marquer comme lu automatiquement
- Historique complet visible

### 4. Statut des Messages

**États possibles :**
- **Envoyé** : Message dans la file d'attente
- **Livré** : Message reçu par le serveur
- **Lu** : Message consulté par le destinataire
- **Erreur** : Échec de l'envoi

**Affichage :**
- Icône de statut à côté du message
- Timestamp de l'envoi
- Timestamp de la lecture

### 5. Suppression de Conversations

**Fonctionnement :**
- Suppression douce (archivage)
- Possibilité de restaurer
- Confirmation avant suppression
- Effacement des messages associés

**Permissions :**
- Chaque utilisateur peut supprimer ses conversations
- Les participants ne voient plus la conversation

---

## 🎨 Interface Utilisateur

### Page Messagerie

**Emplacement :** `public/pages/messages.php`

**Layout :**
- **Panneau gauche** : Liste des conversations
  - Champ de recherche
  - Conversations triées
  - Indicateurs de non-lus

- **Panneau droit** : Zone de chat
  - En-tête avec nom du contact
  - Historique des messages
  - Zone de saisie avec bouton d'envoi

**Responsive :**
- Desktop : Deux panneaux côte à côte
- Mobile : Mode plein écran avec bouton retour

**Design :**
- Messages de l'utilisateur : Bulles à droite
- Messages du contact : Bulles à gauche
- Timestamps visibles au survol
- Avatars des utilisateurs

---

## 📡 API

Les endpoints API de messagerie sont documentés dans [API Messages](../api/MESSAGES.md).

**Endpoints principaux :**
- `GET /api/messages/conversations.php` - Lister les conversations
- `GET /api/messages/conversation.php?id=XXX` - Détails d'une conversation
- `POST /api/messages/send.php` - Envoyer un message
- `DELETE /api/messages/conversation.php?id=XXX` - Supprimer conversation
- `PUT /api/messages/mark-read.php` - Marquer comme lu
- `GET /api/messages/unread-count.php` - Nombre de non-lus

---

## 🐛 Dépannage

### Problèmes Courants

#### 1. Les messages ne s'envoient pas

**Causes possibles :**
- Connexion réseau instable
- Conversation invalide
- Utilisateur non authentifié
- Limite de caractères dépassée

**Solutions :**
```bash
# Vérifier les logs
tail -f storage/logs/app.log

# Vérifier l'authentification
curl -X GET http://localhost/Novatis/public/api/user.php
```

#### 2. Les messages ne s'affichent pas

**Vérifications :**
- Rechargement de la page
- Vider le cache du navigateur
- Vérifier la connexion à la base de données
- Vérifier les permissions de l'utilisateur

#### 3. Les notifications ne fonctionnent pas

**Vérifications :**
- Notifications du navigateur activées
- Permission d'affichage accordée
- Service Worker actif
- Configuration des e-mails SMTP

#### 4. Les vieilles conversations restent visibles

**Solution :**
```sql
-- Vérifier l'état des conversations
SELECT * FROM messages WHERE conversation_id = 'XXX' AND deleted_at IS NOT NULL;

-- Forcer la suppression
DELETE FROM messages WHERE conversation_id = 'XXX' AND deleted_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

---

## 📚 Ressources

- [Documentation API Messages](../api/MESSAGES.md)
- [Système de Notifications](NOTIFICATIONS.md)
- [Configuration des Paramètres](PARAMETRES.md)
- [Guide de Déploiement](../deploiement/DEPLOIEMENT.md)

---

<div align="center">

[← Retour à la Documentation](../DOCUMENTATION.md) • [API Messages →](../api/MESSAGES.md)

</div>
