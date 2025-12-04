# 🚀 Novatis

> Plateforme professionnelle de mise en relation entre clients et prestataires de services

[![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php)](https://php.net)
[![React](https://img.shields.io/badge/React-18-61DAFB?logo=react)](https://reactjs.org)
[![Tailwind](https://img.shields.io/badge/Tailwind-3.0-38B2AC?logo=tailwindcss)](https://tailwindcss.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql)](https://mysql.com)

---

## 📖 Présentation

**Novatis** est une plateforme web moderne qui connecte des clients avec des prestataires de services professionnels. Elle offre un environnement sécurisé et intuitif pour la gestion complète des services : recherche, commande, paiement, et communication.

### ✨ Fonctionnalités Principales

- 🔐 **Authentification sécurisée** - Inscription, connexion, OAuth (Google, Microsoft, GitHub), 2FA
- 👤 **Gestion de profils** - Profils utilisateurs et prestataires complets avec portfolios
- 💼 **Marketplace de services** - Publication, recherche et filtrage de services par catégories
- 💬 **Messagerie intégrée** - Communication client-prestataire en temps réel
- 📦 **Gestion de commandes** - Système complet de commandes et suivi des projets
- ⭐ **Système d'avis** - Évaluations et commentaires vérifiés
- 🔔 **Notifications** - Alertes en temps réel et historique
- 🌍 **Multi-langues** - Français et Anglais (i18next)
- 🌓 **Thème clair/sombre** - Mode sombre automatique avec préférences utilisateur
- 📱 **Responsive** - Design adaptatif mobile, tablette et desktop

---

## 🚀 Démarrage Rapide

### Prérequis

- **PHP** >= 8.0
- **MySQL** >= 5.7 ou **MariaDB** >= 10.3
- **Composer** (gestionnaire de dépendances PHP)
- **Apache** avec mod_rewrite
- **Git** (optionnel)

### Installation Express

```bash
# 1. Cloner le projet
git clone [URL_DU_REPO] Novatis
cd Novatis

# 2. Installer les dépendances
composer install

# 3. Configurer l'environnement
cp .env.example .env
# Éditez .env avec vos paramètres

# 4. Créer la base de données
mysql -u root -p -e "CREATE DATABASE novatis_db CHARACTER SET utf8mb4"

# 5. Importer la structure SQL
# (Depuis phpMyAdmin ou fichiers dans database/backups/)

# 6. Accéder au site
http://localhost/Novatis/public/
```

📖 **Pour une installation détaillée, consultez [la documentation complète](docs/DOCUMENTATION.md)**

---

## 📚 Documentation

### 📖 Documentation Complète

**[→ Accéder à la documentation complète](docs/DOCUMENTATION.md)**

La documentation complète couvre :
- Guide d'installation détaillé
- Configuration et paramétrage
- Toutes les fonctionnalités en détail
- Documentation API complète
- Guide de déploiement
- Dépannage et support

### 🎯 Accès Rapide

| Besoin | Documentation |
|--------|---------------|
| 📖 **Documentation complète** | [DOCUMENTATION.md](docs/DOCUMENTATION.md) |
| 🚀 **Installation rapide** | [Installation](docs/guides/installation/INSTALLATION.md) |
| 🔧 **Fonctionnalités** | [Voir toutes les fonctionnalités](docs/DOCUMENTATION.md#fonctionnalités) |
| 🌐 **API** | [Documentation API](docs/api/API.md) |
| 🚀 **Déploiement** | [Guide de déploiement](docs/deploiement/DEPLOIEMENT.md) |
| 🐛 **Dépannage** | [Troubleshooting](docs/guides/troubleshooting/TROUBLESHOOTING.md) |

---

## 📁 Structure du Projet

```
Novatis/
├── bootstrap/          # Initialisation de l'application
├── config/             # Configuration (DB, mail, app)
├── database/           # SQL et migrations
├── docs/              # 📚 Documentation complète
├── includes/          # Composants réutilisables (Header, Footer)
├── public/            # 🌐 Racine web (DOCUMENT ROOT)
│   ├── api/          # Endpoints API REST
│   ├── assets/       # CSS, JS, Images
│   ├── locales/      # Traductions i18n
│   └── pages/        # Pages de l'application
├── scripts/          # Scripts utilitaires
├── src/              # Code source PHP (PSR-4)
├── storage/          # Logs, cache, uploads
└── vendor/           # Dépendances Composer
```

---

## 🛠️ Technologies

### Backend
- **PHP 8.0+** - Langage serveur
- **MySQL/MariaDB** - Base de données
- **Composer** - Gestionnaire de dépendances
- **PSR-4** - Autoloading
- **Dotenv** - Variables d'environnement

### Frontend
- **React 18** - Composants UI
- **Tailwind CSS 3** - Framework CSS utility-first
- **i18next** - Internationalisation
- **Babel** - Transpilation JSX

### Architecture
- **MVC Pattern** - Séparation des préoccupations
- **Repository Pattern** - Couche d'accès aux données
- **Singleton Pattern** - Gestion des connexions
- **PSR-12** - Standards de codage PHP

---

## 🔐 Sécurité

### Mesures Implémentées

✅ Variables d'environnement (`.env`)
✅ Mots de passe hashés (Bcrypt)
✅ Requêtes préparées (PDO)
✅ Sessions sécurisées
✅ Validation des entrées
✅ Protection XSS
✅ Protection CSRF
✅ Lazy Loading DB

---

## 🤝 Contribution

Les contributions sont les bienvenues !

1. **Fork** le projet
2. Créer une branche (`git checkout -b feature/NouvelleFeature`)
3. Commit (`git commit -m 'Ajout NouvelleFeature'`)
4. Push (`git push origin feature/NouvelleFeature`)
5. Ouvrir une **Pull Request**

---

## 🐛 Support

### En cas de problème

1. **Consultez** [la documentation complète](docs/DOCUMENTATION.md)
2. **Consultez** [le guide de dépannage](docs/guides/troubleshooting/TROUBLESHOOTING.md)
3. **Activez le debug** : `APP_DEBUG=true` dans `.env`
4. **Consultez les logs** : `storage/logs/` et `C:\xampp\apache\logs\`
5. **Créez une issue** sur GitHub

---

## 📄 Licence

**Propriétaire** - © 2024 Novatis. Tous droits réservés.

---

## 📊 Statut

- **Version:** 2.0.0
- **Statut:** 🟢 Actif
- **Dernière mise à jour:** Octobre 2025
- **Environnement:** Production Ready

---

<div align="center">

**Fait avec ❤️ par l'équipe Novatis**

[Documentation](docs/DOCUMENTATION.md) • [API](docs/API.md) • [Support](docs/guides/troubleshooting/TROUBLESHOOTING.md)

</div>
