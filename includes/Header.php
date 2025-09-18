<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<style>
  body {
    overflow-x: hidden; /* empêche le scroll horizontal sur toute la page */
  }
</style>

<div id="header-root"></div>

<script type="text/babel">
  const { useState, useEffect, useRef } = React;

  function Header() {
    const [open, setOpen] = useState(false); // mobile menu
    const [dropdownOpen, setDropdownOpen] = useState(false); // profil
    const [activeIndex, setActiveIndex] = useState(0);
    const [highlight, setHighlight] = useState({ left: 0, width: 0 });
    const navRefs = useRef([]);

    const items = [
      { name: "Accueil", href: "<?= BASE_URL ?>", file: "index.php" },
      { name: "Trouver un prestataire", href: "<?= BASE_URL ?>/Prestataires", file: "Prestataires.php" },
    ];

    useEffect(() => {
      const current = "<?= $currentPage ?>";
      const index = items.findIndex(item => item.file === current);
      setActiveIndex(index !== -1 ? index : 0);
    }, []);

    useEffect(() => {
      const timeout = setTimeout(() => {
        const el = navRefs.current[activeIndex];
        if (el) {
          const span = el.querySelector("span");
          if (span) {
            const left = span.offsetLeft + el.offsetLeft - 12;
            const width = span.offsetWidth + 24;
            setHighlight({ left, width });
          }
        }
      }, 50);
      return () => clearTimeout(timeout);
    }, [activeIndex]);

    return (
      <nav className="bg-white shadow-md px-6 py-3 fixed top-0 left-0 right-0 z-50 overflow-visible">
        <div className="max-w-7xl mx-auto flex items-center justify-between">

          {/* Logo + Nom */}
          <a href="<?= BASE_URL ?>" className="flex items-center space-x-2">
            <img src="<?= BASE_URL ?>/asset/img/logo.png" alt="Logo" className="w-12 h-12 rounded-full" />
            <span className="text-xl font-bold">Novatis</span>
          </a>

          {/* Menu desktop */}
          <div className="hidden md:flex items-center space-x-6 relative">
            <div
              className="absolute rounded transition-all duration-500 ease-out"
              style={{
                width: `${highlight.width}px`,
                left: `${highlight.left}px`,
                backgroundColor: "black",
                top: "50%",
                transform: "translateY(-50%)",
                height: "2em",
                borderRadius: "0.5rem",
                zIndex: 0,
                pointerEvents: "none",
                position: "absolute"
              }}
            />
            {items.map((item, i) => (
              <a
                key={i}
                href={item.href}
                ref={el => navRefs.current[i] = el}
                className="relative px-2 font-medium z-10"
                style={{ color: i === activeIndex ? "white" : "black" }}
                onMouseEnter={() => setActiveIndex(i)}
              >
                <span>{item.name}</span>
              </a>
            ))}
          </div>

          {/* Search bar desktop */}
          <div className="hidden md:block flex-1 px-6">
            <input
              type="text"
              placeholder="Rechercher..."
              className="w-full border rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          {/* Profil utilisateur */}
          <div className="relative ml-4">
            <button
              onClick={() => {
                if (window.innerWidth >= 768) {
                  setDropdownOpen(!dropdownOpen);
                }
              }}
              className="flex items-center space-x-2 focus:outline-none"
            >
              <span className="font-medium text-gray-700">Jean Dupont</span>
              <img
                src="https://via.placeholder.com/40"
                alt="avatar"
                className="w-10 h-10 rounded-full border"
              />
            </button>

            {dropdownOpen && (
              <div className="absolute right-0 mt-5 w-48 bg-white border rounded-lg shadow-lg py-2 z-50">
                {/* Paramètres avec roue crantée */}
                <a href="#" className="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 2122 2122"
                    className="w-5 h-5 mr-2"
                    fill="currentColor"
                  >
                    <path d="M1909.18,1148.97v-176.63h-107.48c-4.98-42.32-13.52-83.56-25.3-123.41l98.63-42.43-69.8-162.26-98.67,42.44c-17.77-30.62-37.65-59.9-59.45-87.57l76-76-124.9-124.9-75.99,76c-32.94-25.95-68.09-49.18-105.16-69.37l39.75-99.74-164.09-65.39-39.75,99.76c-33.78-8.99-68.49-15.66-103.98-19.85v-107.49h-176.63v107.49c-42.34,4.99-83.56,13.52-123.41,25.29l-42.43-98.63-162.25,69.8,42.44,98.67c-30.64,17.77-59.9,37.65-87.57,59.45l-76-76-124.9,124.9,76,75.99c-25.94,32.94-49.18,68.09-69.37,105.16l-99.74-39.75-65.39,164.09,99.76,39.75c-8.99,33.78-15.66,68.49-19.85,103.98h-107.49v176.63h107.49c4.98,42.34,13.52,83.56,25.29,123.41l-98.63,42.43,69.8,162.26,98.67-42.44c17.77,30.62,37.65,59.9,59.45,87.57l-76,76,124.9,124.9,75.99-76c32.94,25.95,68.09,49.18,105.16,69.37l-39.75,99.74,164.09,65.39,39.75-99.76c33.78,8.99,68.49,15.66,103.98,19.85v107.49h176.63v-107.49c42.34-4.99,83.56-13.52,123.41-25.3l42.43,98.63,162.25-69.8-42.44-98.67c30.64-17.77,59.9-37.65,87.57-59.45l76,76,124.9-124.9-76-75.99c25.94-32.94,49.18-68.09,69.37-105.16l99.74,39.75,65.39-164.09-99.76-39.75c8.99-33.78,15.66-68.49,19.85-103.98h107.49ZM1061,1412.12c-193.51,0-350.61-157.1-350.61-350.61s157.1-350.61,350.61-350.61,350.61,157.1,350.61,350.61-157.1,350.61-350.61,350.61Z"/>
                  </svg>
                  Paramètres
                </a>
                <a href="#" className="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-2" fill="none"
                       viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                          d="M3 3h7v7H3V3zM14 3h7v4h-7V3zM3 14h7v7H3v-7zM14 14h7v7h-7v-7z"/>
                  </svg>
                  Dashboard
                </a>
                <a href="#" className="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-2" fill="none"
                       viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
                  </svg>
                  Déconnexion
                </a>
              </div>
            )}
          </div>

          {/* Mobile toggle */}
          <button onClick={() => setOpen(!open)} className="md:hidden ml-4">
            <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="black">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                    d={open ? "M6 18L18 6M6 6l12 12" : "M4 6h16M4 12h16M4 18h16"} />
            </svg>
          </button>

        </div>

        {/* Mobile menu */}
        {open && (
          <div className="md:hidden mt-4 space-y-2">
            {items.map((item, i) => (
              <a key={i} href={item.href} className="block bg-white px-4 py-2 rounded shadow">
                {item.name}
              </a>
            ))}
            <input
              type="text"
              placeholder="Rechercher..."
              className="w-full border rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 mt-2"
            />
            <div className="mt-2 border-t pt-2">
              <a href="#" className="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 2122 2122"
                className="w-5 h-5 mr-2"
                fill="currentColor"
                >
                <path d="M1909.18,1148.97v-176.63h-107.48c-4.98-42.32-13.52-83.56-25.3-123.41l98.63-42.43-69.8-162.26-98.67,42.44c-17.77-30.62-37.65-59.9-59.45-87.57l76-76-124.9-124.9-75.99,76c-32.94-25.95-68.09-49.18-105.16-69.37l39.75-99.74-164.09-65.39-39.75,99.76c-33.78-8.99-68.49-15.66-103.98-19.85v-107.49h-176.63v107.49c-42.34,4.99-83.56,13.52-123.41,25.29l-42.43-98.63-162.25,69.8,42.44,98.67c-30.64,17.77-59.9,37.65-87.57,59.45l-76-76-124.9,124.9,76,75.99c-25.94,32.94-49.18,68.09-69.37,105.16l-99.74-39.75-65.39,164.09,99.76,39.75c-8.99,33.78-15.66,68.49-19.85,103.98h-107.49v176.63h107.49c4.98,42.34,13.52,83.56,25.29,123.41l-98.63,42.43,69.8,162.26,98.67-42.44c17.77,30.62,37.65,59.9,59.45,87.57l-76,76,124.9,124.9,75.99-76c32.94,25.95,68.09,49.18,105.16,69.37l-39.75,99.74,164.09,65.39,39.75-99.76c33.78,8.99,68.49,15.66,103.98,19.85v107.49h176.63v-107.49c42.34-4.99,83.56-13.52,123.41-25.3l42.43,98.63,162.25-69.8-42.44-98.67c30.64-17.77,59.9-37.65,87.57-59.45l76,76,124.9-124.9-76-75.99c25.94-32.94,49.18-68.09,69.37-105.16l99.74,39.75,65.39-164.09-99.76-39.75c8.99-33.78,15.66-68.49,19.85-103.98h107.49ZM1061,1412.12c-193.51,0-350.61-157.1-350.61-350.61s157.1-350.61,350.61-350.61,350.61,157.1,350.61,350.61-157.1,350.61-350.61,350.61Z"/>
                </svg>
                Paramètres
              </a>
              <a href="#" className="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-2" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                        d="M3 3h7v7H3V3zM14 3h7v4h-7V3zM3 14h7v7H3v-7zM14 14h7v7h-7v-7z"/>
                </svg>
                Dashboard
              </a>
              <a href="#" className="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-2" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
                </svg>
                Déconnexion
              </a>
            </div>
          </div>
        )}

      </nav>
    );
  }

  ReactDOM.createRoot(document.getElementById("header-root")).render(<Header />);
</script>
