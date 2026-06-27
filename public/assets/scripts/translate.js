/**
 * Script de traduction automatique avec DeepL API
 *
 * Usage: node translate.js
 *
 * Ce script traduit automatiquement les fichiers JSON français vers l'anglais et l'espagnol
 *
 * Pour utiliser DeepL:
 * 1. Créer un compte gratuit sur https://www.deepl.com/pro-api
 * 2. Récupérer votre clé API
 * 3. Créer un fichier .env avec: DEEPL_API_KEY=votre_clé_ici
 */

const fs = require('fs');
const path = require('path');
const https = require('https');

// Configuration
const DEEPL_API_KEY = process.env.DEEPL_API_KEY || 'VOTRE_CLE_DEEPL_ICI';
const DEEPL_API_URL = 'api-free.deepl.com'; // Utiliser 'api.deepl.com' pour la version Pro

const BASE_DIR = path.join(__dirname, '../public/locales');
const SOURCE_LANG = 'fr';
const TARGET_LANGS = ['en', 'es'];

/**
 * Appeler l'API DeepL pour traduire du texte
 */
function translateWithDeepL(text, targetLang) {
  return new Promise((resolve, reject) => {
    const postData = new URLSearchParams({
      auth_key: DEEPL_API_KEY,
      text: text,
      source_lang: SOURCE_LANG.toUpperCase(),
      target_lang: targetLang.toUpperCase(),
      preserve_formatting: '1',
      tag_handling: 'xml'
    }).toString();

    const options = {
      hostname: DEEPL_API_URL,
      path: '/v2/translate',
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'Content-Length': Buffer.byteLength(postData)
      }
    };

    const req = https.request(options, (res) => {
      let data = '';

      res.on('data', (chunk) => {
        data += chunk;
      });

      res.on('end', () => {
        try {
          const response = JSON.parse(data);
          if (response.translations && response.translations[0]) {
            resolve(response.translations[0].text);
          } else {
            reject(new Error('Format de réponse invalide'));
          }
        } catch (error) {
          reject(error);
        }
      });
    });

    req.on('error', (error) => {
      reject(error);
    });

    req.write(postData);
    req.end();
  });
}

/**
 * Traduire un objet JSON récursivement
 */
async function translateObject(obj, targetLang, depth = 0) {
  const result = {};
  const indent = '  '.repeat(depth);

  for (const [key, value] of Object.entries(obj)) {
    if (typeof value === 'object' && value !== null) {
      // Récursion pour les objets imbriqués
      console.log(`${indent}📁 ${key}`);
      result[key] = await translateObject(value, targetLang, depth + 1);
    } else if (typeof value === 'string') {
      // Traduire les chaînes de caractères
      try {
        console.log(`${indent}🔄 Traduction de "${key}": ${value.substring(0, 50)}...`);
        const translation = await translateWithDeepL(value, targetLang);
        result[key] = translation;
        console.log(`${indent}✅ → ${translation.substring(0, 50)}...`);

        // Pause pour éviter de surcharger l'API (limite: 1 requête/seconde pour le plan gratuit)
        await new Promise(resolve => setTimeout(resolve, 1000));
      } catch (error) {
        console.error(`${indent}❌ Erreur lors de la traduction de "${key}":`, error.message);
        result[key] = value; // Garder l'original en cas d'erreur
      }
    } else {
      result[key] = value;
    }
  }

  return result;
}

/**
 * Traduire un fichier JSON
 */
async function translateFile(filename, targetLang) {
  const sourcePath = path.join(BASE_DIR, SOURCE_LANG, filename);
  const targetPath = path.join(BASE_DIR, targetLang, filename);

  console.log(`\n📝 Traduction de ${filename} vers ${targetLang.toUpperCase()}...`);

  try {
    // Lire le fichier source
    const sourceContent = fs.readFileSync(sourcePath, 'utf8');
    const sourceData = JSON.parse(sourceContent);

    // Traduire le contenu
    const translatedData = await translateObject(sourceData, targetLang);

    // Créer le dossier de destination s'il n'existe pas
    const targetDir = path.dirname(targetPath);
    if (!fs.existsSync(targetDir)) {
      fs.mkdirSync(targetDir, { recursive: true });
    }

    // Écrire le fichier traduit
    fs.writeFileSync(targetPath, JSON.stringify(translatedData, null, 2), 'utf8');

    console.log(`✅ Fichier traduit: ${targetPath}`);
  } catch (error) {
    console.error(`❌ Erreur lors de la traduction de ${filename}:`, error.message);
  }
}

/**
 * Fonction principale
 */
async function main() {
  console.log('🌍 Démarrage de la traduction automatique avec DeepL...\n');

  // Vérifier la clé API
  if (DEEPL_API_KEY === 'VOTRE_CLE_DEEPL_ICI') {
    console.error('❌ Erreur: Veuillez configurer votre clé API DeepL');
    console.log('💡 Instructions:');
    console.log('   1. Créer un compte sur https://www.deepl.com/pro-api');
    console.log('   2. Récupérer votre clé API');
    console.log('   3. Définir la variable d\'environnement: DEEPL_API_KEY=votre_clé');
    console.log('   Ou modifier directement le script translate.js');
    process.exit(1);
  }

  // Lister les fichiers à traduire
  const sourceDir = path.join(BASE_DIR, SOURCE_LANG);
  const files = fs.readdirSync(sourceDir).filter(file => file.endsWith('.json'));

  console.log(`📚 Fichiers à traduire: ${files.join(', ')}\n`);

  // Traduire vers chaque langue cible
  for (const targetLang of TARGET_LANGS) {
    console.log(`\n🎯 === Traduction vers ${targetLang.toUpperCase()} ===`);

    for (const file of files) {
      await translateFile(file, targetLang);
    }
  }

  console.log('\n🎉 Traduction terminée !');
}

// Exécuter le script
if (require.main === module) {
  main().catch(error => {
    console.error('❌ Erreur fatale:', error);
    process.exit(1);
  });
}

module.exports = { translateWithDeepL, translateObject };
