# 📥 Guide d'Installation - Novatis

Guide complet pour installer Novatis en local sur votre machine.

---

## 📋 Table des Matières

1. [Prérequis](#prérequis)
2. [Installation XAMPP](#installation-xampp)
3. [Installation de Composer](#installation-de-composer)
4. [Installation de Novatis](#installation-de-novatis)
5. [Configuration](#configuration)
6. [Base de Données](#base-de-données)
7. [Premiers Tests](#premiers-tests)
8. [Dépannage](#dépannage)

---

## 🎯 Prérequis

Avant de commencer, assurez-vous d'avoir :

### Windows
- **Windows 10/11** (64-bit)
- **7-Zip** ou **WinRAR** pour extraire les archives
- **Navigateur web** moderne (Chrome, Firefox, Edge)
- **Éditeur de code** (VS Code recommandé)

### Logiciels Requis
- **XAMPP** (inclut Apache, MySQL, PHP)
- **Composer** (gestionnaire de dépendances PHP)
- **Git** (optionnel, pour cloner le projet)

---

## 🔧 Installation XAMPP

XAMPP est une distribution Apache facile à installer contenant MySQL, PHP et Perl.

### 1. Télécharger XAMPP

Téléchargez XAMPP depuis [apachefriends.org](https://www.apachefriends.org/)

**Version recommandée :** XAMPP avec PHP 8.0 ou supérieur

### 2. Installer XAMPP

1. Lancez l'installateur téléchargé
2. Acceptez les paramètres par défaut
3. Installez dans `C:\xampp` (recommandé)
4. Attendez la fin de l'installation
5. Lancez XAMPP Control Panel

### 3. Démarrer les Services

Dans le XAMPP Control Panel :

1. Cliquez sur **"Start"** pour **Apache**
2. Cliquez sur **"Start"** pour **MySQL**
3. Vérifiez que les deux services sont bien lancés (fond vert)

### 4. Tester l'Installation

Ouvrez votre navigateur et allez sur :
```
http://localhost
```

Vous devriez voir la page d'accueil de XAMPP.

---

## 📦 Installation de Composer

Composer est le gestionnaire de dépendances pour PHP.

### Windows

1. Téléchargez [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe)
2. Lancez l'installateur
3. Laissez-le détecter automatiquement PHP de XAMPP (`C:\xampp\php\php.exe`)
4. Terminez l'installation

### Vérifier l'Installation

Ouvrez un terminal (CMD ou PowerShell) et tapez :

```bash
composer --version
```

Vous devriez voir la version de Composer installée.

---

## 🚀 Installation de Novatis

### Méthode 1 : Cloner avec Git (Recommandé)

Si vous avez Git installé :

```bash
# Aller dans le dossier htdocs de XAMPP
cd C:\xampp\htdocs

# Cloner le projet
git clone [URL_DU_REPO] Novatis

# Aller dans le dossier du projet
cd Novatis
```

### Méthode 2 : Téléchargement ZIP

1. Téléchargez le ZIP du projet depuis GitHub
2. Extrayez le contenu dans `C:\xampp\htdocs\`
3. Renommez le dossier en `Novatis`

### Installer les Dépendances

Dans le dossier du projet, ouvrez un terminal et exécutez :

```bash
composer install
```

Cette commande va télécharger et installer toutes les dépendances PHP nécessaires.

---

## ⚙️ Configuration

### 1. Créer le Fichier .env

Dans le dossier racine du projet (`C:\xampp\htdocs\Novatis`), copiez le fichier `.env.example` en `.env` :

```bash
copy .env.example .env
```

### 2. Éditer le Fichier .env

Ouvrez le fichier `.env` avec un éditeur de texte et configurez :

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

# Mail (Configuration Gmail - Optionnel)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre_email@gmail.com
MAIL_PASSWORD=votre_mot_de_passe_app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@novatis.com
MAIL_FROM_NAME="${APP_NAME}"

# OAuth (Optionnel)
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
MICROSOFT_CLIENT_ID=
MICROSOFT_CLIENT_SECRET=
GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
```

**Notes :**
- Laissez `DB_PASSWORD` vide si vous n'avez pas configuré de mot de passe MySQL
- Les paramètres mail et OAuth sont optionnels pour le développement local

---

## 💾 Base de Données

### 1. Créer la Base de Données

#### Via phpMyAdmin (Interface web)

1. Ouvrez [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Cliquez sur **"Nouvelle base de données"**
3. Nom : `novatis_db`
4. Interclassement : `utf8mb4_unicode_ci`
5. Cliquez sur **"Créer"**

#### Via la Ligne de Commande

```bash
# Ouvrir MySQL
cd C:\xampp\mysql\bin
mysql -u root

# Créer la base de données
CREATE DATABASE novatis_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 2. Importer la Structure

#### Option A : Fichiers SQL de sauvegarde

Si vous avez un fichier SQL de sauvegarde dans `database/backups/` :

1. Allez sur [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Sélectionnez la base `novatis_db`
3. Cliquez sur l'onglet **"Importer"**
4. Choisissez le fichier SQL dans `database/backups/`
5. Cliquez sur **"Exécuter"**

#### Option B : Scripts de migration

Si vous avez des fichiers de migration dans `database/migrations/` :

Exécutez chaque fichier SQL dans l'ordre :

```bash
cd C:\xampp\htdocs\Novatis\database\migrations

# Importer chaque fichier
C:\xampp\mysql\bin\mysql -u root novatis_db < 001_create_users_table.sql
C:\xampp\mysql\bin\mysql -u root novatis_db < 002_create_services_table.sql
# ... etc
```

### 3. Vérifier l'Import

Retournez sur phpMyAdmin et vérifiez que toutes les tables sont créées :
- `users`
- `services`
- `orders`
- `messages`
- `notifications`
- `reviews`
- `categories`
- etc.

---

## ✅ Premiers Tests

### 1. Accéder au Site

Ouvrez votre navigateur et allez sur :

```
http://localhost/Novatis/public/
```

Vous devriez voir la page d'accueil de Novatis.

### 2. Créer un Compte

1. Cliquez sur **"S'inscrire"** ou **"Connexion"**
2. Remplissez le formulaire d'inscription
3. Créez votre premier compte utilisateur

### 3. Explorer les Fonctionnalités

- Parcourir les services
- Accéder au dashboard
- Tester le système de notifications
- Essayer le changement de thème (clair/sombre)
- Tester le changement de langue (FR/EN)

---

## 🐛 Dépannage

### Apache ne démarre pas

**Erreur :** Port 80 déjà utilisé

**Solutions :**
1. Vérifiez si Skype ou un autre logiciel utilise le port 80
2. Changez le port d'Apache :
   - Dans XAMPP Control Panel, cliquez sur **"Config"** > **"httpd.conf"**
   - Cherchez `Listen 80` et remplacez par `Listen 8080`
   - Redémarrez Apache
   - Accédez au site via `http://localhost:8080/Novatis/public/`

### MySQL ne démarre pas

**Erreur :** Port 3306 déjà utilisé

**Solutions :**
1. Vérifiez si un autre service MySQL est déjà lancé
2. Arrêtez les autres instances de MySQL dans les Services Windows

### Page blanche ou Erreur 500

**Causes possibles :**
- Fichier `.env` manquant ou mal configuré
- Erreur PHP dans le code
- Problème de permissions

**Solutions :**
```bash
# Vérifier les logs Apache
C:\xampp\apache\logs\error.log

# Activer le debug dans .env
APP_DEBUG=true
```

### CSS/JS ne se chargent pas

**Erreur :** 404 sur les fichiers CSS/JS

**Cause :** `BASE_URL` mal configuré dans `.env`

**Solution :**
```env
# Dans .env
APP_URL=http://localhost/Novatis
```

### Connexion à la base de données échoue

**Erreur :** "Could not connect to database"

**Solutions :**
1. Vérifiez que MySQL est bien démarré dans XAMPP
2. Vérifiez les paramètres dans `.env` :
   ```env
   DB_HOST=localhost
   DB_DATABASE=novatis_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```
3. Testez la connexion MySQL :
   ```bash
   C:\xampp\mysql\bin\mysql -u root
   ```

### Composer Install échoue

**Erreur :** "composer: command not found"

**Solution :**
1. Réinstallez Composer
2. Ajoutez Composer au PATH système
3. Redémarrez le terminal

### Variables d'environnement non chargées

**Erreur :** Les variables du fichier `.env` ne sont pas prises en compte

**Solutions :**
1. Vérifiez que le fichier s'appelle bien `.env` (pas `.env.txt`)
2. Vérifiez que le fichier est à la racine du projet
3. Redémarrez Apache

---

## 📊 Structure des Dossiers

Après installation, votre projet devrait avoir cette structure :

```
C:\xampp\htdocs\Novatis\
├── bootstrap/          # Initialisation de l'application
├── config/            # Configuration (DB, mail, app)
├── database/          # SQL et migrations
├── docs/              # Documentation
├── includes/          # Composants réutilisables (Header, Footer)
├── public/            # Racine web (DOCUMENT ROOT)
│   ├── api/          # Endpoints API REST
│   ├── assets/       # CSS, JS, Images
│   ├── locales/      # Traductions i18n
│   └── pages/        # Pages de l'application
├── scripts/          # Scripts utilitaires
├── src/              # Code source PHP (PSR-4)
├── storage/          # Logs, cache, uploads
├── vendor/           # Dépendances Composer
├── .env              # Configuration environnement
└── composer.json     # Dépendances du projet
```

---

## 🎉 Installation Terminée !

Félicitations ! Votre installation de Novatis est maintenant complète.

### Prochaines Étapes

1. **Explorez la plateforme** : Créez des services, passez des commandes
2. **Lisez la documentation** : [Documentation complète](../../DOCUMENTATION.md)
3. **Découvrez l'API** : [Documentation API](../../api/API.md)
4. **Configurez OAuth** : [Guide OAuth](../../fonctionnalites/AUTHENTIFICATION.md)
5. **Personnalisez** : Modifiez les couleurs, logos, etc.

---

## 📚 Ressources

- [Documentation Complète](../../DOCUMENTATION.md)
- [Guide de Dépannage](../troubleshooting/TROUBLESHOOTING.md)
- [Fonctionnalités](../../fonctionnalites/FONCTIONNALITES.md)
- [API](../../api/API.md)
- [Déploiement en Production](../../deploiement/DEPLOIEMENT.md)

---

<div align="center">

**Guide d'installation maintenu par l'équipe Novatis**

[← Retour à la Documentation](../../DOCUMENTATION.md)

</div>

---

*Dernière mise à jour : Octobre 2025*
