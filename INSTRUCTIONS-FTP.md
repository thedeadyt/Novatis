# 📤 Instructions pour corriger le .env via FTP

## ✅ Fichier créé et prêt

Le fichier `.env` correct a été créé : **[.env.alwaysdata-corrected](file:///var/www/html/novatis/.env.alwaysdata-corrected)**

## 🔧 Méthode 1 : Via l'interface web AlwaysData (PLUS SIMPLE)

1. **Connectez-vous à AlwaysData** : https://admin.alwaysdata.com
2. **Allez dans "SSH" ou "Fichiers"** (gestionnaire de fichiers web)
3. **Naviguez vers** : `/home/alex2pro/www/`
4. **Localisez le fichier** `.env`
5. **Cliquez sur "Éditer"**
6. **Trouvez la ligne** : `APP_URL=/novatis`
7. **Changez-la en** : `APP_URL=`
8. **Sauvegardez** le fichier

## 🔧 Méthode 2 : Via FTP (FileZilla)

### Étape 1 : Téléchargez FileZilla
https://filezilla-project.org/download.php?type=client

### Étape 2 : Configurez la connexion

- **Hôte** : `ftp-novatis.alwaysdata.net`
- **Utilisateur** : `novatis`
- **Mot de passe** : `11122005`
- **Port** : `21`

### Étape 3 : Connectez-vous et naviguez

1. Cliquez sur "Connexion rapide"
2. Dans le panneau de droite (serveur distant), naviguez vers `/www/`
3. Trouvez le fichier `.env`

### Étape 4 : Remplacez le fichier

**Option A - Éditer directement :**
1. Clic droit sur `.env` → "Afficher/Éditer"
2. Cherchez la ligne `APP_URL=/novatis`
3. Changez en `APP_URL=`
4. Sauvegardez (Ctrl+S)
5. Fermez l'éditeur - FileZilla demandera si vous voulez uploader → Oui

**Option B - Remplacer le fichier complet :**
1. Sur votre machine locale (panneau de gauche), naviguez vers `/var/www/html/novatis/`
2. Trouvez le fichier `.env.alwaysdata-corrected`
3. Glissez-le vers le panneau de droite dans `/www/`
4. Renommez-le en `.env` (écrasera l'ancien)

## 🔧 Méthode 3 : Via ligne de commande (pour les experts)

Si vous avez curl/wget installé :

```bash
# Créer le fichier .env local avec le bon contenu
cd /var/www/html/novatis

# Uploader via FTP
curl -T .env.alwaysdata-corrected -u novatis:11122005 ftp://ftp-novatis.alwaysdata.net/www/.env
```

## ✅ Vérification

Après avoir remplacé le fichier `.env` :

1. **Videz le cache** du navigateur (Ctrl+Shift+Delete)
2. **Rechargez** https://novatis.alwaysdata.net (Ctrl+Shift+R)
3. **Vérifiez la console** - plus d'erreurs 404 !

## 🎯 Ce qui a été corrigé

**AVANT (incorrect) :**
```
APP_URL=/novatis
```

**APRÈS (correct) :**
```
APP_URL=
```

Cette correction permet aux URLs de se générer sans le préfixe `/novatis` :
- ❌ Avant : `https://novatis.alwaysdata.net/novatis/assets/...`
- ✅ Après : `https://novatis.alwaysdata.net/assets/...`

## 🆘 Besoin d'aide ?

Si vous avez des difficultés :
1. Utilisez l'interface web AlwaysData (méthode 1) - c'est la plus simple
2. Ou envoyez-moi une capture d'écran de l'erreur
