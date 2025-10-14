# 🎨 Guide d'intégration du système de thème global

## Fichiers créés

### 1. **CSS Thème Global**
📁 `/public/assets/css/theme.css`
- Contient tous les styles pour le mode clair et sombre
- Applique automatiquement les couleurs en fonction de la classe `dark` sur `<html>`

### 2. **JavaScript Thème Global**
📁 `/public/assets/js/theme.js`
- Gère le passage entre mode clair et sombre
- Sauvegarde la préférence dans localStorage
- Détecte automatiquement la préférence système
- S'initialise automatiquement au chargement de la page

### 3. **Header commun**
📁 `/public/includes/header.php`
- Header réutilisable avec navigation
- Bouton de toggle du thème intégré
- Dropdown utilisateur avec profil/paramètres/déconnexion

### 4. **Footer commun**
📁 `/public/includes/footer.php`
- Footer réutilisable avec liens
- Réseaux sociaux
- Copyright

---

## 🚀 Comment intégrer le thème sur une page

### Méthode 1 : Utiliser le header/footer commun (RECOMMANDÉ)

```php
<?php
require_once __DIR__ . '/../../config/config.php';

// Vérifier si l'utilisateur est connecté (optionnel)
// isUserLoggedIn(true);

// Définir le titre de la page (optionnel)
$page_title = 'Ma Page - Novatis';

// Inclure le header
include __DIR__ . '/../includes/header.php';
?>

<!-- Votre contenu ici -->
<main class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-custom-black">Contenu de la page</h1>
    <p class="text-gray-600">Texte qui s'adaptera au thème</p>
</main>

<?php
// Inclure le footer
include __DIR__ . '/../includes/footer.php';
?>
```

**Avantages** :
- ✅ Header avec bouton de thème déjà intégré
- ✅ Navigation uniforme sur tout le site
- ✅ Pas besoin de gérer manuellement les scripts

---

### Méthode 2 : Intégration manuelle

Si vous ne pouvez pas utiliser le header commun, ajoutez ces lignes dans le `<head>` :

```html
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ma Page</title>

    <!-- Variables CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/variables.css">

    <!-- ⭐ THÈME CSS (OBLIGATOIRE) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            darkMode: 'class', // ⭐ IMPORTANT : activer le mode dark par classe
            theme: {
                extend: {
                    colors: {
                        'custom-bg': '#e8e8e8',
                        'custom-white': '#ffffff',
                        'custom-black': '#1f2020',
                        'custom-red': '#B41200',
                        'accent-1': '#1f2020',
                        'accent-2': '#7F0D00',
                        'hover-1': '#464646',
                        'hover-2': '#E04830'
                    }
                }
            }
        }
    </script>

    <!-- ⭐ SCRIPT THÈME (OBLIGATOIRE) -->
    <script src="<?= BASE_URL ?>/assets/js/theme.js"></script>
</head>
<body>
    <!-- Votre contenu -->
</body>
</html>
```

### Ajouter un bouton de toggle manuel

```html
<!-- Bouton simple -->
<button class="theme-toggle-btn" data-theme-toggle="true" aria-label="Changer le thème">
    <i class="fas fa-moon"></i> Thème
</button>

<!-- Ou un checkbox toggle -->
<label class="toggle-switch">
    <input type="checkbox" id="darkModeToggle" class="theme-toggle-checkbox">
    <span class="slider"></span>
</label>
```

Le script `theme.js` détectera automatiquement ces éléments et les rendra fonctionnels.

---

## 🎯 Classes CSS à utiliser

### Textes
```html
<!-- Texte principal (s'adapte automatiquement) -->
<h1 class="text-custom-black">Titre</h1>
<p class="text-gray-600">Paragraphe</p>
<span class="text-gray-700">Texte secondaire</span>
```

### Backgrounds
```html
<!-- Backgrounds (s'adaptent automatiquement) -->
<div class="bg-white">Carte blanche</div>
<div class="bg-gray-50">Fond gris clair</div>
<div class="bg-custom-bg">Fond de la page</div>
```

### Bordures
```html
<!-- Bordures (s'adaptent automatiquement) -->
<div class="border border-gray-200">Avec bordure</div>
<div class="border-t border-gray-300">Bordure supérieure</div>
```

### Formulaires
```html
<!-- Inputs (s'adaptent automatiquement) -->
<input class="form-input" type="text" placeholder="Votre texte">
<select class="form-input">
    <option>Option 1</option>
</select>
<textarea class="form-input"></textarea>
```

---

## 📚 API JavaScript

### Méthodes disponibles

```javascript
// Initialiser le thème (déjà fait automatiquement)
ThemeManager.init();

// Basculer entre clair et sombre
ThemeManager.toggle();

// Définir un thème spécifique
ThemeManager.setTheme('dark');  // ou 'light'

// Obtenir le thème actuel
const theme = ThemeManager.getCurrentTheme(); // 'dark' ou 'light'

// Vérifier si mode sombre
const isDark = ThemeManager.isDark(); // true ou false

// Créer un bouton de toggle programmatiquement
ThemeManager.createToggleButton('.mon-conteneur', {
    className: 'mon-bouton-theme',
    showLabel: true,  // Afficher le texte
    position: 'append' // ou 'prepend'
});
```

### Écouter les changements de thème

```javascript
window.addEventListener('themeChanged', (event) => {
    const isDark = event.detail.isDark;
    console.log('Thème changé vers:', isDark ? 'sombre' : 'clair');

    // Faire des actions personnalisées
    if (isDark) {
        // Actions pour mode sombre
    } else {
        // Actions pour mode clair
    }
});
```

---

## ✅ Pages déjà mises à jour

- ✅ **Parametres.php** - Le toggle existant fonctionne avec le nouveau système
- ✅ **includes/header.php** - Header avec toggle intégré
- ✅ **includes/footer.php** - Footer commun

---

## 📋 TODO : Pages à mettre à jour

### Priorité HAUTE (pages principales)
- [ ] `Dashboard.php`
- [ ] `Autentification.php` (déjà React, vérifier l'intégration)
- [ ] `profil.php`
- [ ] `Prestataires.php`

### Priorité MOYENNE
- [ ] `Favoris.php`
- [ ] `notifications.php`
- [ ] `Contact.php`
- [ ] `Apropos.php`

### Priorité BASSE
- [ ] `verify-email.php`
- [ ] `delete-account.php`
- [ ] `logout.php`

---

## 🔧 Personnalisation

### Modifier les couleurs du mode sombre

Éditez `/public/assets/css/theme.css` et modifiez les couleurs dans la section `html.dark` :

```css
html.dark {
    background-color: #0f172a;  /* Bleu très foncé */
    color: #f1f5f9;              /* Blanc cassé */
}

/* Modifier une couleur spécifique */
html.dark .bg-white {
    background-color: #1e293b !important;  /* Bleu-gris foncé */
}
```

### Ajouter des styles personnalisés

```css
/* Dans votre fichier CSS ou dans un <style> */
html.dark .ma-classe-custom {
    background-color: #ma-couleur-dark;
    color: #autre-couleur;
}
```

---

## 🐛 Debugging

### Le thème ne s'applique pas ?

1. **Vérifier que le CSS est bien chargé** :
   ```html
   <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
   ```

2. **Vérifier que le JS est bien chargé** :
   ```html
   <script src="<?= BASE_URL ?>/assets/js/theme.js"></script>
   ```

3. **Vérifier dans la console** :
   ```javascript
   console.log(localStorage.getItem('novatis_theme')); // Devrait afficher 'dark' ou 'light'
   console.log(document.documentElement.classList.contains('dark')); // true si mode sombre
   ```

4. **Vérifier la classe sur `<html>`** :
   Inspectez l'élément `<html>` → devrait avoir la classe `dark` si mode sombre actif

### Le toggle ne fonctionne pas ?

1. Vérifier que l'élément a bien l'attribut :
   - `id="darkModeToggle"` pour les checkboxes
   - `data-theme-toggle="true"` pour les boutons
   - classe `theme-toggle-checkbox` pour les checkboxes personnalisées

2. Vérifier dans la console :
   ```javascript
   console.log(typeof ThemeManager); // Devrait afficher 'object'
   ```

---

## 💡 Bonnes pratiques

1. **Toujours utiliser les classes utilitaires** : `text-gray-600`, `bg-white`, etc.
2. **Éviter les couleurs en dur** : Utiliser `text-custom-black` au lieu de `text-black`
3. **Tester les deux modes** : Vérifier que tout est lisible en mode clair ET sombre
4. **Utiliser le header commun** quand possible pour une expérience uniforme

---

## 🚀 Prochaines étapes

1. Mettre à jour toutes les pages listées dans "TODO"
2. Tester chaque page en mode clair et sombre
3. Ajuster les couleurs si nécessaire
4. Documenter les cas spéciaux

---

**Besoin d'aide ?** Consultez les fichiers suivants pour des exemples :
- `/public/includes/header.php` - Exemple d'intégration complète
- `/public/pages/Parametres.php` - Exemple de toggle dans une page
- `/public/assets/js/theme.js` - Documentation de l'API JavaScript
