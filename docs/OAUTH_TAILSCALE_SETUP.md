# Configuration OAuth pour Tailscale + Réseau Local

## 🔧 Problème résolu
Les URLs de callback détectent maintenant automatiquement si vous utilisez :
- **Réseau local** : `http://192.168.1.38/novatis`
- **Tailscale** : `http://100.78.233.118/novatis`

## ✅ Configuration actuelle

**Fichier modifié :** `/var/www/html/novatis/config/oauth.local.php`

Le fichier utilise maintenant `$baseUrl` qui détecte automatiquement l'URL via `$_SERVER['HTTP_HOST']`.

Cela signifie que les redirect_uri s'adaptent automatiquement :
- Si vous accédez via `192.168.1.38` → callback vers `http://192.168.1.38/novatis/api/oauth/callback.php`
- Si vous accédez via `100.78.233.118` → callback vers `http://100.78.233.118/novatis/api/oauth/callback.php`

## 📝 Étapes pour mettre à jour les applications OAuth

### 1️⃣ Google OAuth

1. Allez sur **https://console.cloud.google.com/**
2. Sélectionnez votre projet (ou créez-en un nouveau)
3. Menu **APIs & Services** > **Credentials**
4. Cliquez sur votre Client ID OAuth 2.0 existant : `378413768163-18h1j2mmvkf9b5ll1v4nc8omuqhcnbs4`
5. Dans **Authorized redirect URIs**, **ajoutez LES DEUX URLs** :
   ```
   http://192.168.1.38/novatis/api/oauth/callback.php?provider=google
   http://100.78.233.118/novatis/api/oauth/callback.php?provider=google
   ```
6. Cliquez sur **Save**

**Lien direct :** https://console.cloud.google.com/apis/credentials

✅ **Important :** Gardez les deux URLs pour que ça fonctionne en local ET via Tailscale !

---

### 2️⃣ Microsoft Azure

1. Allez sur **https://portal.azure.com/**
2. Recherchez **App registrations** dans la barre de recherche
3. Sélectionnez votre application : `4fce303c-54f4-4227-aec5-9a1f03d8a52d`
4. Dans le menu gauche, cliquez sur **Authentication**
5. Dans **Redirect URIs** > **Web**, **ajoutez LES DEUX URLs** :
   ```
   http://192.168.1.38/novatis/api/oauth/callback.php?provider=microsoft
   http://100.78.233.118/novatis/api/oauth/callback.php?provider=microsoft
   ```
6. Cliquez sur **Save**

**Lien direct :** https://portal.azure.com/#view/Microsoft_AAD_RegisteredApps/ApplicationMenuBlade/~/Authentication/appId/4fce303c-54f4-4227-aec5-9a1f03d8a52d

✅ **Important :** Gardez les deux URLs pour que ça fonctionne en local ET via Tailscale !

---

### 3️⃣ GitHub OAuth

⚠️ **Note :** GitHub ne permet qu'**UNE SEULE** callback URL par application.

**Solution 1 : Créer 2 applications GitHub** (Recommandé)

**Application 1 - Local :**
1. Allez sur **https://github.com/settings/developers**
2. Créez une nouvelle OAuth App "Novatis Local"
3. **Homepage URL:** `http://192.168.1.38/novatis`
4. **Authorization callback URL:** `http://192.168.1.38/novatis/api/oauth/callback.php?provider=github`

**Application 2 - Tailscale :**
1. Créez une autre OAuth App "Novatis Tailscale"
2. **Homepage URL:** `http://100.78.233.118/novatis`
3. **Authorization callback URL:** `http://100.78.233.118/novatis/api/oauth/callback.php?provider=github`

**Solution 2 : Utiliser uniquement Tailscale**

Si vous voulez une seule app GitHub :
1. Gardez l'app actuelle : `Ov23liWt1MZec2E0aSd7`
2. Mettez **Authorization callback URL:** `http://100.78.233.118/novatis/api/oauth/callback.php?provider=github`
3. Utilisez toujours Novatis via Tailscale pour GitHub OAuth

**Lien direct :** https://github.com/settings/developers

---

## 🚀 Test après configuration

**Test via réseau local :**
1. Ouvrez : `http://192.168.1.38/novatis`
2. Testez la connexion Google ✅
3. Testez la connexion Microsoft ✅
4. Testez la connexion GitHub (si configuré pour local)

**Test via Tailscale :**
1. Ouvrez : `http://100.78.233.118/novatis`
2. Testez la connexion Google ✅
3. Testez la connexion Microsoft ✅
4. Testez la connexion GitHub (si configuré pour Tailscale)

## 🔄 Option alternative : MagicDNS

Si vous voulez utiliser un nom de domaine au lieu de l'IP :

1. Activez MagicDNS dans Tailscale
2. Votre machine sera accessible via : `http://alex2/novatis`
3. Mettez à jour les redirect_uri vers :
   ```
   http://alex2/novatis/api/oauth/callback.php?provider=google
   http://alex2/novatis/api/oauth/callback.php?provider=microsoft
   http://alex2/novatis/api/oauth/callback.php?provider=github
   ```

## ⚠️ Notes importantes

1. **HTTP vs HTTPS** : Tailscale utilise HTTP par défaut. Pour HTTPS, vous devrez configurer un certificat SSL.

2. **Multiple URLs** : Vous pouvez avoir plusieurs redirect URIs dans vos apps OAuth (local, Tailscale, production)

3. **IP dynamique** : L'IP Tailscale (`100.78.233.118`) est stable mais peut changer si vous réinitialisez Tailscale

## 🛠️ Troubleshooting

### Erreur "redirect_uri_mismatch"
➡️ Vérifiez que l'URL configurée dans la console OAuth correspond **exactement** à celle dans `oauth.local.php`

### Erreur "Application not found"
➡️ Vérifiez que vos Client IDs et Secrets sont corrects

### Connexion qui ne fonctionne pas
➡️ Vérifiez que vous accédez bien à Novatis via `http://100.78.233.118/novatis` (et non `192.168.1.38`)

## 📋 Checklist

- [ ] Mettre à jour Google Console
- [ ] Mettre à jour Microsoft Azure
- [ ] Mettre à jour GitHub Settings
- [ ] Tester connexion Google
- [ ] Tester connexion Microsoft
- [ ] Tester connexion GitHub

---

**✅ Configuration du fichier PHP déjà faite !**

Il vous reste uniquement à mettre à jour les consoles OAuth des providers.
