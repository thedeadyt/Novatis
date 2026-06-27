# 🌙 Gestion du Thème Clair/Sombre

Documentation complète du système de gestion des thèmes clair et sombre.

---

## 📋 Vue d'ensemble

Le système de thème de Novatis offre une expérience utilisateur entièrement adaptée avec support des modes clair et sombre. L'interface s'ajuste automatiquement selon les préférences du navigateur et de l'utilisateur. Les prestataires et les éléments dynamiques s'adaptent au thème sélectionné. Le système inclut la détection automatique des préférences système et la sauvegarde des choix de l'utilisateur.

---

## ✨ Fonctionnalités

### 1. Sélection de Thème

**Options disponibles :**
- Mode clair (light)
- Mode sombre (dark)
- Automatique (auto - selon le système)

**Stockage :**
- LocalStorage du navigateur
- Base de données utilisateur (si connecté)
- Préférence système (fallback)

**Code exemple :**
```javascript
// Récupérer le thème actuel
const getCurrentTheme = () => {
  return localStorage.getItem('theme') || 'auto';
};

// Changer le thème
const setTheme = (theme) => {
  localStorage.setItem('theme', theme);

  const response = await fetch(`${BASE_URL}/api/theme/set.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ theme: theme })
  });

  applyTheme(theme);
};

// Appliquer le thème
const applyTheme = (theme) => {
  const html = document.documentElement;

  if (theme === 'auto') {
    theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }

  html.setAttribute('data-theme', theme);
  document.body.classList.remove('light-mode', 'dark-mode');
  document.body.classList.add(`${theme}-mode`);
};
```

### 2. Détection Automatique

**Préférence Système :**
- Lecture de `prefers-color-scheme`
- Support du mode sombre système
- Suivi des changements système en temps réel

**Hiérarchie :**
1. Préférence utilisateur stockée
2. Préférence système
3. Mode clair (par défaut)

**Code exemple :**
```javascript
// Écouter les changements du système
const darkModeQuery = window.matchMedia('(prefers-color-scheme: dark)');

darkModeQuery.addEventListener('change', (e) => {
  if (localStorage.getItem('theme') === 'auto') {
    applyTheme('auto');
  }
});
```

### 3. Variables CSS

**Système de couleurs :**
- Variables CSS pour chaque couleur
- Définition par thème
- Réutilisabilité et maintenabilité

**Exemple :**
```css
:root[data-theme="light"] {
  --color-primary: #007bff;
  --color-secondary: #6c757d;
  --color-bg: #ffffff;
  --color-bg-secondary: #f8f9fa;
  --color-text: #212529;
  --color-text-secondary: #6c757d;
  --color-border: #dee2e6;
  --color-shadow: rgba(0, 0, 0, 0.1);
}

:root[data-theme="dark"] {
  --color-primary: #0d6efd;
  --color-secondary: #adb5bd;
  --color-bg: #212529;
  --color-bg-secondary: #343a40;
  --color-text: #f8f9fa;
  --color-text-secondary: #adb5bd;
  --color-border: #495057;
  --color-shadow: rgba(0, 0, 0, 0.3);
}
```

### 4. Persistance du Thème

**LocalStorage :**
- Clé : `theme`
- Valeurs : `light`, `dark`, `auto`
- Persiste lors de la fermeture du navigateur

**Base de Données :**
- Enregistrement pour les utilisateurs connectés
- Consultation lors de la connexion
- Mise à jour lors du changement

**Code exemple :**
```javascript
// Charger le thème au démarrage
const loadTheme = () => {
  let theme = localStorage.getItem('theme');

  if (!theme && currentUser) {
    // Récupérer depuis la base de données
    fetch(`${BASE_URL}/api/user/theme.php`)
      .then(r => r.json())
      .then(data => {
        theme = data.theme || 'auto';
        localStorage.setItem('theme', theme);
        applyTheme(theme);
      });
  } else {
    applyTheme(theme || 'auto');
  }
};

// Appeler au chargement
document.addEventListener('DOMContentLoaded', loadTheme);
```

### 5. Transitions Fluides

**Animations :**
- Transition de couleur en 0.3s
- Pas de scintillement
- Fluidité optimale

```css
* {
  transition: background-color 0.3s ease,
              color 0.3s ease,
              border-color 0.3s ease,
              box-shadow 0.3s ease;
}

html[data-theme="dark"] * {
  transition: background-color 0.3s ease,
              color 0.3s ease;
}
```

### 6. Images et Médias

**Adaptation :**
- Images légères pour mode clair
- Images sombres pour mode sombre
- Logos adaptés au thème
- SVG avec couleurs variables

**Exemple :**
```html
<!-- Image adaptative -->
<img
  src="/assets/logo-light.png"
  alt="Logo"
  class="theme-adaptive"
  data-light-src="/assets/logo-light.png"
  data-dark-src="/assets/logo-dark.png"
>

<!-- SVG adaptatif -->
<svg class="theme-adaptive">
  <path fill="var(--color-primary)" d="..."/>
</svg>
```

```javascript
// Gérer les images adaptatives
document.querySelectorAll('.theme-adaptive').forEach(img => {
  const theme = document.documentElement.getAttribute('data-theme');
  const actualTheme = theme === 'auto'
    ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
    : theme;

  img.src = img.dataset[`${actualTheme}Src`];
});
```

### 7. Sélecteur de Thème

**Interface :**
- Bouton en haut à droite
- Dropdown avec 3 options
- Icônes : soleil, lune, auto
- Thème actuel indiqué

**Composant :**
```html
<div class="theme-switcher">
  <button class="theme-btn" id="themeBtn">
    <span class="theme-icon">🌙</span>
  </button>

  <div class="theme-menu" id="themeMenu">
    <button data-theme="light" class="theme-option">
      <span>☀️</span> Clair
    </button>
    <button data-theme="dark" class="theme-option">
      <span>🌙</span> Sombre
    </button>
    <button data-theme="auto" class="theme-option">
      <span>⚙️</span> Automatique
    </button>
  </div>
</div>
```

---

## 🎨 Implémentation Visuelle

### Éléments Affectés

**Fond :**
- Arrière-plan principal
- Arrière-plan secondaire
- Couleur du texte

**Composants :**
- Boutons
- Cartes (cards)
- Formulaires
- Modales
- Toasts

**Bordures :**
- Séparateurs
- Contours des champs
- Ombres

### Contraste et Accessibilité

**Ratios WCAG :**
- Texte normal : 4.5:1
- Texte large : 3:1
- Mode sombre optimisé pour réduire la fatigue oculaire

---

## 📡 API

Les endpoints API de thème sont documentés dans [API Thème](../api/THEME.md).

**Endpoints principaux :**
- `GET /api/theme/get.php` - Récupérer le thème
- `POST /api/theme/set.php` - Définir le thème
- `GET /api/theme/preferences.php` - Préférences du système
- `GET /api/theme/colors.php` - Palette de couleurs

---

## 🐛 Dépannage

### Problèmes Courants

#### 1. Le thème ne change pas

**Causes possibles :**
- JavaScript désactivé
- LocalStorage désactivé
- Cache du navigateur
- Service Worker en cache

**Solutions :**
```bash
# Vider le cache
curl -X POST http://localhost/Novatis/public/api/cache/clear.php

# Forcer le rechargement
Ctrl+Shift+R (Windows/Linux)
Cmd+Shift+R (Mac)
```

#### 2. Scintillement au chargement

**Causes :**
- CSS du thème chargé après le contenu
- Délai de détection du thème

**Solutions :**
- Charger le thème dans `<head>` inline
- Pré-charger les CSS du thème
- Masquer le contenu jusqu'au chargement

```html
<head>
  <script>
    // Charger le thème avant le rendu
    (function() {
      const theme = localStorage.getItem('theme') || 'auto';
      if (theme === 'auto') {
        const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
      } else {
        document.documentElement.setAttribute('data-theme', theme);
      }
    })();
  </script>
</head>
```

#### 3. Images non adaptées au thème

**Solutions :**
- Utiliser des SVG avec variables CSS
- Implémenter des images adaptatives
- Utiliser le mode sombre des images

#### 4. Mauvais contraste en mode sombre

**Vérifications :**
- Ratio de contraste des couleurs
- Teste d'accessibilité
- Test avec un lecteur d'écran

```bash
# Analyser l'accessibilité
npm install -g axe-cli
axe http://localhost/Novatis/
```

#### 5. Les composants tiers ne s'adaptent pas

**Solutions :**
- Forcer les variables CSS
- Ajouter des règles CSS spécifiques
- Contacter le mainteneur du composant

```css
/* Forcer l'adaptation */
[data-theme="dark"] .external-component {
  background-color: var(--color-bg-secondary);
  color: var(--color-text);
  border-color: var(--color-border);
}
```

---

## 📚 Ressources

- [Documentation API Thème](../api/THEME.md)
- [Configuration des Paramètres](PARAMETRES.md)
- [Guide de Style](../guides/DESIGN.md)
- [Guide de Déploiement](../deploiement/DEPLOIEMENT.md)

---

<div align="center">

[← Retour à la Documentation](../DOCUMENTATION.md) • [API Thème →](../api/THEME.md)

</div>
