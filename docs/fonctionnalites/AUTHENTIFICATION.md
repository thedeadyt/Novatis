# 🔐 Authentification

Documentation complète du système d'authentification de Novatis.

---

## 📋 Vue d'ensemble

Le système d'authentification de Novatis offre plusieurs méthodes de connexion sécurisées :
- Authentification classique (email/mot de passe)
- OAuth 2.0 (Google, Microsoft, GitHub)
- Authentification à deux facteurs (2FA)
- Vérification par email
- Réinitialisation de mot de passe

---

## ✨ Fonctionnalités

### 1. Inscription

**Page :** `/Autentification?action=register`

**Fonctionnement :**
- Formulaire d'inscription avec validation
- Champs requis : nom, prénom, email, mot de passe
- Validation côté client et serveur
- Hash du mot de passe avec Bcrypt
- Envoi d'un email de vérification

**Code exemple :**
```javascript
// Inscription via l'interface
const handleRegister = async (userData) => {
  const response = await fetch(`${BASE_URL}/api/auth/register.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(userData)
  });
  return await response.json();
};
```

### 2. Connexion

**Page :** `/Autentification?action=login`

**Méthodes disponibles :**

#### A. Connexion classique
- Email + Mot de passe
- Validation des identifiants
- Création de session sécurisée
- Redirection vers le dashboard

#### B. Connexion OAuth
- **Google** : Connexion avec compte Google
- **Microsoft** : Connexion avec compte Microsoft
- **GitHub** : Connexion avec compte GitHub

**Processus OAuth :**
1. Clic sur le bouton de connexion sociale
2. Redirection vers le fournisseur OAuth
3. Autorisation de l'utilisateur
4. Retour vers Novatis avec le code d'autorisation
5. Échange du code contre un token d'accès
6. Récupération des informations utilisateur
7. Création ou mise à jour du compte
8. Création de session
9. Redirection vers le dashboard

**Code exemple :**
```javascript
// Connexion via OAuth (Google)
const handleGoogleLogin = () => {
  window.location.href = `${BASE_URL}/api/oauth/authorize.php?provider=google`;
};
```

### 3. Authentification à Deux Facteurs (2FA)

**Page :** `/Parametres?section=security`

**Fonctionnement :**
- Activation/désactivation du 2FA
- Génération d'un code QR TOTP
- Vérification avec application d'authentification (Google Authenticator, Authy, etc.)
- Codes de secours générés lors de l'activation

**Processus d'activation :**
1. Accéder aux paramètres de sécurité
2. Activer le 2FA
3. Scanner le code QR avec une application d'authentification
4. Entrer le code de vérification
5. Sauvegarder les codes de secours

**Processus de connexion avec 2FA :**
1. Entrer email et mot de passe
2. Si 2FA activé, demande du code TOTP
3. Entrer le code à 6 chiffres
4. Validation et création de session

### 4. Vérification par Email

**Page :** `/verify-email?token=XXX`

**Fonctionnement :**
- Email envoyé après inscription
- Lien avec token unique et expirant
- Validation du token
- Activation du compte

**Email de vérification contient :**
- Lien de vérification sécurisé
- Token valide pendant 24 heures
- Instructions claires

### 5. Réinitialisation de Mot de Passe

**Pages :**
- Demande : `/Autentification?action=forgot-password`
- Réinitialisation : `/reset-password?token=XXX`

**Processus :**
1. Demande de réinitialisation (email)
2. Envoi d'un email avec lien de réinitialisation
3. Clic sur le lien (valide 1 heure)
4. Saisie du nouveau mot de passe
5. Confirmation et mise à jour
6. Connexion avec le nouveau mot de passe

---

## 🔒 Sécurité

### Mesures Implémentées

#### 1. Mots de Passe
- **Hachage** : Bcrypt avec cost 12
- **Exigences** : Minimum 8 caractères
- **Validation** : Côté client et serveur

#### 2. Sessions
- **Durée** : 24 heures par défaut
- **Sécurité** : Cookies HttpOnly, Secure (en HTTPS), SameSite
- **Regénération** : ID de session régénéré après connexion

#### 3. Tokens
- **Vérification email** : Token unique, expire après 24h
- **Réinitialisation** : Token unique, expire après 1h
- **Génération** : Aléatoire et cryptographiquement sûr

#### 4. Protection Contre les Attaques

**Brute Force :**
- Limitation du nombre de tentatives
- Délai progressif entre les tentatives
- Blocage temporaire après échecs répétés

**Injection SQL :**
- Requêtes préparées (PDO)
- Validation des entrées

**XSS :**
- Échappement des sorties
- Content Security Policy

**CSRF :**
- Tokens CSRF sur les formulaires
- Validation côté serveur

---

## 🔑 OAuth - Configuration

### Configuration des Providers

Pour activer les connexions OAuth, configurez les clés dans le fichier `.env` :

```env
# Google OAuth
GOOGLE_CLIENT_ID=votre_google_client_id
GOOGLE_CLIENT_SECRET=votre_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost/Novatis/public/api/oauth/callback.php

# Microsoft OAuth
MICROSOFT_CLIENT_ID=votre_microsoft_client_id
MICROSOFT_CLIENT_SECRET=votre_microsoft_client_secret
MICROSOFT_REDIRECT_URI=http://localhost/Novatis/public/api/oauth/callback.php

# GitHub OAuth
GITHUB_CLIENT_ID=votre_github_client_id
GITHUB_CLIENT_SECRET=votre_github_client_secret
GITHUB_REDIRECT_URI=http://localhost/Novatis/public/api/oauth/callback.php
```

### Obtenir les Clés OAuth

#### Google
1. Aller sur [Google Cloud Console](https://console.cloud.google.com/)
2. Créer un projet
3. Activer Google+ API
4. Créer des identifiants OAuth 2.0
5. Configurer l'écran de consentement
6. Ajouter les URIs de redirection autorisées

#### Microsoft
1. Aller sur [Azure Portal](https://portal.azure.com/)
2. Créer une application dans Azure AD
3. Configurer les autorisations API
4. Générer un secret client
5. Ajouter les URIs de redirection

#### GitHub
1. Aller sur [GitHub Developer Settings](https://github.com/settings/developers)
2. Créer une nouvelle OAuth App
3. Renseigner les informations
4. Copier le Client ID et générer un Client Secret

---

## 📡 API

Les endpoints API d'authentification sont documentés dans [API Auth](../api/AUTH.md).

**Endpoints principaux :**
- `POST /api/auth/register.php` - Inscription
- `POST /api/auth/login.php` - Connexion
- `POST /api/auth/logout.php` - Déconnexion
- `POST /api/auth/forgot-password.php` - Demande réinitialisation
- `POST /api/auth/reset-password.php` - Réinitialisation mot de passe
- `GET /api/oauth/authorize.php` - Initier connexion OAuth
- `GET /api/oauth/callback.php` - Callback OAuth

---

## 🎨 Interface Utilisateur

### Page d'Authentification

**Emplacement :** `public/pages/Autentification.php`

**Composants :**
- Onglets Login/Register
- Formulaires avec validation en temps réel
- Boutons de connexion sociale
- Lien de mot de passe oublié
- Messages d'erreur/succès

**Design :**
- Responsive (mobile, tablette, desktop)
- Mode clair/sombre
- Animations fluides
- Validation visuelle des champs

---

## 🐛 Dépannage

### Problèmes Courants

#### 1. Connexion échoue sans message d'erreur

**Causes possibles :**
- Session PHP non démarrée
- Problème de connexion à la base de données
- Email non vérifié

**Solutions :**
```bash
# Vérifier les logs
tail -f storage/logs/app.log

# Vérifier la session
php -i | grep session
```

#### 2. OAuth ne fonctionne pas

**Vérifications :**
- Clés OAuth correctement configurées dans `.env`
- URIs de redirection correspondent exactement
- Permissions API activées

#### 3. Emails de vérification non envoyés

**Vérifications :**
- Configuration SMTP dans `.env`
- Paramètres du serveur mail corrects
- Vérifier les logs d'envoi

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre_email@gmail.com
MAIL_PASSWORD=votre_mot_de_passe_app
```

#### 4. 2FA bloque l'accès au compte

**Solution :**
- Utiliser un code de secours
- Contacter l'administrateur pour désactiver le 2FA

---

## 📚 Ressources

- [Documentation API Auth](../api/AUTH.md)
- [Documentation OAuth](../api/OAUTH.md)
- [Configuration des Paramètres](PARAMETRES.md)
- [Guide de Déploiement](../deploiement/DEPLOIEMENT.md)

---

<div align="center">

[← Retour à la Documentation](../DOCUMENTATION.md) • [API Auth →](../api/AUTH.md)

</div>
