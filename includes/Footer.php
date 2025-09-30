<div id="footer-root"></div>

<script type="text/babel">
  function Footer() {
    const links = [
      { name: "Accueil", href: "<?= BASE_URL ?>" },
      { name: "Prestataires", href: "#", disabled: true },
      { name: "Contact", href: "#", disabled: true },
      { name: "À propos", href: "#", disabled: true },
    ];

    const legalLinks = [
      { name: "Mentions légales", href: "#", disabled: true },
      { name: "Politique de confidentialité", href: "#", disabled: true },
    ];

    return (
      <footer className="bg-gray-900 text-gray-200 mt-2">
        <div className="max-w-7xl mx-auto px-6 py-6 grid md:grid-cols-3 gap-6">
          
          {/* Logo + description */}
          <div>
            <a href="<?= BASE_URL ?>" className="flex items-center space-x-2 mb-4">
              <img src="<?= BASE_URL ?>/asset/img/logo.svg" alt="Logo" className="w-12 h-12 rounded-full" />
              <span className="text-xl font-bold">Novatis</span>
            </a>
            <p className="text-sm text-gray-400">
              Plateforme de mise en relation entre les étudiants pour partager leurs services et leurs projets.
            </p>
          </div>

          {/* Liens rapides centrés */}
          <div className="flex flex-col items-center">
            <h3 className="font-semibold mb-3">Navigation</h3>
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
            <h3 className="font-semibold mb-3">Informations légales</h3>
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
          © {new Date().getFullYear()} Novatis Tous droits réservés.
        </div>
      </footer>
    );
  }

  ReactDOM.createRoot(document.getElementById("footer-root")).render(<Footer />);
</script>
