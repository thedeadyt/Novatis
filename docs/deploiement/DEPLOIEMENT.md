# 🚀 Guide de Déploiement - Novatis

Guide complet pour déployer Novatis en production.

---

## 📋 Table des Matières

1. [Prérequis](#prérequis)
2. [Préparation du Serveur](#préparation-du-serveur)
3. [Installation](#installation)
4. [Configuration](#configuration)
5. [Sécurité](#sécurité)
6. [Optimisations](#optimisations)
7. [Mise en Production](#mise-en-production)
8. [Maintenance](#maintenance)
9. [Sauvegarde](#sauvegarde)
10. [Dépannage](#dépannage)

---

## 🎯 Prérequis

### Serveur

**Spécifications minimales :**
- **CPU** : 2 cœurs
- **RAM** : 2 GB
- **Stockage** : 20 GB SSD
- **Bande passante** : 100 Mbps

**Spécifications recommandées :**
- **CPU** : 4+ cœurs
- **RAM** : 4+ GB
- **Stockage** : 50+ GB SSD
- **Bande passante** : 1 Gbps

### Logiciels Requis

- **OS** : Ubuntu 20.04/22.04 LTS ou Debian 11+
- **Serveur Web** : Apache 2.4+ ou Nginx 1.18+
- **PHP** : 8.0+ avec extensions (pdo, pdo_mysql, mbstring, openssl, curl)
- **Base de données** : MySQL 8.0+ ou MariaDB 10.5+
- **Composer** : 2.0+
- **Git** : Pour le déploiement
- **SSL/TLS** : Certificat Let's Encrypt

### Nom de Domaine

- Nom de domaine configuré pointant vers votre serveur
- Certificat SSL/TLS (Let's Encrypt recommandé)

---

## 🛠️ Préparation du Serveur

### 1. Mise à jour du Système

```bash
sudo apt update && sudo apt upgrade -y
```

### 2. Installation d'Apache

```bash
# Installation
sudo apt install apache2 -y

# Activation des modules
sudo a2enmod rewrite
sudo a2enmod ssl
sudo a2enmod headers

# Démarrage
sudo systemctl start apache2
sudo systemctl enable apache2
```

### 3. Installation de PHP 8.0+

```bash
# Ajouter le repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Installer PHP et extensions
sudo apt install php8.0 php8.0-cli php8.0-common php8.0-mysql php8.0-zip \
php8.0-gd php8.0-mbstring php8.0-curl php8.0-xml php8.0-bcmath -y

# Vérifier la version
php -v
```

### 4. Installation de MySQL

```bash
# Installation
sudo apt install mysql-server -y

# Sécurisation
sudo mysql_secure_installation

# Configuration
sudo mysql

# Créer un utilisateur pour Novatis
CREATE USER 'novatis_user'@'localhost' IDENTIFIED BY 'mot_de_passe_fort';
CREATE DATABASE novatis_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON novatis_db.* TO 'novatis_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 5. Installation de Composer

```bash
# Téléchargement
cd ~
curl -sS https://getcomposer.org/installer -o composer-setup.php

# Installation
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Vérification
composer --version
```

---

## 📦 Installation

### 1. Cloner le Projet

```bash
# Aller dans le répertoire web
cd /var/www

# Cloner le dépôt
sudo git clone [URL_DU_REPO] novatis
cd novatis

# Donner les permissions
sudo chown -R www-data:www-data /var/www/novatis
sudo chmod -R 755 /var/www/novatis
```

### 2. Installer les Dépendances

```bash
# Installer via Composer
composer install --no-dev --optimize-autoloader
```

### 3. Configuration des Permissions

```bash
# Permissions pour les dossiers storage et uploads
sudo chmod -R 775 storage/
sudo chmod -R 775 public/uploads/

# Propriétaire Apache
sudo chown -R www-data:www-data storage/
sudo chown -R www-data:www-data public/uploads/
```

---

## ⚙️ Configuration

### 1. Fichier .env

```bash
# Copier le fichier d'exemple
cp .env.example .env

# Éditer avec nano ou vim
nano .env
```

**Configuration Production :**

```env
# Application
APP_NAME=Novatis
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votredomaine.com

# Base de données
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=novatis_db
DB_USERNAME=novatis_user
DB_PASSWORD=votre_mot_de_passe_fort

# Mail (Gmail, SendGrid, Mailgun, etc.)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre_email@gmail.com
MAIL_PASSWORD=votre_mot_de_passe_app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@votredomaine.com
MAIL_FROM_NAME="${APP_NAME}"

# OAuth (Production)
GOOGLE_CLIENT_ID=votre_google_client_id_prod
GOOGLE_CLIENT_SECRET=votre_google_client_secret_prod
GOOGLE_REDIRECT_URI=https://votredomaine.com/api/oauth/callback.php

MICROSOFT_CLIENT_ID=votre_microsoft_client_id_prod
MICROSOFT_CLIENT_SECRET=votre_microsoft_client_secret_prod
MICROSOFT_REDIRECT_URI=https://votredomaine.com/api/oauth/callback.php

GITHUB_CLIENT_ID=votre_github_client_id_prod
GITHUB_CLIENT_SECRET=votre_github_client_secret_prod
GITHUB_REDIRECT_URI=https://votredomaine.com/api/oauth/callback.php

# Session (Sécurité)
SESSION_LIFETIME=1440
SESSION_SECURE=true
SESSION_HTTP_ONLY=true
```

### 2. Import de la Base de Données

```bash
# Importer la structure
mysql -u novatis_user -p novatis_db < database/backups/novatis_structure.sql

# Importer les données (si nécessaire)
mysql -u novatis_user -p novatis_db < database/backups/novatis_data.sql
```

### 3. Configuration Apache

Créer un VirtualHost :

```bash
sudo nano /etc/apache2/sites-available/novatis.conf
```

**Contenu du fichier :**

```apache
<VirtualHost *:80>
    ServerName votredomaine.com
    ServerAlias www.votredomaine.com

    DocumentRoot /var/www/novatis/public

    <Directory /var/www/novatis/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/novatis_error.log
    CustomLog ${APACHE_LOG_DIR}/novatis_access.log combined

    # Redirection HTTPS (après installation du certificat)
    # RewriteEngine on
    # RewriteCond %{SERVER_NAME} =votredomaine.com [OR]
    # RewriteCond %{SERVER_NAME} =www.votredomaine.com
    # RewriteRule ^ https://%{SERVER_NAME}%{REQUEST_URI} [END,NE,R=permanent]
</VirtualHost>
```

**Activer le site :**

```bash
# Activer le site
sudo a2ensite novatis.conf

# Désactiver le site par défaut
sudo a2dissite 000-default.conf

# Recharger Apache
sudo systemctl reload apache2
```

### 4. Installation du Certificat SSL (Let's Encrypt)

```bash
# Installer Certbot
sudo apt install certbot python3-certbot-apache -y

# Obtenir le certificat
sudo certbot --apache -d votredomaine.com -d www.votredomaine.com

# Renouvellement automatique
sudo systemctl status certbot.timer
```

Certbot va automatiquement modifier votre VirtualHost pour ajouter le HTTPS.

---

## 🔒 Sécurité

### 1. Pare-feu (UFW)

```bash
# Installer et configurer UFW
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS
sudo ufw enable
sudo ufw status
```

### 2. Sécurisation de PHP

Éditer `/etc/php/8.0/apache2/php.ini` :

```ini
# Désactiver les fonctions dangereuses
disable_functions = exec,passthru,shell_exec,system,proc_open,popen

# Limiter les uploads
upload_max_filesize = 10M
post_max_size = 10M

# Masquer la version PHP
expose_php = Off

# Sessions sécurisées
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
```

Redémarrer Apache :

```bash
sudo systemctl restart apache2
```

### 3. Sécurisation d'Apache

Éditer `/etc/apache2/conf-available/security.conf` :

```apache
# Masquer la version
ServerTokens Prod
ServerSignature Off

# Headers de sécurité
Header always set X-Content-Type-Options "nosniff"
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
```

Activer et recharger :

```bash
sudo a2enmod headers
sudo systemctl reload apache2
```

### 4. Permissions Strictes

```bash
# Fichiers : 644
find /var/www/novatis -type f -exec chmod 644 {} \;

# Dossiers : 755
find /var/www/novatis -type d -exec chmod 755 {} \;

# Storage et uploads : 775
chmod -R 775 /var/www/novatis/storage
chmod -R 775 /var/www/novatis/public/uploads

# .env : 600 (lecture seule propriétaire)
chmod 600 /var/www/novatis/.env
```

---

## ⚡ Optimisations

### 1. PHP OPcache

Éditer `/etc/php/8.0/apache2/php.ini` :

```ini
[opcache]
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
```

### 2. Compression Gzip

Activer dans Apache :

```bash
sudo a2enmod deflate
```

Ajouter dans VirtualHost ou `.htaccess` :

```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>
```

### 3. Cache du Navigateur

Ajouter dans `.htaccess` :

```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

### 4. Optimisation MySQL

Éditer `/etc/mysql/mysql.conf.d/mysqld.cnf` :

```ini
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
max_connections = 200
```

Redémarrer MySQL :

```bash
sudo systemctl restart mysql
```

---

## 🚀 Mise en Production

### Checklist Avant le Lancement

- [ ] `.env` configuré en mode production (`APP_DEBUG=false`)
- [ ] Base de données importée
- [ ] Certificat SSL installé et fonctionnel
- [ ] Permissions des fichiers correctes
- [ ] Pare-feu configuré
- [ ] Sauvegardes configurées
- [ ] Tests de fonctionnement effectués
- [ ] OAuth configuré avec URLs de production
- [ ] Emails de test envoyés et reçus
- [ ] Monitoring mis en place

### Tests de Vérification

```bash
# Vérifier PHP
php -v

# Vérifier Apache
sudo apache2ctl configtest
sudo systemctl status apache2

# Vérifier MySQL
sudo systemctl status mysql
mysql -u novatis_user -p -e "SHOW DATABASES;"

# Vérifier les logs
sudo tail -f /var/log/apache2/novatis_error.log
```

---

## 🔧 Maintenance

### Mises à Jour

```bash
# Mise à jour du code
cd /var/www/novatis
sudo git pull origin main

# Mise à jour des dépendances
composer install --no-dev --optimize-autoloader

# Vider le cache (si applicable)
# php artisan cache:clear

# Redémarrer Apache
sudo systemctl restart apache2
```

### Monitoring

**Logs à surveiller :**
- Apache : `/var/log/apache2/novatis_error.log`
- Application : `/var/www/novatis/storage/logs/`
- MySQL : `/var/log/mysql/error.log`
- System : `/var/log/syslog`

**Outils recommandés :**
- **Uptime Robot** : Surveillance uptime
- **Google Analytics** : Statistiques de trafic
- **Sentry** : Tracking des erreurs
- **Grafana + Prometheus** : Monitoring avancé

---

## 💾 Sauvegarde

### 1. Sauvegarde de la Base de Données

**Script de sauvegarde automatique :**

```bash
#!/bin/bash
# /usr/local/bin/backup-novatis-db.sh

BACKUP_DIR="/var/backups/novatis/db"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="novatis_db"
DB_USER="novatis_user"
DB_PASS="votre_mot_de_passe"

# Créer le dossier si nécessaire
mkdir -p $BACKUP_DIR

# Backup
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/novatis_db_$DATE.sql.gz

# Garder seulement les 30 derniers jours
find $BACKUP_DIR -name "*.sql.gz" -mtime +30 -delete

echo "Backup completed: novatis_db_$DATE.sql.gz"
```

Rendre exécutable :

```bash
sudo chmod +x /usr/local/bin/backup-novatis-db.sh
```

**Cron job (tous les jours à 3h00) :**

```bash
sudo crontab -e

# Ajouter :
0 3 * * * /usr/local/bin/backup-novatis-db.sh
```

### 2. Sauvegarde des Fichiers

```bash
#!/bin/bash
# /usr/local/bin/backup-novatis-files.sh

BACKUP_DIR="/var/backups/novatis/files"
DATE=$(date +%Y%m%d)
SOURCE="/var/www/novatis"

mkdir -p $BACKUP_DIR

# Backup uploads et storage
tar -czf $BACKUP_DIR/novatis_files_$DATE.tar.gz \
    $SOURCE/public/uploads \
    $SOURCE/storage \
    $SOURCE/.env

# Garder 30 jours
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete

echo "Files backup completed: novatis_files_$DATE.tar.gz"
```

---

## 🐛 Dépannage

### Erreur 500

**Vérifier :**
```bash
# Logs Apache
sudo tail -f /var/log/apache2/novatis_error.log

# Logs de l'application
sudo tail -f /var/www/novatis/storage/logs/app.log

# Permissions
ls -la /var/www/novatis
```

### Problème de Connexion à la DB

```bash
# Tester la connexion
mysql -u novatis_user -p novatis_db

# Vérifier les paramètres .env
cat /var/www/novatis/.env | grep DB_
```

### Site Lent

**Vérifier :**
- Utilisation CPU/RAM : `htop`
- Connexions MySQL : `mysql -e "SHOW PROCESSLIST;"`
- Logs : vérifier les requêtes lentes

---

## 📚 Ressources

- [Documentation Complète](../DOCUMENTATION.md)
- [Configuration Apache](https://httpd.apache.org/docs/2.4/)
- [Let's Encrypt](https://letsencrypt.org/)
- [MySQL Performance](https://dev.mysql.com/doc/refman/8.0/en/optimization.html)

---

<div align="center">

**Guide maintenu par l'équipe Novatis**

[← Retour à la Documentation](../DOCUMENTATION.md)

</div>

---

*Dernière mise à jour : Octobre 2025*
