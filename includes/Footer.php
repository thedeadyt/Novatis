<div id="footer-root"></div>
//-- IGNORE ---
<script type="text/babel">
  function Footer() {
    const links = [
      { name: "Accueil", href: "<?= BASE_URL ?>" },
      { name: "Prestataires", href: "<?= BASE_URL ?>/Prestataires" },
      { name: "Contact", href: "<?= BASE_URL ?>/Contact" },
      { name: "À propos", href: "<?= BASE_URL ?>/A-propos" },
    ];

    const legalLinks = [
      { name: "Mentions légales", href: "<?= BASE_URL ?>/mentions-legales" },
      { name: "Politique de confidentialité", href: "<?= BASE_URL ?>/confidentialite" },
    ];

    return (
      <footer className="bg-gray-900 text-gray-200 mt-20">
        <div className="max-w-7xl mx-auto px-6 py-10 grid md:grid-cols-3 gap-10">
          
          {/* Logo + description */}
          <div>
            <a href="<?= BASE_URL ?>" className="flex items-center space-x-2 mb-4">
              <img src="<?= BASE_URL ?>/asset/img/logo.png" alt="Logo" className="w-12 h-12 rounded-full" />
              <span className="text-xl font-bold">&lt;Novatis/&gt;</span>
            </a>
            <p className="text-sm text-gray-400">
              Plateforme de mise en relation entre clients et prestataires.
            </p>
          </div>

          {/* Liens rapides */}
          <div>
            <h3 className="font-semibold mb-3">Navigation</h3>
            <ul className="space-y-2">
              {links.map((link, i) => (
                <li key={i}>
                  <a href={link.href} className="hover:text-white transition-colors">
                    {link.name}
                  </a>
                </li>
              ))}
            </ul>
          </div>

          {/* Liens légaux */}
          <div>
            <h3 className="font-semibold mb-3">Informations légales</h3>
            <ul className="space-y-2">
              {legalLinks.map((link, i) => (
                <li key={i}>
                  <a href={link.href} className="hover:text-white transition-colors">
                    {link.name}
                  </a>
                </li>
              ))}
            </ul>
          </div>
        </div>

        {/* Bas du footer */}
        <div className="border-t border-gray-700 py-4 text-center text-sm text-gray-400">
          © {new Date().getFullYear()} &lt;Novatis/&gt;. Tous droits réservés.
        </div>
      </footer>
    );
  }

  ReactDOM.createRoot(document.getElementById("footer-root")).render(<Footer />);
</script>
