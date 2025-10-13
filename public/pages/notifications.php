<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/NotificationService.php';

// Vérifie si l'utilisateur est connecté
isUserLoggedIn(true);

$user = getCurrentUser();
$pdo = getDBConnection();
$currentPage = 'notifications.php';

// Inclure le header
include __DIR__ . '/../../includes/Header.php';

// Initialiser le service de notifications
$notificationService = new NotificationService($pdo);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Novatis</title>

    <!-- Variables CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/variables.css">

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- React & Babel -->
    <script src="https://unpkg.com/react@18/umd/react.development.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

    <style>
        body {
            font-family: var(--font-tinos);
            background-color: var(--color-bg);
        }

        .notification-item {
            transition: all 0.3s ease;
        }

        .notification-item:hover {
            transform: translateX(4px);
        }

        .notification-unread {
            background: linear-gradient(to right, #eff6ff, #ffffff);
            border-left: 4px solid var(--color-red);
        }

        .notification-read {
            background: #ffffff;
            border-left: 4px solid #e5e7eb;
            opacity: 0.8;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }
    </style>
</head>

<body class="pt-24 min-h-screen">
    <div id="notifications-root"></div>

    <script type="text/babel">
        const { useState, useEffect } = React;

        const NotificationsPage = () => {
            const [notifications, setNotifications] = useState([]);
            const [filter, setFilter] = useState('all'); // all, unread, read
            const [loading, setLoading] = useState(true);

            useEffect(() => {
                loadNotifications();
            }, [filter]);

            const loadNotifications = async () => {
                setLoading(true);
                try {
                    const unreadParam = filter === 'unread' ? '&unread=true' : '';
                    const response = await fetch(`<?= BASE_URL ?>/api/notifications/get.php?action=list${unreadParam}&limit=100`);
                    const data = await response.json();

                    if (data.success) {
                        let filteredNotifs = data.notifications;
                        if (filter === 'read') {
                            filteredNotifs = data.notifications.filter(n => n.is_read == 1);
                        }
                        setNotifications(filteredNotifs);
                    }
                } catch (error) {
                    console.error('Erreur:', error);
                } finally {
                    setLoading(false);
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
                        loadNotifications();
                    }
                } catch (error) {
                    console.error('Erreur:', error);
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
                    console.error('Erreur:', error);
                }
            };

            const deleteNotification = async (notificationId) => {
                if (!confirm('Supprimer cette notification ?')) return;

                try {
                    const response = await fetch('<?= BASE_URL ?>/api/notifications/update.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'delete', notification_id: notificationId })
                    });

                    const data = await response.json();
                    if (data.success) {
                        loadNotifications();
                    }
                } catch (error) {
                    console.error('Erreur:', error);
                }
            };

            const getNotificationIcon = (type) => {
                const icons = {
                    order: '🛒',
                    message: '💬',
                    security: '🔒',
                    payment: '💳',
                    service_update: '🔔'
                };
                return icons[type] || '📢';
            };

            const formatDate = (dateString) => {
                const date = new Date(dateString);
                const now = new Date();
                const diff = Math.floor((now - date) / 1000);

                if (diff < 60) return 'À l\'instant';
                if (diff < 3600) return `Il y a ${Math.floor(diff / 60)} min`;
                if (diff < 86400) return `Il y a ${Math.floor(diff / 3600)} h`;
                if (diff < 604800) return `Il y a ${Math.floor(diff / 86400)} j`;

                return date.toLocaleDateString('fr-FR', {
                    day: 'numeric',
                    month: 'short',
                    year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
                });
            };

            const unreadCount = notifications.filter(n => !n.is_read).length;

            return React.createElement('div', { className: "max-w-4xl mx-auto px-4 py-8" },
                // Header
                React.createElement('div', { className: "bg-white rounded-lg shadow-sm p-6 mb-6" },
                    React.createElement('div', { className: "flex items-center justify-between mb-4" },
                        React.createElement('h1', { className: "text-3xl font-bold text-gray-900" }, '🔔 Notifications'),
                        React.createElement('div', { className: "flex gap-2" },
                            unreadCount > 0 && React.createElement('button', {
                                onClick: markAllAsRead,
                                className: "px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium"
                            }, `Tout marquer comme lu (${unreadCount})`)
                        )
                    ),

                    // Filtres
                    React.createElement('div', { className: "flex gap-2" },
                        React.createElement('button', {
                            onClick: () => setFilter('all'),
                            className: `px-4 py-2 rounded-lg font-medium transition ${
                                filter === 'all'
                                    ? 'bg-red-600 text-white'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                            }`
                        }, 'Toutes'),
                        React.createElement('button', {
                            onClick: () => setFilter('unread'),
                            className: `px-4 py-2 rounded-lg font-medium transition ${
                                filter === 'unread'
                                    ? 'bg-red-600 text-white'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                            }`
                        }, `Non lues (${unreadCount})`),
                        React.createElement('button', {
                            onClick: () => setFilter('read'),
                            className: `px-4 py-2 rounded-lg font-medium transition ${
                                filter === 'read'
                                    ? 'bg-red-600 text-white'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                            }`
                        }, 'Lues')
                    )
                ),

                // Liste des notifications
                loading
                    ? React.createElement('div', { className: "text-center py-12" },
                        React.createElement('div', { className: "animate-spin rounded-full h-12 w-12 border-b-2 border-red-600 mx-auto" })
                    )
                    : notifications.length === 0
                    ? React.createElement('div', { className: "bg-white rounded-lg shadow-sm p-12 text-center" },
                        React.createElement('div', { className: "text-6xl mb-4" }, '📭'),
                        React.createElement('h2', { className: "text-xl font-semibold text-gray-900 mb-2" }, 'Aucune notification'),
                        React.createElement('p', { className: "text-gray-600" }, 'Vous n\'avez aucune notification pour le moment')
                    )
                    : React.createElement('div', { className: "space-y-3" },
                        notifications.map((notif) =>
                            React.createElement('div', {
                                key: notif.id,
                                className: `notification-item ${
                                    notif.is_read ? 'notification-read' : 'notification-unread'
                                } rounded-lg p-4 shadow-sm fade-in cursor-pointer`
                            },
                                React.createElement('div', { className: "flex items-start justify-between" },
                                    React.createElement('div', {
                                        className: "flex-1",
                                        onClick: () => {
                                            if (!notif.is_read) markAsRead(notif.id);
                                            if (notif.link) window.location.href = notif.link;
                                        }
                                    },
                                        React.createElement('div', { className: "flex items-start gap-3" },
                                            React.createElement('span', { className: "text-3xl" }, getNotificationIcon(notif.type)),
                                            React.createElement('div', { className: "flex-1" },
                                                React.createElement('h3', { className: "font-semibold text-gray-900 mb-1" }, notif.title),
                                                React.createElement('p', { className: "text-gray-700 text-sm mb-2" }, notif.message),
                                                React.createElement('div', { className: "flex items-center gap-3 text-xs text-gray-500" },
                                                    React.createElement('span', null, formatDate(notif.created_at)),
                                                    !notif.is_read && React.createElement('span', {
                                                        className: "px-2 py-1 bg-red-100 text-red-700 rounded-full font-medium"
                                                    }, 'Nouveau')
                                                )
                                            )
                                        )
                                    ),
                                    // Actions
                                    React.createElement('div', { className: "flex gap-2 ml-2" },
                                        !notif.is_read && React.createElement('button', {
                                            onClick: (e) => {
                                                e.stopPropagation();
                                                markAsRead(notif.id);
                                            },
                                            className: "p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition",
                                            title: "Marquer comme lu"
                                        },
                                            React.createElement('svg', { className: "w-5 h-5", fill: "none", stroke: "currentColor", viewBox: "0 0 24 24" },
                                                React.createElement('path', {
                                                    strokeLinecap: "round",
                                                    strokeLinejoin: "round",
                                                    strokeWidth: 2,
                                                    d: "M5 13l4 4L19 7"
                                                })
                                            )
                                        ),
                                        React.createElement('button', {
                                            onClick: (e) => {
                                                e.stopPropagation();
                                                deleteNotification(notif.id);
                                            },
                                            className: "p-2 text-red-600 hover:bg-red-50 rounded-lg transition",
                                            title: "Supprimer"
                                        },
                                            React.createElement('svg', { className: "w-5 h-5", fill: "none", stroke: "currentColor", viewBox: "0 0 24 24" },
                                                React.createElement('path', {
                                                    strokeLinecap: "round",
                                                    strokeLinejoin: "round",
                                                    strokeWidth: 2,
                                                    d: "M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                })
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    )
            );
        };

        ReactDOM.createRoot(document.getElementById('notifications-root')).render(
            React.createElement(NotificationsPage)
        );
    </script>
</body>
</html>
