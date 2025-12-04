# 📚 Documentation Complète - Novatis

Bienvenue dans la documentation complète de la plateforme Novatis !

---

## 📋 Table des Matières

1. [Introduction](#introduction)
2. [Installation](#installation)
3. [Fonctionnalités](#fonctionnalités)
4. [API](#api)
5. [Déploiement](#déploiement)
6. [Support](#support)

---

## 🎯 Introduction

Novatis est une plateforme professionnelle de mise en relation entre clients et prestataires de services. Elle offre un environnement complet pour la gestion des services, commandes, paiements et communications.

### Technologies Utilisées

- **Backend**: PHP 8.0+, MySQL/MariaDB
- **Frontend**: React 18, Tailwind CSS 3
- **Architecture**: MVC, Repository Pattern, PSR-4
- **Sécurité**: Authentification OAuth, 2FA, Sessions sécurisées

---

## 🚀 Installation

### Installation Rapide

```bash
# Cloner le projet
git clone [URL_DU_REPO] Novatis
cd Novatis

# Installer les dépendances
composer install

# Configurer l'environnement
cp .env.example .env
# Éditez .env avec vos paramètres

# Créer la base de données
mysql -u root -p -e "CREATE DATABASE novatis_db CHARACTER SET utf8mb4"

# Importer la structure SQL
# (Depuis phpMyAdmin ou database/backups/)

# Accéder au site
http://localhost/Novatis/public/
```

### Configuration

#### Fichier .env

```env
# Application
APP_NAME=Novatis
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/Novatis

# Base de données
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=novatis_db
DB_USERNAME=root
DB_PASSWORD=

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre_email@gmail.com
MAIL_PASSWORD=votre_mot_de_passe_app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@novatis.com
MAIL_FROM_NAME="${APP_NAME}"

# OAuth (optionnel)
GOOGLE_CLIENT_ID=votre_google_client_id
GOOGLE_CLIENT_SECRET=votre_google_client_secret
MICROSOFT_CLIENT_ID=votre_microsoft_client_id
MICROSOFT_CLIENT_SECRET=votre_microsoft_client_secret
GITHUB_CLIENT_ID=votre_github_client_id
GITHUB_CLIENT_SECRET=votre_github_client_secret
```

---

## 🔧 Fonctionnalités

Novatis offre un ensemble complet de fonctionnalités pour gérer une marketplace de services professionnels.

### 📖 Documentation des Fonctionnalités

**[→ Documentation complète des fonctionnalités](fonctionnalites/FONCTIONNALITES.md)**

Novatis offre 11 fonctionnalités principales :

- 🔐 **Authentification** - Inscription, connexion, OAuth (Google, Microsoft, GitHub), 2FA
- 👤 **Gestion des Profils** - Profils utilisateurs et prestataires avec portfolios
- 💼 **Services** - Publication, recherche et gestion de services par catégories
- 📦 **Commandes** - Système complet de commandes et suivi des projets
- 💬 **Messagerie** - Communication en temps réel entre clients et prestataires
- 🔔 **Notifications** - Alertes en temps réel et historique complet
- ⭐ **Avis et Évaluations** - Système d'évaluation et de réputation
- ❤️ **Favoris** - Sauvegarde de prestataires favoris
- ⚙️ **Paramètres** - Configuration complète du compte
- 🌍 **Multi-langues** - Support Français et Anglais avec i18next
- 🌓 **Thème Clair/Sombre** - Mode sombre automatique avec préférences

→ **Pour plus de détails sur chaque fonctionnalité, consultez [la documentation complète](fonctionnalites/FONCTIONNALITES.md)**

---

## 🌐 API

Novatis expose une API REST complète pour toutes les opérations de la plateforme.

### 📖 Documentation API

**[→ Documentation API Complète](api/API.md)**

La documentation API couvre :
- Authentification et sécurité
- Tous les endpoints disponibles
- Paramètres de requêtes et réponses
- Exemples de requêtes
- Codes d'erreur et gestion

---

## 🚀 Déploiement

Pour déployer Novatis en production :

**[→ Guide de Déploiement Complet](deploiement/DEPLOIEMENT.md)**

Le guide couvre :
- Préparation du serveur
- Configuration de production
- Sécurité et optimisations
- Mise en production
- Maintenance

---

## 🐛 Support

### Dépannage

Si vous rencontrez des problèmes :

1. **Vérifiez la configuration** :
   - Fichier `.env` correctement configuré
   - Base de données accessible
   - Permissions correctes sur les dossiers

2. **Activez le mode debug** :
   ```env
   APP_DEBUG=true
   ```

3. **Consultez les logs** :
   - Logs de l'application : `storage/logs/`
   - Logs Apache : `C:\xampp\apache\logs\` (Windows) ou `/var/log/apache2/` (Linux)

### Erreurs Courantes

#### Erreur 500 - Internal Server Error

**Causes possibles :**
- Fichier `.env` manquant ou mal configuré
- Erreur de connexion à la base de données
- Erreur PHP dans le code

**Solution :**
```bash
# Vérifier les logs
tail -f storage/logs/app.log

# Activer le debug
APP_DEBUG=true dans .env
```

#### CSS/JS ne se chargent pas (404)

**Cause :** BASE_URL mal configuré

**Solution :**
```env
# Dans .env
APP_URL=http://localhost/Novatis
```

#### Connexion à la base de données échoue

**Solution :**
```env
# Vérifier les paramètres dans .env
DB_HOST=localhost
DB_DATABASE=novatis_db
DB_USERNAME=root
DB_PASSWORD=votre_mot_de_passe
```

### Contact

Pour toute question ou problème :

1. Consultez cette documentation
2. Vérifiez les logs d'erreur
3. Créez une issue sur GitHub
4. Contactez l'équipe de support

---

## 📊 Ressources Additionnelles

- [Guide d'Installation Rapide](../README.md#installation-express)
- [Structure du Projet](../README.md#structure-du-projet)
- [Technologies Utilisées](../README.md#technologies)
- [Sécurité](../README.md#sécurité)

---

<div align="center">

**Documentation maintenue par l'équipe Novatis**

[Retour au README](../README.md) • [Fonctionnalités](fonctionnalites/FONCTIONNALITES.md) • [API](api/API.md) • [Déploiement](deploiement/DEPLOIEMENT.md)

</div>

---

*Dernière mise à jour : Octobre 2025*
