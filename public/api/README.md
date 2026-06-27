# 📁 Structure de l'API Novatis

Cette documentation décrit l'organisation des fichiers API du projet Novatis.

## 📂 Organisation des dossiers

### 🔐 `/auth` - Authentification
Gestion de la connexion et de l'inscription des utilisateurs.
- `login.php` - Connexion utilisateur
- `register.php` - Inscription nouveau compte

**Utilisé par :**
- `pages/Autentification.php`

---

### 🏠 `/dashboard` - Tableau de bord
API pour le dashboard principal de l'application.
- `dashboard.php` - Données du dashboard

**Utilisé par :**
- `pages/Dashboard.php`

---

### 💼 `/services` - Services & Catégories
Gestion des services proposés par les freelances.
- `services.php` - CRUD des services
- `categories.php` - Gestion des catégories de services
- `portfolio.php` - Portfolio des prestataires

**Utilisé par :**
- `pages/Contact.php` (affichage services)
- `pages/Dashboard.php` (gestion services)

---

### 🛒 `/orders` - Commandes & Avis
Gestion des commandes et des évaluations.
- `orders.php` - Gestion des commandes (création, suivi, statut)
- `reviews.php` - Système d'évaluation (notes et commentaires)

**Utilisé par :**
- `pages/Contact.php` (passer commande)
- `pages/Dashboard.php` (suivi commandes)

---

### 💬 `/messaging` - Messagerie
Système de messagerie entre utilisateurs.
- `messages.php` - Envoi/réception de messages entre acheteurs et vendeurs

**Utilisé par :**
- `pages/Dashboard.php` (conversations)

---

### 🔔 `/notifications` - Notifications
Système de notifications en temps réel.
- `notifications.php` - Récupération et marquage des notifications
- `create_notification.php` - Fonction helper pour créer des notifications

**Utilisé par :**
- `Header.php` (affichage notifications)
- Autres APIs (création automatique de notifications)

---

### ⚙️ `/parametres` - Paramètres
Configuration du compte utilisateur.
- `settings.php` - Gestion des paramètres (profil, sécurité, confidentialité, etc.)

**Utilisé par :**
- `pages/Parametres.php`

---

### 👥 `/admin` - Administration
Outils d'administration (réservé aux admins).
- `users.php` - Gestion des utilisateurs
- `support.php` - Support et tickets

**Utilisé par :**
- `pages/Dashboard.php` (panel admin)

---

## 🔄 Dépendances entre APIs

```
orders.php ──┐
reviews.php ─┼──> create_notification.php
messages.php ┘
```

## 📝 Notes importantes

### Chemins relatifs
Les fichiers dans les sous-dossiers utilisent :
- `__DIR__ . '/../../../config/config.php'` pour accéder à la config
- `__DIR__ . '/../notifications/create_notification.php'` pour les notifications

### Standards
- Toutes les APIs retournent du JSON
- Authentification requise (sauf auth)
- Gestion des erreurs uniformisée
- Logging des actions importantes

## 🔧 Base de données

Les APIs utilisent la connexion PDO définie dans `config/config.php`.

Pour les paramètres avancés, voir : `database/novatis_settings.sql`
