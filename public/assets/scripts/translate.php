<?php
/**
 * Script de traduction automatique avec DeepL API (version PHP)
 *
 * Usage: php translate.php
 *
 * Alternative au script Node.js pour ceux qui préfèrent PHP
 */

// Configuration
define('DEEPL_API_KEY', 'VOTRE_CLE_DEEPL_ICI'); // Remplacer par votre clé API
define('DEEPL_API_URL', 'https://api-free.deepl.com/v2/translate'); // Utiliser 'api.deepl.com' pour la version Pro

define('BASE_DIR', __DIR__ . '/../public/locales');
define('SOURCE_LANG', 'FR');
$TARGET_LANGS = ['EN', 'ES'];

/**
 * Appeler l'API DeepL pour traduire du texte
 */
function translateWithDeepL($text, $targetLang) {
    $postData = [
        'auth_key' => DEEPL_API_KEY,
        'text' => $text,
        'source_lang' => SOURCE_LANG,
        'target_lang' => $targetLang,
        'preserve_formatting' => '1',
        'tag_handling' => 'xml'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, DEEPL_API_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        throw new Exception('Erreur cURL: ' . curl_error($ch));
    }

    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception("Erreur API DeepL (code $httpCode): $response");
    }

    $data = json_decode($response, true);

    if (!isset($data['translations'][0]['text'])) {
        throw new Exception('Format de réponse invalide');
    }

    return $data['translations'][0]['text'];
}

/**
 * Traduire un objet JSON récursivement
 */
function translateObject($obj, $targetLang, $depth = 0) {
    $result = [];
    $indent = str_repeat('  ', $depth);

    foreach ($obj as $key => $value) {
        if (is_array($value) || is_object($value)) {
            // Récursion pour les objets imbriqués
            echo "{$indent}📁 $key\n";
            $result[$key] = translateObject($value, $targetLang, $depth + 1);
        } elseif (is_string($value)) {
            // Traduire les chaînes de caractères
            try {
                $preview = mb_substr($value, 0, 50);
                echo "{$indent}🔄 Traduction de \"$key\": $preview...\n";

                $translation = translateWithDeepL($value, $targetLang);
                $result[$key] = $translation;

                $translationPreview = mb_substr($translation, 0, 50);
                echo "{$indent}✅ → $translationPreview...\n";

                // Pause pour éviter de surcharger l'API
                sleep(1);
            } catch (Exception $e) {
                echo "{$indent}❌ Erreur: " . $e->getMessage() . "\n";
                $result[$key] = $value; // Garder l'original en cas d'erreur
            }
        } else {
            $result[$key] = $value;
        }
    }

    return $result;
}

/**
 * Traduire un fichier JSON
 */
function translateFile($filename, $targetLang) {
    $sourcePath = BASE_DIR . '/fr/' . $filename;
    $targetPath = BASE_DIR . '/' . strtolower($targetLang) . '/' . $filename;

    echo "\n📝 Traduction de $filename vers $targetLang...\n";

    try {
        // Lire le fichier source
        $sourceContent = file_get_contents($sourcePath);
        $sourceData = json_decode($sourceContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Erreur de parsing JSON: ' . json_last_error_msg());
        }

        // Traduire le contenu
        $translatedData = translateObject($sourceData, $targetLang);

        // Créer le dossier de destination s'il n'existe pas
        $targetDir = dirname($targetPath);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Écrire le fichier traduit
        file_put_contents(
            $targetPath,
            json_encode($translatedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        echo "✅ Fichier traduit: $targetPath\n";
    } catch (Exception $e) {
        echo "❌ Erreur lors de la traduction de $filename: " . $e->getMessage() . "\n";
    }
}

/**
 * Fonction principale
 */
function main() {
    global $TARGET_LANGS;

    echo "🌍 Démarrage de la traduction automatique avec DeepL...\n\n";

    // Vérifier la clé API
    if (DEEPL_API_KEY === 'VOTRE_CLE_DEEPL_ICI') {
        echo "❌ Erreur: Veuillez configurer votre clé API DeepL\n";
        echo "💡 Instructions:\n";
        echo "   1. Créer un compte sur https://www.deepl.com/pro-api\n";
        echo "   2. Récupérer votre clé API\n";
        echo "   3. Modifier la constante DEEPL_API_KEY dans ce script\n";
        exit(1);
    }

    // Lister les fichiers à traduire
    $sourceDir = BASE_DIR . '/fr';
    $files = array_filter(
        scandir($sourceDir),
        function($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'json';
        }
    );

    echo "📚 Fichiers à traduire: " . implode(', ', $files) . "\n";

    // Traduire vers chaque langue cible
    foreach ($TARGET_LANGS as $targetLang) {
        echo "\n🎯 === Traduction vers $targetLang ===\n";

        foreach ($files as $file) {
            translateFile($file, $targetLang);
        }
    }

    echo "\n🎉 Traduction terminée !\n";
}

// Exécuter le script
main();
