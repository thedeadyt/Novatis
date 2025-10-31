/**
 * Composant React pour le sélecteur de langue
 * Affiche des drapeaux cliquables pour changer la langue
 */

// Éviter les déclarations multiples si le script est chargé plusieurs fois
if (typeof window.LanguageSwitcher === 'undefined') {

  // Ce composant doit être utilisé dans un contexte React avec i18next
  const LanguageSwitcher = () => {
  const [currentLang, setCurrentLang] = React.useState(window.getCurrentLanguage ? window.getCurrentLanguage() : 'fr');
  const [isOpen, setIsOpen] = React.useState(false);
  const dropdownRef = React.useRef(null);

  // Fermer le dropdown si on clique en dehors
  React.useEffect(() => {
    const handleClickOutside = (event) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setIsOpen(false);
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  // Écouter les changements de langue
  React.useEffect(() => {
    const handleLanguageChanged = () => {
      setCurrentLang(window.getCurrentLanguage ? window.getCurrentLanguage() : 'fr');
    };

    window.addEventListener('i18nReady', handleLanguageChanged);
    window.addEventListener('languageChanged', handleLanguageChanged);
    return () => {
      window.removeEventListener('i18nReady', handleLanguageChanged);
      window.removeEventListener('languageChanged', handleLanguageChanged);
    };
  }, []);

  const languages = [
    {
      code: 'fr',
      name: 'Français',
      flag: '🇫🇷',
      emoji: 'FR'
    },
    {
      code: 'en',
      name: 'English',
      flag: '🇬🇧',
      emoji: 'EN'
    }
  ];

  const handleLanguageChange = (langCode) => {
    if (window.changeLanguage) {
      window.changeLanguage(langCode);
      setCurrentLang(langCode);
      setIsOpen(false);
    }
  };

  const currentLanguage = languages.find(lang => lang.code === currentLang) || languages[0];

  return React.createElement(
    'div',
    { className: 'relative', ref: dropdownRef },

    // Bouton principal
    React.createElement(
      'button',
      {
        onClick: () => setIsOpen(!isOpen),
        className: 'flex items-center space-x-2 px-3 py-2 hover:bg-gray-100 rounded-lg transition-colors focus:outline-none',
        'aria-label': 'Changer de langue',
        title: 'Changer de langue'
      },
      React.createElement(
        'span',
        { className: 'text-xl' },
        currentLanguage.flag
      ),
      React.createElement(
        'span',
        { className: 'hidden md:inline text-sm font-medium text-gray-700' },
        currentLanguage.emoji
      ),
      React.createElement(
        'svg',
        {
          className: `w-4 h-4 text-gray-500 transition-transform ${isOpen ? 'rotate-180' : ''}`,
          fill: 'none',
          stroke: 'currentColor',
          viewBox: '0 0 24 24'
        },
        React.createElement('path', {
          strokeLinecap: 'round',
          strokeLinejoin: 'round',
          strokeWidth: '2',
          d: 'M19 9l-7 7-7-7'
        })
      )
    ),

    // Dropdown des langues
    isOpen && React.createElement(
      'div',
      {
        className: 'absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-50 overflow-hidden'
      },
      languages.map(lang =>
        React.createElement(
          'button',
          {
            key: lang.code,
            onClick: () => handleLanguageChange(lang.code),
            className: `w-full flex items-center space-x-3 px-4 py-3 hover:bg-gray-50 transition-colors ${
              lang.code === currentLang ? 'bg-blue-50' : ''
            }`,
          },
          React.createElement(
            'span',
            { className: 'text-2xl' },
            lang.flag
          ),
          React.createElement(
            'div',
            { className: 'flex-1 text-left' },
            React.createElement(
              'div',
              { className: `font-medium ${lang.code === currentLang ? 'text-blue-600' : 'text-gray-900'}` },
              lang.name
            ),
            React.createElement(
              'div',
              { className: 'text-xs text-gray-500' },
              lang.code.toUpperCase()
            )
          ),
          lang.code === currentLang && React.createElement(
            'svg',
            {
              className: 'w-5 h-5 text-blue-600',
              fill: 'none',
              stroke: 'currentColor',
              viewBox: '0 0 24 24'
            },
            React.createElement('path', {
              strokeLinecap: 'round',
              strokeLinejoin: 'round',
              strokeWidth: '2',
              d: 'M5 13l4 4L19 7'
            })
          )
        )
      )
    )
  );
  };

  // Exposer le composant globalement pour utilisation dans d'autres scripts
  window.LanguageSwitcher = LanguageSwitcher;

  console.log('✅ LanguageSwitcher component loaded');
}
