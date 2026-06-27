# 🚨 Correction Urgente - Erreurs 404

## Symptôme

Erreurs dans la console :
```
GET https://novatis.alwaysdata.net/novatis/assets/css/Variables.css 404
GET https://novatis.alwaysdata.net/novatis/assets/js/theme.js 404
```

## ⚡ Solution Rapide (2 minutes)

### Méthode 1 : Script automatique (recommandé)

1. **Transférez le script de correction sur AlwaysData**

```bash
# Sur votre machine locale
scp /var/www/html/novatis/fix-alwaysdata.sh alex2pro@ssh-alex2pro.alwaysdata.net:/home/alex2pro/
```

2. **Connectez-vous à AlwaysData et exécutez le script**

```bash
ssh alex2pro@ssh-alex2pro.alwaysdata.net
cd /home/alex2pro/www
bash ~/fix-alwaysdata.sh
```

3. **Rechargez la page** (Ctrl+Shift+R pour vider le cache)

---

### Méthode 2 : Correction manuelle

1. **Connectez-vous en SSH**

```bash
ssh alex2pro@ssh-alex2pro.alwaysdata.net
```

2. **Éditez le fichier .env**

```bash
cd /home/alex2pro/www
nano .env
```

3. **Trouvez la ligne APP_URL et modifiez-la**

**Avant (incorrect):**
```
APP_URL=/novatis
```

**Après (correct):**
```
APP_URL=
```

*Laissez APP_URL vide (rien après le =)*

4. **Sauvegardez** (Ctrl+O, Enter, Ctrl+X)

5. **Vérifiez la correction**

```bash
cat .env | grep APP_URL
# Doit afficher: APP_URL=
```

6. **Rechargez votre site** : https://novatis.alwaysdata.net (Ctrl+Shift+R)

---

## 🔍 Pourquoi ce problème ?

Votre site est hébergé à la **racine** de `novatis.alwaysdata.net`, pas dans un sous-dossier `/novatis`.

- ❌ **Incorrect**: `https://novatis.alwaysdata.net/novatis/assets/...`
- ✅ **Correct**: `https://novatis.alwaysdata.net/assets/...`

Quand `APP_URL=/novatis`, PHP génère des URLs avec le préfixe `/novatis`.
Quand `APP_URL=` (vide), PHP génère des URLs sans préfixe.

---

## ✅ Vérification

Après la correction, vous devriez voir :

1. ✅ Les CSS se chargent correctement
2. ✅ Les images s'affichent
3. ✅ Les catégories apparaissent sur la page d'accueil
4. ✅ Aucune erreur 404 dans la console du navigateur

---

## 🆘 Si ça ne fonctionne toujours pas

### Problème 1 : Les fichiers ne sont pas au bon endroit

**Vérifiez la structure :**

```bash
ssh alex2pro@ssh-alex2pro.alwaysdata.net
ls -la /home/alex2pro/www/

# Vous devriez voir :
# - public/
# - config/
# - storage/
# - .env
# etc.
```

**Si les fichiers sont dans** `/home/alex2pro/www/novatis/` **au lieu de** `/home/alex2pro/www/` :

```bash
# Déplacez tout
cd /home/alex2pro/www
mv novatis/* .
mv novatis/.env .
mv novatis/.htaccess .
rmdir novatis
```

### Problème 2 : Configuration du site dans AlwaysData

Vérifiez dans l'interface AlwaysData (Sites Web) :

- **Répertoire racine** doit être : `/home/alex2pro/www/public`
- **PAS** : `/home/alex2pro/www/novatis/public`

Si c'est incorrect, modifiez-le dans l'interface AlwaysData.

### Problème 3 : Cache du navigateur

Videz complètement le cache :
- **Chrome/Edge** : Ctrl+Shift+Delete
- **Firefox** : Ctrl+Shift+Delete
- Cochez "Images et fichiers en cache"
- Cliquez sur "Effacer les données"

Puis rechargez avec Ctrl+Shift+R

---

## 📞 Besoin d'aide ?

Si le problème persiste après ces étapes :

1. Vérifiez les logs :
```bash
ssh alex2pro@ssh-alex2pro.alwaysdata.net
cat /home/alex2pro/www/storage/logs/app.log
```

2. Testez l'accès direct aux fichiers :
```bash
curl -I https://novatis.alwaysdata.net/assets/css/Variables.css
# Devrait retourner 200 OK
```

3. Vérifiez la configuration PHP dans l'interface AlwaysData
