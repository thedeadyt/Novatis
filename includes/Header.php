<?php
$currentPage = basename($_SERVER['PHP_SELF']);

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Récupérer les informations de l'utilisateur connecté
$user = $_SESSION['user'] ?? null;
$userName = 'Utilisateur';
if ($user) {
    $userName = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
    if (empty($userName)) {
        $userName = $user['pseudo'] ?? 'Utilisateur';
    }
}
$userInfo = [
    'isLoggedIn' => $user !== null,
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

  // Traductions
  const t = {
    home: "<?= __('nav_home') ?>",
    findProvider: "<?= __('nav_find_provider') ?>",
    dashboard: "<?= __('nav_dashboard') ?>",
    profile: "<?= __('nav_profile') ?>",
    settings: "<?= __('nav_settings') ?>",
    logout: "<?= __('nav_logout') ?>",
    login: "<?= __('nav_login') ?>",
    register: "<?= __('nav_register') ?>",
    notifications: "<?= __('nav_notifications') ?>",
    markAllRead: "<?= __('nav_mark_all_read') ?>",
    viewAll: "<?= __('nav_view_all') ?>",
    noNotifications: "<?= __('nav_no_notifications') ?>"
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
    const [open, setOpen] = useState(false); // mobile menu
    const [dropdownOpen, setDropdownOpen] = useState(false); // profil
    const [notificationOpen, setNotificationOpen] = useState(false); // notifications
    const [notifications, setNotifications] = useState([]);
    const [unreadCount, setUnreadCount] = useState(0);
    const [activeIndex, setActiveIndex] = useState(0);
    const [highlight, setHighlight] = useState({ left: 0, width: 0 });
    const navRefs = useRef([]);

    const items = [
      { name: t.home, href: "<?= BASE_URL ?>", file: "index.php" },
      { name: t.findProvider, href: "<?= BASE_URL ?>/Prestataires", file: "Prestataires.php" },
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
        const response = await fetch('<?= BASE_URL ?>/api/notifications/get.php?action=list&unread=true&limit=5');
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
        const response = await fetch('<?= BASE_URL ?>/api/notifications/update.php', {
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
        const response = await fetch('<?= BASE_URL ?>/api/notifications/update.php', {
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

      if (seconds < 60) return 'À l\'instant';
      if (seconds < 3600) return Math.floor(seconds / 60) + ' min';
      if (seconds < 86400) return Math.floor(seconds / 3600) + ' h';
      if (seconds < 2592000) return Math.floor(seconds / 86400) + ' j';
      return date.toLocaleDateString('fr-FR');
    };

    return (
      <nav className="bg-white shadow-md px-6 py-3 fixed top-0 left-0 right-0 z-50 overflow-visible">
        <div className="max-w-7xl mx-auto flex items-center justify-between">

          {/* Logo + Nom */}
          <a href="<?= BASE_URL ?>" className="flex items-center space-x-2">
            <img src="<?= BASE_URL ?>/asset/img/logo.svg" alt="Logo" className="w-12 h-12 rounded-full" />
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
          {userInfo.isLoggedIn ? (
            <div className="flex items-center space-x-2">
              {/* Icône Notifications */}
              <div className="relative">
                <button
                  onClick={() => setNotificationOpen(!notificationOpen)}
                  className="relative p-2 hover:bg-gray-50 rounded-lg transition-colors focus:outline-none"
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
                    <div className="px-4 py-3 border-b border-gray-200 flex items-center justify-between bg-gray-50">
                      <h3 className="font-semibold text-gray-900">{t.notifications}</h3>
                      {unreadCount > 0 && (
                        <button onClick={markAllAsRead} className="text-xs text-blue-600 hover:text-blue-800 font-medium">
                          {t.markAllRead}
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
                          <p className="text-sm">{t.noNotifications}</p>
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
                                <div className="w-2 h-2 bg-blue-600 rounded-full flex-shrink-0 mt-2"></div>
                              )}
                            </div>
                          </div>
                        ))
                      )}
                    </div>

                    {/* Footer */}
                    {notifications.length > 0 && (
                      <div className="px-4 py-3 border-t border-gray-200 bg-gray-50 text-center">
                        <a href="<?= BASE_URL ?>/notifications" className="text-sm text-blue-600 hover:text-blue-800 font-medium">
                          {t.viewAll}
                        </a>
                      </div>
                    )}
                  </div>
                )}
              </div>

              {/* Menu Profil */}
              <div className="relative ml-2">
                <button
                  onClick={() => {
                    if (window.innerWidth >= 768) {
                      setDropdownOpen(!dropdownOpen);
                    }
                  }}
                  className="flex items-center space-x-2 focus:outline-none hover:bg-gray-50 rounded-lg px-2 py-1 transition-colors"
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
                <div className="absolute right-0 mt-5 w-80 bg-white border rounded-lg shadow-lg py-3 z-50">
                  {/* Info utilisateur */}
                  <div className="px-5 py-4 border-b border-gray-100">
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
                        <p className="font-semibold text-gray-900 text-base">{userInfo.pseudo || userInfo.name}</p>
                        <p className="text-sm text-gray-500 mt-1">{userInfo.email}</p>
                        {userInfo.role === 'admin' && (
                          <span className="inline-flex items-center px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full mt-2">
                            Administrateur
                          </span>
                        )}
                      </div>
                    </div>
                  </div>

                  {/* Menu items */}
                  <div className="py-2">
                    <a href="<?= BASE_URL ?>/Dashboard" className="flex items-center px-5 py-3 text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                      <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 mr-3 text-gray-400" fill="none"
                           viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                              d="M3 3h7v7H3V3zM14 3h7v4h-7V3zM3 14h7v7H3v-7zM14 14h7v7h-7v-7z"/>
                      </svg>
                      Dashboard
                    </a>
                    <a href="<?= BASE_URL ?>/profil?id=<?= $user['id'] ?>" className="flex items-center px-5 py-3 text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                      <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 mr-3 text-gray-400" fill="none"
                           viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                      </svg>
                      Voir mon profil
                    </a>
                    <a href="<?= BASE_URL ?>/Parametres?section=profile" className="flex items-center px-5 py-3 text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 2122 2122"
                        className="w-6 h-6 mr-3 text-gray-400"
                        fill="currentColor"
                      >
                        <path d="M1909.18,1148.97v-176.63h-107.48c-4.98-42.32-13.52-83.56-25.3-123.41l98.63-42.43-69.8-162.26-98.67,42.44c-17.77-30.62-37.65-59.9-59.45-87.57l76-76-124.9-124.9-75.99,76c-32.94-25.95-68.09-49.18-105.16-69.37l39.75-99.74-164.09-65.39-39.75,99.76c-33.78-8.99-68.49-15.66-103.98-19.85v-107.49h-176.63v107.49c-42.34,4.99-83.56,13.52-123.41,25.29l-42.43-98.63-162.25,69.8,42.44,98.67c-30.64,17.77-59.9,37.65-87.57,59.45l-76-76-124.9,124.9,76,75.99c-25.94,32.94-49.18,68.09-69.37,105.16l-99.74-39.75-65.39,164.09,99.76,39.75c-8.99,33.78-15.66,68.49-19.85,103.98h-107.49v176.63h107.49c4.98,42.34,13.52,83.56,25.29,123.41l-98.63,42.43,69.8,162.26,98.67-42.44c17.77,30.62,37.65,59.9,59.45,87.57l-76,76,124.9,124.9,75.99-76c32.94,25.95,68.09,49.18,105.16,69.37l-39.75,99.74,164.09,65.39,39.75-99.76c33.78,8.99,68.49,15.66,103.98,19.85v107.49h176.63v-107.49c42.34-4.99,83.56-13.52,123.41-25.3l42.43,98.63,162.25-69.8-42.44-98.67c30.64-17.77,59.9-37.65,87.57-59.45l76,76,124.9-124.9-76-75.99c25.94-32.94,49.18-68.09,69.37-105.16l99.74,39.75,65.39-164.09-99.76-39.75c8.99-33.78,15.66-68.49,19.85-103.98h107.49ZM1061,1412.12c-193.51,0-350.61-157.1-350.61-350.61s157.1-350.61,350.61-350.61,350.61,157.1,350.61,350.61-157.1,350.61-350.61,350.61Z"/>
                      </svg>
                      Paramètres
                    </a>
                  </div>
                  <div className="border-t border-gray-100 pt-2">
                    <a href="<?= BASE_URL ?>/logout" className="flex items-center px-5 py-3 text-base font-medium text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors">
                      <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 mr-3 text-red-400" fill="none"
                           viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
                      </svg>
                      Déconnexion
                    </a>
                  </div>
                </div>
              )}
              </div>
            </div>
          ) : (
            /* Boutons connexion/inscription pour utilisateurs non connectés */
            <div className="flex items-center space-x-3">
              <a
                href="<?= BASE_URL ?>/Autentification?mode=login"
                className="text-gray-700 hover:text-gray-900 font-medium px-3 py-2 rounded-md transition-colors"
              >
                Connexion
              </a>
              <a
                href="<?= BASE_URL ?>/Autentification?mode=register"
                className="bg-black text-white hover:bg-gray-800 font-medium px-4 py-2 rounded-md transition-colors"
              >
                S'inscrire
              </a>
            </div>
          )}

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

            {userInfo.isLoggedIn ? (
              <div className="mt-2 border-t pt-2">
                {/* Info utilisateur mobile */}
                <div className="flex items-center space-x-3 px-4 py-3 bg-gray-50 rounded-lg mb-2">
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
                    <p className="font-medium text-gray-900">{userInfo.pseudo || userInfo.name}</p>
                    {userInfo.role === 'admin' && (
                      <span className="text-xs text-red-600 font-medium">Administrateur</span>
                    )}
                  </div>
                </div>

                {/* Menu liens */}
                <a href="<?= BASE_URL ?>/pages/Dashboard" className="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-2" fill="none"
                       viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                          d="M3 3h7v7H3V3zM14 3h7v4h-7V3zM3 14h7v7H3v-7zM14 14h7v7h-7v-7z"/>
                  </svg>
                  Dashboard
                </a>
                <a href="<?= BASE_URL ?>/Parametres?section=profile" className="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
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
                <a href="<?= BASE_URL ?>/logout" className="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-gray-100 rounded">
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-2" fill="none"
                       viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
                  </svg>
                  Déconnexion
                </a>
              </div>
            ) : (
              /* Liens connexion/inscription pour mobile */
              <div className="mt-2 border-t pt-2 space-y-2">
                <a href="<?= BASE_URL ?>/Autentification?mode=login" className="block bg-white text-center px-4 py-2 rounded shadow">
                  Connexion
                </a>
                <a href="<?= BASE_URL ?>/Autentification?mode=register" className="block bg-black text-white text-center px-4 py-2 rounded shadow">
                  S'inscrire
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
