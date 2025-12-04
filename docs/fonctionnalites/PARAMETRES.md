# ⚙️ Paramètres Utilisateur

Documentation complète de la gestion des paramètres et préférences utilisateur.

---

## 📋 Vue d'ensemble

Le système de paramètres de Novatis permet aux utilisateurs de gérer complètement leur profil, leurs préférences et leurs paramètres de sécurité. Les utilisateurs peuvent modifier leurs informations personnelles, configurer les notifications, gérer la sécurité du compte, les préférences de communication et les paramètres de confidentialité. Chaque modification est tracée et sécurisée.

---

## ✨ Fonctionnalités

### 1. Profil Utilisateur

**Page :** `/Parametres?section=profil`

**Informations modifiables :**
- Prénom et nom
- Email (avec vérification)
- Photo de profil
- Biographie/Description
- Localisation
- Numéro de téléphone
- Langue préférée
- Fuseau horaire

**Validations :**
- Email unique et valide
- Longeur maximale des champs
- Format de téléphone valide
- Upload de photo sécurisé

**Code exemple :**
```javascript
// Mettre à jour le profil
const updateProfile = async (profileData) => {
  const response = await fetch(`${BASE_URL}/api/user/profile.php`, {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(profileData)
  });
  return await response.json();
};

// Mettre à jour la photo de profil
const updateAvatar = async (file) => {
  const formData = new FormData();
  formData.append('avatar', file);
  return await fetch(`${BASE_URL}/api/user/avatar.php`, {
    method: 'POST',
    body: formData
  });
};
```

### 2. Sécurité du Compte

**Page :** `/Parametres?section=security`

**Options disponibles :**

#### A. Mot de Passe
- Changement de mot de passe
- Vérification de l'ancien mot de passe
- Exigences de force du mot de passe
- Historique des changements

#### B. Authentification à Deux Facteurs (2FA)
- Activation/désactivation TOTP
- Code QR pour scanner
- Codes de secours
- Authentification par biométrie

#### C. Sessions Actives
- Liste des appareils connectés
- Informations : navigateur, OS, IP, date
- Déconnexion à distance
- Alertes de connexion suspicieuse

#### D. Logs de Sécurité
- Historique des connexions
- Modifications de paramètres
- Actions sensibles
- Adresses IP et navigateurs

**Code exemple :**
```javascript
// Changer le mot de passe
const changePassword = async (oldPassword, newPassword) => {
  return await fetch(`${BASE_URL}/api/user/change-password.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ old_password: oldPassword, new_password: newPassword })
  });
};

// Activer 2FA
const enable2FA = async () => {
  return await fetch(`${BASE_URL}/api/user/2fa/enable.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' }
  });
};
```

### 3. Notifications

**Page :** `/Parametres?section=notifications`

**Paramètres :**
- Notifications email (activé/désactivé)
- Notifications navigateur (activé/désactivé)
- Notifications push (activé/désactivé)
- Fréquence (immédiat, quotidien, hebdomadaire)

**Par type :**
- Nouveaux messages
- Mises à jour de commandes
- Avis reçus
- Activité sur le compte
- Newsletters

**Horaires silencieux :**
- Heure de début et fin
- Jour de la semaine

### 4. Confidentialité

**Page :** `/Parametres?section=privacy`

**Paramètres :**
- Visibilité du profil (public/privé)
- Affichage du statut en ligne
- Affichage du statut de lecture (messages)
- Qui peut m'envoyer des messages
- Qui peut voir ma liste de favoris
- Qui peut voir mes avis

### 5. Préférences de Communication

**Page :** `/Parametres?section=communication`

**Options :**
- Langue de l'interface
- Fuseau horaire
- Format de date et heure
- Devise préférée (si applicable)
- Langue préférée pour les emails

### 6. Suppression de Compte

**Page :** `/Parametres?section=delete-account` ou `/delete-account`

**Processus :**
1. Confirmation de l'identité
2. Avertissement des conséquences
3. Délai de rétention (30 jours)
4. Suppression définitive après 30 jours

**Ce qui se passe :**
- Compte désactivé immédiatement
- Données visibles masquées
- Conversations archivées
- Avis conservés (anonymisés après 30 jours)
- Données personnelles supprimées après 30 jours

**Code exemple :**
```javascript
// Initier la suppression de compte
const deleteAccount = async (password) => {
  return await fetch(`${BASE_URL}/api/user/delete-account.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ password: password })
  });
};

// Annuler la suppression de compte
const cancelAccountDeletion = async () => {
  return await fetch(`${BASE_URL}/api/user/cancel-deletion.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' }
  });
};
```

### 7. Données Personnelles

**Page :** `/Parametres?section=data`

**Options :**
- Télécharger les données au format JSON
- Télécharger les données au format CSV
- Droit à l'oubli
- Historique d'activité

### 8. Intégrations Connexes

**OAuth :**
- Comptes Google, Microsoft, GitHub connectés
- Déconnexion des services
- Permissions accordées

---

## 🎨 Interface Utilisateur

### Page Paramètres

**Emplacement :** `public/pages/Parametres.php`

**Navigation :**
- Barre latérale avec sections
- Icônes pour chaque section
- Section actuelle mise en évidence

**Sections :**
1. Profil - Informations personnelles
2. Sécurité - Mot de passe, 2FA, sessions
3. Notifications - Préférences de notification
4. Confidentialité - Visibilité et partage
5. Communication - Langue, fuseau horaire
6. Données - Téléchargement, suppression
7. Suppression - Supprimer le compte

**Design :**
- Mode clair/sombre
- Responsive (mobile, desktop)
- Groupes logiques de paramètres
- Messages de confirmation pour les changements

### Composants

**Champs de texte :**
- Validation en temps réel
- Messages d'erreur clairs
- Sauvegarde automatique (draft)

**Sélecteurs :**
- Dropdowns pour sélection
- Boutons radio pour choix exclusifs
- Checkbox pour options multiples

**Upload de fichier :**
- Zone de drag-drop
- Aperçu de l'image
- Rotation/recadrage (pour avatar)

---

## 📡 API

Les endpoints API de paramètres sont documentés dans [API Paramètres](../api/SETTINGS.md).

**Endpoints principaux :**
- `GET /api/user/profile.php` - Récupérer le profil
- `PUT /api/user/profile.php` - Mettre à jour le profil
- `POST /api/user/avatar.php` - Mettre à jour l'avatar
- `POST /api/user/change-password.php` - Changer le mot de passe
- `GET /api/user/2fa/setup.php` - Configuration 2FA
- `POST /api/user/2fa/enable.php` - Activer 2FA
- `POST /api/user/2fa/disable.php` - Désactiver 2FA
- `GET /api/user/sessions.php` - Lister les sessions
- `DELETE /api/user/sessions.php?id=XXX` - Déconnecter session
- `GET /api/user/preferences.php` - Récupérer préférences
- `PUT /api/user/preferences.php` - Mettre à jour préférences
- `POST /api/user/delete-account.php` - Initier suppression
- `POST /api/user/cancel-deletion.php` - Annuler suppression

---

## 🐛 Dépannage

### Problèmes Courants

#### 1. Impossible de changer le mot de passe

**Causes possibles :**
- Ancien mot de passe incorrect
- Mot de passe ne respecte pas les exigences
- Compte non authentifié

**Solutions :**
```bash
# Vérifier les exigences de mot de passe
curl -X GET http://localhost/Novatis/public/api/user/password-requirements.php

# Vérifier l'historique des changements
SELECT * FROM password_history WHERE user_id = 'XXX' ORDER BY created_at DESC;
```

#### 2. 2FA ne fonctionne pas

**Vérifications :**
- Application d'authentification synchronisée
- Fuseau horaire du serveur correct
- Code QR scanné correctement
- Codes de secours sauvegardés

#### 3. L'email n'est pas changé

**Causes possibles :**
- Email déjà en utilisation
- Email non valide
- Vérification email requise

#### 4. Les paramètres ne sont pas sauvegardés

**Solutions :**
- Vérifier la connexion réseau
- Recharger la page
- Vider le cache du navigateur

```bash
# Vérifier les logs
tail -f storage/logs/app.log | grep settings
```

#### 5. La suppression de compte ne fonctionne pas

**Vérifications :**
- Mot de passe correct
- Compréhension des conséquences
- Délai de 30 jours respecté

---

## 📚 Ressources

- [Documentation API Paramètres](../api/SETTINGS.md)
- [Authentification](AUTHENTIFICATION.md)
- [Système de Notifications](NOTIFICATIONS.md)
- [Guide de Déploiement](../deploiement/DEPLOIEMENT.md)

---

<div align="center">

[← Retour à la Documentation](../DOCUMENTATION.md) • [API Paramètres →](../api/SETTINGS.md)

</div>
