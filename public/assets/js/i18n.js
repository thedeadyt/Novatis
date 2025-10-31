/**
 * Configuration i18next pour Novatis
 * Gestion de l'internationalisation (i18n) pour français et anglais
 */

(function() {
  'use strict';

  // Configuration i18next
  const initI18n = async () => {
    // Vérifier si i18next est chargé
    if (typeof i18next === 'undefined') {
      console.error('i18next n\'est pas chargé. Assurez-vous d\'inclure la librairie.');
      return;
    }

    // Langues supportées (Français et Anglais uniquement)
    const supportedLanguages = ['fr', 'en'];

    // Récupérer la langue depuis localStorage ou la préférence utilisateur (BDD)
    const savedLanguage = localStorage.getItem('novatis_language');

    // Récupérer la langue de la page si définie côté serveur (via PHP)
    const serverLanguage = document.documentElement.getAttribute('data-user-lang');

    // Détecter la langue du navigateur
    const browserLanguage = navigator.language.split('-')[0];

    // Ordre de priorité : localStorage > serveur > navigateur > défaut (fr)
    const fallbackLanguage = savedLanguage || serverLanguage ||
                             (supportedLanguages.includes(browserLanguage) ? browserLanguage : 'fr');

    try {
      await i18next
        .use(i18nextHttpBackend)
        .use(i18nextBrowserLanguageDetector)
        .init({
          lng: fallbackLanguage,
          fallbackLng: 'fr',
          supportedLngs: supportedLanguages,
          debug: false, // Mettre à true pour le debug

          // Configuration du backend (chargement des fichiers JSON)
          backend: {
            loadPath: '/Novatis/public/locales/{{lng}}/{{ns}}.json',
          },

          // Namespaces (fichiers de traduction)
          ns: ['common', 'settings', 'auth', 'dashboard', 'pages'],
          defaultNS: 'common',

          // Détection de la langue
          detection: {
            order: ['localStorage', 'navigator', 'htmlTag'],
            caches: ['localStorage'],
            lookupLocalStorage: 'novatis_language',
          },

          // Interpolation
          interpolation: {
            escapeValue: false, // React échappe déjà les valeurs
          },

          // Comportement de chargement
          load: 'languageOnly', // Ignorer les codes de région (fr-FR -> fr)
        });

      console.log('✅ i18next initialisé avec la langue:', i18next.language);

      // Mettre à jour l'attribut lang de la page
      document.documentElement.setAttribute('lang', i18next.language);

      // Émettre un événement personnalisé pour notifier que i18n est prêt
      window.dispatchEvent(new CustomEvent('i18nReady', {
        detail: { language: i18next.language }
      }));

      // Traduire les éléments avec data-i18n
      // Attendre que le DOM soit complètement chargé
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
          translateDataAttributes();
          // Re-traduire après des délais pour le contenu React
          setTimeout(translateDataAttributes, 100);
          setTimeout(translateDataAttributes, 300);
          setTimeout(translateDataAttributes, 500);
          setTimeout(translateDataAttributes, 1000);
          setTimeout(translateDataAttributes, 2000);
        });
      } else {
        translateDataAttributes();
        // Re-traduire après des délais pour le contenu React
        setTimeout(translateDataAttributes, 100);
        setTimeout(translateDataAttributes, 300);
        setTimeout(translateDataAttributes, 500);
        setTimeout(translateDataAttributes, 1000);
        setTimeout(translateDataAttributes, 2000);
      }

      // Observer les changements du DOM pour traduire le nouveau contenu
      const observer = new MutationObserver((mutations) => {
        const hasDataI18n = mutations.some(mutation =>
          Array.from(mutation.addedNodes).some(node =>
            node.nodeType === 1 && (node.hasAttribute('data-i18n') || node.querySelector('[data-i18n]'))
          )
        );

        if (hasDataI18n) {
          translateDataAttributes();
        }
      });

      // Observer le body pour les nouveaux éléments ajoutés
      if (document.body) {
        observer.observe(document.body, {
          childList: true,
          subtree: true
        });
      }

      // Observer les changements de langue
      i18next.on('languageChanged', (lng) => {
        console.log('🌍 Langue changée:', lng);
        document.documentElement.setAttribute('lang', lng);
        localStorage.setItem('novatis_language', lng);
        translateDataAttributes();

        // Sauvegarder dans la BDD si l'utilisateur est connecté
        saveLanguagePreference(lng);
      });

    } catch (error) {
      console.error('❌ Erreur lors de l\'initialisation de i18next:', error);
    }
  };

  /**
   * Traduire les éléments HTML avec l'attribut data-i18n
   * Exemple: <h1 data-i18n="settings.title">Paramètres</h1>
   */
  function translateDataAttributes() {
    const elements = document.querySelectorAll('[data-i18n]');
    console.log(`🔤 Traduction de ${elements.length} éléments avec data-i18n`);

    elements.forEach(element => {
      const key = element.getAttribute('data-i18n');
      const namespace = element.getAttribute('data-i18n-ns') || 'common';

      if (key && i18next.isInitialized) {
        const translation = i18next.t(key, { ns: namespace });

        // Vérifier si la traduction est valide (pas une clé)
        if (translation && translation !== key) {
          // Gérer les différents types d'éléments
          if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
            const attr = element.getAttribute('data-i18n-attr') || 'placeholder';
            element.setAttribute(attr, translation);
          } else {
            element.textContent = translation;
          }
        } else {
          console.warn(`⚠️ Traduction manquante pour: ${namespace}.${key}`);
        }
      }
    });
  }

  /**
   * Sauvegarder la préférence de langue dans la base de données
   */
  async function saveLanguagePreference(language) {
    try {
      const response = await fetch('/Novatis/public/api/parametres/settings.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update_language&language=${encodeURIComponent(language)}`
      });

      const data = await response.json();
      if (!data.success) {
        console.warn('⚠️ Impossible de sauvegarder la langue dans la BDD:', data.message);
      }
    } catch (error) {
      // Silencieux si l'utilisateur n'est pas connecté
      console.debug('La langue n\'a pas été sauvegardée (utilisateur non connecté?)');
    }
  }

  /**
   * Changer la langue
   * @param {string} language - Code de langue (fr, en)
   */
  window.changeLanguage = function(language) {
    if (typeof i18next !== 'undefined') {
      i18next.changeLanguage(language).then(() => {
        // Émettre un événement personnalisé pour notifier les composants React
        window.dispatchEvent(new CustomEvent('languageChanged', {
          detail: { language: language }
        }));

        // Re-traduire tous les éléments avec data-i18n
        translateDataAttributes();

        console.log('🌍 Langue changée en:', language);
      });
    }
  };

  /**
   * Obtenir la langue actuelle
   * @returns {string} Code de langue
   */
  window.getCurrentLanguage = function() {
    return i18next ? i18next.language : 'fr';
  };

  /**
   * Traduire une clé manuellement
   * @param {string} key - Clé de traduction
   * @param {string} namespace - Namespace (optionnel)
   * @returns {string} Texte traduit
   */
  window.t = function(key, namespace = 'common') {
    return i18next ? i18next.t(key, { ns: namespace }) : key;
  };

  // Initialiser i18next quand le DOM est prêt
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initI18n);
  } else {
    initI18n();
  }

  // Exposer i18next globalement pour React
  window.i18n = {
    t: (key, options) => i18next.t(key, options),
    changeLanguage: (lng) => i18next.changeLanguage(lng),
    language: () => i18next.language,
  };
})();
