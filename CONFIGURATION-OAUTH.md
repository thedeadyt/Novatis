# 🔐 Configuration OAuth pour Novatis

## 📋 URLs de redirection à configurer

Pour chaque plateforme OAuth, vous devez configurer l'URL de redirection suivante :

### Google OAuth
**URL de redirection :**
```
https://novatis.alwaysdata.net/api/oauth/callback/google.php
```

### Microsoft OAuth
**URL de redirection :**
```
https://novatis.alwaysdata.net/api/oauth/callback/microsoft.php
```

### GitHub OAuth
**URL de redirection :**
```
https://novatis.alwaysdata.net/api/oauth/callback/github.php
```

---

## 🔗 Liens de configuration

### 1. Google Cloud Console

**Créer une application OAuth Google :**

1. Allez sur : https://console.cloud.google.com/
2. Créez un nouveau projet ou sélectionnez-en un existant
3. Allez dans **APIs & Services** → **Credentials** : https://console.cloud.google.com/apis/credentials
4. Cliquez sur **+ CREATE CREDENTIALS** → **OAuth client ID**
5. Type d'application : **Application Web**
6. Nom : `Novatis`
7. **Origines JavaScript autorisées** :
   ```
   https://novatis.alwaysdata.net
   ```
8. **URI de redirection autorisés** :
   ```
   https://novatis.alwaysdata.net/api/oauth/callback/google.php
   ```
9. Cliquez sur **CREATE**
10. Copiez le **Client ID** et le **Client Secret**

---

### 2. Microsoft Azure Portal

**Créer une application OAuth Microsoft :**

1. Allez sur : https://portal.azure.com/
2. Recherchez **"Azure Active Directory"** ou **"Microsoft Entra ID"**
3. Allez dans **App registrations** : https://portal.azure.com/#view/Microsoft_AAD_RegisteredApps/ApplicationsListBlade
4. Cliquez sur **+ New registration**
5. Nom : `Novatis`
6. **Types de comptes pris en charge** :
   - Sélectionnez "Comptes dans un annuaire d'organisation et comptes personnels Microsoft"
7. **URI de redirection** :
   - Type : **Web**
   - URI :
     ```
     https://novatis.alwaysdata.net/api/oauth/callback/microsoft.php
     ```
8. Cliquez sur **Register**
9. Copiez **Application (client) ID**
10. Allez dans **Certificates & secrets** → **+ New client secret**
11. Description : `Novatis Secret`
12. Expiration : Choisissez la durée souhaitée
13. Cliquez sur **Add**
14. Copiez la **Value** du secret (attention : elle ne sera affichée qu'une fois !)

---

### 3. GitHub Developer Settings

**Créer une application OAuth GitHub :**

1. Allez sur : https://github.com/settings/developers
2. Cliquez sur **OAuth Apps** : https://github.com/settings/applications/new
3. Ou cliquez sur **New OAuth App**
4. **Application name** : `Novatis`
5. **Homepage URL** :
   ```
   https://novatis.alwaysdata.net
   ```
6. **Application description** : `Marketplace de services professionnels`
7. **Authorization callback URL** :
   ```
   https://novatis.alwaysdata.net/api/oauth/callback/github.php
   ```
8. Cliquez sur **Register application**
9. Copiez le **Client ID**
10. Cliquez sur **Generate a new client secret**
11. Copiez le **Client Secret** (il ne sera affiché qu'une fois !)

---

## 🛠️ Configuration sur AlwaysData

Une fois que vous avez obtenu vos identifiants OAuth, vous devez les ajouter au fichier `.env` sur AlwaysData.

### Méthode 1 : Script automatique (recommandé)

```bash
cd /var/www/html/novatis
python3 /tmp/update_oauth_credentials.py
```

Le script vous demandera d'entrer vos identifiants et mettra à jour automatiquement le fichier `.env`.

### Méthode 2 : Modification manuelle via FTP

1. Connectez-vous via FTP à `ftp-novatis.alwaysdata.net`
2. Ouvrez le fichier `/www/.env`
3. Modifiez les lignes suivantes :

```env
# OAuth - Google
GOOGLE_CLIENT_ID=votre_google_client_id
GOOGLE_CLIENT_SECRET=votre_google_client_secret
GOOGLE_REDIRECT_URI=https://novatis.alwaysdata.net/api/oauth/callback/google.php

# OAuth - Microsoft
MICROSOFT_CLIENT_ID=votre_microsoft_client_id
MICROSOFT_CLIENT_SECRET=votre_microsoft_client_secret
MICROSOFT_REDIRECT_URI=https://novatis.alwaysdata.net/api/oauth/callback/microsoft.php

# OAuth - GitHub
GITHUB_CLIENT_ID=votre_github_client_id
GITHUB_CLIENT_SECRET=votre_github_client_secret
GITHUB_REDIRECT_URI=https://novatis.alwaysdata.net/api/oauth/callback/github.php
```

4. Sauvegardez le fichier

---

## ✅ Vérification

Pour vérifier que OAuth fonctionne :

1. Allez sur : https://novatis.alwaysdata.net/Autentification?mode=login
2. Vous devriez voir les boutons de connexion pour :
   - 🔴 Continuer avec Google
   - 🔵 Continuer avec Microsoft
   - ⚫ Continuer avec GitHub
3. Testez la connexion avec chaque fournisseur OAuth configuré

---

## 🔒 Sécurité

### Important :
- **Ne partagez JAMAIS vos Client Secrets**
- Les secrets ne doivent être visibles que dans le fichier `.env`
- Ne les commitez jamais dans Git
- Changez-les régulièrement

### En cas de compromission :
1. Révoquez immédiatement les secrets depuis la console du fournisseur
2. Générez de nouveaux secrets
3. Mettez à jour le fichier `.env`

---

## 🆘 Dépannage

### Erreur "redirect_uri_mismatch"
- Vérifiez que l'URL de redirection dans la console du fournisseur correspond **exactement** à celle configurée
- Vérifiez qu'il n'y a pas d'espace ou de caractère invisible
- L'URL doit être en HTTPS

### Erreur "invalid_client"
- Vérifiez que le Client ID et Client Secret sont corrects
- Vérifiez qu'il n'y a pas d'espaces avant ou après dans le `.env`

### L'authentification échoue silencieusement
- Vérifiez les logs : `/www/storage/logs/app.log`
- Vérifiez que les URLs de callback sont accessibles publiquement

---

## 📞 Support

Pour plus d'aide :
- Documentation Google OAuth : https://developers.google.com/identity/protocols/oauth2
- Documentation Microsoft OAuth : https://learn.microsoft.com/en-us/azure/active-directory/develop/
- Documentation GitHub OAuth : https://docs.github.com/en/apps/oauth-apps

---

**Dernière mise à jour** : 2025-11-16
