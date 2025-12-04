# ✅ Checklist de Déploiement Novatis

Utilisez cette checklist pour vérifier que tout fonctionne correctement après le déploiement.

## 📋 Avant le déploiement

- [ ] Le fichier `.env.production` contient `APP_URL=` (vide, car pas de sous-dossier)
- [ ] Les identifiants de base de données sont corrects dans `.env.production`
- [ ] Le projet fonctionne en local sans erreur
- [ ] Tous les fichiers ont été testés

## 📤 Pendant le déploiement

- [ ] Les fichiers ont été transférés avec succès
- [ ] Le fichier `.env` a été copié depuis `.env.production`
- [ ] Les permissions du dossier `storage` sont configurées (777)
- [ ] Les dépendances Composer ont été installées (si nécessaire)

## 🔍 Après le déploiement

### Tests de base

- [ ] Le site est accessible : https://novatis.alwaysdata.net
- [ ] La page d'accueil se charge sans erreur
- [ ] Les fichiers CSS se chargent (pas d'erreur 404 dans la console)
- [ ] Les fichiers JavaScript se chargent (pas d'erreur 404 dans la console)
- [ ] Les logos et images s'affichent correctement

### Tests des fonctionnalités

- [ ] La connexion fonctionne
- [ ] L'inscription fonctionne
- [ ] Les catégories se chargent sur la page d'accueil
- [ ] La recherche fonctionne
- [ ] Les profils utilisateurs s'affichent
- [ ] Le changement de langue fonctionne
- [ ] Le changement de thème (clair/sombre) fonctionne

### Tests de l'API

- [ ] L'API home.php retourne des données : https://novatis.alwaysdata.net/api/home.php
- [ ] Les autres endpoints API fonctionnent

### Vérifications de sécurité

- [ ] HTTPS est activé et fonctionne
- [ ] `APP_DEBUG=false` dans `.env`
- [ ] `APP_ENV=production` dans `.env`
- [ ] Les logs d'erreurs ne s'affichent pas à l'utilisateur

## 🐛 Si quelque chose ne fonctionne pas

### Erreurs 404 sur les assets (CSS/JS/Images)

**Problème**: Les fichiers Variables.css, theme.css, etc. ne se chargent pas

**Solution**:
```bash
# Vérifiez APP_URL dans .env
ssh alex2pro@ssh-alex2pro.alwaysdata.net
cat /home/alex2pro/www/.env | grep APP_URL
# Doit afficher: APP_URL= (vide, car pas de sous-dossier)
```

### Erreur 500 - Internal Server Error

**Problème**: Le serveur renvoie une erreur 500

**Solution**:
```bash
# Vérifiez les logs
ssh alex2pro@ssh-alex2pro.alwaysdata.net
cat /home/alex2pro/www/storage/logs/app.log
# ou
cat /home/alex2pro/admin/logs/web/novatis.alwaysdata.net.error.log
```

### L'API home.php renvoie une erreur

**Problème**: L'appel à /api/home.php échoue

**Solution**:
```bash
# Testez l'accès direct
curl -I https://novatis.alwaysdata.net/api/home.php

# Vérifiez les permissions
ssh alex2pro@ssh-alex2pro.alwaysdata.net
ls -la /home/alex2pro/www/public/api/
```

### Les catégories ne se chargent pas

**Problème**: "Erreur chargement catégories" dans la console

**Causes possibles**:
1. Base de données inaccessible
2. APP_URL incorrect
3. Session PHP non configurée

**Solution**:
```bash
# Testez la base de données
ssh alex2pro@ssh-alex2pro.alwaysdata.net
cd /home/alex2pro/www
php -r "
require 'bootstrap/app.php';
try {
    \$pdo = App\Database\Connection::getInstance();
    echo 'Connexion OK';
} catch (Exception \$e) {
    echo 'Erreur: ' . \$e->getMessage();
}
"
```

## 📞 Contacts en cas de blocage

- **Support AlwaysData**: https://admin.alwaysdata.com/support/
- **Documentation Novatis**: Voir [DEPLOIEMENT.md](./DEPLOIEMENT.md)

---

**Dernière vérification**: ___________
**Déployé par**: ___________
**Date**: ___________
