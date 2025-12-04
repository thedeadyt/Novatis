# API Paramètres

Documentation de l'API Paramètres de Novatis.

---

## Vue d'ensemble

L'API Paramètres permet aux utilisateurs de gérer leurs préférences, paramètres de sécurité, confidentialité et d'autres configurations de compte.

**Base URL :** `/api/parametres/`

---

## Authentification

Tous les endpoints de cette API nécessitent une authentification utilisateur.

---

## Endpoints

### 1. Mettre à jour le profil

**Méthode :** `POST`
**URL :** `/api/parametres/settings.php`
**Authentification :** Requise

Met à jour les informations de profil de l'utilisateur.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| action | string | Oui | Valeur : `update_profile` |
| firstname | string | Oui | Prénom |
| lastname | string | Oui | Nom |
| pseudo | string | Oui | Pseudo unique |
| email | string | Oui | Adresse email |
| phone | string | Non | Numéro de téléphone |
| bio | string | Non | Biographie |
| location | string | Non | Localisation |
| website | string | Non | Site web |
| timezone | string | Non | Fuseau horaire (défaut: Europe/Paris) |

**Exemple de requête :**

```javascript
const response = await fetch('/api/parametres/settings.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded'
  },
  body: new URLSearchParams({
    action: 'update_profile',
    firstname: 'Jean',
    lastname: 'Dupont',
    pseudo: 'jeandupont',
    email: 'jean@example.com',
    phone: '+33612345678',
    bio: 'Designer graphique professionnel',
    location: 'Paris, France',
    timezone: 'Europe/Paris'
  })
});
const data = await response.json();
```

**Réponse :** Redirection avec message de succès/erreur en session.

---

### 2. Changer le mot de passe

**Méthode :** `POST`
**URL :** `/api/parametres/settings.php`
**Authentification :** Requise

Modifie le mot de passe de l'utilisateur.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| action | string | Oui | Valeur : `change_password` |
| current_password | string | Oui | Mot de passe actuel |
| new_password | string | Oui | Nouveau mot de passe (minimum 8 caractères) |
| confirm_password | string | Oui | Confirmation du nouveau mot de passe |

**Exemple de requête :**

```javascript
const response = await fetch('/api/parametres/settings.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded'
  },
  body: new URLSearchParams({
    action: 'change_password',
    current_password: 'OldPassword123',
    new_password: 'NewPassword456',
    confirm_password: 'NewPassword456'
  })
});
```

**Validations :**
- Le mot de passe actuel doit être correct
- Les deux nouveaux mots de passe doivent correspondre
- Le nouveau mot de passe doit avoir au minimum 8 caractères

---

### 3. Activer/Désactiver l'authentification à 2 facteurs

**Méthode :** `POST`
**URL :** `/api/parametres/settings.php`
**Authentification :** Requise

Active ou désactive l'authentification à 2 facteurs (A2F).

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| action | string | Oui | Valeur : `toggle_2fa` |

**Exemple de requête :**

```javascript
const response = await fetch('/api/parametres/settings.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded'
  },
  body: new URLSearchParams({
    action: 'toggle_2fa'
  })
});
```

**Réponse :** Redirection avec un secret QR code si activation.

---

### 4. Mettre à jour les préférences de notifications

**Méthode :** `POST`
**URL :** `/api/parametres/settings.php`
**Authentification :** Requise

Configure les préférences de notification de l'utilisateur.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| action | string | Oui | Valeur : `update_notifications` |
| email_notifications | boolean | Non | Activer les notifications par email |
| push_notifications | boolean | Non | Activer les notifications push |

**Exemple de requête :**

```javascript
const response = await fetch('/api/parametres/settings.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded'
  },
  body: new URLSearchParams({
    action: 'update_notifications',
    email_notifications: 'on',
    push_notifications: 'off'
  })
});
```

---

### 5. Mettre à jour les paramètres de confidentialité

**Méthode :** `POST`
**URL :** `/api/parametres/settings.php`
**Authentification :** Requise

Configure les paramètres de confidentialité et de visibilité du profil.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| action | string | Oui | Valeur : `update_privacy` |
| profile_visibility | string | Non | Visibilité du profil (`public`, `private`, `restricted`) |
| show_email | boolean | Non | Afficher l'email sur le profil |
| show_phone | boolean | Non | Afficher le téléphone sur le profil |
| allow_search_engines | boolean | Non | Autoriser l'indexation par les moteurs de recherche |
| data_sharing | boolean | Non | Partager les données avec des partenaires |

**Exemple de requête :**

```javascript
const response = await fetch('/api/parametres/settings.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded'
  },
  body: new URLSearchParams({
    action: 'update_privacy',
    profile_visibility: 'public',
    show_email: 'off',
    show_phone: 'off',
    allow_search_engines: 'on',
    data_sharing: 'off'
  })
});
```

---

### 6. Mettre à jour les paramètres d'affichage

**Méthode :** `POST`
**URL :** `/api/parametres/settings.php`
**Authentification :** Requise

Configure les préférences d'affichage (thème, devise, etc.).

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| action | string | Oui | Valeur : `update_display` |
| dark_mode | boolean | Non | Activer le mode sombre |
| currency | string | Non | Devise par défaut (EUR, USD, GBP, etc.) |

**Exemple de requête :**

```javascript
const response = await fetch('/api/parametres/settings.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded'
  },
  body: new URLSearchParams({
    action: 'update_display',
    dark_mode: 'on',
    currency: 'EUR'
  })
});
```

---

### 7. Mettre à jour la langue

**Méthode :** `POST`
**URL :** `/api/parametres/settings.php`
**Authentification :** Requise

Change la langue de l'interface.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| action | string | Oui | Valeur : `update_language` |
| language | string | Oui | Code de langue (`fr` ou `en`) |

**Exemple de requête :**

```javascript
const response = await fetch('/api/parametres/settings.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded'
  },
  body: new URLSearchParams({
    action: 'update_language',
    language: 'fr'
  })
});
const data = await response.json();
```

**Réponse (succès) :**

```json
{
  "success": true,
  "message": "Langue mise à jour avec succès",
  "language": "fr"
}
```

**Réponses possibles :**
- `fr` - Français
- `en` - Anglais

---

### 8. Configurer l'authentification à 2 facteurs

**Méthode :** `POST`
**URL :** `/api/parametres/2fa-setup.php`
**Authentification :** Requise

Configure l'authentification à 2 facteurs avec un secret et un code QR.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| secret | string | Non | Secret TOTP |
| backup_codes | array | Non | Codes de sauvegarde |

---

### 9. Supprimer le compte

**Méthode :** `POST`
**URL :** `/api/parametres/settings.php` (ou `/pages/delete-account.php`)
**Authentification :** Requise

Supprime définitivement le compte utilisateur et toutes les données associées.

**Paramètres :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| action | string | Oui | Valeur : `delete_account` |
| confirm_deletion | boolean | Oui | Confirmation explicite |
| password | string | Oui | Mot de passe pour confirmation |

**Exemple de requête :**

```javascript
const response = await fetch('/api/parametres/settings.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded'
  },
  body: new URLSearchParams({
    action: 'delete_account',
    confirm_deletion: 'yes',
    password: 'MonMotDePasse123'
  })
});
```

**Avertissement :** Cette action est irréversible.

---

## Tables de paramètres

### user_preferences
Stocke les préférences utilisateur.

```php
[
  'email_notifications' => true,
  'push_notifications' => false,
  'dark_mode' => false,
  'language' => 'fr',
  'timezone' => 'Europe/Paris',
  'currency' => 'EUR'
]
```

### user_security
Stocke les informations de sécurité.

```php
[
  'two_factor_enabled' => true,
  'two_factor_secret' => 'ABC123...',
  'backup_codes' => ['code1', 'code2', ...],
  'last_password_change' => '2024-01-15',
  'login_attempts' => 0,
  'locked_until' => null
]
```

### user_privacy
Stocke les paramètres de confidentialité.

```php
[
  'profile_visibility' => 'public',
  'show_email' => false,
  'show_phone' => false,
  'allow_search_engines' => true,
  'data_sharing' => false,
  'analytics_tracking' => true,
  'marketing_emails' => true
]
```

---

## Codes d'erreur HTTP

| Code | Message | Description |
|------|---------|-------------|
| 200 | OK | Requête réussie |
| 400 | Bad Request | Paramètres manquants ou invalides |
| 401 | Unauthorized | Authentification requise |
| 403 | Forbidden | Accès refusé |
| 405 | Method Not Allowed | Seule POST/GET autorisée |
| 500 | Server Error | Erreur serveur |

---

## Validations

- **Email** : Doit être unique et valide
- **Pseudo** : Doit être unique et contenir au moins 3 caractères
- **Mot de passe** : Minimum 8 caractères pour le nouveau
- **Langue** : Doit être `fr` ou `en`
- **Fuseau horaire** : Doit être un fuseau valide
- **Devise** : Code ISO 4217 valide

---

## Sécurité

- Les mots de passe sont toujours hachés
- L'authentification à 2 facteurs utilise TOTP
- Les codes de sauvegarde peuvent être utilisés en remplacement
- La suppression de compte est irréversible
- Un mot de passe est requis pour les actions sensibles

---

## Ressources

- [Documentation API](API.md)
- [Authentification (AUTH.md)](AUTH.md)
- [Documentation Complète](../DOCUMENTATION.md)

---

<div align="center">
[← Avis](AVIS.md) • [Favoris →](FAVORIS.md)
</div>
