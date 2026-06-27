<?php
$currentPage = basename($_SERVER['PHP_SELF']);

// Récupérer les informations de l'utilisateur connecté
$user = getCurrentUser();
$userName = 'Utilisateur';
if ($user) {
    $userName = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
    if (empty($userName)) {
        $userName = $user['pseudo'] ?? 'Utilisateur';
    }
}
$userInfo = [
    'isLoggedIn' => $user !== null,
    'id' => $user['id'] ?? null,
    'name' => $userName,
    'pseudo' => $user['pseudo'] ?? null,
    'email' => $user['email'] ?? '',
    'avatar' => $user['avatar'] ?? null,
    'role' => $user['role'] ?? 'user'
];
?>

<style>
  body {
    overflow-x: hidden; /* empêche le scroll horizontal sur toute la page */
  }
</style>

<div id="header-root"></div>

<script type="text/babel">
  const { useState, useEffect, useRef } = React;

  // Données utilisateur depuis PHP
  const userInfo = <?= json_encode($userInfo) ?>;
  const BASE_URL = <?= json_encode(BASE_URL) ?>;

  // Hook pour i18next (traductions)
  const useTranslation = () => {
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
        return window.t(`header.${key}`);
      }
      // Fallback si i18next n'est pas encore chargé
      const fallbacks = {
        home: "Accueil",
        findProvider: "Trouver un prestataire",
        dashboard: "Dashboard",
        profile: "Voir mon profil",
        settings: "Paramètres",
        logout: "Déconnexion",
        login: "Connexion",
        register: "S'inscrire",
        notifications: "Notifications",
        markAllRead: "Tout marquer comme lu",
        viewAll: "Voir tout",
        noNotifications: "Aucune notification",
        favorites: "Mes Favoris",
        administrator: "Administrateur",
        search: "Rechercher..."
      };
      return fallbacks[key] || key;
    };

    return { t };
  };

  // SVG Avatar anonyme
  const AnonymousAvatar = ({ className, size = 40 }) => (
    React.createElement('div', {
      className: `${className} flex items-center justify-center bg-gray-300 rounded-full`,
      style: { width: size, height: size }
    },
      React.createElement('svg', {
        width: size * 0.6,
        height: size * 0.6,
        viewBox: "0 0 24 24",
        fill: "none",
        xmlns: "http://www.w3.org/2000/svg"
      },
        React.createElement('path', {
          d: "M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z",
          fill: "#9CA3AF"
        })
      )
    )
  );

  function Header() {
    const { t } = useTranslation(); // Utiliser le hook de traduction
    const [open, setOpen] = useState(false); // mobile menu
    const [dropdownOpen, setDropdownOpen] = useState(false); // profil
    const [notificationOpen, setNotificationOpen] = useState(false); // notifications
    const [notifications, setNotifications] = useState([]);
    const [unreadCount, setUnreadCount] = useState(0);
    const [activeIndex, setActiveIndex] = useState(0);
    const [highlight, setHighlight] = useState({ left: 0, width: 0 });
    const [isDarkMode, setIsDarkMode] = useState(document.documentElement.classList.contains('dark'));
    const [searchQuery, setSearchQuery] = useState('');
    const [currentLang, setCurrentLang] = useState(localStorage.getItem('language') || 'fr');
    const [searchResults, setSearchResults] = useState([]);
    const [searchOpen, setSearchOpen] = useState(false);
    const searchTimeoutRef = useRef(null);
    const navRefs = useRef([]);

    // Écouter les changements de thème
    useEffect(() => {
      const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          if (mutation.attributeName === 'class') {
            setIsDarkMode(document.documentElement.classList.contains('dark'));
          }
        });
      });

      observer.observe(document.documentElement, { attributes: true });

      return () => observer.disconnect();
    }, []);

    const items = [
      { name: t('home'), href: `${BASE_URL}`, file: "index.php" },
      { name: t('findProvider'), href: `${BASE_URL}/Prestataires`, file: "Prestataires.php" },
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

    // Charger les notifications si l'utilisateur est connecté
    useEffect(() => {
      if (userInfo.isLoggedIn) {
        loadNotifications();
        // Rafraîchir toutes les 30 secondes
        const interval = setInterval(loadNotifications, 30000);
        return () => clearInterval(interval);
      }
    }, []);

    const loadNotifications = async () => {
      try {
        const response = await fetch(`${BASE_URL}/api/notifications/get.php?action=list&unread=true&limit=5`);
        const data = await response.json();
        if (data.success) {
          setNotifications(data.notifications);
          setUnreadCount(data.unread_count);
        }
      } catch (error) {
        console.error('Erreur chargement notifications:', error);
      }
    };

    const markAsRead = async (notificationId) => {
      try {
        const response = await fetch(`${BASE_URL}/api/notifications/update.php`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'mark_read', notification_id: notificationId })
        });
        const data = await response.json();
        if (data.success) {
          loadNotifications(); // Recharger
        }
      } catch (error) {
        console.error('Erreur marquage comme lu:', error);
      }
    };

    const markAllAsRead = async () => {
      try {
        const response = await fetch(`${BASE_URL}/api/notifications/update.php`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'mark_all_read' })
        });
        const data = await response.json();
        if (data.success) {
          loadNotifications();
        }
      } catch (error) {
        console.error('Erreur marquage tout comme lu:', error);
      }
    };

    const handleSearch = (value) => {
      setSearchQuery(value);
      
      if (searchTimeoutRef.current) {
        clearTimeout(searchTimeoutRef.current);
      }

      if (value.length < 2) {
        setSearchResults([]);
        setSearchOpen(false);
        return;
      }

      searchTimeoutRef.current = setTimeout(async () => {
        try {
          const response = await fetch(`${BASE_URL}/api/search.php?q=${encodeURIComponent(value)}`);
          const data = await response.json();
          setSearchResults(data.results || []);
          setSearchOpen(data.results && data.results.length > 0);
        } catch (error) {
          console.error('Erreur recherche:', error);
          setSearchResults([]);
        }
      }, 300);
    };

    const handleSearchResultClick = (result) => {
      window.location.href = `${BASE_URL}/profil?id=${result.provider_id}`;
      setSearchQuery('');
      setSearchResults([]);
      setSearchOpen(false);
    };

    const getNotificationIcon = (type) => {
      switch(type) {
        case 'order': return '🛍️';
        case 'message': return '💬';
        case 'security': return '🔒';
        case 'payment': return '💰';
        case 'service_update': return '✨';
        default: return '🔔';
      }
    };

    const formatTimeAgo = (dateString) => {
      const date = new Date(dateString);
      const now = new Date();
      const seconds = Math.floor((now - date) / 1000);

      const currentLang = window.getCurrentLanguage ? window.getCurrentLanguage() : 'fr';

      if (seconds < 60) return window.t ? window.t('time.justNow') : 'À l\'instant';
      if (seconds < 3600) return Math.floor(seconds / 60) + (window.t ? ' ' + window.t('time.minutes') : ' min');
      if (seconds < 86400) return Math.floor(seconds / 3600) + (window.t ? ' ' + window.t('time.hours') : ' h');
      if (seconds < 2592000) return Math.floor(seconds / 86400) + (window.t ? ' ' + window.t('time.days') : ' j');
      return date.toLocaleDateString(currentLang);
    };

    return (
      <nav className="bg-white dark:bg-slate-800 shadow-md px-4 sm:px-6 py-3 fixed top-0 left-0 right-0 z-50 overflow-visible">
        <div className="max-w-7xl mx-auto flex items-center justify-between">

          {/* Logo + Nom */}
          <a href={BASE_URL} className="flex items-center space-x-2 flex-shrink-0">
            <img
              src={isDarkMode ? `${BASE_URL}/assets/img/logos/Logo_Novatis_nobg_white.png` : `${BASE_URL}/assets/img/logos/Logo_Novatis_nobg.png`}
              alt="Logo"
              className="w-10 sm:w-12 h-10 sm:h-12 rounded-full transition-all duration-300"
            />
            <span className="hidden sm:inline text-lg sm:text-xl font-bold">Novatis</span>
          </a>

          {/* Menu desktop */}
          <div className="hidden md:flex items-center space-x-6 relative flex-1">
            <div
              className="absolute rounded transition-all duration-500 ease-out nav-highlight-slider"
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
          <div className="hidden md:block flex-1 px-6 relative">
            <input
              type="text"
              placeholder={t('search')}
              value={searchQuery}
              onChange={(e) => handleSearch(e.target.value)}
              onFocus={() => searchResults.length > 0 && setSearchOpen(true)}
              className="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
            />
            
            {/* Dropdown de résultats de recherche */}
            {searchOpen && searchResults.length > 0 && (
              <div className="absolute top-full mt-2 left-6 right-6 bg-white dark:bg-slate-700 border dark:border-slate-600 rounded-lg shadow-lg z-50 max-h-96 overflow-hidden">
                {searchResults.map((result, index) => (
                  <div
                    key={index}
                    onClick={() => handleSearchResultClick(result)}
                    className="p-4 border-b border-gray-100 dark:border-slate-600 hover:bg-gray-50 dark:hover:bg-slate-600 cursor-pointer transition-colors flex items-start space-x-3"
                  >
                    {result.avatar ? (
                      React.createElement('img', {
                        src: result.avatar,
                        alt: result.provider_name,
                        className: "w-10 h-10 rounded-full object-cover flex-shrink-0"
                      })
                    ) : (
                      React.createElement(AnonymousAvatar, { size: 40 })
                    )}
                    <div className="flex-1 min-w-0">
                      <p className="font-semibold text-gray-900 dark:text-white text-sm truncate">{result.title}</p>
                      <p className="text-xs text-gray-600 dark:text-gray-400 mt-0.5">Par {result.provider_name}</p>
                      {result.category && (
                        <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">{result.category}</p>
                      )}
                      <p className="text-sm font-bold text-red-600 dark:text-red-400 mt-1">{result.price}€</p>
                    </div>
                    {result.rating && (
                      <div className="flex items-center space-x-1 flex-shrink-0">
                        <svg className="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <span className="text-xs font-semibold text-gray-700 dark:text-gray-300">{result.rating}</span>
                      </div>
                    )}
                  </div>
                ))}
              </div>
            )}
          </div>

          {/* Actions desktop - Langue, thème, notifications, profil */}
          {userInfo.isLoggedIn ? (
            <div className="hidden md:flex items-center space-x-2">
              {/* Sélecteur de langue */}
              {typeof window.LanguageSwitcher !== 'undefined' && React.createElement(window.LanguageSwitcher)}

              {/* Toggle de thème */}
              <button
                onClick={() => window.ThemeManager && window.ThemeManager.toggle()}
                className="p-2 hover:bg-gray-50 dark:hover:bg-slate-700 rounded-lg transition-colors focus:outline-none"
                aria-label="Changer le thème"
                data-theme-toggle="true"
              >
                {isDarkMode ? (
                  // Soleil (mode sombre actif)
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 text-gray-700 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                  </svg>
                ) : (
                  // Lune (mode clair actif)
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                  </svg>
                )}
              </button>

              {/* Icône Notifications */}
              <div className="relative">
                <button
                  onClick={() => setNotificationOpen(!notificationOpen)}
                  className="relative p-2 hover:bg-gray-50 dark:hover:bg-slate-700 rounded-lg transition-colors focus:outline-none"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                  </svg>
                  {unreadCount > 0 && (
                    <span className="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                      {unreadCount > 9 ? '9+' : unreadCount}
                    </span>
                  )}
                </button>

                {/* Dropdown Notifications */}
                {notificationOpen && (
                  <div className="absolute right-0 mt-2 w-96 bg-white border rounded-lg shadow-lg z-50 max-h-[500px] overflow-hidden flex flex-col">
                    {/* Header */}
                    <div className="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-800">
                      <h3 className="font-semibold text-gray-900 dark:text-white">{t('notifications')}</h3>
                      {unreadCount > 0 && (
                        <button onClick={markAllAsRead} className="text-xs text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-medium">
                          {t('markAllRead')}
                        </button>
                      )}
                    </div>

                    {/* Liste des notifications */}
                    <div className="overflow-y-auto flex-1">
                      {notifications.length === 0 ? (
                        <div className="p-8 text-center text-gray-500">
                          <svg xmlns="http://www.w3.org/2000/svg" className="h-12 w-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                          </svg>
                          <p className="text-sm">{t('noNotifications')}</p>
                        </div>
                      ) : (
                        notifications.map((notif) => (
                          <div
                            key={notif.id}
                            className={`p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors ${!notif.is_read ? 'bg-blue-50' : ''}`}
                            onClick={() => {
                              if (!notif.is_read) markAsRead(notif.id);
                              if (notif.link) window.location.href = notif.link;
                            }}
                          >
                            <div className="flex items-start space-x-3">
                              <span className="text-2xl">{getNotificationIcon(notif.type)}</span>
                              <div className="flex-1 min-w-0">
                                <p className={`text-sm font-medium text-gray-900 ${!notif.is_read ? 'font-semibold' : ''}`}>
                                  {notif.title}
                                </p>
                                <p className="text-sm text-gray-600 mt-1 line-clamp-2">
                                  {notif.message}
                                </p>
                                <p className="text-xs text-gray-400 mt-1">
                                  {formatTimeAgo(notif.created_at)}
                                </p>
                              </div>
                              {!notif.is_read && (
                                <div className="w-2 h-2 bg-red-600 dark:bg-red-400 rounded-full flex-shrink-0 mt-2"></div>
                              )}
                            </div>
                          </div>
                        ))
                      )}
                    </div>

                    {/* Footer */}
                    {notifications.length > 0 && (
                      <div className="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-center">
                        <a href={`${BASE_URL}/notifications`} className="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-medium">
                          {t('viewAll')}
                        </a>
                      </div>
                    )}
                  </div>
                )}
              </div>

              {/* Menu Profil */}
              <div className="relative ml-2 hidden md:block">
                <button
                  onClick={() => {
                    if (window.innerWidth >= 768) {
                      setDropdownOpen(!dropdownOpen);
                    }
                  }}
                  className="flex items-center space-x-2 focus:outline-none hover:bg-gray-50 dark:hover:bg-slate-700 rounded-lg px-2 py-1 transition-colors"
                >
                <div className="text-right hidden sm:block">
                  <span className="font-medium text-gray-700 block">{userInfo.pseudo || userInfo.name}</span>
                  {userInfo.role === 'admin' && (
                    <span className="text-xs text-red-600 font-medium">Admin</span>
                  )}
                </div>
                {userInfo.avatar ? (
                  React.createElement('img', {
                    src: userInfo.avatar,
                    alt: "Avatar",
                    className: "w-10 h-10 rounded-full border-2 border-gray-200 object-cover",
                    onError: (e) => {
                      // Si l'image échoue, remplacer par le SVG
                      const parent = e.target.parentNode;
                      parent.replaceChild(
                        React.createElement(AnonymousAvatar, { className: "border-2 border-gray-200", size: 40 }),
                        e.target
                      );
                    }
                  })
                ) : (
                  React.createElement(AnonymousAvatar, { className: "border-2 border-gray-200", size: 40 })
                )}
              </button>

              {dropdownOpen && (
                <div className="absolute right-0 mt-5 w-80 bg-white dark:bg-slate-700 border dark:border-slate-600 rounded-lg shadow-lg py-3 z-50">
                  {/* Info utilisateur */}
                  <div className="px-5 py-4 border-b border-gray-100 dark:border-slate-600">
                    <div className="flex items-center space-x-4">
                      {userInfo.avatar ? (
                        React.createElement('img', {
                          src: userInfo.avatar,
                          alt: "Avatar",
                          className: "w-14 h-14 rounded-full object-cover"
                        })
                      ) : (
                        React.createElement(AnonymousAvatar, { size: 56 })
                      )}
                      <div className="flex-1">
                        <p className="font-semibold text-gray-900 dark:text-white text-base">{userInfo.pseudo || userInfo.name}</p>
                        <p className="text-sm text-gray-500 dark:text-gray-300 mt-1">{userInfo.email}</p>
                        {userInfo.role === 'admin' && (
                          <span className="inline-flex items-center px-2 py-1 text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-full mt-2">
                            {t('administrator')}
                          </span>
                        )}
                      </div>
                    </div>
                  </div>

                  {/* Menu items */}
                  <div className="py-2">
                    <a href={`${BASE_URL}/Dashboard`} className="flex items-center px-5 py-3 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-600 hover:text-gray-900 dark:hover:text-white transition-colors">
                      <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 mr-3 text-gray-400 dark:text-gray-400" fill="none"
                           viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                              d="M3 3h7v7H3V3zM14 3h7v4h-7V3zM3 14h7v7H3v-7zM14 14h7v7h-7v-7z"/>
                      </svg>
                      {t('dashboard')}
                    </a>
                    <a href={`${BASE_URL}/profil?id=${userInfo.id || ''}`} className="flex items-center px-5 py-3 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-600 hover:text-gray-900 dark:hover:text-white transition-colors">
                      <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 mr-3 text-gray-400 dark:text-gray-400" fill="none"
                           viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                      </svg>
                      {t('profile')}
                    </a>
                    <a href={`${BASE_URL}/Favoris`} className="flex items-center px-5 py-3 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-600 hover:text-gray-900 dark:hover:text-white transition-colors">
                      <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 mr-3 text-gray-400 dark:text-gray-400" fill="none"
                           viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                              d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                      </svg>
                      {t('favorites')}
                    </a>
                    <a href={`${BASE_URL}/Parametres?section=profile`} className="flex items-center px-5 py-3 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-600 hover:text-gray-900 dark:hover:text-white transition-colors">
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 2122 2122"
                        className="w-6 h-6 mr-3 text-gray-400 dark:text-gray-400"
                        fill="currentColor"
                      >
                        <path d="M1909.18,1148.97v-176.63h-107.48c-4.98-42.32-13.52-83.56-25.3-123.41l98.63-42.43-69.8-162.26-98.67,42.44c-17.77-30.62-37.65-59.9-59.45-87.57l76-76-124.9-124.9-75.99,76c-32.94-25.95-68.09-49.18-105.16-69.37l39.75-99.74-164.09-65.39-39.75,99.76c-33.78-8.99-68.49-15.66-103.98-19.85v-107.49h-176.63v107.49c-42.34,4.99-83.56,13.52-123.41,25.29l-42.43-98.63-162.25,69.8,42.44,98.67c-30.64,17.77-59.9,37.65-87.57,59.45l-76-76-124.9,124.9,76,75.99c-25.94,32.94-49.18,68.09-69.37,105.16l-99.74-39.75-65.39,164.09,99.76,39.75c-8.99,33.78-15.66,68.49-19.85,103.98h-107.49v176.63h107.49c4.98,42.34,13.52,83.56,25.29,123.41l-98.63,42.43,69.8,162.26,98.67-42.44c17.77,30.62,37.65,59.9,59.45,87.57l-76,76,124.9,124.9,75.99-76c32.94,25.95,68.09,49.18,105.16,69.37l-39.75,99.74,164.09,65.39,39.75-99.76c33.78,8.99,68.49,15.66,103.98,19.85v107.49h176.63v-107.49c42.34-4.99,83.56-13.52,123.41-25.3l42.43,98.63,162.25-69.8-42.44-98.67c30.64-17.77,59.9-37.65,87.57-59.45l76,76,124.9-124.9-76-75.99c25.94-32.94,49.18-68.09,69.37-105.16l99.74,39.75,65.39-164.09-99.76-39.75c8.99-33.78,15.66-68.49,19.85-103.98h107.49ZM1061,1412.12c-193.51,0-350.61-157.1-350.61-350.61s157.1-350.61,350.61-350.61,350.61,157.1,350.61,350.61-157.1,350.61-350.61,350.61Z"/>
                      </svg>
                      {t('settings')}
                    </a>
                  </div>
                  <div className="border-t border-gray-100 dark:border-slate-600 pt-2">
                    <a href={`${BASE_URL}/logout`} className="flex items-center px-5 py-3 text-base font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-slate-600 hover:text-red-700 dark:hover:text-red-300 transition-colors">
                      <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 mr-3 text-red-400 dark:text-red-400" fill="none"
                           viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
                      </svg>
                      {t('logout')}
                    </a>
                  </div>
                </div>
              )}
              </div>
            </div>
          ) : (
            /* Boutons connexion/inscription pour utilisateurs non connectés */
            <div className="hidden md:flex items-center space-x-3">
              {/* Sélecteur de langue pour utilisateurs non connectés */}
              {typeof window.LanguageSwitcher !== 'undefined' && React.createElement(window.LanguageSwitcher)}

              {/* Toggle de thème pour utilisateurs non connectés */}
              <button
                onClick={() => window.ThemeManager && window.ThemeManager.toggle()}
                className="p-2 hover:bg-gray-50 dark:hover:bg-slate-700 rounded-lg transition-colors focus:outline-none"
                aria-label="Changer le thème"
                data-theme-toggle="true"
              >
                {isDarkMode ? (
                  // Soleil (mode sombre actif)
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                  </svg>
                ) : (
                  // Lune (mode clair actif)
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                  </svg>
                )}
              </button>

              <a
                href={`${BASE_URL}/Autentification?mode=login`}
                className="text-gray-700 hover:text-gray-900 font-medium px-3 py-2 rounded-md transition-colors"
              >
                {t('login')}
              </a>
              <a
                href={`${BASE_URL}/Autentification?mode=register`}
                className="bg-black text-white hover:bg-gray-800 font-medium px-4 py-2 rounded-md transition-colors"
              >
                {t('register')}
              </a>
            </div>
          )}

          {/* Mobile toggle - Hamburger Button */}
          <button 
            onClick={() => setOpen(!open)} 
            className="md:hidden ml-4 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors"
            aria-label="Toggle menu"
          >
            <svg className="w-6 h-6 text-gray-900 dark:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                    d={open ? "M6 18L18 6M6 6l12 12" : "M4 6h16M4 12h16M4 18h16"} />
            </svg>
          </button>

        </div>

        {/* Mobile menu */}
        {open && (
          <div className="md:hidden mt-4 space-y-2 bg-white dark:bg-slate-700 rounded-lg p-4 shadow-lg border dark:border-slate-600">
            {/* Navigation Items */}
            <div className="space-y-1">
              {items.map((item, i) => (
                <a 
                  key={i} 
                  href={item.href} 
                  className="block px-4 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-600 transition-colors font-medium"
                >
                  {item.name}
                </a>
              ))}
            </div>

            {/* Mobile Search */}
            <div className="border-t border-gray-200 dark:border-slate-600 pt-3 mt-3">
              <div className="relative">
                <input
                  type="text"
                  placeholder={t('search')}
                  value={searchQuery}
                  onChange={(e) => handleSearch(e.target.value)}
                  onFocus={() => searchResults.length > 0 && setSearchOpen(true)}
                  className="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
                />
                
                {/* Dropdown mobile */}
                {searchOpen && searchResults.length > 0 && (
                  <div className="absolute top-full mt-1 left-0 right-0 bg-white dark:bg-slate-600 border dark:border-slate-500 rounded-lg shadow-lg z-50 max-h-80 overflow-hidden">
                    {searchResults.map((result, index) => (
                      <div
                        key={index}
                        onClick={() => {
                          handleSearchResultClick(result);
                          setOpen(false);
                        }}
                        className="p-3 border-b border-gray-100 dark:border-slate-500 hover:bg-gray-50 dark:hover:bg-slate-700 cursor-pointer transition-colors flex items-start space-x-3"
                      >
                        {result.avatar ? (
                          React.createElement('img', {
                            src: result.avatar,
                            alt: result.provider_name,
                            className: "w-8 h-8 rounded-full object-cover flex-shrink-0"
                          })
                        ) : (
                          React.createElement(AnonymousAvatar, { size: 32 })
                        )}
                        <div className="flex-1 min-w-0">
                          <p className="font-semibold text-gray-900 dark:text-white text-sm truncate">{result.title}</p>
                          <p className="text-xs text-gray-600 dark:text-gray-300 mt-0.5">Par {result.provider_name}</p>
                          <p className="text-sm font-bold text-red-600 dark:text-red-400 mt-1">{result.price}€</p>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </div>

            {/* Utilities Section */}
            <div className="border-t border-gray-200 dark:border-slate-600 pt-3 mt-3 space-y-2">
              {/* Language Selector */}
              <select
                onChange={(e) => {
                  const newLang = e.target.value;
                  setCurrentLang(newLang);
                  localStorage.setItem("language", newLang);
                  if (window.i18n && window.i18n.changeLanguage) {
                    window.i18n.changeLanguage(newLang);
                  }
                  setTimeout(() => window.location.reload(), 100);
                }}
                value={currentLang}
                className="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
              >
                <option value="fr">🇫🇷 Français</option>
                <option value="en">🇬🇧 English</option>
              </select>

              {/* Theme Toggle */}
              <button
                onClick={() => {
                  document.documentElement.classList.toggle('dark');
                  setIsDarkMode(!isDarkMode);
                  localStorage.setItem('dark-mode', !isDarkMode);
                }}
                className="w-full flex items-center justify-center px-4 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-600 transition-colors"
                aria-label="Change theme"
              >
                {isDarkMode ? (
                  <>
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    {currentLang === 'fr' ? 'Mode clair' : 'Light mode'}
                  </>
                ) : (
                  <>
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    {currentLang === 'fr' ? 'Mode sombre' : 'Dark mode'}
                  </>
                )}
              </button>
            </div>

            {userInfo.isLoggedIn ? (
              <div className="border-t border-gray-200 dark:border-slate-600 pt-3 mt-3">
                {/* User Info Card */}
                <div className="flex items-center space-x-3 px-4 py-3 bg-gray-50 dark:bg-slate-600 rounded-lg mb-3">
                  {userInfo.avatar ? (
                    React.createElement('img', {
                      src: userInfo.avatar,
                      alt: "Avatar",
                      className: "w-10 h-10 rounded-full object-cover"
                    })
                  ) : (
                    React.createElement(AnonymousAvatar, { size: 40 })
                  )}
                  <div>
                    <p className="font-medium text-gray-900 dark:text-white">{userInfo.pseudo || userInfo.name}</p>
                    {userInfo.role === 'admin' && (
                      <span className="text-xs text-red-600 dark:text-red-400 font-medium">{t('administrator')}</span>
                    )}
                  </div>
                </div>

                {/* User Menu Links */}
                <div className="space-y-1">
                  <a href={`${BASE_URL}/Dashboard`} className="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-600 rounded transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-2" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                            d="M3 3h7v7H3V3zM14 3h7v4h-7V3zM3 14h7v7H3v-7zM14 14h7v7h-7v-7z"/>
                    </svg>
                    {t('dashboard')}
                  </a>
                  <a href={`${BASE_URL}/Favoris`} className="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-600 rounded transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-2" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    {t('favorites')}
                  </a>
                  <a href={`${BASE_URL}/Parametres?section=profile`} className="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-600 rounded transition-colors">
                    <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 2122 2122"
                    className="w-5 h-5 mr-2"
                    fill="currentColor"
                    >
                    <path d="M1909.18,1148.97v-176.63h-107.48c-4.98-42.32-13.52-83.56-25.3-123.41l98.63-42.43-69.8-162.26-98.67,42.44c-17.77-30.62-37.65-59.9-59.45-87.57l76-76-124.9-124.9-75.99,76c-32.94-25.95-68.09-49.18-105.16-69.37l39.75-99.74-164.09-65.39-39.75,99.76c-33.78-8.99-68.49-15.66-103.98-19.85v-107.49h-176.63v107.49c-42.34,4.99-83.56,13.52-123.41,25.29l-42.43-98.63-162.25,69.8,42.44,98.67c-30.64,17.77-59.9,37.65-87.57,59.45l-76-76-124.9,124.9,76,75.99c-25.94,32.94-49.18,68.09-69.37,105.16l-99.74-39.75-65.39,164.09,99.76,39.75c-8.99,33.78-15.66,68.49-19.85,103.98h-107.49v176.63h107.49c4.98,42.34,13.52,83.56,25.29,123.41l-98.63,42.43,69.8,162.26,98.67-42.44c17.77,30.62,37.65,59.9,59.45,87.57l-76,76,124.9,124.9,75.99-76c32.94,25.95,68.09,49.18,105.16,69.37l-39.75,99.74,164.09,65.39,39.75-99.76c33.78,8.99,68.49,15.66,103.98,19.85v107.49h176.63v-107.49c42.34-4.99,83.56-13.52,123.41-25.3l42.43,98.63,162.25-69.8-42.44-98.67c30.64-17.77,59.9-37.65,87.57-59.45l76,76,124.9-124.9-76-75.99c25.94-32.94,49.18-68.09,69.37-105.16l99.74,39.75,65.39-164.09-99.76-39.75c8.99-33.78,15.66-68.49,19.85-103.98h107.49ZM1061,1412.12c-193.51,0-350.61-157.1-350.61-350.61s157.1-350.61,350.61-350.61,350.61,157.1,350.61,350.61-157.1,350.61-350.61,350.61Z"/>
                    </svg>
                    {t('settings')}
                  </a>
                  <a href={`${BASE_URL}/logout`} className="flex items-center px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-slate-600 rounded transition-colors font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-2" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
                    </svg>
                    {t('logout')}
                  </a>
                </div>
              </div>
            ) : (
              /* Auth Links for mobile */
              <div className="border-t border-gray-200 dark:border-slate-600 pt-3 mt-3 space-y-2">
                <a href={`${BASE_URL}/Autentification?mode=login`} className="block bg-gray-100 dark:bg-slate-600 text-center text-gray-700 dark:text-white px-4 py-2 rounded font-medium hover:bg-gray-200 dark:hover:bg-slate-500 transition-colors">
                  {t('login')}
                </a>
                <a href={`${BASE_URL}/Autentification?mode=register`} className="block bg-red-600 dark:bg-red-700 text-white text-center px-4 py-2 rounded font-medium hover:bg-red-700 dark:hover:bg-red-600 transition-colors">
                  {t('register')}
                </a>
              </div>
            )}
          </div>
        )}

      </nav>
    );
  }

  ReactDOM.createRoot(document.getElementById("header-root")).render(<Header />);
</script>
