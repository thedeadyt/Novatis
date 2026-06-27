# 📚 Index de la Documentation Novatis

Index complet de toute la documentation disponible pour Novatis.

---

## 🎯 Démarrage Rapide

### Nouveau sur Novatis ?

1. **[README.md](../README.md)** - Vue d'ensemble du projet
2. **[Installation](guides/installation/INSTALLATION.md)** - Guide d'installation pas à pas
3. **[Documentation Complète](DOCUMENTATION.md)** - Documentation complète

---

## 📖 Documentation Principale

### Documentation Générale

| Document | Description | Lien |
|----------|-------------|------|
| **README** | Vue d'ensemble et démarrage rapide | [README.md](../README.md) |
| **Documentation Complète** | Documentation principale | [DOCUMENTATION.md](DOCUMENTATION.md) |
| **Index** | Ce fichier - Index complet | [INDEX.md](INDEX.md) |

---

## 🔧 Installation et Configuration

| Guide | Description | Lien |
|-------|-------------|------|
| **Installation** | Guide complet d'installation locale | [INSTALLATION.md](guides/installation/INSTALLATION.md) |
| **Déploiement** | Guide de déploiement en production | [DEPLOIEMENT.md](deploiement/DEPLOIEMENT.md) |
| **Dépannage** | Solutions aux problèmes courants | [TROUBLESHOOTING.md](guides/troubleshooting/TROUBLESHOOTING.md) |

---

## ✨ Fonctionnalités

### Documentation Générale

| Document | Lien |
|----------|------|
| **Vue d'ensemble des fonctionnalités** | [FONCTIONNALITES.md](fonctionnalites/FONCTIONNALITES.md) |

### Documentation Détaillée par Fonctionnalité

| Fonctionnalité | Description | Lien |
|----------------|-------------|------|
| 🔐 **Authentification** | Inscription, connexion, OAuth, 2FA | [AUTHENTIFICATION.md](fonctionnalites/AUTHENTIFICATION.md) |
| 👤 **Profils** | Gestion des profils utilisateurs | [PROFILS.md](fonctionnalites/PROFILS.md) |
| 💼 **Services** | Marketplace de services | [SERVICES.md](fonctionnalites/SERVICES.md) |
| 📦 **Commandes** | Gestion des commandes | [COMMANDES.md](fonctionnalites/COMMANDES.md) |
| 💬 **Messagerie** | Système de messagerie | [MESSAGERIE.md](fonctionnalites/MESSAGERIE.md) |
| 🔔 **Notifications** | Notifications en temps réel | [NOTIFICATIONS.md](fonctionnalites/NOTIFICATIONS.md) |
| ⭐ **Avis** | Système d'évaluations | [AVIS.md](fonctionnalites/AVIS.md) |
| ❤️ **Favoris** | Gestion des favoris | [FAVORIS.md](fonctionnalites/FAVORIS.md) |
| ⚙️ **Paramètres** | Configuration utilisateur | [PARAMETRES.md](fonctionnalites/PARAMETRES.md) |
| 🌍 **Multi-langues** | Système i18n | [MULTILANGUE.md](fonctionnalites/MULTILANGUE.md) |
| 🌓 **Thème** | Mode clair/sombre | [THEME.md](fonctionnalites/THEME.md) |

---

## 🌐 Documentation API

### API Générale

| Document | Description | Lien |
|----------|-------------|------|
| **API Générale** | Vue d'ensemble de l'API REST | [API.md](api/API.md) |

### APIs par Catégorie

#### Authentification

| API | Description | Lien |
|-----|-------------|------|
| **Auth** | Inscription, connexion, déconnexion | [AUTH.md](api/auth/AUTH.md) |
| **OAuth** | Google, Microsoft, GitHub | [OAUTH.md](api/auth/OAUTH.md) |

#### Services et Commandes

| API | Description | Lien |
|-----|-------------|------|
| **Services** | CRUD des services | [SERVICES.md](api/services/SERVICES.md) |
| **Commandes** | Gestion des commandes | [COMMANDES.md](api/commandes/COMMANDES.md) |
| **Avis** | Système d'évaluations | [AVIS.md](api/commandes/AVIS.md) |

#### Communication

| API | Description | Lien |
|-----|-------------|------|
| **Messages** | Messagerie | [MESSAGES.md](api/messagerie/MESSAGES.md) |
| **Notifications** | Notifications | [NOTIFICATIONS.md](api/notifications/NOTIFICATIONS.md) |

#### Utilisateur

| API | Description | Lien |
|-----|-------------|------|
| **Profils** | Gestion des profils | [PROFILS.md](api/parametres/PROFILS.md) |
| **Paramètres** | Configuration | [PARAMETRES.md](api/parametres/PARAMETRES.md) |
| **Favoris** | Favoris | [FAVORIS.md](api/parametres/FAVORIS.md) |

---

## 🚀 Déploiement

| Document | Description | Lien |
|----------|-------------|------|
| **Guide de Déploiement** | Déploiement en production | [DEPLOIEMENT.md](deploiement/DEPLOIEMENT.md) |

Le guide couvre :
- Configuration du serveur
- Installation sur serveur Linux
- Configuration Apache/Nginx
- Certificat SSL
- Optimisations
- Sauvegardes
- Maintenance

---

## 🐛 Support et Dépannage

| Document | Description | Lien |
|----------|-------------|------|
| **Troubleshooting** | Guide de dépannage complet | [TROUBLESHOOTING.md](guides/troubleshooting/TROUBLESHOOTING.md) |

Problèmes couverts :
- Erreurs serveur (500, 404, 403)
- Problèmes de base de données
- Problèmes d'affichage
- Problèmes d'authentification
- Problèmes de configuration
- Problèmes de performance
- Problèmes OAuth
- Problèmes d'email

---

## 📁 Structure de la Documentation

```
docs/
│
├── DOCUMENTATION.md          # Documentation principale
├── INDEX.md                  # Cet index
│
├── api/                      # Documentation API
│   ├── API.md               # Vue d'ensemble API
│   ├── auth/                # APIs d'authentification
│   │   ├── AUTH.md
│   │   └── OAUTH.md
│   ├── services/            # APIs de services
│   │   └── SERVICES.md
│   ├── commandes/           # APIs de commandes
│   │   ├── COMMANDES.md
│   │   └── AVIS.md
│   ├── messagerie/          # APIs de messagerie
│   │   └── MESSAGES.md
│   ├── notifications/       # APIs de notifications
│   │   └── NOTIFICATIONS.md
│   └── parametres/          # APIs de paramètres
│       ├── PROFILS.md
│       ├── PARAMETRES.md
│       └── FAVORIS.md
│
├── fonctionnalites/         # Documentation des fonctionnalités
│   ├── FONCTIONNALITES.md   # Vue d'ensemble
│   ├── AUTHENTIFICATION.md
│   ├── PROFILS.md
│   ├── SERVICES.md
│   ├── COMMANDES.md
│   ├── MESSAGERIE.md
│   ├── NOTIFICATIONS.md
│   ├── AVIS.md
│   ├── FAVORIS.md
│   ├── PARAMETRES.md
│   ├── MULTILANGUE.md
│   └── THEME.md
│
├── guides/                   # Guides pratiques
│   ├── installation/
│   │   └── INSTALLATION.md
│   └── troubleshooting/
│       └── TROUBLESHOOTING.md
│
└── deploiement/             # Déploiement
    └── DEPLOIEMENT.md
```

---

## 🎯 Parcours de Lecture Recommandés

### Pour un Développeur Débutant

1. [README.md](../README.md) - Vue d'ensemble
2. [INSTALLATION.md](guides/installation/INSTALLATION.md) - Installer Novatis
3. [DOCUMENTATION.md](DOCUMENTATION.md) - Parcourir la doc complète
4. [FONCTIONNALITES.md](fonctionnalites/FONCTIONNALITES.md) - Découvrir les fonctionnalités
5. [API.md](api/API.md) - Explorer l'API

### Pour un Développeur Expérimenté

1. [README.md](../README.md) - Vue d'ensemble rapide
2. [INSTALLATION.md](guides/installation/INSTALLATION.md) - Installation express
3. [API.md](api/API.md) - Documentation API complète
4. [DEPLOIEMENT.md](deploiement/DEPLOIEMENT.md) - Déploiement en production

### Pour un Administrateur Système

1. [README.md](../README.md) - Présentation
2. [INSTALLATION.md](guides/installation/INSTALLATION.md) - Prérequis et installation
3. [DEPLOIEMENT.md](deploiement/DEPLOIEMENT.md) - Configuration serveur
4. [TROUBLESHOOTING.md](guides/troubleshooting/TROUBLESHOOTING.md) - Dépannage

### Pour un Utilisateur Final

1. [README.md](../README.md) - Qu'est-ce que Novatis ?
2. [FONCTIONNALITES.md](fonctionnalites/FONCTIONNALITES.md) - Fonctionnalités disponibles
3. [Documentation spécifique](fonctionnalites/) - Fonctionnalité qui vous intéresse

---

## 📊 Statistiques de la Documentation

### Nombre de Fichiers

- **Total :** 26+ fichiers de documentation
- **API :** 11 fichiers
- **Fonctionnalités :** 11 fichiers
- **Guides :** 2 fichiers
- **Déploiement :** 1 fichier

### Couverture

- ✅ Installation complète
- ✅ Configuration
- ✅ Toutes les fonctionnalités
- ✅ API complète
- ✅ Déploiement production
- ✅ Dépannage
- ✅ Exemples de code

---

## 🔍 Recherche Rapide

### Par Besoin

**Je veux installer Novatis :**
- [Guide d'installation](guides/installation/INSTALLATION.md)

**Je veux comprendre comment fonctionne [fonctionnalité] :**
- [Documentation des fonctionnalités](fonctionnalites/)

**Je veux utiliser l'API :**
- [Documentation API](api/API.md)

**Je veux déployer en production :**
- [Guide de déploiement](deploiement/DEPLOIEMENT.md)

**J'ai un problème :**
- [Guide de dépannage](guides/troubleshooting/TROUBLESHOOTING.md)

### Par Technologie

**PHP :**
- [Installation](guides/installation/INSTALLATION.md)
- [Configuration](DOCUMENTATION.md#configuration)
- [Déploiement](deploiement/DEPLOIEMENT.md)

**API REST :**
- [Documentation API](api/API.md)
- [Authentification](api/auth/AUTH.md)
- [Endpoints](api/)

**Base de Données :**
- [Configuration DB](DOCUMENTATION.md#configuration)
- [Dépannage DB](guides/troubleshooting/TROUBLESHOOTING.md#problèmes-de-base-de-données)

**Frontend (React) :**
- [Fonctionnalités](fonctionnalites/)
- [Multi-langues](fonctionnalites/MULTILANGUE.md)
- [Thème](fonctionnalites/THEME.md)

---

## 📝 Contribuer à la Documentation

Si vous souhaitez améliorer la documentation :

1. Identifiez ce qui manque ou peut être amélioré
2. Créez une issue sur GitHub
3. Proposez une Pull Request avec vos modifications
4. Respectez le format Markdown et la structure existante

---

## 📞 Support

En cas de problème :

1. **Cherchez dans la documentation** - Utilisez cet index
2. **Consultez le guide de dépannage** - [TROUBLESHOOTING.md](guides/troubleshooting/TROUBLESHOOTING.md)
3. **Vérifiez les logs** - Activez le debug
4. **Créez une issue** - Sur GitHub avec détails

---

<div align="center">

**Documentation maintenue par l'équipe Novatis**

**Version :** 2.0.0 • **Dernière mise à jour :** Octobre 2025

[← Retour au README](../README.md) • [Documentation Complète](DOCUMENTATION.md)

</div>

---

*Index généré automatiquement - Octobre 2025*
