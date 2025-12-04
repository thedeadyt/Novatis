<?php
/**
 * Helper pour l'internationalisation (i18n)
 * À inclure dans le <head> de toutes les pages pour activer i18next
 */

// Récupérer la langue de l'utilisateur
$user = getCurrentUser();
$userLang = 'fr'; // Par défaut

if ($user) {
    // Essayer de récupérer depuis la BDD
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT language FROM user_preferences WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $prefs = $stmt->fetch();
        if ($prefs && isset($prefs['language'])) {
            $userLang = $prefs['language'];
            $_SESSION['user_language'] = $userLang;
        }
    } catch (Exception $e) {
        // Erreur silencieuse
    }
}

// Fallback sur la session
if (isset($_SESSION['user_language'])) {
    $userLang = $_SESSION['user_language'];
}

// Valider la langue (seulement fr et en)
if (!in_array($userLang, ['fr', 'en'])) {
    $userLang = 'fr';
}
?>

<!-- i18next - Librairies CDN -->
<script src="https://unpkg.com/i18next@23.7.6/dist/umd/i18next.min.js"></script>
<script src="https://unpkg.com/i18next-http-backend@2.4.2/i18nextHttpBackend.min.js"></script>
<script src="https://unpkg.com/i18next-browser-languagedetector@7.2.0/i18nextBrowserLanguageDetector.min.js"></script>

<!-- Configuration i18n -->
<script src="<?= BASE_URL ?>/assets/js/i18n.js"></script>
<script src="<?= BASE_URL ?>/assets/js/LanguageSwitcher.js"></script>

<!-- Langue de l'utilisateur -->
<script>
    // Exposer la langue PHP vers JavaScript
    window.serverLanguage = '<?= $userLang ?>';
</script>
