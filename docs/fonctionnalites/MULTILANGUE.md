# 🌍 Système Multilingue (i18n)

Documentation complète du système de traduction et d'internationalisation (i18n).

---

## 📋 Vue d'ensemble

Le système multilingue de Novatis permet une interface entièrement localisée dans plusieurs langues. Utilisant le standard i18n, il offre une traduction complète de l'interface, du contenu utilisateur et des messages système. Les utilisateurs peuvent changer de langue à tout moment, et leur préférence est sauvegardée. Le système supporte également les variations régionales et les formats localisés pour les dates, heures et devises.

---

## ✨ Fonctionnalités

### 1. Langues Supportées

**Langues disponibles :**
- Français (fr)
- Anglais (en)
- Espagnol (es)
- Allemand (de)
- Italien (it)
- Portugais (pt)
- Néerlandais (nl)
- Japonais (ja)
- Chinois (zh)
- Russe (ru)

**Localisation :**
- Chaque langue dans un fichier JSON séparé
- Emplacement : `/public/locales/`
- Format : `[code_langue].json`

### 2. Détection et Sélection de Langue

**Détection automatique :**
- Langue du navigateur
- Langue de l'utilisateur (si connecté)
- Langue du système (fallback)
- Langue par défaut : français

**Changement manuel :**
- Sélecteur en haut à droite
- Stockage en session et base de données
- Application immédiate

**Code exemple :**
```javascript
// Récupérer la langue actuelle
const getCurrentLanguage = () => {
  return localStorage.getItem('language') || 'fr';
};

// Changer la langue
const setLanguage = async (languageCode) => {
  localStorage.setItem('language', languageCode);

  const response = await fetch(`${BASE_URL}/api/i18n/set-language.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ language: languageCode })
  });

  location.reload();
};
```

### 3. Traduction de Contenu

**Textes traduits :**
- Interface utilisateur (boutons, labels, menus)
- Messages d'erreur et de succès
- Descriptions de services
- Contenu d'aide
- Emails
- Notifications

**Structure des fichiers :**
```json
{
  "common": {
    "app_name": "Novatis",
    "welcome": "Bienvenue",
    "save": "Enregistrer",
    "cancel": "Annuler"
  },
  "auth": {
    "login": "Connexion",
    "register": "Inscription",
    "forgot_password": "Mot de passe oublié"
  },
  "dashboard": {
    "title": "Tableau de bord",
    "welcome_message": "Bienvenue, {name}"
  }
}
```

**Utilisation :**
```javascript
// Frontend JavaScript
const message = i18n.t('common.welcome');
const personalized = i18n.t('dashboard.welcome_message', { name: userName });

// Backend PHP
$message = I18n::translate('common.welcome');
$personalized = I18n::translate('dashboard.welcome_message', ['name' => $userName]);
```

### 4. Formatage Localisé

**Dates :**
- Format selon la locale
- Français : DD/MM/YYYY
- Anglais : MM/DD/YYYY
- Japonais : YYYY年MM月DD日

**Heures :**
- Format 24h ou 12h selon la locale
- Fuseau horaire utilisateur
- Heure relative (il y a X minutes)

**Nombres et Devises :**
- Séparateur décimal (point ou virgule)
- Séparateur de milliers
- Symbole de devise
- Symbole de pourcentage

**Code exemple :**
```javascript
// Formater une date
const formattedDate = i18n.formatDate(new Date(), 'short');

// Formater une devise
const formattedPrice = i18n.formatCurrency(99.99, 'EUR');

// Formater un nombre
const formattedNumber = i18n.formatNumber(1234.56);
```

### 5. Pluralisation

**Règles de pluralisation :**
- Appliquées automatiquement selon la langue
- Variations selon la quantité
- Gestion de cas spéciaux (0, 1, 2+)

**Exemple :**
```json
{
  "messages": {
    "item_count": "Il y a 1 élément | Il y a {count} éléments"
  }
}
```

```javascript
// Utilisation
i18n.tc('messages.item_count', 5); // Retourne "Il y a 5 éléments"
i18n.tc('messages.item_count', 1); // Retourne "Il y a 1 élément"
```

### 6. Clé Manquante et Fallback

**Gestion des clés manquantes :**
- Affichage de la clé (mode développement)
- Fallback vers la langue par défaut
- Logging des traductions manquantes

**Hiérarchie :**
1. Langue sélectionnée
2. Langue du navigateur
3. Langue du système (français)
4. Clé non traduite (dev) ou clé (prod)

### 7. Traduction du Contenu Utilisateur

**Services et Descriptions :**
- Les prestataires peuvent enregistrer plusieurs langues
- Contenu traduit par langage
- Affichage selon la langue de l'utilisateur

**Avis :**
- Traduction optionnelle disponible
- Détection de langue automatique
- Affichage dans la langue de l'utilisateur

---

## 🎨 Interface Utilisateur

### Sélecteur de Langue

**Emplacement :** En-tête de la page (haut-droit)

**Composants :**
- Drapeau du pays actuel
- Dropdown avec liste des langues
- Noms en langue locale et anglais
- Changement immédiat de l'interface

**Design :**
- Icônes de drapeau
- Menu déroulant avec scroll
- Langue actuelle mise en évidence
- Responsive (icône seule sur mobile)

### Messages Localisés

**Erreurs et Succès :**
- Notifications avec le bon texte
- Placeholder dans les formulaires
- Messages d'aide contextuels

**Contenu Dynamique :**
- Noms propres inchangés
- Dates et nombres formatés
- Symboles de devise appropriés

---

## 📡 API

Les endpoints API i18n sont documentés dans [API i18n](../api/I18N.md).

**Endpoints principaux :**
- `GET /api/i18n/translations.php?lang=fr` - Récupérer les traductions
- `POST /api/i18n/set-language.php` - Définir la langue
- `GET /api/i18n/languages.php` - Lister les langues
- `GET /api/i18n/detect-language.php` - Détecter langue du navigateur
- `POST /api/i18n/format-date.php` - Formater une date
- `POST /api/i18n/format-currency.php` - Formater une devise

---

## 🐛 Dépannage

### Problèmes Courants

#### 1. Les traductions ne s'affichent pas

**Causes possibles :**
- Fichier de traduction manquant
- Clé de traduction incorrecte
- Langue non supportée
- Cache du navigateur

**Solutions :**
```bash
# Vérifier les fichiers de traduction
ls -la public/locales/

# Vérifier les logs
grep -r "missing_translation" storage/logs/

# Vider le cache
curl -X POST http://localhost/Novatis/public/api/i18n/clear-cache.php
```

#### 2. La langue ne change pas

**Vérifications :**
- JavaScript activé dans le navigateur
- API i18n accessible
- LocalStorage activé
- Cookies actifs

#### 3. Le formatage des dates est incorrect

**Solutions :**
- Vérifier le fuseau horaire du serveur
- Vérifier le format de locale
- Vérifier la timezone utilisateur

```bash
# Vérifier le fuseau horaire serveur
date
php -i | grep timezone

# Vérifier les paramètres locales
locale -a | grep -i fr
```

#### 4. Les caractères spéciaux ne s'affichent pas

**Vérifications :**
- Encodage UTF-8 des fichiers JSON
- Header Content-Type: charset=utf-8
- Balise meta charset en HTML

```html
<meta charset="UTF-8">
```

#### 5. Les traductions manquantes demandent trop de temps

**Solutions :**
- Pré-charger les traductions
- Utiliser un système de cache
- Charger les traductions à la demande

```javascript
// Pré-charger les traductions
const preloadTranslations = async () => {
  const translations = await fetch(`${BASE_URL}/api/i18n/translations.php`);
  return await translations.json();
};
```

---

## 📚 Ressources

- [Documentation API i18n](../api/I18N.md)
- [Configuration des Paramètres](PARAMETRES.md)
- [Guide de Localisation](../guides/LOCALISATION.md)
- [Guide de Déploiement](../deploiement/DEPLOIEMENT.md)

---

<div align="center">

[← Retour à la Documentation](../DOCUMENTATION.md) • [API i18n →](../api/I18N.md)

</div>
