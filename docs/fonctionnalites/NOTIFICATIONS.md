# 🔔 Notifications

Documentation complète du système de notifications en temps réel.

---

## 📋 Vue d'ensemble

Le système de notifications de Novatis offre une communication en temps réel avec les utilisateurs. Les notifications couvrent plusieurs canaux : notifications dans l'application, notifications navigateur, notifications par email et notifications push. Les utilisateurs peuvent personnaliser leurs préférences de notification et consulter l'historique complet de leurs notifications.

---

## ✨ Fonctionnalités

### 1. Types de Notifications

**Notifications disponibles :**

#### A. Messagerie
- Nouveau message reçu
- Réponse à un message
- Conversation archivée

#### B. Commandes
- Nouvelle commande reçue
- Statut de commande mis à jour
- Commande livrée
- Commande payée

#### C. Avis et Évaluations
- Nouvel avis reçu
- Réponse à un avis
- Avis signalé

#### D. Compte
- Nouvel abonnement
- Renouvellement d'abonnement
- Alerte de sécurité
- Changement de profil

### 2. Canaux de Notification

**In-App :**
- Badge sur l'icône de notification
- Centre de notifications avec liste
- Notifications toast temporaires
- Historique complet

**Navigateur :**
- Notifications desktop
- Pop-ups de notification
- Permissions du navigateur
- Paramètres par type

**Email :**
- Résumé quotidien/hebdomadaire
- Notifications immédiates (optionnel)
- Emails personnalisés
- Format HTML

**Push :**
- Notifications mobiles
- Service Worker
- Paramètres par type

### 3. Gestion des Notifications

**Préférences :**
- Activation/désactivation par canal
- Activation/désactivation par type
- Fréquence des résumés
- Horaires silencieux

**Interface :**
- Page dédiée : `/notifications`
- Centre de notifications : Icône de cloche
- Paramètres : `/Parametres?section=notifications`

**Code exemple :**
```javascript
// Récupérer les notifications
const getNotifications = async () => {
  const response = await fetch(`${BASE_URL}/api/notifications/list.php`, {
    method: 'GET',
    headers: { 'Content-Type': 'application/json' }
  });
  return await response.json();
};

// Marquer comme lue
const markAsRead = async (notificationId) => {
  return await fetch(`${BASE_URL}/api/notifications/read.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ notification_id: notificationId })
  });
};
```

### 4. Historique des Notifications

**Page :** `/notifications`

**Fonctionnalités :**
- Liste complète des notifications
- Tri par date (plus récentes en premier)
- Filtrage par type
- Filtrage par statut (lues/non-lues)
- Suppression de notifications
- Marquer comme lue/non-lue en masse

### 5. Statut des Notifications

**États possibles :**
- **Non-lue** : Nouvelle notification
- **Lue** : Consultation confirmée
- **Archivée** : Masquée de la liste
- **Supprimée** : Suppression douce

### 6. Horaires Silencieux

**Fonctionnement :**
- Configuration des heures silencieuses
- Pas de notifications pendant ce créneau
- Les notifications sont stockées
- Résumé à la fin du créneau (optionnel)

**Configuration :**
```javascript
// Définir les horaires silencieux
const setSilentHours = async (startTime, endTime) => {
  return await fetch(`${BASE_URL}/api/notifications/silent-hours.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ start_time: startTime, end_time: endTime })
  });
};
```

---

## 🎨 Interface Utilisateur

### Centre de Notifications

**Emplacement :** Icône cloche en haut à droite

**Composants :**
- Badge avec nombre de non-lues
- Dropdown avec liste des 10 dernières
- Lien « Voir tout »
- Bouton de marquer tout comme lu

**Design :**
- Chaque notification affiche :
  - Icône du type
  - Titre et description
  - Timestamp relatif (il y a X minutes)
  - Statut de lecture (point coloré)

### Page Notifications

**Emplacement :** `public/pages/notifications.php`

**Layout :**
- **Barre de filtres** :
  - Filtres par type
  - Filtres par statut
  - Champ de recherche

- **Liste de notifications** :
  - Lignes cliquables
  - Actions : lire/supprimer
  - Sélection multiple

- **Actions en masse** :
  - Marquer comme lue
  - Archiver
  - Supprimer

### Notification Toast

**Affichage :**
- Position : bas-droit
- Auto-masquage après 5 secondes
- Icône du type
- Titre court
- Lien vers la source (optionnel)

---

## 📡 API

Les endpoints API de notifications sont documentés dans [API Notifications](../api/NOTIFICATIONS.md).

**Endpoints principaux :**
- `GET /api/notifications/list.php` - Lister les notifications
- `POST /api/notifications/read.php` - Marquer comme lue
- `DELETE /api/notifications/delete.php` - Supprimer notification
- `PUT /api/notifications/preferences.php` - Mettre à jour préférences
- `GET /api/notifications/preferences.php` - Récupérer préférences
- `POST /api/notifications/silent-hours.php` - Configurer horaires silencieux
- `GET /api/notifications/unread-count.php` - Nombre de non-lues

---

## 🐛 Dépannage

### Problèmes Courants

#### 1. Les notifications ne s'affichent pas

**Causes possibles :**
- Notifications désactivées dans les paramètres
- Service Worker non actif
- Permissions refusées au navigateur
- Connexion WebSocket fermée

**Solutions :**
```bash
# Vérifier les logs
tail -f storage/logs/notifications.log

# Vérifier la configuration
grep -r "NOTIFICATIONS_ENABLED" config/
```

#### 2. Les notifications email ne sont pas envoyées

**Vérifications :**
- Configuration SMTP dans `.env`
- Adresse email valide
- Notifications email activées dans les paramètres
- Queue d'email traitée

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre_email@gmail.com
MAIL_PASSWORD=votre_mot_de_passe_app
```

#### 3. Les notifications desktop ne demandent pas de permission

**Vérifications :**
- Service Worker enregistré
- Navigateur supporte les notifications
- HTTPS activé
- Permissions du navigateur acceptées

#### 4. Trop de notifications reçues

**Solutions :**
- Configurer les horaires silencieux
- Désactiver les types non souhaités
- Réduire la fréquence des notifications
- Archiver les anciennes notifications

#### 5. Les anciennes notifications restent non-lues

**Solution :**
```sql
-- Marquer toutes les notifications d'avant une date comme lues
UPDATE notifications SET is_read = 1 WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY);
```

---

## 📚 Ressources

- [Documentation API Notifications](../api/NOTIFICATIONS.md)
- [Système de Messagerie](MESSAGERIE.md)
- [Configuration des Paramètres](PARAMETRES.md)
- [Guide de Déploiement](../deploiement/DEPLOIEMENT.md)

---

<div align="center">

[← Retour à la Documentation](../DOCUMENTATION.md) • [API Notifications →](../api/NOTIFICATIONS.md)

</div>
