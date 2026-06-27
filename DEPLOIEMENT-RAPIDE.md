# 🚀 Déploiement Rapide - Novatis sur AlwaysData

## Structure sur AlwaysData

```
/home/alex2pro/
├── www/                    ← Tout le contenu de Novatis ici
│   ├── public/            ← Répertoire racine du site
│   │   ├── index.php
│   │   ├── assets/
│   │   ├── api/
│   │   └── ...
│   ├── config/
│   ├── includes/
│   ├── storage/
│   ├── .env               ← Configuration production
│   └── ...
```

## Configuration AlwaysData (Interface Web)

**Site Web:**
- Adresses: `novatis.alwaysdata.net`
- Type: `PHP`
- Répertoire racine: `/home/alex2pro/www/public`
- Version PHP: `8.1+`
- HTTPS: ✅ Activé

## Déploiement en 5 étapes

### 1️⃣ Créer l'archive localement

```bash
cd /var/www/html/novatis
./deploy-to-alwaysdata.sh
```

### 2️⃣ Transférer l'archive

**Via SCP (automatique avec le script):**
Le script vous proposera de transférer automatiquement.

**Via FTP manuel:**
- Host: `ftp-alex2pro.alwaysdata.net`
- User: `alex2pro`
- Transférez l'archive dans `/home/alex2pro/`

### 3️⃣ Se connecter en SSH

```bash
ssh alex2pro@ssh-alex2pro.alwaysdata.net
```

### 4️⃣ Décompresser dans www/

```bash
cd /home/alex2pro/www
tar -xzf ../novatis-YYYYMMDD-HHMMSS.tar.gz
```

### 5️⃣ Configurer les permissions

```bash
chmod -R 755 /home/alex2pro/www
chmod -R 777 /home/alex2pro/www/storage
```

## Vérification

Visitez: https://novatis.alwaysdata.net

✅ Les assets CSS/JS se chargent
✅ Les catégories s'affichent
✅ Le site est fonctionnel

## En cas de problème

### ❌ Erreurs 404 sur les CSS/JS

```bash
# Vérifiez le .env
ssh alex2pro@ssh-alex2pro.alwaysdata.net
cat /home/alex2pro/www/.env | grep APP_URL
# Résultat attendu: APP_URL= (vide)
```

Si `APP_URL` contient une valeur, éditez le fichier :

```bash
nano /home/alex2pro/www/.env
# Changez en: APP_URL=
```

### ❌ Erreur 500

```bash
# Vérifiez les permissions storage
ssh alex2pro@ssh-alex2pro.alwaysdata.net
chmod -R 777 /home/alex2pro/www/storage

# Vérifiez les logs
cat /home/alex2pro/www/storage/logs/app.log
```

### ❌ Base de données inaccessible

Vérifiez le `.env`:
```
DB_HOST=mysql-alex2pro.alwaysdata.net
DB_NAME=alex2pro_movatis
DB_USER=alex2pro_alex
DB_PASS=Alex.2005
```

## Mise à jour rapide

```bash
# 1. Locale: Nouvelle archive
cd /var/www/html/novatis
./deploy-to-alwaysdata.sh

# 2. AlwaysData: Sauvegarde
ssh alex2pro@ssh-alex2pro.alwaysdata.net
cd /home/alex2pro/www
cp .env .env.backup
tar -czf storage-backup.tar.gz storage/uploads/

# 3. Suppression sélective (garde storage et .env)
rm -rf public config includes src bootstrap vendor

# 4. Décompression nouvelle version
tar -xzf ../novatis-YYYYMMDD-HHMMSS.tar.gz

# 5. Restauration
cp .env.backup .env
tar -xzf storage-backup.tar.gz
chmod -R 777 storage
```

---

**URL du site:** https://novatis.alwaysdata.net
**Dernier déploiement:** _________
