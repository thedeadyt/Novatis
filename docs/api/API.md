# 🌐 API Novatis

Documentation complète de l'API REST de la plateforme Novatis.

---

## 📋 Vue d'ensemble

Novatis est une plateforme de services en ligne qui permet aux utilisateurs de proposer et de commander des services. L'API REST fournit tous les endpoints nécessaires pour interagir avec la plateforme.

**URL de base :** `https://votre-domaine.com/api/`

**Format des réponses :** JSON

---

## 🔑 Authentification globale

La plupart des endpoints Novatis nécessitent une authentification utilisateur. L'authentification se fait via les sessions PHP. Après une connexion réussie, l'utilisateur reçoit une session valide stockée côté serveur.

### Endpoints sans authentification requise
- POST `/auth/register.php` - Créer un compte
- POST `/auth/login.php` - Se connecter
- GET `/oauth/authorize.php` - Initier authentification OAuth
- GET `/oauth/callback.php` - Callback OAuth
- GET `/home/categories.php` - Lister les catégories (public)
- GET `/home/recent-providers.php` - Lister les prestataires récents (public)

### Endpoints requérant une authentification
Tous les autres endpoints nécessitent une session valide.

---

## 📡 Format des requêtes et réponses

### Requête

```javascript
const response = await fetch('https://votre-domaine.com/api/auth/login.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    email: 'utilisateur@example.com',
    password: 'mot_de_passe'
  })
});
```

### Réponse réussie

```json
{
  "success": true,
  "message": "Connexion réussie",
  "data": {
    "id": 1,
    "firstname": "Jean",
    "lastname": "Dupont",
    "email": "jean@example.com",
    "pseudo": "jeandupont"
  }
}
```

### Réponse d'erreur

```json
{
  "success": false,
  "error": "Email/pseudo et mot de passe requis"
}
```

---

## 📊 Codes HTTP

| Code | Signification |
|------|---------------|
| 200 | OK - Requête réussie |
| 201 | Created - Ressource créée |
| 400 | Bad Request - Paramètres manquants ou invalides |
| 401 | Unauthorized - Authentification requise |
| 403 | Forbidden - Accès refusé (permissions insuffisantes) |
| 404 | Not Found - Ressource introuvable |
| 405 | Method Not Allowed - Méthode HTTP non autorisée |
| 500 | Internal Server Error - Erreur serveur |

---

## 📚 Index des APIs

### 🔐 Authentification

**[→ Documentation complète](auth/AUTH.md)**

Endpoints pour l'inscription, la connexion, la déconnexion et la gestion des mots de passe.

- Inscription
- Connexion
- Déconnexion
- Mot de passe oublié
- Réinitialisation de mot de passe
- Authentification à deux facteurs (2FA)

### 🔗 OAuth

**[→ Documentation complète](auth/OAUTH.md)**

Authentification via les fournisseurs OAuth (Google, Microsoft, GitHub).

- Initier connexion OAuth
- Callback OAuth
- Lier un compte OAuth
- Dissocier un compte OAuth

### 👤 Profils Utilisateur

**[→ Documentation complète](parametres/PROFILS.md)**

Gestion des profils utilisateurs et prestataires.

- Consulter un profil
- Modifier son profil
- Upload de photo de profil
- Gestion du portfolio
- Statistiques

### 💼 Services

**[→ Documentation complète](services/SERVICES.md)**

CRUD complet des services proposés par les prestataires.

- Lister les services
- Rechercher des services
- Créer un service
- Modifier un service
- Supprimer un service
- Lister les catégories
- Services prédéfinis

### 📦 Commandes

**[→ Documentation complète](commandes/COMMANDES.md)**

Gestion des commandes entre clients et prestataires.

- Lister les commandes
- Créer une commande
- Modifier le statut d'une commande
- Annuler une commande
- Valider une commande

### ⭐ Avis et Évaluations

**[→ Documentation complète](commandes/AVIS.md)**

Système d'évaluation des services et prestataires.

- Lister les avis
- Créer un avis
- Modifier un avis
- Supprimer un avis
- Répondre à un avis

### 💬 Messagerie

**[→ Documentation complète](messagerie/MESSAGES.md)**

Communication entre clients et prestataires.

- Lister les conversations
- Lister les messages
- Envoyer un message
- Marquer comme lu
- Supprimer une conversation

### 🔔 Notifications

**[→ Documentation complète](notifications/NOTIFICATIONS.md)**

Système de notifications en temps réel.

- Lister les notifications
- Marquer comme lu
- Marquer toutes comme lues
- Supprimer une notification
- Créer une notification (interne)

### ⚙️ Paramètres

**[→ Documentation complète](parametres/PARAMETRES.md)**

Configuration et gestion du compte utilisateur.

- Obtenir les paramètres
- Modifier les paramètres
- Changer le mot de passe
- Activer/Désactiver 2FA
- Configurer les notifications
- Supprimer le compte

### ❤️ Favoris

**[→ Documentation complète](parametres/FAVORIS.md)**

Gestion des prestataires favoris.

- Lister les favoris
- Ajouter un favori
- Retirer un favori
- Vérifier si en favoris
- Basculer le statut favori

---

## 💡 Exemples d'utilisation courante

### Se connecter

```javascript
const response = await fetch('/api/auth/login.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'user@example.com',
    password: 'password123'
  })
});
const data = await response.json();

if (data.success) {
  console.log('Connecté:', data.data);
} else {
  console.error('Erreur:', data.error);
}
```

### Créer un service

```javascript
const response = await fetch('/api/services/services.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    action: 'create',
    title: 'Design de logo professionnel',
    description: 'Je crée des logos professionnels et modernes',
    category_id: 3,
    price: 50,
    delivery_days: 3
  })
});
const data = await response.json();
```

### Envoyer un message

```javascript
const response = await fetch('/api/messaging/messages.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    action: 'send',
    order_id: 5,
    content: 'Bonjour, pouvez-vous me donner plus de détails sur le projet ?'
  })
});
const data = await response.json();
```

### Passer une commande

```javascript
const response = await fetch('/api/orders/orders.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    action: 'create',
    service_id: 12,
    description: 'J\'ai besoin d\'un logo pour mon entreprise',
    address: '123 Rue Example, Paris',
    preferred_date: '2025-11-15'
  })
});
const data = await response.json();
```

---

## ⚠️ Gestion des erreurs

Tous les endpoints retournent un objet JSON avec un champ `success` indiquant si la requête a réussi.

En cas d'erreur, consultez le champ `error` ou `message` pour connaître la raison.

```javascript
const response = await fetch('/api/services/services.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ action: 'create', title: '' })
});

if (!response.ok) {
  console.error('Erreur HTTP :', response.status);
}

const data = await response.json();
if (!data.success) {
  console.error('Erreur API :', data.error);
  // Gérer l'erreur (afficher un message, etc.)
}
```

### Codes d'erreur courants

| Code | Erreur | Solution |
|------|--------|----------|
| 400 | Paramètres manquants | Vérifier les paramètres requis |
| 401 | Non authentifié | Se connecter d'abord |
| 403 | Accès refusé | Vérifier les permissions |
| 404 | Ressource introuvable | Vérifier l'ID de la ressource |
| 500 | Erreur serveur | Contacter le support |

---

## 🚦 Limite de taux (Rate Limiting)

Actuellement, aucune limite de taux stricte n'est implémentée. Veuillez utiliser l'API de manière responsable.

**Bonnes pratiques :**
- Ne pas faire de requêtes en boucle rapide
- Utiliser la pagination pour les listes longues
- Mettre en cache les données statiques (catégories, etc.)
- Gérer les erreurs correctement

---

## 🔧 Environnements

### Développement

```
URL: http://localhost/Novatis/public/api/
Debug: Activé
Logs: storage/logs/
```

### Production

```
URL: https://votredomaine.com/api/
Debug: Désactivé
Logs: storage/logs/ (accès restreint)
SSL: Obligatoire
```

---

## 📖 Support et documentation

Pour plus de détails sur chaque endpoint, consultez la documentation spécifique :

| API | Documentation | Description |
|-----|---------------|-------------|
| 🔐 **Authentification** | [AUTH.md](auth/AUTH.md) | Inscription, connexion, mot de passe |
| 🔗 **OAuth** | [OAUTH.md](auth/OAUTH.md) | Google, Microsoft, GitHub |
| 👤 **Profils** | [PROFILS.md](parametres/PROFILS.md) | Gestion des profils |
| 💼 **Services** | [SERVICES.md](services/SERVICES.md) | CRUD des services |
| 📦 **Commandes** | [COMMANDES.md](commandes/COMMANDES.md) | Gestion des commandes |
| ⭐ **Avis** | [AVIS.md](commandes/AVIS.md) | Évaluations |
| 💬 **Messages** | [MESSAGES.md](messagerie/MESSAGES.md) | Messagerie |
| 🔔 **Notifications** | [NOTIFICATIONS.md](notifications/NOTIFICATIONS.md) | Notifications |
| ⚙️ **Paramètres** | [PARAMETRES.md](parametres/PARAMETRES.md) | Configuration |
| ❤️ **Favoris** | [FAVORIS.md](parametres/FAVORIS.md) | Favoris |

---

## 🐛 Problèmes courants

### 1. Erreur CORS

Si vous développez une application frontend séparée, assurez-vous que les headers CORS sont correctement configurés.

### 2. Session expirée

Les sessions expirent après 24h d'inactivité. L'utilisateur doit se reconnecter.

### 3. Upload de fichiers

Pour l'upload de fichiers (photos), utilisez `FormData` au lieu de JSON.

```javascript
const formData = new FormData();
formData.append('photo', fileInput.files[0]);

const response = await fetch('/api/profil/upload-photo.php', {
  method: 'POST',
  body: formData
});
```

---

<div align="center">

**Documentation maintenue par l'équipe Novatis**

[← Retour à la Documentation](../DOCUMENTATION.md) • [Fonctionnalités](../fonctionnalites/FONCTIONNALITES.md) • [Déploiement](../deploiement/DEPLOIEMENT.md)

</div>

---

*Dernière mise à jour : Octobre 2025*
