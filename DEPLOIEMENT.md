# 🚀 Guide de Déploiement Novatis sur AlwaysData

## 📋 Prérequis

- Un compte AlwaysData actif
- Accès SSH à votre compte AlwaysData
- URL du site: https://novatis.alwaysdata.net

## 🔧 Configuration AlwaysData

### 1. Paramètres du Site Web

Dans l'interface AlwaysData, créez un site avec ces paramètres :

- **Type**: PHP
- **Adresses**: novatis.alwaysdata.net
- **Répertoire racine**: `/home/alex2pro/www/public` (tout le contenu va directement dans www/)
- **Version PHP**: 8.1 ou supérieure
- **HTTPS**: ✅ Activé (recommandé)

### 2. Base de données

Votre base de données est déjà configurée :
- **Hôte**: mysql-alex2pro.alwaysdata.net
- **Base**: alex2pro_movatis
- **Utilisateur**: alex2pro_alex

## 📤 Déploiement

### Option 1 : Via le script automatique

```bash
cd /var/www/html/novatis
./deploy-to-alwaysdata.sh
```

Le script va :
1. Créer une archive avec tous les fichiers nécessaires
2. Copier automatiquement le `.env.production` comme `.env`
3. Vous proposer de transférer l'archive via SCP

### Option 2 : Déploiement manuel

#### Étape 1 : Préparer les fichiers

```bash
cd /var/www/html/novatis

# Copier le fichier .env de production
cp .env.production .env

# Créer une archive (exclure les fichiers inutiles)
tar -czf novatis.tar.gz \
  --exclude='.git' \
  --exclude='node_modules' \
  --exclude='vendor' \
  --exclude='storage/logs/*' \
  --exclude='storage/cache/*' \
  --exclude='*.log' \
  .
```

#### Étape 2 : Transférer les fichiers

**Via SCP (recommandé):**

```bash
scp novatis.tar.gz alex2pro@ssh-alex2pro.alwaysdata.net:/home/alex2pro/
```

**Via FTP:**
Utilisez FileZilla ou un autre client FTP avec ces paramètres :
- **Hôte**: ftp-alex2pro.alwaysdata.net
- **Port**: 21
- **Utilisateur**: alex2pro
- **Mot de passe**: [votre mot de passe AlwaysData]

#### Étape 3 : Décompresser sur AlwaysData

Connectez-vous en SSH :

```bash
ssh alex2pro@ssh-alex2pro.alwaysdata.net
```

Puis décompressez directement dans www/ :

```bash
cd /home/alex2pro/www
tar -xzf ../novatis-YYYYMMDD-HHMMSS.tar.gz
```

#### Étape 4 : Configurer les permissions

```bash
cd /home/alex2pro/www

# Permissions des fichiers
chmod -R 755 .

# Permissions du dossier storage (lecture/écriture)
chmod -R 777 storage
chmod -R 777 storage/logs
chmod -R 777 storage/uploads
chmod -R 777 storage/cache
```

#### Étape 5 : Installer les dépendances Composer (si nécessaire)

```bash
cd /home/alex2pro/www
composer install --no-dev --optimize-autoloader
```

## ✅ Vérification du déploiement

1. Visitez https://novatis.alwaysdata.net
2. Vérifiez que tous les fichiers CSS/JS se chargent correctement
3. Testez la connexion à la base de données
4. Vérifiez les logs : `storage/logs/`

## 🔍 Résolution des problèmes

### Les assets (CSS/JS) ne se chargent pas (404)

**Cause**: Le fichier `.env` n'a pas été mis à jour avec `APP_URL=https://novatis.alwaysdata.net`

**Solution**:
```bash
ssh alex2pro@ssh-alex2pro.alwaysdata.net
cd /home/alex2pro/www
nano .env
# Vérifiez que APP_URL= (vide car pas de sous-dossier)
# ou APP_URL=https://novatis.alwaysdata.net
```

### Erreur 500 - Internal Server Error

**Causes possibles**:
1. Permissions incorrectes sur le dossier `storage`
2. Fichier `.env` manquant ou mal configuré
3. Extensions PHP manquantes

**Solution**:
```bash
# Vérifier les logs
cat storage/logs/app.log

# Corriger les permissions
chmod -R 777 storage

# Vérifier que .env existe
ls -la .env
```

### Base de données inaccessible

**Vérifications**:
1. Connexion depuis l'interface AlwaysData (section "Bases de données")
2. Vérifier les identifiants dans `.env`
3. Tester la connexion manuellement

## 📝 Mise à jour du site

Pour mettre à jour le site après des modifications :

```bash
# 1. Sur votre serveur local, créez une nouvelle archive
cd /var/www/html/novatis
./deploy-to-alwaysdata.sh

# 2. Sur AlwaysData, sauvegardez d'abord le .env et les uploads
ssh alex2pro@ssh-alex2pro.alwaysdata.net
cd /home/alex2pro/www
cp .env .env.backup
tar -czf uploads-backup.tar.gz storage/uploads/

# 3. Supprimez les anciens fichiers (ATTENTION: sauvegardez avant!)
cd /home/alex2pro/www
rm -rf public config includes src bootstrap vendor

# 4. Décompressez la nouvelle version
tar -xzf ../novatis-YYYYMMDD-HHMMSS.tar.gz

# 5. Restaurez .env et les uploads
cp .env.backup .env
tar -xzf uploads-backup.tar.gz

# 6. Réappliquez les permissions
chmod -R 777 storage
```

## 🔐 Sécurité

- ✅ HTTPS activé sur AlwaysData
- ✅ `APP_ENV=production` dans `.env`
- ✅ `APP_DEBUG=false` dans `.env`
- ✅ Fichiers sensibles exclus du dépôt Git
- ⚠️ Changez les clés secrètes (`ENCRYPTION_KEY`, `CSRF_TOKEN_NAME`)

## 📞 Support

En cas de problème :
1. Vérifiez les logs : `storage/logs/app.log`
2. Consultez la documentation AlwaysData : https://help.alwaysdata.com
3. Contactez le support AlwaysData si le problème persiste

---

**Dernière mise à jour**: 2025-11-16
**Version**: 1.0.0
