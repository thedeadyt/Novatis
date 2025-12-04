<div id="footer-root"></div>

<script type="text/babel">
  function Footer() {
    const BASE_URL = <?= json_encode(BASE_URL) ?>;
    const [, forceUpdate] = React.useReducer(x => x + 1, 0);

    React.useEffect(() => {
      const handleLanguageChanged = () => forceUpdate();
      window.addEventListener('i18nReady', handleLanguageChanged);
      window.addEventListener('languageChanged', handleLanguageChanged);

      return () => {
        window.removeEventListener('i18nReady', handleLanguageChanged);
        window.removeEventListener('languageChanged', handleLanguageChanged);
      };
    }, []);

    const t = (key) => {
      if (typeof window.t === 'function') {
        return window.t(`footer.${key}`, 'common') || key;
      }
      return key;
    };

    const links = [
      { name: t('home'), href: `${BASE_URL}` },
      { name: t('providers'), href: `${BASE_URL}/Prestataires` },
      { name: t('contact'), href: `${BASE_URL}/Contact` },
      { name: t('about'), href: `${BASE_URL}/Apropos` },
    ];

    const legalLinks = [
      { name: t('legal'), href: "#", disabled: true },
      { name: t('privacy'), href: "#", disabled: true },
    ];

    return (
      <footer className="bg-gray-900 text-gray-200 mt-2">
        <div className="max-w-7xl mx-auto px-6 py-6 grid md:grid-cols-3 gap-6">

          {/* Logo + description */}
          <div>
            <a href={BASE_URL} className="flex items-center space-x-2 mb-4">
              <img src={`${BASE_URL}/assets/img/logos/Logo_Novatis_nobg_white.png`} alt="Logo" className="w-12 h-12 rounded-full" />
              <span className="text-xl font-bold">Novatis</span>
            </a>
            <p className="text-sm text-gray-400">
              {t('description')}
            </p>
          </div>

          {/* Liens rapides centrés */}
          <div className="flex flex-col items-center">
            <h3 className="font-semibold mb-3">{t('navigation')}</h3>
            <ul className="flex flex-col items-center space-y-2">
              {links.map((link, i) => (
                <li key={i}>
                  <a href={link.href} className={`transition-colors ${link.disabled ? 'text-gray-500 cursor-not-allowed' : 'hover:text-white'}`}>
                    {link.name}
                  </a>
                </li>
              ))}
            </ul>
          </div>

          {/* Liens légaux */}
          <div className="flex flex-col items-center">
            <h3 className="font-semibold mb-3">{t('legalTitle')}</h3>
            <ul className="flex flex-col items-center space-y-2">
              {legalLinks.map((link, i) => (
                <li key={i}>
                  <a href={link.href} className={`transition-colors ${link.disabled ? 'text-gray-500 cursor-not-allowed' : 'hover:text-white'}`}>
                    {link.name}
                  </a>
                </li>
              ))}
            </ul>
          </div>
        </div>

        {/* Bas du footer */}
        <div className="border-t border-gray-700 py-3 text-center text-sm text-gray-400">
          © {new Date().getFullYear()} Novatis {t('rights')}
        </div>
      </footer>
    );
  }

  ReactDOM.createRoot(document.getElementById("footer-root")).render(<Footer />);
</script>
