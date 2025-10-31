# 🐛 Guide de Dépannage - Novatis

Guide complet pour résoudre les problèmes courants de Novatis.

---

## 📋 Table des Matières

1. [Erreurs Serveur](#erreurs-serveur)
2. [Problèmes de Base de Données](#problèmes-de-base-de-données)
3. [Problèmes d'Affichage](#problèmes-daffichage)
4. [Problèmes d'Authentification](#problèmes-dauthentification)
5. [Problèmes de Configuration](#problèmes-de-configuration)
6. [Problèmes de Performance](#problèmes-de-performance)
7. [Problèmes OAuth](#problèmes-oauth)
8. [Problèmes d'Email](#problèmes-demail)
9. [Outils de Débogage](#outils-de-débogage)
10. [Logs et Diagnostics](#logs-et-diagnostics)

---

## 🔴 Erreurs Serveur

### Erreur 500 - Internal Server Error

**Symptômes :** Page blanche avec "500 Internal Server Error"

**Causes possibles :**
1. Fichier `.env` manquant ou mal configuré
2. Erreur de syntaxe PHP dans le code
3. Problème de connexion à la base de données
4. Permissions de fichiers incorrectes

**Solutions :**

#### 1. Activer le mode debug

Éditez `.env` :
```env
APP_DEBUG=true
```

Rechargez la page pour voir les détails de l'erreur.

#### 2. Vérifier les logs Apache

**Windows (XAMPP) :**
```bash
C:\xampp\apache\logs\error.log
```

**Linux :**
```bash
tail -f /var/log/apache2/error.log
```

#### 3. Vérifier les logs de l'application

```bash
# Logs de Novatis
C:\xampp\htdocs\Novatis\storage\logs\app.log
```

#### 4. Vérifier les permissions

```bash
# Windows - Dans le dossier du projet
icacls storage /grant Users:(OI)(CI)F
icacls public\uploads /grant Users:(OI)(CI)F
```

---

### Erreur 404 - Page Not Found

**Symptômes :** "404 Not Found" ou "The requested URL was not found"

**Causes possibles :**
1. URL incorrecte
2. Fichier `.htaccess` manquant ou incorrect
3. `mod_rewrite` non activé dans Apache
4. `BASE_URL` mal configuré

**Solutions :**

#### 1. Vérifier l'URL

Assurez-vous d'accéder au bon chemin :
```
http://localhost/Novatis/public/
```

#### 2. Vérifier .htaccess

Le fichier `public/.htaccess` doit exister et contenir :

```apache
RewriteEngine On
RewriteBase /Novatis/public/

# Redirect to pages if file doesn't exist
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([a-zA-Z0-9À-ÿ_-]+)$ pages/$1.php [L]
```

#### 3. Activer mod_rewrite

**XAMPP :**
1. Ouvrez `C:\xampp\apache\conf\httpd.conf`
2. Recherchez `#LoadModule rewrite_module`
3. Enlevez le `#` au début de la ligne
4. Redémarrez Apache

---

### Erreur 403 - Forbidden

**Symptômes :** "403 Forbidden - You don't have permission to access"

**Causes possibles :**
1. Permissions de fichiers incorrectes
2. Configuration Apache restrictive
3. Fichier `.htaccess` bloque l'accès

**Solutions :**

#### 1. Vérifier les permissions

Les dossiers doivent avoir les permissions `755` et les fichiers `644`.

#### 2. Vérifier httpd.conf

Dans `C:\xampp\apache\conf\httpd.conf`, vérifiez :

```apache
<Directory "C:/xampp/htdocs">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

---

## 💾 Problèmes de Base de Données

### Erreur de connexion à la base de données

**Symptômes :** "Could not connect to database" ou "Connection refused"

**Solutions :**

#### 1. Vérifier que MySQL est démarré

**XAMPP Control Panel :**
- Le bouton MySQL doit être vert avec "Stop"

Si MySQL ne démarre pas :
```bash
# Vérifier les logs MySQL
C:\xampp\mysql\data\mysql_error.log
```

#### 2. Vérifier les paramètres .env

```env
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=novatis_db
DB_USERNAME=root
DB_PASSWORD=
```

#### 3. Tester la connexion MySQL

```bash
cd C:\xampp\mysql\bin
mysql -u root -p

# Une fois connecté :
SHOW DATABASES;
USE novatis_db;
SHOW TABLES;
```

#### 4. Vérifier que la base de données existe

```bash
mysql -u root -p
CREATE DATABASE IF NOT EXISTS novatis_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

### Erreur "Table doesn't exist"

**Symptômes :** "Table 'novatis_db.users' doesn't exist"

**Cause :** Tables non créées ou import SQL incomplet

**Solutions :**

#### 1. Importer la structure SQL

Via phpMyAdmin :
1. Allez sur http://localhost/phpmyadmin
2. Sélectionnez `novatis_db`
3. Cliquez sur "Importer"
4. Sélectionnez le fichier SQL dans `database/backups/`
5. Cliquez sur "Exécuter"

#### 2. Vérifier que toutes les tables existent

Dans phpMyAdmin, vous devriez voir ces tables :
- users
- services
- orders
- messages
- notifications
- reviews
- categories
- favorites
- oauth_connections

---

### Erreur "Undefined variable $pdo"

**Symptômes :** "Warning: Undefined variable $pdo"

**Cause :** La variable `$pdo` n'est pas initialisée

**Solution :**

Ajoutez au début du fichier PHP :

```php
<?php
require_once __DIR__ . '/../../config/Config.php';

// Obtenir la connexion à la base de données
$pdo = getDBConnection();
```

---

## 🎨 Problèmes d'Affichage

### CSS/JavaScript ne se chargent pas (404)

**Symptômes :** Page sans style, erreurs 404 sur les fichiers CSS/JS

**Cause :** `BASE_URL` mal configuré

**Solutions :**

#### 1. Vérifier .env

```env
APP_URL=http://localhost/Novatis
```

**Important :** Pas de `/` à la fin et pas de `/public`

#### 2. Vérifier bootstrap/app.php

Le fichier doit contenir :

```php
define('BASE_URL', rtrim(env('APP_URL'), '/') . '/public');
```

#### 3. Vider le cache du navigateur

Appuyez sur `Ctrl + F5` pour recharger sans cache.

---

### Thème sombre/clair ne fonctionne pas

**Symptômes :** Le changement de thème ne s'applique pas

**Solutions :**

#### 1. Vérifier le JavaScript

Ouvrez la console du navigateur (`F12`) et vérifiez les erreurs.

#### 2. Vérifier le LocalStorage

Dans la console :
```javascript
localStorage.getItem('theme')
```

Si `null`, le thème n'est pas sauvegardé.

#### 3. Vérifier theme.js

Le fichier `public/assets/js/theme.js` doit être chargé correctement.

---

### Traductions (i18n) ne fonctionnent pas

**Symptômes :** Texte en anglais ou clés de traduction affichées

**Solutions :**

#### 1. Vérifier les fichiers de traduction

Les fichiers doivent exister :
```
public/locales/fr/translation.json
public/locales/en/translation.json
```

#### 2. Vérifier i18n.js

Le fichier `public/assets/js/i18n.js` doit être chargé :

```javascript
// Dans la console du navigateur
i18next.language  // Devrait afficher 'fr' ou 'en'
```

#### 3. Vérifier le localStorage

```javascript
localStorage.getItem('language')
```

---

## 🔐 Problèmes d'Authentification

### Impossible de se connecter

**Symptômes :** "Email/mot de passe incorrect" même avec les bons identifiants

**Solutions :**

#### 1. Vérifier que le compte existe

```sql
SELECT * FROM users WHERE email = 'votre_email@example.com';
```

#### 2. Réinitialiser le mot de passe

```sql
UPDATE users
SET password = '$2y$12$LdwN7h4YCZqR.YxO5CqG8uLs8/qJQk4YvN7h4YCZqR.YxO5CqG8uL'  -- mot de passe: "password"
WHERE email = 'votre_email@example.com';
```

#### 3. Vérifier les sessions PHP

Dans `.env` :
```env
SESSION_DRIVER=file
SESSION_LIFETIME=1440
```

Vérifiez que le dossier `storage/sessions/` existe et est accessible en écriture.

---

### Session perdue après redirection

**Symptômes :** Déconnexion automatique après chaque action

**Cause :** Problème de configuration des sessions ou de cookies

**Solutions :**

#### 1. Vérifier php.ini

```ini
session.save_handler = files
session.save_path = "C:/xampp/tmp"
session.use_cookies = 1
session.cookie_lifetime = 0
session.cookie_httponly = 1
```

#### 2. Vérifier que le dossier tmp existe

```bash
C:\xampp\tmp
```

Si le dossier n'existe pas, créez-le.

---

### 2FA bloque l'accès au compte

**Symptômes :** Code 2FA ne fonctionne pas, impossible de se connecter

**Solutions :**

#### 1. Utiliser un code de secours

Lors de l'activation du 2FA, des codes de secours sont générés. Utilisez-en un.

#### 2. Désactiver le 2FA via la base de données

```sql
UPDATE users
SET two_factor_enabled = 0, two_factor_secret = NULL
WHERE email = 'votre_email@example.com';
```

---

## ⚙️ Problèmes de Configuration

### Variables d'environnement non chargées

**Symptômes :** Les variables du `.env` retournent `null`

**Solutions :**

#### 1. Vérifier que le fichier s'appelle bien `.env`

Pas `.env.txt` ou `.env.example`

#### 2. Vérifier que le fichier est à la racine

```
C:\xampp\htdocs\Novatis\.env
```

#### 3. Vérifier la fonction env()

Dans `bootstrap/helpers.php`, la fonction doit checker `$_ENV`, `$_SERVER` et `getenv()` :

```php
function env(string $key, $default = null) {
    if (isset($_ENV[$key])) {
        return $_ENV[$key];
    }
    if (isset($_SERVER[$key])) {
        return $_SERVER[$key];
    }
    $value = getenv($key);
    return $value !== false ? $value : $default;
}
```

#### 4. Redémarrer Apache

Après modification du `.env`, redémarrez Apache dans XAMPP.

---

### Composer install échoue

**Symptômes :** Erreurs lors de `composer install`

**Solutions :**

#### 1. Vérifier que Composer est installé

```bash
composer --version
```

#### 2. Nettoyer le cache Composer

```bash
composer clear-cache
composer install
```

#### 3. Mettre à jour Composer

```bash
composer self-update
```

#### 4. Installer les dépendances une par une

```bash
composer require vlucas/phpdotenv
composer require phpmailer/phpmailer
```

---

## ⚡ Problèmes de Performance

### Site très lent

**Causes possibles :**
1. Base de données lente
2. Logs trop volumineux
3. Pas de cache
4. Debug activé en production

**Solutions :**

#### 1. Désactiver le debug en production

```env
APP_DEBUG=false
```

#### 2. Nettoyer les logs

```bash
# Supprimer les anciens logs
del /Q C:\xampp\htdocs\Novatis\storage\logs\*.log
```

#### 3. Optimiser MySQL

Dans `C:\xampp\mysql\bin\my.ini` :

```ini
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
```

Redémarrez MySQL.

#### 4. Activer OPcache

Dans `php.ini` :

```ini
[opcache]
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=10000
```

---

## 🔗 Problèmes OAuth

### Connexion Google/Microsoft/GitHub ne fonctionne pas

**Symptômes :** Erreur lors de la redirection OAuth

**Solutions :**

#### 1. Vérifier les clés OAuth dans .env

```env
GOOGLE_CLIENT_ID=votre_client_id
GOOGLE_CLIENT_SECRET=votre_client_secret
GOOGLE_REDIRECT_URI=http://localhost/Novatis/public/api/oauth/callback.php
```

#### 2. Vérifier les URIs de redirection

Dans la console du provider OAuth (Google, Microsoft, GitHub), l'URI de redirection doit correspondre **exactement** :

```
http://localhost/Novatis/public/api/oauth/callback.php
```

#### 3. Activer les APIs nécessaires

**Google :** Activez Google+ API dans la console
**Microsoft :** Activez les permissions `User.Read`
**GitHub :** Vérifiez que l'app OAuth est active

#### 4. Vérifier les logs

```bash
tail -f storage/logs/oauth.log
```

---

## 📧 Problèmes d'Email

### Emails ne sont pas envoyés

**Symptômes :** Pas d'email de vérification/réinitialisation

**Solutions :**

#### 1. Vérifier la configuration SMTP dans .env

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre_email@gmail.com
MAIL_PASSWORD=votre_mot_de_passe_app
MAIL_ENCRYPTION=tls
```

#### 2. Utiliser un mot de passe d'application Gmail

Si vous utilisez Gmail avec 2FA :
1. Allez sur https://myaccount.google.com/apppasswords
2. Générez un mot de passe d'application
3. Utilisez ce mot de passe dans `MAIL_PASSWORD`

#### 3. Tester l'envoi d'email

Créez un fichier `test_email.php` :

```php
<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer();
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->Port = 587;
$mail->SMTPAuth = true;
$mail->Username = 'votre_email@gmail.com';
$mail->Password = 'votre_mot_de_passe_app';
$mail->SMTPSecure = 'tls';
$mail->setFrom('noreply@novatis.com', 'Novatis');
$mail->addAddress('destinataire@example.com');
$mail->Subject = 'Test';
$mail->Body = 'Email de test';

if ($mail->send()) {
    echo 'Email envoyé !';
} else {
    echo 'Erreur : ' . $mail->ErrorInfo;
}
```

#### 4. Vérifier les logs d'erreur

```bash
tail -f storage/logs/mail.log
```

---

## 🛠️ Outils de Débogage

### 1. Console du Navigateur

Ouvrez avec `F12` pour voir :
- Erreurs JavaScript
- Requêtes réseau
- Erreurs de chargement de ressources

### 2. Logs PHP

Activer l'affichage des erreurs dans `php.ini` :

```ini
display_errors = On
error_reporting = E_ALL
log_errors = On
error_log = "C:/xampp/php/logs/php_error.log"
```

### 3. var_dump() et print_r()

Pour déboguer des variables :

```php
var_dump($variable);
print_r($array);
```

### 4. Logs personnalisés

Créer des logs dans le code :

```php
error_log("Debug: " . print_r($variable, true));
```

---

## 📊 Logs et Diagnostics

### Emplacements des Logs

**Apache :**
```
C:\xampp\apache\logs\error.log
C:\xampp\apache\logs\access.log
```

**MySQL :**
```
C:\xampp\mysql\data\mysql_error.log
```

**PHP :**
```
C:\xampp\php\logs\php_error.log
```

**Application Novatis :**
```
C:\xampp\htdocs\Novatis\storage\logs\app.log
C:\xampp\htdocs\Novatis\storage\logs\error.log
```

### Lire les Logs en Temps Réel

**Windows (PowerShell) :**
```powershell
Get-Content -Path "C:\xampp\apache\logs\error.log" -Wait -Tail 50
```

---

## 🆘 Obtenir de l'Aide

Si vous ne trouvez pas de solution :

1. **Consultez la documentation complète :** [DOCUMENTATION.md](../../DOCUMENTATION.md)
2. **Vérifiez les logs** pour identifier l'erreur exacte
3. **Activez le mode debug** pour voir les détails
4. **Créez une issue** sur GitHub avec :
   - Description du problème
   - Message d'erreur complet
   - Logs pertinents
   - Étapes pour reproduire

---

## 📚 Ressources

- [Documentation Complète](../../DOCUMENTATION.md)
- [Guide d'Installation](../installation/INSTALLATION.md)
- [Fonctionnalités](../../fonctionnalites/FONCTIONNALITES.md)
- [API](../../api/API.md)
- [Déploiement](../../deploiement/DEPLOIEMENT.md)

---

<div align="center">

**Guide maintenu par l'équipe Novatis**

[← Retour à la Documentation](../../DOCUMENTATION.md)

</div>

---

*Dernière mise à jour : Octobre 2025*
