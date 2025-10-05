# Système Multi-Langue - Novatis

## Vue d'ensemble

Le système de langue permet aux utilisateurs de basculer entre le français, l'anglais et l'espagnol. Les préférences sont sauvegardées en base de données et restaurées automatiquement à chaque connexion.

## Architecture

### 1. Fichiers de langue

Les traductions sont stockées dans `includes/lang/` :
- `fr.php` - Français (langue par défaut)
- `en.php` - English
- `es.php` - Español

Chaque fichier retourne un tableau associatif avec des clés de traduction :

```php
<?php
return [
    'settings_title' => 'Paramètres',  // FR
    'settings_title' => 'Settings',    // EN
    'settings_title' => 'Configuración', // ES
];
?>
```

### 2. Classe Language

La classe `Language` (`includes/Language.php`) gère :
- **Initialisation** : Détection de la langue préférée
- **Chargement** : Import des fichiers de traduction
- **Récupération** : Méthode `get()` pour obtenir les traductions
- **Changement** : Méthode `setLanguage()` pour basculer de langue

#### Priorité de détection de langue

1. **Base de données** (table `user_preferences`)
2. **Session** (`$_SESSION['language']`)
3. **Cookie** (`language`)
4. **Navigateur** (header `Accept-Language`)
5. **Par défaut** : `fr`

### 3. Fonction helper

La fonction globale `__($key)` permet d'accéder facilement aux traductions :

```php
// Au lieu de :
echo Language::get('settings_title');

// On utilise :
echo __('settings_title');
```

## Utilisation

### Dans une page PHP

```php
<?php
require_once __DIR__ . '/../../config/config.php';

// La langue est automatiquement initialisée via config.php
?>

<h1><?= __('settings_title') ?></h1>
<p><?= __('settings_subtitle') ?></p>
```

### Ajouter une nouvelle traduction

1. Ajouter la clé dans `includes/lang/fr.php` :
```php
'mon_texte' => 'Ceci est mon texte',
```

2. Ajouter la traduction dans `includes/lang/en.php` :
```php
'mon_texte' => 'This is my text',
```

3. Ajouter la traduction dans `includes/lang/es.php` :
```php
'mon_texte' => 'Este es mi texto',
```

4. Utiliser dans le code :
```php
<p><?= __('mon_texte') ?></p>
```

## Changement de langue

### Via l'interface utilisateur

Les utilisateurs changent de langue dans **Paramètres > Affichage** :

1. Sélectionner la langue dans le menu déroulant
2. Le formulaire se soumet automatiquement (`onchange="this.form.submit()"`)
3. La langue est sauvegardée dans `user_preferences.language`
4. La session et le cookie sont mis à jour
5. La page se recharge avec la nouvelle langue

### Via code

```php
Language::setLanguage('en');
```

Cette méthode :
- Met à jour `$_SESSION['language']`
- Crée/met à jour le cookie `language`
- Recharge les traductions

## Base de données

### Table user_preferences

```sql
CREATE TABLE user_preferences (
    user_id INT NOT NULL,
    language VARCHAR(10) DEFAULT 'fr',
    -- autres colonnes...
    UNIQUE KEY unique_user (user_id)
);
```

### API settings.php

L'action `update_display` sauvegarde la langue :

```php
function updateDisplay($pdo, $user) {
    $language = $_POST['language'] ?? 'fr';

    // Validation
    $validLanguages = ['fr', 'en', 'es'];
    if (!in_array($language, $validLanguages)) {
        $language = 'fr';
    }

    // Sauvegarde en BDD
    $stmt = $pdo->prepare("
        INSERT INTO user_preferences (user_id, language)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE language = VALUES(language)
    ");
    $stmt->execute([$user['id'], $language]);

    // Mise à jour session
    Language::setLanguage($language);
}
```

## Pages traduites

Actuellement, les traductions sont implémentées sur :

### Parametres.php
- ✅ Navigation sidebar
- ✅ Section Affichage
- ✅ Section Intégrations
- ✅ Header (Connecté en tant que, Déconnexion)

### Fichiers de langue complets

Tous les fichiers de langue contiennent des clés pour :
- Navigation
- Dashboard
- Messages
- Paramètres (toutes sections)
- Profil
- Sécurité
- Notifications
- Confidentialité
- Affichage
- Intégrations
- Termes communs

## Ajouter des traductions aux autres pages

Pour traduire une page existante :

1. **Identifier les textes** à traduire
2. **Ajouter les clés** dans les 3 fichiers de langue
3. **Remplacer le texte** par `<?= __('cle') ?>`

**Exemple** :

Avant :
```php
<h1>Bienvenue sur Novatis</h1>
```

Après :
```php
<h1><?= __('home_welcome') ?></h1>
```

Avec dans les fichiers de langue :
```php
// fr.php
'home_welcome' => 'Bienvenue sur Novatis',

// en.php
'home_welcome' => 'Welcome to Novatis',

// es.php
'home_welcome' => 'Bienvenido a Novatis',
```

## Langues disponibles

| Code | Langue | Drapeau |
|------|--------|---------|
| `fr` | Français | 🇫🇷 |
| `en` | English | 🇬🇧 |
| `es` | Español | 🇪🇸 |

## Ajouter une nouvelle langue

1. Créer `includes/lang/de.php` (exemple : allemand)
2. Copier la structure de `fr.php`
3. Traduire toutes les valeurs
4. Ajouter dans `Language.php` :
```php
private static $availableLanguages = [
    'fr' => 'Français',
    'en' => 'English',
    'es' => 'Español',
    'de' => 'Deutsch',  // Nouvelle langue
];
```
5. Mettre à jour `settings.php` validation :
```php
$validLanguages = ['fr', 'en', 'es', 'de'];
```
6. Ajouter dans Parametres.php :
```html
<option value="de" <?= $preferences['language'] === 'de' ? 'selected' : '' ?>>🇩🇪 Deutsch</option>
```

## Tests

### Test en ligne de commande

```bash
php -r "
require_once 'includes/Language.php';
Language::init(null, null);

echo 'FR: ' . __('settings_title') . PHP_EOL;

Language::setLanguage('en');
echo 'EN: ' . __('settings_title') . PHP_EOL;

Language::setLanguage('es');
echo 'ES: ' . __('settings_title') . PHP_EOL;
"
```

Résultat attendu :
```
FR: Paramètres
EN: Settings
ES: Configuración
```

### Test navigateur

1. Se connecter à Novatis
2. Aller dans **Paramètres > Affichage**
3. Changer la langue dans le menu déroulant
4. Vérifier que les textes changent immédiatement
5. Rafraîchir la page - la langue doit persister
6. Se déconnecter et reconnecter - la langue doit être conservée

## Notes techniques

- **Performance** : Les traductions sont chargées une seule fois en mémoire
- **Fallback** : Si une clé n'existe pas, la clé elle-même est retournée
- **Session** : La langue est stockée dans `$_SESSION['language']`
- **Cookie** : Un cookie `language` est créé pour 30 jours
- **Initialisation** : `config.php` initialise automatiquement le système

## Bonnes pratiques

1. ✅ Utiliser des clés descriptives : `settings_title` plutôt que `st`
2. ✅ Grouper par section : `profile_*`, `security_*`, `notif_*`
3. ✅ Traduire TOUTES les langues en même temps
4. ✅ Tester après chaque ajout
5. ❌ Ne pas mettre de HTML dans les traductions
6. ❌ Ne pas mettre de variables dans les clés

## Dépannage

### La langue ne change pas
- Vérifier que `config.php` est bien inclus
- Vérifier que la session est démarrée
- Vérifier les permissions sur le dossier `includes/lang/`

### Traductions manquantes
- Vérifier que la clé existe dans les 3 fichiers
- Vérifier la syntaxe PHP (`,` entre les éléments)
- Vérifier que le fichier se termine par `?>`

### Erreur "Class 'Language' not found"
- Vérifier que `config.php` est inclus
- Vérifier le chemin vers `Language.php`

## Prochaines étapes

Pour compléter la traduction du site :

1. Dashboard.php
2. Header.php (navigation)
3. Messages (interface)
4. Profil.php
5. Services (listings et détails)
6. Page d'accueil
7. Authentification

Le système est prêt, il suffit d'ajouter `<?= __('cle') ?>` progressivement !
