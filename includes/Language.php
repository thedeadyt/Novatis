<?php
class Language {
    private static $translations = [];
    private static $currentLang = 'fr';
    private static $availableLanguages = [
        'fr' => 'Français',
        'en' => 'English',
        'es' => 'Español'
    ];

    /**
     * Initialise le système de langue
     */
    public static function init($pdo = null, $userId = null) {
        // Démarrer la session si pas déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Ordre de priorité : 1. BDD, 2. Session, 3. Cookie, 4. Navigateur, 5. Défaut (fr)
        $lang = 'fr';

        // 1. Récupérer depuis la BDD si utilisateur connecté
        if ($pdo && $userId) {
            try {
                $stmt = $pdo->prepare("SELECT language FROM user_preferences WHERE user_id = ?");
                $stmt->execute([$userId]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($result && !empty($result['language'])) {
                    $lang = $result['language'];
                }
            } catch (Exception $e) {
                // Ignorer l'erreur, utiliser les autres sources
            }
        }

        // 2. Session
        if (isset($_SESSION['language'])) {
            $lang = $_SESSION['language'];
        }

        // 3. Cookie
        elseif (isset($_COOKIE['language'])) {
            $lang = $_COOKIE['language'];
        }

        // 4. Langue du navigateur
        elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            if (array_key_exists($browserLang, self::$availableLanguages)) {
                $lang = $browserLang;
            }
        }

        self::setLanguage($lang);
    }

    /**
     * Définit la langue active
     */
    public static function setLanguage($lang) {
        if (!array_key_exists($lang, self::$availableLanguages)) {
            $lang = 'fr';
        }

        self::$currentLang = $lang;
        $_SESSION['language'] = $lang;

        // Cookie valide 30 jours
        setcookie('language', $lang, time() + (30 * 24 * 60 * 60), '/');

        // Charger les traductions
        $langFile = __DIR__ . "/lang/{$lang}.php";
        if (file_exists($langFile)) {
            self::$translations = include $langFile;
        }
    }

    /**
     * Récupère une traduction
     */
    public static function get($key, $default = null) {
        if (isset(self::$translations[$key])) {
            return self::$translations[$key];
        }
        return $default ?? $key;
    }

    /**
     * Alias court pour get()
     */
    public static function t($key, $default = null) {
        return self::get($key, $default);
    }

    /**
     * Récupère la langue actuelle
     */
    public static function getCurrentLanguage() {
        return self::$currentLang;
    }

    /**
     * Récupère toutes les langues disponibles
     */
    public static function getAvailableLanguages() {
        return self::$availableLanguages;
    }
}

// Fonction helper globale
if (!function_exists('__')) {
    function __($key, $default = null) {
        return Language::get($key, $default);
    }
}
?>
