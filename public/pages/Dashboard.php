<?php
require_once __DIR__ . '/../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

// Récupération des données utilisateur
$user = $_SESSION['user'];
$isAdmin = isset($user['role']) && $user['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Novatis | Dashboard</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>

    <!-- Variables CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/variables.css">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Config pour utiliser vos variables CSS -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'custom-bg': '#e8e8e8',
                        'custom-white': '#e8e8e8',
                        'custom-black': '#1f2020',
                        'custom-red': '#B41200',
                        'accent-1': '#1f2020',
                        'accent-2': '#7F0D00',
                        'hover-1': '#464646',
                        'hover-2': '#E04830'
                    }
                }
            }
        }
    </script>

    <!-- React & ReactDOM -->
    <script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

    <style>
        body {
            font-family: var(--font-tinos);
            background-color: var(--color-bg);
            color: var(--color-black);
        }

        .btn-primary {
            background: var(--color-red);
            color: var(--color-white);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--color-hover-2);
        }

        .btn-secondary {
            background: var(--color-white);
            color: var(--color-red);
            border: 1px solid var(--color-red);
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: var(--color-red);
            color: var(--color-white);
        }

        .card-custom {
            background: var(--color-white);
            box-shadow: var(--shadow-md);
            border-radius: 8px;
        }

        .nav-item-active {
            background: var(--color-red) !important;
            color: var(--color-white) !important;
        }

        .nav-item:hover {
            background: var(--color-hover-4);
        }

        .text-primary {
            color: var(--color-red);
        }

        .text-primary:hover {
            color: var(--color-hover-2);
        }

        .sidebar-mobile {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            top: 80px; /* Décaler en dessous du header */
            height: calc(100vh - 80px); /* Ajuster la hauteur */
        }

        .sidebar-mobile.open {
            transform: translateX(0);
        }

        @media (min-width: 1024px) {
            .sidebar-mobile {
                top: 80px; /* Même pour desktop */
                height: calc(100vh - 80px);
                position: fixed;
            }
        }

        .message-input {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            font-family: var(--font-tinos);
        }

        .message-input:focus {
            outline: none;
            border-color: var(--color-red);
            box-shadow: 0 0 0 3px rgba(180, 18, 0, 0.1);
        }

        .status-pending { background: #fef3c7; color: #92400e; }
        .status-in_progress { background: #dbeafe; color: #1e40af; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-completed { background: #dcfce7; color: #166534; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .status-open { background: #fef3c7; color: #92400e; }
        .status-resolved { background: #dcfce7; color: #166534; }
        .status-closed { background: #f3f4f6; color: #6b7280; }

        .modal-overlay {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(2px);
        }

        #dashboard-root {
            padding-top: 80px; /* Pour compenser le header fixe */
        }

        .notifications-dropdown {
            animation: fadeInDown 0.2s ease-out;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .notification-item {
            transition: all 0.2s ease;
        }

        .notification-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .star-rating button {
            cursor: pointer;
            user-select: none;
            outline: none;
        }

        .star-rating button:focus {
            outline: 2px solid #fbbf24;
            outline-offset: 2px;
        }

        .star-filled {
            color: #fbbf24;
            text-shadow: 0 0 5px rgba(251, 191, 36, 0.3);
        }

        .star-empty {
            color: #d1d5db;
        }

        .star-hover {
            color: #f59e0b;
            transform: scale(1.1);
        }
    </style>
</head>

<body style="background-color: var(--color-bg); color: var(--color-black);">
    <?php include __DIR__ . '/../../includes/Header.php';?>
    <div id="dashboard-root"></div>

    <script type="text/babel">
        // Fix pour les hooks React avec CDN
        const React = window.React;
        const ReactDOM = window.ReactDOM;
        const { useState, useEffect } = React;

        // Données utilisateur depuis PHP
        const userData = {
            id: <?= json_encode($user['id'] ?? 0) ?>,
            name: <?= json_encode($user['name'] ?? 'Utilisateur') ?>,
            email: <?= json_encode($user['email'] ?? '') ?>,
            role: <?= json_encode($user['role'] ?? 'user') ?>,
            avatar: <?= json_encode($user['avatar'] ?? null) ?>,
            rating: <?= json_encode($user['rating'] ?? 0) ?>,
            baseUrl: <?= json_encode(BASE_URL) ?>
        };

        const isAdmin = <?= json_encode($isAdmin) ?>;

        // SVG Avatar anonyme
        const AnonymousAvatar = ({ className = "", size = 40 }) => (
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

        // API Functions
        const apiCall = async (endpoint, method = 'GET', data = null) => {
            try {
                const options = {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                    }
                };

                if (data && method !== 'GET') {
                    options.body = JSON.stringify(data);
                }

                const response = await fetch(`${userData.baseUrl}/api/${endpoint}`, options);
                return await response.json();
            } catch (error) {
                console.error('Erreur API:', error);
                return { success: false, error: error.message };
            }
        };

        // Composant Dropdown Notifications
        const NotificationsDropdown = ({ notifications, unreadCount, onClose, onMarkAsRead, onMarkAllAsRead, onDelete, onNavigate }) => {
            const getNotificationIcon = (type) => {
                const icons = {
                    order: 'fas fa-briefcase',
                    message: 'fas fa-envelope',
                    system: 'fas fa-info-circle',
                    payment: 'fas fa-euro-sign',
                    service: 'fas fa-cog',
                    review: 'fas fa-star'
                };
                return icons[type] || 'fas fa-bell';
            };

            const getNotificationColor = (type) => {
                const colors = {
                    order: 'text-blue-600',
                    message: 'text-green-600',
                    system: 'text-gray-600',
                    payment: 'text-yellow-600',
                    service: 'text-purple-600',
                    review: 'text-orange-600'
                };
                return colors[type] || 'text-gray-600';
            };

            const formatTimeAgo = (timestamp) => {
                const now = new Date();
                const time = new Date(timestamp);
                const diffInMinutes = Math.floor((now - time) / 60000);

                if (diffInMinutes < 1) return 'À l\'instant';
                if (diffInMinutes < 60) return `Il y a ${diffInMinutes} min`;
                if (diffInMinutes < 1440) return `Il y a ${Math.floor(diffInMinutes / 60)} h`;
                return `Il y a ${Math.floor(diffInMinutes / 1440)} j`;
            };

            const handleNotificationClick = (notification) => {
                // Marquer comme lue si pas encore lue
                if (!notification.is_read) {
                    onMarkAsRead(notification.id);
                }

                // Rediriger vers l'action si elle existe
                if (notification.action_url) {
                    // Si c'est un lien interne au dashboard
                    if (notification.action_url.includes('dashboard')) {
                        const urlParams = new URLSearchParams(notification.action_url.split('?')[1]);
                        const tab = urlParams.get('tab');
                        if (tab && onNavigate) {
                            onNavigate(tab);
                        }
                    } else {
                        window.location.href = notification.action_url;
                    }
                }
                onClose();
            };

            // Fermer le dropdown si on clique à l'extérieur
            React.useEffect(() => {
                const handleClickOutside = (event) => {
                    if (!event.target.closest('.notifications-dropdown')) {
                        onClose();
                    }
                };
                document.addEventListener('mousedown', handleClickOutside);
                return () => document.removeEventListener('mousedown', handleClickOutside);
            }, [onClose]);

            return React.createElement('div', {
                className: "notifications-dropdown absolute right-0 top-12 w-96 bg-white border rounded-lg shadow-lg z-50 max-h-96 overflow-hidden"
            },
                // Header
                React.createElement('div', { className: "flex items-center justify-between p-4 border-b bg-gray-50" },
                    React.createElement('h3', { className: "font-medium text-gray-900" },
                        `Notifications${unreadCount > 0 ? ` (${unreadCount} non lues)` : ''}`
                    ),
                    React.createElement('div', { className: "flex items-center space-x-2" },
                        unreadCount > 0 && React.createElement('button', {
                            onClick: onMarkAllAsRead,
                            className: "text-xs text-blue-600 hover:text-blue-800 font-medium"
                        }, "Tout marquer lu"),
                        React.createElement('button', {
                            onClick: onClose,
                            className: "text-gray-500 hover:text-gray-700"
                        }, React.createElement('i', { className: "fas fa-times" }))
                    )
                ),

                // Liste des notifications
                React.createElement('div', { className: "max-h-80 overflow-y-auto" },
                    notifications.length === 0 ?
                        React.createElement('div', { className: "p-8 text-center text-gray-500" },
                            React.createElement('i', { className: "fas fa-bell-slash text-3xl mb-2 opacity-50" }),
                            React.createElement('p', null, "Aucune notification")
                        ) :
                        notifications.map(notification =>
                            React.createElement('div', {
                                key: notification.id,
                                onClick: () => handleNotificationClick(notification),
                                className: `notification-item p-4 border-b hover:bg-gray-50 cursor-pointer transition-colors group ${
                                    !notification.is_read ? 'bg-blue-50 border-l-4 border-l-blue-500' : ''
                                }`
                            },
                                React.createElement('div', { className: "flex items-start space-x-3" },
                                    React.createElement('div', {
                                        className: `flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center ${
                                            !notification.is_read ? 'bg-blue-100' : 'bg-gray-100'
                                        }`
                                    },
                                        React.createElement('i', {
                                            className: `${getNotificationIcon(notification.type)} text-sm ${getNotificationColor(notification.type)}`
                                        })
                                    ),
                                    React.createElement('div', { className: "flex-1 min-w-0" },
                                        React.createElement('div', { className: "flex items-start justify-between" },
                                            React.createElement('h4', {
                                                className: `text-sm font-medium text-gray-900 ${!notification.is_read ? 'font-semibold' : ''}`
                                            }, notification.title),
                                            React.createElement('div', { className: "flex items-center space-x-1 ml-2" },
                                                React.createElement('span', { className: "text-xs text-gray-500" },
                                                    formatTimeAgo(notification.created_at)
                                                ),
                                                React.createElement('button', {
                                                    onClick: (e) => {
                                                        e.stopPropagation();
                                                        onDelete(notification.id);
                                                    },
                                                    className: "text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity"
                                                },
                                                    React.createElement('i', { className: "fas fa-trash text-xs" })
                                                )
                                            )
                                        ),
                                        React.createElement('p', { className: "text-sm text-gray-600 mt-1 line-clamp-2" },
                                            notification.message
                                        ),
                                        !notification.is_read && React.createElement('div', {
                                            className: "w-2 h-2 bg-blue-500 rounded-full mt-1"
                                        })
                                    )
                                )
                            )
                        )
                ),

                // Footer avec actions
                notifications.length > 0 && React.createElement('div', { className: "p-3 border-t bg-gray-50 text-center" },
                    React.createElement('button', {
                        className: "text-sm text-blue-600 hover:text-blue-800 font-medium"
                    }, "Voir toutes les notifications")
                )
            );
        };

        // Notifications Tab Component
        const NotificationsTab = ({ notifications, unreadCount, onMarkAsRead, onMarkAllAsRead, onDelete, onNavigate }) => {
            const getNotificationIcon = (type) => {
                const icons = {
                    order: 'fas fa-briefcase',
                    message: 'fas fa-envelope',
                    system: 'fas fa-info-circle',
                    payment: 'fas fa-euro-sign',
                    service: 'fas fa-cog',
                    review: 'fas fa-star'
                };
                return icons[type] || 'fas fa-bell';
            };

            const getNotificationColor = (type) => {
                const colors = {
                    order: 'text-blue-600',
                    message: 'text-green-600',
                    system: 'text-gray-600',
                    payment: 'text-yellow-600',
                    service: 'text-purple-600',
                    review: 'text-orange-600'
                };
                return colors[type] || 'text-gray-600';
            };

            const formatTimeAgo = (timestamp) => {
                const now = new Date();
                const time = new Date(timestamp);
                const diffInMinutes = Math.floor((now - time) / 60000);

                if (diffInMinutes < 1) return 'À l\'instant';
                if (diffInMinutes < 60) return `Il y a ${diffInMinutes} min`;
                if (diffInMinutes < 1440) return `Il y a ${Math.floor(diffInMinutes / 60)} h`;
                return `Il y a ${Math.floor(diffInMinutes / 1440)} j`;
            };

            const handleNotificationClick = (notification) => {
                // Marquer comme lue si pas encore lue
                if (!notification.is_read) {
                    onMarkAsRead(notification.id);
                }

                // Rediriger vers l'action si elle existe
                if (notification.action_url && notification.action_url.includes('dashboard')) {
                    const urlParams = new URLSearchParams(notification.action_url.split('?')[1]);
                    const tab = urlParams.get('tab');
                    if (tab && onNavigate) {
                        onNavigate(tab);
                    }
                }
            };

            return React.createElement('div', { className: "bg-white rounded-lg p-6", style: {boxShadow: 'var(--shadow-md)'} },
                // Header avec actions
                React.createElement('div', { className: "flex items-center justify-between mb-6" },
                    React.createElement('h3', { className: "text-lg font-bold" },
                        `Notifications${unreadCount > 0 ? ` (${unreadCount} non lues)` : ''}`
                    ),
                    unreadCount > 0 && React.createElement('button', {
                        onClick: onMarkAllAsRead,
                        className: "btn-secondary px-4 py-2 rounded-lg text-sm"
                    }, "Marquer tout comme lu")
                ),

                // Liste des notifications
                notifications.length === 0 ?
                    React.createElement('div', { className: "text-center py-12 text-gray-500" },
                        React.createElement('i', { className: "fas fa-bell-slash text-4xl mb-4 opacity-50" }),
                        React.createElement('h4', { className: "text-lg font-medium mb-2" }, "Aucune notification"),
                        React.createElement('p', null, "Vous n'avez pas encore de notifications.")
                    ) :
                    React.createElement('div', { className: "space-y-3" },
                        notifications.map(notification =>
                            React.createElement('div', {
                                key: notification.id,
                                onClick: () => handleNotificationClick(notification),
                                className: `p-4 rounded-lg border cursor-pointer hover:shadow-md transition-all group ${
                                    !notification.is_read
                                        ? 'bg-blue-50 border-blue-200 hover:bg-blue-100'
                                        : 'bg-white border-gray-200 hover:bg-gray-50'
                                }`
                            },
                                React.createElement('div', { className: "flex items-start space-x-4" },
                                    React.createElement('div', {
                                        className: `flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center ${
                                            !notification.is_read ? 'bg-blue-100' : 'bg-gray-100'
                                        }`
                                    },
                                        React.createElement('i', {
                                            className: `${getNotificationIcon(notification.type)} ${getNotificationColor(notification.type)}`
                                        })
                                    ),
                                    React.createElement('div', { className: "flex-1 min-w-0" },
                                        React.createElement('div', { className: "flex items-start justify-between" },
                                            React.createElement('h4', {
                                                className: `font-medium ${!notification.is_read ? 'font-semibold text-gray-900' : 'text-gray-800'}`
                                            }, notification.title),
                                            React.createElement('div', { className: "flex items-center space-x-2 ml-4" },
                                                React.createElement('span', { className: "text-xs text-gray-500 whitespace-nowrap" },
                                                    formatTimeAgo(notification.created_at)
                                                ),
                                                React.createElement('button', {
                                                    onClick: (e) => {
                                                        e.stopPropagation();
                                                        onDelete(notification.id);
                                                    },
                                                    className: "text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity p-1"
                                                },
                                                    React.createElement('i', { className: "fas fa-trash text-xs" })
                                                )
                                            )
                                        ),
                                        React.createElement('p', { className: "text-sm text-gray-600 mt-1" },
                                            notification.message
                                        ),
                                        !notification.is_read && React.createElement('div', { className: "flex items-center mt-2" },
                                            React.createElement('div', { className: "w-2 h-2 bg-blue-500 rounded-full mr-2" }),
                                            React.createElement('span', { className: "text-xs text-blue-600 font-medium" }, "Nouveau")
                                        )
                                    )
                                )
                            )
                        )
                    )
            );
        };

        // Stars Display Component (read-only)
        const StarsDisplay = ({ rating = 0, size = "text-sm", showNumber = true }) => {
            const safeRating = Math.max(0, Math.min(5, rating || 0));
            const fullStars = Math.floor(safeRating);
            const hasHalfStar = safeRating % 1 >= 0.5;

            return React.createElement('div', { className: "flex items-center space-x-1" },
                React.createElement('div', { className: "flex" },
                    [1, 2, 3, 4, 5].map(i => {
                        let starClass = `${size}`;
                        let starSymbol = "☆";

                        if (i <= fullStars) {
                            starClass += " text-yellow-400";
                            starSymbol = "★";
                        } else if (i === fullStars + 1 && hasHalfStar) {
                            starClass += " text-yellow-400";
                            starSymbol = "⭐";
                        } else {
                            starClass += " text-gray-300";
                        }

                        return React.createElement('span', {
                            key: i,
                            className: starClass
                        }, starSymbol);
                    })
                ),
                showNumber && React.createElement('span', {
                    className: "text-sm text-gray-600 ml-2"
                }, `(${safeRating.toFixed(1)})`)
            );
        };

        // Rating Modal Component
        const RatingModal = ({ isOpen, onClose, order, onSubmit }) => {
            const [rating, setRating] = useState(5);
            const [hoverRating, setHoverRating] = useState(0);
            const [comment, setComment] = useState('');
            const [submitting, setSubmitting] = useState(false);

            const getRatingMessage = (rating) => {
                const messages = {
                    1: "😞 Très décevant - Le travail ne correspond pas du tout à vos attentes",
                    2: "😕 Décevant - Le travail présente des lacunes importantes",
                    3: "😐 Correct - Le travail est acceptable mais peut être amélioré",
                    4: "😊 Bien - Le travail est de bonne qualité avec quelques points d'amélioration",
                    5: "🌟 Excellent - Le travail dépasse vos attentes !"
                };
                return messages[rating] || "";
            };

            const handleSubmit = async (e) => {
                e.preventDefault();
                if (submitting) return;

                setSubmitting(true);
                try {
                    await onSubmit(order.id, rating, comment);
                    onClose();
                    setRating(5);
                    setHoverRating(0);
                    setComment('');
                } catch (error) {
                    console.error('Erreur soumission évaluation:', error);
                } finally {
                    setSubmitting(false);
                }
            };

            if (!isOpen) return null;

            return React.createElement('div', {
                className: "fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            },
                React.createElement('div', {
                    className: "bg-white rounded-lg p-6 w-full max-w-md"
                },
                    React.createElement('h3', { className: "text-lg font-bold mb-4" },
                        "Évaluer cette commande"
                    ),
                    React.createElement('div', { className: "mb-4" },
                        React.createElement('p', { className: "text-sm text-gray-600 mb-2" },
                            `Service: ${order?.service_title || 'Service'}`
                        ),
                        React.createElement('p', { className: "text-sm text-gray-600" },
                            `Prestataire: ${order?.seller_name || 'Prestataire'}`
                        )
                    ),

                    React.createElement('form', { onSubmit: handleSubmit },
                        // Rating stars
                        React.createElement('div', { className: "mb-4" },
                            React.createElement('label', { className: "block text-sm font-medium mb-2" },
                                "Votre évaluation"
                            ),
                            React.createElement('div', { className: "flex items-center space-x-2 mb-3" },
                                React.createElement('div', { className: "star-rating flex space-x-1" },
                                    [1, 2, 3, 4, 5].map(star =>
                                        React.createElement('button', {
                                            key: star,
                                            type: "button",
                                            onClick: () => setRating(star),
                                            onMouseEnter: () => setHoverRating(star),
                                            onMouseLeave: () => setHoverRating(0),
                                            className: `text-4xl transition-all duration-200 transform hover:scale-125 ${
                                                star <= (hoverRating || rating) ? 'star-filled' : 'star-empty'
                                            } ${hoverRating === star ? 'star-hover' : ''}`,
                                            style: {
                                                filter: star <= (hoverRating || rating) ? 'drop-shadow(0 2px 4px rgba(251, 191, 36, 0.4))' : 'none'
                                            }
                                        }, star <= (hoverRating || rating) ? "★" : "☆")
                                    )
                                ),
                                React.createElement('span', { className: "text-sm font-medium text-gray-600 ml-2" },
                                    hoverRating ? `${hoverRating}/5` : `${rating}/5`
                                )
                            ),
                            React.createElement('div', { className: "mt-2 p-3 bg-gray-50 rounded-lg" },
                                React.createElement('p', { className: "text-sm text-gray-700 font-medium" },
                                    getRatingMessage(hoverRating || rating)
                                )
                            )
                        ),

                        // Comment
                        React.createElement('div', { className: "mb-6" },
                            React.createElement('label', { className: "block text-sm font-medium mb-2" },
                                "Commentaire (optionnel)"
                            ),
                            React.createElement('textarea', {
                                value: comment,
                                onChange: (e) => setComment(e.target.value),
                                placeholder: "Partagez votre expérience avec ce prestataire...",
                                className: "w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none",
                                rows: 4
                            })
                        ),

                        // Buttons
                        React.createElement('div', { className: "flex space-x-3" },
                            React.createElement('button', {
                                type: "button",
                                onClick: onClose,
                                className: "flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                            }, "Annuler"),
                            React.createElement('button', {
                                type: "submit",
                                disabled: submitting,
                                className: `flex-1 px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors disabled:opacity-50`
                            }, submitting ? "Envoi..." : "Publier l'évaluation")
                        )
                    )
                )
            );
        };

        // Reviews Tab Component
        const ReviewsTab = ({ userData }) => {
            const [reviews, setReviews] = useState([]);
            const [loading, setLoading] = useState(true);

            useEffect(() => {
                loadReviews();
            }, []);

            const loadReviews = async () => {
                setLoading(true);
                try {
                    const result = await apiCall('reviews.php');
                    if (result.success) {
                        setReviews(result.reviews);
                    }
                } catch (error) {
                    console.error('Erreur chargement évaluations:', error);
                } finally {
                    setLoading(false);
                }
            };

            const formatDate = (dateString) => {
                return new Date(dateString).toLocaleDateString('fr-FR', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            };

            if (loading) {
                return React.createElement('div', { className: "flex items-center justify-center h-64" },
                    React.createElement('div', { className: "text-lg" }, "Chargement des évaluations...")
                );
            }

            const receivedReviews = reviews.filter(review => review.reviewee_id == userData.id);
            const givenReviews = reviews.filter(review => review.reviewer_id == userData.id);

            return React.createElement('div', { className: "space-y-6" },
                // Statistiques
                React.createElement('div', { className: "grid grid-cols-1 md:grid-cols-3 gap-4" },
                    React.createElement('div', { className: "bg-white p-6 rounded-lg", style: {boxShadow: 'var(--shadow-md)'} },
                        React.createElement('div', { className: "text-center" },
                            React.createElement('div', { className: "text-2xl font-bold text-blue-600" },
                                receivedReviews.length
                            ),
                            React.createElement('p', { className: "text-sm text-gray-600 mt-1" },
                                "Évaluations reçues"
                            )
                        )
                    ),
                    React.createElement('div', { className: "bg-white p-6 rounded-lg", style: {boxShadow: 'var(--shadow-md)'} },
                        React.createElement('div', { className: "text-center" },
                            receivedReviews.length > 0 ?
                                React.createElement(StarsDisplay, {
                                    rating: receivedReviews.reduce((sum, r) => sum + r.rating, 0) / receivedReviews.length,
                                    size: "text-xl",
                                    showNumber: true
                                }) :
                                React.createElement('div', null,
                                    React.createElement('div', { className: "text-2xl font-bold text-gray-400" }, "—"),
                                    React.createElement('p', { className: "text-sm text-gray-600 mt-1" }, "Note moyenne")
                                ),
                            receivedReviews.length > 0 && React.createElement('p', { className: "text-sm text-gray-600 mt-1" },
                                "Note moyenne"
                            )
                        )
                    ),
                    React.createElement('div', { className: "bg-white p-6 rounded-lg", style: {boxShadow: 'var(--shadow-md)'} },
                        React.createElement('div', { className: "text-center" },
                            React.createElement('div', { className: "text-2xl font-bold text-green-600" },
                                givenReviews.length
                            ),
                            React.createElement('p', { className: "text-sm text-gray-600 mt-1" },
                                "Évaluations données"
                            )
                        )
                    )
                ),

                // Évaluations reçues
                React.createElement('div', { className: "bg-white rounded-lg p-6", style: {boxShadow: 'var(--shadow-md)'} },
                    React.createElement('h3', { className: "text-lg font-bold mb-4" },
                        "Évaluations reçues"
                    ),
                    receivedReviews.length === 0 ?
                        React.createElement('div', { className: "text-center py-8 text-gray-500" },
                            React.createElement('i', { className: "fas fa-star text-4xl mb-4 opacity-50" }),
                            React.createElement('h4', { className: "text-lg font-medium mb-2" }, "Aucune évaluation reçue"),
                            React.createElement('p', null, "Vos clients pourront vous évaluer après livraison.")
                        ) :
                        React.createElement('div', { className: "space-y-4" },
                            receivedReviews.map(review =>
                                React.createElement('div', { key: review.id, className: "border rounded-lg p-4" },
                                    React.createElement('div', { className: "flex items-start justify-between mb-3" },
                                        React.createElement('div', null,
                                            React.createElement('h4', { className: "font-medium" },
                                                review.reviewer_name
                                            ),
                                            React.createElement('p', { className: "text-sm text-gray-600" },
                                                `Pour: ${review.service_title}`
                                            )
                                        ),
                                        React.createElement('div', { className: "text-right" },
                                            React.createElement(StarsDisplay, { rating: review.rating, showNumber: false }),
                                            React.createElement('p', { className: "text-xs text-gray-500 mt-1" },
                                                formatDate(review.created_at)
                                            )
                                        )
                                    ),
                                    review.comment && React.createElement('div', { className: "mt-3 p-3 bg-gray-50 rounded-lg" },
                                        React.createElement('p', { className: "text-sm text-gray-700 italic" },
                                            `"${review.comment}"`
                                        )
                                    )
                                )
                            )
                        )
                ),

                // Évaluations données
                givenReviews.length > 0 && React.createElement('div', { className: "bg-white rounded-lg p-6", style: {boxShadow: 'var(--shadow-md)'} },
                    React.createElement('h3', { className: "text-lg font-bold mb-4" },
                        "Évaluations données"
                    ),
                    React.createElement('div', { className: "space-y-4" },
                        givenReviews.map(review =>
                            React.createElement('div', { key: review.id, className: "border rounded-lg p-4" },
                                React.createElement('div', { className: "flex items-start justify-between mb-3" },
                                    React.createElement('div', null,
                                        React.createElement('h4', { className: "font-medium" },
                                            `Évaluation de ${review.reviewee_name}`
                                        ),
                                        React.createElement('p', { className: "text-sm text-gray-600" },
                                            `Pour: ${review.service_title}`
                                        )
                                    ),
                                    React.createElement('div', { className: "text-right" },
                                        React.createElement(StarsDisplay, { rating: review.rating, showNumber: false }),
                                        React.createElement('p', { className: "text-xs text-gray-500 mt-1" },
                                            formatDate(review.created_at)
                                        )
                                    )
                                ),
                                review.comment && React.createElement('div', { className: "mt-3 p-3 bg-gray-50 rounded-lg" },
                                    React.createElement('p', { className: "text-sm text-gray-700 italic" },
                                        `"${review.comment}"`
                                    )
                                )
                            )
                        )
                    )
                )
            );
        };

        // Dashboard Component
        const Dashboard = () => {
            const [activeTab, setActiveTab] = useState('overview');
            const [sidebarOpen, setSidebarOpen] = useState(false);
            const [data, setData] = useState({
                stats: {},
                services: [],
                orders: [],
                formerClients: [],
                portfolio: [],
                categories: [],
                unreadMessages: 0,
                messages: [],
                users: [],
                conversations: [],
                supportTickets: []
            });
            const [notifications, setNotifications] = useState([]);
            const [unreadCount, setUnreadCount] = useState(0);
            const [showNotifications, setShowNotifications] = useState(false);
            const [ratingModal, setRatingModal] = useState({ isOpen: false, order: null });
            const [loading, setLoading] = useState(true);

            // Load data on mount
            useEffect(() => {
                loadDashboardData();
                loadNotifications();
                // Rafraîchir les notifications toutes les 30 secondes
                const interval = setInterval(loadNotifications, 30000);
                return () => clearInterval(interval);
            }, []);

            const loadDashboardData = async () => {
                setLoading(true);

                try {
                    const result = await apiCall('dashboard.php');
                    if (result.success) {
                        setData(prevData => ({
                            ...prevData,
                            stats: result.data.stats,
                            services: result.data.services,
                            orders: result.data.orders,
                            formerClients: result.data.former_clients,
                            portfolio: result.data.portfolio,
                            categories: result.data.categories,
                            unreadMessages: result.data.unread_messages
                        }));
                    }
                } catch (error) {
                    console.error('Erreur lors du chargement:', error);
                }

                setLoading(false);
            };

            const loadNotifications = async () => {
                try {
                    const result = await apiCall('notifications.php');
                    if (result.success) {
                        setNotifications(result.notifications);
                        setUnreadCount(result.unread_count);
                    }
                } catch (error) {
                    console.error('Erreur chargement notifications:', error);
                }
            };

            const markNotificationAsRead = async (notificationId) => {
                try {
                    const result = await apiCall('notifications.php', 'PUT', { notification_id: notificationId });
                    if (result.success) {
                        loadNotifications(); // Recharger les notifications
                    }
                } catch (error) {
                    console.error('Erreur marquage notification:', error);
                }
            };

            const markAllAsRead = async () => {
                try {
                    const result = await apiCall('notifications.php', 'PUT', { mark_all_read: true });
                    if (result.success) {
                        loadNotifications(); // Recharger les notifications
                    }
                } catch (error) {
                    console.error('Erreur marquage toutes notifications:', error);
                }
            };

            const deleteNotification = async (notificationId) => {
                try {
                    const result = await apiCall('notifications.php', 'DELETE', { notification_id: notificationId });
                    if (result.success) {
                        loadNotifications(); // Recharger les notifications
                    }
                } catch (error) {
                    console.error('Erreur suppression notification:', error);
                }
            };

            const submitRating = async (orderId, rating, comment) => {
                try {
                    const result = await apiCall('reviews.php', 'POST', {
                        order_id: orderId,
                        rating: rating,
                        comment: comment
                    });

                    if (result.success) {
                        // Recharger les données
                        loadDashboardData();
                        loadNotifications();
                        alert('Évaluation publiée avec succès !');
                    } else {
                        throw new Error(result.error || 'Erreur lors de la publication');
                    }
                } catch (error) {
                    console.error('Erreur soumission évaluation:', error);
                    alert('Erreur: ' + error.message);
                    throw error;
                }
            };

            const openRatingModal = (order) => {
                setRatingModal({ isOpen: true, order });
            };

            const closeRatingModal = () => {
                setRatingModal({ isOpen: false, order: null });
            };

            const handleLogout = () => {
                window.location.href = userData.baseUrl + '/logout';
            };

            const renderContent = () => {
                if (loading) {
                    return React.createElement('div', { className: "flex items-center justify-center h-64" },
                        React.createElement('div', { className: "text-lg" }, "Chargement...")
                    );
                }

                switch(activeTab) {
                    case 'overview': return React.createElement(OverviewTab, { data, loadData: loadDashboardData });
                    case 'notifications': return React.createElement(NotificationsTab, { notifications, unreadCount, onMarkAsRead: markNotificationAsRead, onMarkAllAsRead: markAllAsRead, onDelete: deleteNotification, onNavigate: setActiveTab });
                    case 'messages': return React.createElement(MessagesTab, { data, setData, loadNotifications });
                    case 'services': return React.createElement(ServicesTab, { data, loadData: loadDashboardData });
                    case 'orders': return React.createElement(OrdersTab, { data, openRatingModal });
                    case 'reviews': return React.createElement(ReviewsTab, { userData });
                    case 'portfolio': return React.createElement(PortfolioTab, { data, loadData: loadDashboardData });
                    case 'former-clients': return React.createElement(FormerClientsTab, { data });
                    case 'admin-users': return React.createElement(AdminUsersTab, { data, loadData: loadDashboardData });
                    case 'admin-categories': return React.createElement(AdminCategoriesTab, { data, loadData: loadDashboardData });
                    case 'admin-support': return React.createElement(AdminSupportTab, { data, loadData: loadDashboardData });
                    default: return React.createElement(OverviewTab, { data, loadData: loadDashboardData });
                }
            };

            const getPageTitle = () => {
                switch(activeTab) {
                    case 'overview': return 'Vue d\'ensemble';
                    case 'notifications': return 'Notifications';
                    case 'messages': return 'Messages';
                    case 'reviews': return 'Mes Évaluations';
                    case 'services': return isAdmin ? 'Tous les Services' : 'Mes Services';
                    case 'orders': return 'Commandes';
                    case 'portfolio': return 'Portfolio';
                    case 'former-clients': return 'Anciens Clients';
                    case 'admin-users': return 'Gestion Utilisateurs';
                    case 'admin-categories': return 'Gestion Catégories';
                    case 'admin-support': return 'Support';
                    default: return 'Dashboard';
                }
            };

            return React.createElement('div', { className: "flex h-screen", style: {backgroundColor: 'var(--color-bg)'} },
                // Sidebar
                React.createElement(Sidebar, {
                    activeTab,
                    setActiveTab,
                    isOpen: sidebarOpen,
                    onClose: () => setSidebarOpen(false),
                    data,
                    handleLogout,
                    unreadCount
                }),

                // Rating Modal
                React.createElement(RatingModal, {
                    isOpen: ratingModal.isOpen,
                    onClose: closeRatingModal,
                    order: ratingModal.order,
                    onSubmit: submitRating
                }),

                // Overlay mobile
                sidebarOpen && React.createElement('div', {
                    className: "fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden",
                    onClick: () => setSidebarOpen(false)
                }),

                // Main Content
                React.createElement('div', { className: "flex-1 flex flex-col overflow-hidden lg:ml-0" },
                    // Header
                    React.createElement('header', { className: "bg-white border-b px-4 py-4 lg:px-6", style: {boxShadow: 'var(--shadow-sm)'} },
                        React.createElement('div', { className: "flex items-center justify-between" },
                            React.createElement('div', { className: "flex items-center space-x-4" },
                                React.createElement('button', {
                                    onClick: () => setSidebarOpen(!sidebarOpen),
                                    className: "lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors"
                                }, React.createElement('i', { className: "fas fa-bars" })),
                                React.createElement('h1', { className: "text-xl lg:text-2xl font-bold", style: {fontFamily: 'var(--font-heading)', color: 'var(--color-black)'} }, getPageTitle())
                            ),
                            React.createElement('div', { className: "flex items-center space-x-4" },
                                // Icône de notifications
                                React.createElement('div', { className: "relative" },
                                    React.createElement('button', {
                                        onClick: () => setShowNotifications(!showNotifications),
                                        className: "flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-100 transition-colors relative"
                                    },
                                        React.createElement('i', {
                                            className: `fas fa-bell text-lg ${unreadCount > 0 ? 'text-red-600' : 'text-gray-600'}`
                                        }),
                                        unreadCount > 0 && React.createElement('div', {
                                            className: "absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold"
                                        }, unreadCount > 99 ? '99+' : unreadCount)
                                    ),
                                    // Dropdown des notifications
                                    showNotifications && React.createElement(NotificationsDropdown, {
                                        notifications,
                                        unreadCount,
                                        onClose: () => setShowNotifications(false),
                                        onMarkAsRead: markNotificationAsRead,
                                        onMarkAllAsRead: markAllAsRead,
                                        onDelete: deleteNotification,
                                        onNavigate: setActiveTab
                                    })
                                ),

                                // Notification Messages uniquement si messages non lus
                                data.unreadMessages > 0 && React.createElement('div', {
                                    className: "flex items-center bg-red-50 border border-red-200 text-red-800 px-3 py-2 rounded-lg text-sm cursor-pointer hover:bg-red-100 transition-colors",
                                    onClick: () => setActiveTab('messages')
                                },
                                    React.createElement('i', { className: "fas fa-envelope mr-2" }),
                                    React.createElement('span', { className: "font-medium mr-1" }, data.unreadMessages),
                                    React.createElement('span', null,
                                        data.unreadMessages === 1 ? "nouveau message" : "nouveaux messages"
                                    )
                                )
                            )
                        )
                    ),

                    // Main Content Area
                    React.createElement('main', { className: "flex-1 overflow-y-auto p-4 lg:p-6" }, renderContent())
                )
            );
        };

        // Sidebar Component
        const Sidebar = ({ activeTab, setActiveTab, isOpen, onClose, data, handleLogout, unreadCount }) => {
            const userMenuItems = [
                { id: 'overview', icon: 'fas fa-chart-line', label: 'Vue d\'ensemble' },
                { id: 'notifications', icon: 'fas fa-bell', label: 'Notifications', badge: unreadCount },
                { id: 'messages', icon: 'fas fa-envelope', label: 'Messages', badge: data.unreadMessages || 0 },
                { id: 'services', icon: 'fas fa-cog', label: 'Mes Services' },
                { id: 'orders', icon: 'fas fa-briefcase', label: 'Commandes' },
                { id: 'reviews', icon: 'fas fa-star', label: 'Évaluations' },
                { id: 'portfolio', icon: 'fas fa-images', label: 'Portfolio' },
                { id: 'former-clients', icon: 'fas fa-users', label: 'Anciens Clients' }
            ];

            const adminMenuItems = [
                { id: 'overview', icon: 'fas fa-chart-line', label: 'Vue d\'ensemble' },
                { id: 'admin-users', icon: 'fas fa-users', label: 'Utilisateurs' },
                { id: 'services', icon: 'fas fa-cog', label: 'Tous les Services' },
                { id: 'orders', icon: 'fas fa-briefcase', label: 'Toutes les Commandes' },
                { id: 'admin-categories', icon: 'fas fa-tags', label: 'Catégories' },
                { id: 'admin-support', icon: 'fas fa-headset', label: 'Support', badge: 0 }
            ];

            const menuItems = isAdmin ? adminMenuItems : userMenuItems;

            return React.createElement('div', {
                className: `fixed left-0 top-0 h-full w-64 bg-white z-50 sidebar-mobile ${isOpen ? 'open' : ''} lg:translate-x-0 lg:static lg:z-auto`
            },

                // Navigation
                React.createElement('nav', { className: "p-4 h-full" },
                    React.createElement('div', { className: "space-y-2" },
                        menuItems.map(item =>
                            React.createElement('button', {
                                key: item.id,
                                onClick: () => setActiveTab(item.id),
                                className: `w-full flex items-center justify-between px-4 py-3 rounded-lg text-left transition-colors nav-item ${activeTab === item.id ? 'nav-item-active' : ''}`
                            },
                                React.createElement('div', { className: "flex items-center space-x-3" },
                                    React.createElement('i', { className: item.icon }),
                                    React.createElement('span', { className: "font-medium" }, item.label)
                                ),
                                item.badge > 0 && React.createElement('span', {
                                    className: `px-2 py-1 text-xs rounded-full ${activeTab === item.id ? 'bg-white text-red-600' : 'text-white'}`,
                                    style: {background: activeTab === item.id ? 'white' : 'var(--color-red)'}
                                }, item.badge)
                            )
                        )
                    )
                )
            );
        };

        // Overview Tab
        const OverviewTab = ({ data, loadData }) => {
            const StatsCard = ({ title, value, icon, color, isRating = false }) =>
                React.createElement('div', { className: "bg-white rounded-lg p-6", style: {boxShadow: 'var(--shadow-md)'} },
                    React.createElement('div', { className: "flex items-center justify-between" },
                        React.createElement('div', null,
                            React.createElement('p', { className: "text-sm opacity-70" }, title),
                            isRating ?
                                React.createElement('div', { className: "mt-2" },
                                    React.createElement(StarsDisplay, { rating: value, size: "text-xl", showNumber: true })
                                ) :
                                React.createElement('p', { className: "text-2xl font-bold", style: {color: 'var(--color-black)'} }, value)
                        ),
                        React.createElement('div', { className: `w-12 h-12 rounded-lg flex items-center justify-center ${color}` },
                            React.createElement('i', { className: `${icon} text-2xl` })
                        )
                    )
                );

            const formatStatus = (status) => {
                const statusLabels = {
                    pending: 'En attente',
                    in_progress: 'En cours',
                    delivered: 'Livré',
                    completed: 'Terminé',
                    cancelled: 'Annulé'
                };
                return statusLabels[status] || status;
            };

            const getStatsData = () => {
                if (isAdmin) {
                    return [
                        { title: "Utilisateurs", value: data.stats.total_users || 0, icon: "fas fa-users", color: "bg-blue-100" },
                        { title: "Commandes", value: data.stats.total_orders || 0, icon: "fas fa-box", color: "bg-green-100" },
                        { title: "Services", value: data.stats.active_services || 0, icon: "fas fa-cog", color: "bg-purple-100" },
                        { title: "Tickets", value: data.stats.open_tickets || 0, icon: "fas fa-ticket-alt", color: "bg-red-100" }
                    ];
                } else {
                    return [
                        { title: "Revenus", value: `${data.stats.earnings || 0}€`, icon: "fas fa-euro-sign", color: "bg-green-100" },
                        { title: "Ventes", value: data.stats.sales || 0, icon: "fas fa-briefcase", color: "bg-blue-100" },
                        { title: "Achats", value: data.stats.purchases || 0, icon: "fas fa-shopping-cart", color: "bg-purple-100" },
                        { title: "Note Moyenne", value: data.stats.rating || 0, icon: "fas fa-star", color: "bg-yellow-100", isRating: true }
                    ];
                }
            };

            return React.createElement('div', { className: "space-y-6" },
                // Stats
                React.createElement('div', { className: "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6" },
                    getStatsData().map(stat =>
                        React.createElement(StatsCard, {
                            key: stat.title,
                            title: stat.title,
                            value: stat.value,
                            icon: stat.icon,
                            color: stat.color,
                            isRating: stat.isRating || false
                        })
                    )
                ),

                // Recent Activity
                React.createElement('div', { className: "grid grid-cols-1 lg:grid-cols-2 gap-6" },
                    // Services/Commandes
                    React.createElement('div', { className: "bg-white rounded-lg p-6", style: {boxShadow: 'var(--shadow-md)'} },
                        React.createElement('h3', { className: "text-lg font-bold mb-4" }, isAdmin ? "Services Récents" : "Mes Services"),
                        React.createElement('div', { className: "space-y-3" },
                            (data.services || []).slice(0, 3).map(service =>
                                React.createElement('div', { key: service.id, className: "flex items-center justify-between p-3 bg-gray-50 rounded-lg" },
                                    React.createElement('div', null,
                                        React.createElement('p', { className: "font-medium" }, service.title),
                                        React.createElement('p', { className: "text-sm opacity-70" }, `${service.price}€`),
                                        isAdmin && React.createElement('p', { className: "text-xs text-gray-500" }, `par ${service.user_name}`)
                                    ),
                                    React.createElement('span', {
                                        className: `px-2 py-1 text-xs rounded-full ${service.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}`
                                    }, service.status)
                                )
                            ),
                            (data.services || []).length === 0 && React.createElement('p', { className: "text-gray-500 text-center py-4" }, "Aucun service")
                        )
                    ),

                    // Commandes
                    React.createElement('div', { className: "bg-white rounded-lg p-6", style: {boxShadow: 'var(--shadow-md)'} },
                        React.createElement('h3', { className: "text-lg font-bold mb-4" }, "Commandes Récentes"),
                        React.createElement('div', { className: "space-y-3" },
                            (data.orders || []).slice(0, 3).map(order =>
                                React.createElement('div', { key: order.id, className: "flex items-center justify-between p-3 bg-gray-50 rounded-lg" },
                                    React.createElement('div', null,
                                        React.createElement('p', { className: "font-medium" }, order.title),
                                        React.createElement('p', { className: "text-sm opacity-70" }, `${order.price}€`),
                                        React.createElement('p', { className: "text-xs text-gray-500" },
                                            isAdmin ? `${order.buyer_name} > ${order.seller_name}` :
                                            order.user_role === 'buyer' ? `Vendeur: ${order.seller_name}` : `Acheteur: ${order.buyer_name}`
                                        )
                                    ),
                                    React.createElement('span', {
                                        className: `px-2 py-1 text-xs rounded-full status-${order.status}`
                                    }, formatStatus(order.status))
                                )
                            ),
                            (data.orders || []).length === 0 && React.createElement('p', { className: "text-gray-500 text-center py-4" }, "Aucune commande")
                        )
                    )
                )
            );
        };

        // Messages Tab
        const MessagesTab = ({ data, setData, loadNotifications }) => {
            const [conversations, setConversations] = useState([]);
            const [selectedConversation, setSelectedConversation] = useState(null);
            const [messages, setMessages] = useState([]);
            const [newMessage, setNewMessage] = useState('');
            const [sending, setSending] = useState(false);
            const [loading, setLoading] = useState(true);

            useEffect(() => {
                loadConversations();
            }, []);

            const loadConversations = async () => {
                setLoading(true);
                const result = await apiCall('messages.php?conversations=1');
                if (result.success) {
                    setConversations(result.conversations);
                }
                setLoading(false);
            };


            const loadMessages = async (orderId) => {
                const result = await apiCall(`messages.php?order_id=${orderId}`);
                if (result.success) {
                    setMessages(result.messages);
                    setSelectedConversation(result.order);
                }
            };

            const sendMessage = async () => {
                if (!newMessage.trim() || !selectedConversation || sending) return;

                setSending(true);
                try {
                    const result = await apiCall('messages.php', 'POST', {
                        order_id: selectedConversation.id,
                        content: newMessage
                    });

                    if (result.success) {
                        setNewMessage('');
                        await loadMessages(selectedConversation.id);
                        await loadConversations();
                        // Recharger les notifications après l'envoi
                        if (loadNotifications) {
                            setTimeout(() => loadNotifications(), 500);
                        }
                    } else {
                        console.error('Erreur envoi message:', result.error);
                        alert('Erreur lors de l\'envoi du message: ' + (result.error || 'Erreur inconnue'));
                    }
                } catch (error) {
                    console.error('Erreur lors de l\'envoi:', error);
                    alert('Erreur lors de l\'envoi du message');
                } finally {
                    setSending(false);
                }
            };

            if (loading) {
                return React.createElement('div', { className: "flex items-center justify-center h-64" },
                    React.createElement('div', { className: "text-lg" }, "Chargement...")
                );
            }

            if (selectedConversation) {
                return React.createElement('div', { className: "flex h-full bg-white rounded-lg", style: {boxShadow: 'var(--shadow-md)'} },
                    React.createElement('div', { className: "flex-1 flex flex-col" },
                        // Chat Header
                        React.createElement('div', { className: "flex items-center justify-between p-4 border-b" },
                            React.createElement('div', { className: "flex items-center space-x-3" },
                                React.createElement('button', {
                                    onClick: () => { setSelectedConversation(null); setMessages([]); },
                                    className: "text-gray-500 hover:text-gray-700"
                                }, "← Retour"),
                                React.createElement('h3', { className: "font-medium" }, selectedConversation.title)
                            )
                        ),

                        // Messages
                        React.createElement('div', { className: "flex-1 overflow-y-auto p-4 space-y-4" },
                            messages.length === 0 && React.createElement('div', { className: "text-center text-gray-500 mt-8" },
                                "Aucun message. Commencez la conversation !"
                            ),
                            messages.map(msg =>
                                React.createElement('div', {
                                    key: msg.id,
                                    className: `flex ${msg.sender_id == userData.id ? 'justify-end' : 'justify-start'}`
                                },
                                    React.createElement('div', {
                                        className: `max-w-xs lg:max-w-md px-4 py-2 rounded-lg ${
                                            msg.sender_id == userData.id
                                                ? 'text-white'
                                                : 'bg-gray-100 text-gray-800'
                                        }`,
                                        style: msg.sender_id == userData.id ? {background: 'var(--color-red)'} : {}
                                    },
                                        React.createElement('p', { className: "text-sm" }, msg.content),
                                        React.createElement('p', { className: `text-xs mt-1 ${msg.sender_id == userData.id ? 'text-red-100' : 'text-gray-500'}` },
                                            new Date(msg.created_at).toLocaleString()
                                        )
                                    )
                                )
                            )
                        ),

                        // Message Input
                        React.createElement('div', { className: "p-4 border-t" },
                            React.createElement('div', { className: "flex space-x-2" },
                                React.createElement('input', {
                                    type: "text",
                                    value: newMessage,
                                    onChange: (e) => setNewMessage(e.target.value),
                                    onKeyPress: (e) => e.key === 'Enter' && !sending && newMessage.trim() && sendMessage(),
                                    placeholder: "Tapez votre message...",
                                    className: "flex-1 message-input"
                                }),
                                React.createElement('button', {
                                    onClick: sendMessage,
                                    disabled: sending || !newMessage.trim(),
                                    className: `btn-primary px-4 py-2 rounded-lg ${sending ? 'opacity-50 cursor-not-allowed' : ''}`
                                }, sending ? "Envoi..." : "Envoyer")
                            )
                        )
                    )
                );
            }

            return React.createElement('div', { className: "bg-white rounded-lg", style: {boxShadow: 'var(--shadow-md)'} },
                React.createElement('div', { className: "p-6 border-b" },
                    React.createElement('h3', { className: "text-lg font-bold" }, "Messages")
                ),

                React.createElement('div', { className: "p-6" },
                    conversations.length === 0
                        ? React.createElement('div', { className: "text-center py-8 text-gray-500" }, "Aucune conversation")
                        : React.createElement('div', { className: "space-y-4" },
                            conversations.map(conv =>
                                React.createElement('div', {
                                    key: conv.order_id,
                                    onClick: () => loadMessages(conv.order_id),
                                    className: "flex items-center space-x-4 p-4 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                                },
                                    conv.contact_avatar ?
                                        React.createElement('img', {
                                            src: conv.contact_avatar,
                                            alt: conv.contact_name,
                                            className: "w-12 h-12 rounded-full object-cover",
                                            onError: (e) => {
                                                const parent = e.target.parentNode;
                                                e.target.style.display = 'none';
                                                const svgDiv = document.createElement('div');
                                                parent.appendChild(svgDiv);
                                                ReactDOM.render(React.createElement(AnonymousAvatar, { size: 48 }), svgDiv);
                                            }
                                        }) :
                                        React.createElement(AnonymousAvatar, { size: 48 }),
                                    React.createElement('div', { className: "flex-1" },
                                        React.createElement('div', { className: "flex items-center justify-between" },
                                            React.createElement('p', { className: "font-medium" }, conv.contact_name),
                                            React.createElement('p', { className: "text-sm text-gray-500" },
                                                conv.last_message_time ? new Date(conv.last_message_time).toLocaleDateString() : ''
                                            )
                                        ),
                                        React.createElement('p', { className: "text-sm text-gray-600 truncate" }, conv.order_title),
                                        conv.last_message && React.createElement('p', { className: "text-sm text-gray-500 truncate" }, conv.last_message)
                                    ),
                                    conv.unread_count > 0 && React.createElement('div', {
                                        className: "w-2 h-2 rounded-full",
                                        style: {background: 'var(--color-red)'}
                                    })
                                )
                            )
                        )
                )
            );
        };

        // Service Modal Component
        const ServiceModal = ({ isOpen, onClose, editingService, serviceFormData, setServiceFormData, onSubmit, categories }) => {
            if (!isOpen) return null;

            return React.createElement('div', { className: "fixed inset-0 z-50 flex items-center justify-center modal-overlay" },
                React.createElement('div', { className: "bg-white rounded-lg p-6 w-full max-w-md mx-4" },
                    React.createElement('h3', { className: "text-lg font-bold mb-4" },
                        editingService ? 'Modifier le service' : 'Nouveau service'
                    ),
                    React.createElement('form', { onSubmit: onSubmit, className: "space-y-4" },
                        React.createElement('input', {
                            type: "text",
                            value: serviceFormData.title,
                            onChange: (e) => setServiceFormData({...serviceFormData, title: e.target.value}),
                            placeholder: "Titre du service",
                            className: "w-full p-3 border rounded-lg",
                            required: true
                        }),
                        React.createElement('textarea', {
                            value: serviceFormData.description,
                            onChange: (e) => setServiceFormData({...serviceFormData, description: e.target.value}),
                            placeholder: "Description du service",
                            className: "w-full p-3 border rounded-lg",
                            rows: 3,
                            required: true
                        }),
                        React.createElement('select', {
                            value: serviceFormData.category_id,
                            onChange: (e) => setServiceFormData({...serviceFormData, category_id: e.target.value}),
                            className: "w-full p-3 border rounded-lg"
                        },
                            React.createElement('option', { value: "" }, "Sélectionner une catégorie"),
                            categories.map(cat =>
                                React.createElement('option', { key: cat.id, value: cat.id }, cat.name)
                            )
                        ),
                        React.createElement('input', {
                            type: "number",
                            value: serviceFormData.price,
                            onChange: (e) => setServiceFormData({...serviceFormData, price: e.target.value}),
                            placeholder: "Prix (€)",
                            className: "w-full p-3 border rounded-lg",
                            min: "0",
                            step: "0.01",
                            required: true
                        }),
                        React.createElement('input', {
                            type: "number",
                            value: serviceFormData.delivery_days,
                            onChange: (e) => setServiceFormData({...serviceFormData, delivery_days: e.target.value}),
                            placeholder: "Délai de livraison (jours)",
                            className: "w-full p-3 border rounded-lg",
                            min: "1",
                            required: true
                        }),
                        React.createElement('input', {
                            type: "url",
                            value: serviceFormData.image,
                            onChange: (e) => setServiceFormData({...serviceFormData, image: e.target.value}),
                            placeholder: "URL de l'image (optionnel)",
                            className: "w-full p-3 border rounded-lg"
                        }),
                        React.createElement('div', { className: "flex space-x-2" },
                            React.createElement('button', {
                                type: "submit",
                                className: "btn-primary px-4 py-2 rounded-lg flex-1"
                            }, editingService ? 'Modifier' : 'Créer'),
                            React.createElement('button', {
                                type: "button",
                                onClick: onClose,
                                className: "btn-secondary px-4 py-2 rounded-lg flex-1"
                            }, "Annuler")
                        )
                    )
                )
            );
        };

        // Services Tab
        const ServicesTab = ({ data, loadData }) => {
            const [showServiceModal, setShowServiceModal] = useState(false);
            const [editingService, setEditingService] = useState(null);
            const [serviceFormData, setServiceFormData] = useState({
                title: '',
                description: '',
                category_id: '',
                price: '',
                delivery_days: '',
                image: ''
            });

            const handleServiceSubmit = async (e) => {
                e.preventDefault();

                const method = editingService ? 'PUT' : 'POST';
                const payload = editingService
                    ? { ...serviceFormData, id: editingService.id }
                    : serviceFormData;

                const result = await apiCall('services.php', method, payload);

                if (result.success) {
                    handleCloseModal();
                    loadData();
                } else {
                    alert(result.error || 'Erreur lors de la sauvegarde');
                }
            };

            const handleCloseModal = () => {
                setShowServiceModal(false);
                setEditingService(null);
                setServiceFormData({ title: '', description: '', category_id: '', price: '', delivery_days: '', image: '' });
            };

            const handleEdit = (service) => {
                setEditingService(service);
                setServiceFormData({
                    title: service.title,
                    description: service.description,
                    category_id: service.category_id || '',
                    price: service.price,
                    delivery_days: service.delivery_days,
                    image: service.image || ''
                });
                setShowServiceModal(true);
            };

            const handleDelete = async (serviceId) => {
                if (confirm('Êtes-vous sûr de vouloir supprimer ce service ?')) {
                    const result = await apiCall('services.php', 'DELETE', { id: serviceId });
                    if (result.success) {
                        loadData();
                    } else {
                        alert(result.error || 'Erreur lors de la suppression');
                    }
                }
            };

            return React.createElement('div', { className: "space-y-6" },
                React.createElement('div', { className: "bg-white rounded-lg p-6", style: {boxShadow: 'var(--shadow-md)'} },
                    React.createElement('div', { className: "flex items-center justify-between mb-6" },
                        React.createElement('h3', { className: "text-lg font-bold" }, isAdmin ? "Tous les Services" : "Mes Services"),
                        !isAdmin && React.createElement('button', {
                            onClick: () => setShowServiceModal(true),
                            className: "btn-primary px-4 py-2 rounded-lg"
                        }, "Nouveau service")
                    ),

                (data.services || []).length === 0
                    ? React.createElement('div', { className: "text-center py-8 text-gray-500" }, "Aucun service")
                    : React.createElement('div', { className: "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" },
                        (data.services || []).map(service =>
                            React.createElement('div', { key: service.id, className: "border rounded-lg p-4 hover:shadow-lg transition-shadow" },
                                service.image && React.createElement('img', {
                                    src: service.image,
                                    alt: service.title,
                                    className: "w-full h-32 object-cover rounded-lg mb-3"
                                }),
                                React.createElement('h4', { className: "font-medium mb-2" }, service.title),
                                React.createElement('p', { className: "text-sm text-gray-600 mb-3 line-clamp-2" }, service.description),
                                React.createElement('div', { className: "flex items-center justify-between mb-3" },
                                    React.createElement('span', { className: "font-bold text-lg", style: {color: 'var(--color-red)'} }, `${service.price}€`),
                                    React.createElement('span', {
                                        className: `px-2 py-1 text-xs rounded-full ${service.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}`
                                    }, service.status)
                                ),
                                isAdmin && React.createElement('p', { className: "text-xs text-gray-500 mb-3" }, `par ${service.user_name}`),
                                React.createElement('div', { className: "text-xs text-gray-500 mb-3" },
                                    `Livraison: ${service.delivery_days} jour${service.delivery_days > 1 ? 's' : ''}`
                                ),
                                // Boutons d'action pour les utilisateurs (leurs propres services) ou les admins
                                ((!isAdmin && service.user_id == userData.id) || isAdmin) &&
                                React.createElement('div', { className: "flex space-x-2 mt-3" },
                                    React.createElement('button', {
                                        onClick: () => handleEdit(service),
                                        className: "btn-secondary px-3 py-1 text-sm rounded flex-1"
                                    }, "Modifier"),
                                    React.createElement('button', {
                                        onClick: () => handleDelete(service.id),
                                        className: "px-3 py-1 text-sm rounded flex-1 bg-red-100 text-red-800 hover:bg-red-200"
                                    }, "Supprimer")
                                )
                            )
                        )
                    )
                ),

                React.createElement(ServiceModal, {
                    isOpen: showServiceModal,
                    onClose: handleCloseModal,
                    editingService,
                    serviceFormData,
                    setServiceFormData,
                    onSubmit: handleServiceSubmit,
                    categories: data.categories || []
                })
            );
        };

        // Orders Tab
        const OrdersTab = ({ data, openRatingModal }) => {
            const [orders, setOrders] = useState([]);
            const [loading, setLoading] = useState(true);

            useEffect(() => {
                loadOrders();
            }, []);

            const loadOrders = async () => {
                setLoading(true);
                const result = await apiCall('orders.php');
                if (result.success) {
                    setOrders(result.orders);
                }
                setLoading(false);
            };

            const updateOrderStatus = async (orderId, newStatus) => {
                const result = await apiCall('orders.php', 'PUT', {
                    order_id: orderId,
                    status: newStatus
                });

                if (result.success) {
                    loadOrders();
                }
            };

            const formatStatus = (status) => {
                const statusLabels = {
                    pending: 'En attente',
                    in_progress: 'En cours',
                    delivered: 'Livré',
                    completed: 'Terminé',
                    cancelled: 'Annulé'
                };
                return statusLabels[status] || status;
            };

            if (loading) {
                return React.createElement('div', { className: "flex items-center justify-center h-64" },
                    React.createElement('div', { className: "text-lg" }, "Chargement...")
                );
            }

            return React.createElement('div', { className: "bg-white rounded-lg p-6", style: {boxShadow: 'var(--shadow-md)'} },
                React.createElement('h3', { className: "text-lg font-bold mb-6" }, "Commandes"),

                orders.length === 0
                    ? React.createElement('div', { className: "text-center py-8 text-gray-500" }, "Aucune commande")
                    : React.createElement('div', { className: "space-y-4" },
                        orders.map(order =>
                            React.createElement('div', { key: order.id, className: "border rounded-lg p-4" },
                                React.createElement('div', { className: "flex items-center justify-between mb-3" },
                                    React.createElement('h4', { className: "font-medium" }, order.title),
                                    React.createElement('span', {
                                        className: `px-3 py-1 text-sm rounded-full status-${order.status}`
                                    }, formatStatus(order.status))
                                ),
                                React.createElement('div', { className: "grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600" },
                                    React.createElement('div', null,
                                        React.createElement('span', { className: "font-medium" }, "Prix: "),
                                        React.createElement('span', null, `${order.price}€`)
                                    ),
                                    React.createElement('div', null,
                                        React.createElement('span', { className: "font-medium" },
                                            isAdmin ? "Acheteur: " : order.user_role === 'buyer' ? "Vendeur: " : "Acheteur: "
                                        ),
                                        React.createElement('span', null,
                                            isAdmin ? order.buyer_name :
                                            order.user_role === 'buyer' ? order.seller_name : order.buyer_name
                                        )
                                    ),
                                    order.deadline && React.createElement('div', null,
                                        React.createElement('span', { className: "font-medium" }, "Échéance: "),
                                        React.createElement('span', null, new Date(order.deadline).toLocaleDateString())
                                    )
                                ),
                                order.description && React.createElement('p', { className: "text-sm text-gray-600 mt-3" }, order.description),

                                // Actions pour vendeur ou admin
                                (order.seller_id == userData.id || isAdmin) && order.status !== 'completed' && order.status !== 'cancelled' &&
                                React.createElement('div', { className: "flex space-x-2 mt-4" },
                                    order.status === 'pending' && React.createElement('button', {
                                        onClick: () => updateOrderStatus(order.id, 'in_progress'),
                                        className: "btn-primary px-3 py-1 text-sm rounded"
                                    }, "Accepter"),
                                    order.status === 'in_progress' && React.createElement('button', {
                                        onClick: () => updateOrderStatus(order.id, 'delivered'),
                                        className: "btn-primary px-3 py-1 text-sm rounded"
                                    }, "Marquer comme livré"),
                                    React.createElement('button', {
                                        onClick: () => updateOrderStatus(order.id, 'cancelled'),
                                        className: "btn-secondary px-3 py-1 text-sm rounded"
                                    }, "Annuler")
                                ),

                                // Bouton d'évaluation pour l'acheteur quand la commande est livrée
                                order.status === 'delivered' && order.buyer_id == userData.id && !isAdmin &&
                                React.createElement('div', { className: "mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg" },
                                    React.createElement('div', { className: "flex items-center justify-between" },
                                        React.createElement('div', null,
                                            React.createElement('p', { className: "text-sm font-medium text-yellow-800" },
                                                "📦 Commande livrée ! Évaluez votre prestataire"
                                            ),
                                            React.createElement('p', { className: "text-xs text-yellow-600 mt-1" },
                                                "Votre avis aidera les autres utilisateurs"
                                            )
                                        ),
                                        React.createElement('button', {
                                            onClick: () => openRatingModal(order),
                                            className: "bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                                        }, "⭐ Évaluer")
                                    )
                                )
                            )
                        )
                    )
            );
        };

        // Portfolio Modal Component
        const PortfolioModal = ({ isOpen, onClose, editingProject, formData, setFormData, onSubmit, categories }) => {
            if (!isOpen) return null;

            return React.createElement('div', { className: "fixed inset-0 z-50 flex items-center justify-center modal-overlay" },
                React.createElement('div', { className: "bg-white rounded-lg p-6 w-full max-w-md mx-4" },
                    React.createElement('h3', { className: "text-lg font-bold mb-4" },
                        editingProject ? 'Modifier le projet' : 'Nouveau projet'
                    ),
                    React.createElement('form', { onSubmit: onSubmit, className: "space-y-4" },
                        React.createElement('input', {
                            type: "text",
                            value: formData.title,
                            onChange: (e) => setFormData({...formData, title: e.target.value}),
                            placeholder: "Titre du projet",
                            className: "w-full p-3 border rounded-lg",
                            required: true
                        }),
                        React.createElement('textarea', {
                            value: formData.description,
                            onChange: (e) => setFormData({...formData, description: e.target.value}),
                            placeholder: "Description du projet",
                            className: "w-full p-3 border rounded-lg",
                            rows: 3,
                            required: true
                        }),
                        React.createElement('select', {
                            value: formData.category_id,
                            onChange: (e) => setFormData({...formData, category_id: e.target.value}),
                            className: "w-full p-3 border rounded-lg"
                        },
                            React.createElement('option', { value: "" }, "Sélectionner une catégorie"),
                            categories.map(cat =>
                                React.createElement('option', { key: cat.id, value: cat.id }, cat.name)
                            )
                        ),
                        React.createElement('input', {
                            type: "url",
                            value: formData.image,
                            onChange: (e) => setFormData({...formData, image: e.target.value}),
                            placeholder: "URL de l'image (optionnel)",
                            className: "w-full p-3 border rounded-lg"
                        }),
                        React.createElement('div', { className: "flex space-x-2" },
                            React.createElement('button', {
                                type: "submit",
                                className: "btn-primary px-4 py-2 rounded-lg flex-1"
                            }, editingProject ? 'Modifier' : 'Ajouter'),
                            React.createElement('button', {
                                type: "button",
                                onClick: onClose,
                                className: "btn-secondary px-4 py-2 rounded-lg flex-1"
                            }, "Annuler")
                        )
                    )
                )
            );
        };

        // Portfolio Tab
        const PortfolioTab = ({ data, loadData }) => {
            const [showModal, setShowModal] = useState(false);
            const [editingProject, setEditingProject] = useState(null);
            const [formData, setFormData] = useState({
                title: '',
                description: '',
                category_id: '',
                image: ''
            });

            const handleSubmit = async (e) => {
                e.preventDefault();

                const method = editingProject ? 'PUT' : 'POST';
                const payload = editingProject
                    ? { ...formData, id: editingProject.id }
                    : formData;

                const result = await apiCall('portfolio.php', method, payload);

                if (result.success) {
                    handleCloseModal();
                    loadData();
                }
            };

            const handleCloseModal = () => {
                setShowModal(false);
                setEditingProject(null);
                setFormData({ title: '', description: '', category_id: '', image: '' });
            };

            const handleEdit = (project) => {
                setEditingProject(project);
                setFormData({
                    title: project.title,
                    description: project.description,
                    category_id: project.category_id || '',
                    image: project.image || ''
                });
                setShowModal(true);
            };

            const handleDelete = async (projectId) => {
                if (confirm('Êtes-vous sûr de vouloir supprimer ce projet ?')) {
                    const result = await apiCall('portfolio.php', 'DELETE', { id: projectId });
                    if (result.success) {
                        loadData();
                    }
                }
            };


            return React.createElement('div', { className: "space-y-6" },
                React.createElement('div', { className: "bg-white rounded-lg p-6", style: {boxShadow: 'var(--shadow-md)'} },
                    React.createElement('div', { className: "flex items-center justify-between mb-6" },
                        React.createElement('h3', { className: "text-lg font-bold" }, "Portfolio"),
                        React.createElement('button', {
                            onClick: () => setShowModal(true),
                            className: "btn-primary px-4 py-2 rounded-lg"
                        }, "Ajouter un projet")
                    ),

                    (data.portfolio || []).length === 0
                        ? React.createElement('div', { className: "text-center py-8 text-gray-500" }, "Aucun projet dans votre portfolio")
                        : React.createElement('div', { className: "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" },
                            (data.portfolio || []).map(project =>
                                React.createElement('div', { key: project.id, className: "border rounded-lg overflow-hidden hover:shadow-lg transition-shadow" },
                                    project.image && React.createElement('img', {
                                        src: project.image,
                                        alt: project.title,
                                        className: "w-full h-48 object-cover"
                                    }),
                                    React.createElement('div', { className: "p-4" },
                                        React.createElement('h4', { className: "font-medium mb-2" }, project.title),
                                        React.createElement('p', { className: "text-sm text-gray-600 mb-3" }, project.description),
                                        project.category_name && React.createElement('span', {
                                            className: "px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full"
                                        }, project.category_name),
                                        React.createElement('div', { className: "flex space-x-2 mt-4" },
                                            React.createElement('button', {
                                                onClick: () => handleEdit(project),
                                                className: "btn-secondary px-3 py-1 text-sm rounded flex-1"
                                            }, "Modifier"),
                                            React.createElement('button', {
                                                onClick: () => handleDelete(project.id),
                                                className: "px-3 py-1 text-sm rounded flex-1 bg-red-100 text-red-800 hover:bg-red-200"
                                            }, "Supprimer")
                                        )
                                    )
                                )
                            )
                        )
                ),

                React.createElement(PortfolioModal, {
                    isOpen: showModal,
                    onClose: handleCloseModal,
                    editingProject,
                    formData,
                    setFormData,
                    onSubmit: handleSubmit,
                    categories: data.categories || []
                })
            );
        };

        // Former Clients Tab
        const FormerClientsTab = ({ data }) => {
            return React.createElement('div', { className: "bg-white rounded-lg p-6", style: {boxShadow: 'var(--shadow-md)'} },
                React.createElement('h3', { className: "text-lg font-bold mb-6" }, "Anciens Clients"),

                (data.formerClients || []).length === 0
                    ? React.createElement('div', { className: "text-center py-8 text-gray-500" }, "Aucun client pour le moment")
                    : React.createElement('div', { className: "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" },
                        (data.formerClients || []).map(client =>
                            React.createElement('div', { key: client.id, className: "border rounded-lg p-4 hover:shadow-lg transition-shadow" },
                                React.createElement('div', { className: "flex items-center space-x-3 mb-3" },
                                    client.avatar ?
                                        React.createElement('img', {
                                            src: client.avatar,
                                            alt: client.name,
                                            className: "w-12 h-12 rounded-full object-cover",
                                            onError: (e) => {
                                                const parent = e.target.parentNode;
                                                e.target.style.display = 'none';
                                                const svgDiv = document.createElement('div');
                                                parent.appendChild(svgDiv);
                                                ReactDOM.render(React.createElement(AnonymousAvatar, { size: 48 }), svgDiv);
                                            }
                                        }) :
                                        React.createElement(AnonymousAvatar, { size: 48 }),
                                    React.createElement('div', null,
                                        React.createElement('h4', { className: "font-medium" }, client.name),
                                        React.createElement('p', { className: "text-sm text-gray-600" }, client.email)
                                    )
                                ),
                                React.createElement('div', { className: "space-y-2 text-sm" },
                                    React.createElement('div', { className: "flex justify-between" },
                                        React.createElement('span', null, "Commandes:"),
                                        React.createElement('span', { className: "font-medium" }, client.order_count)
                                    ),
                                    React.createElement('div', { className: "flex justify-between" },
                                        React.createElement('span', null, "Dernière commande:"),
                                        React.createElement('span', { className: "font-medium" },
                                            new Date(client.last_order).toLocaleDateString()
                                        )
                                    )
                                )
                            )
                        )
                    )
            );
        };

        // Admin Users Tab
        const AdminUsersTab = ({ data, loadData }) => {
            const [users, setUsers] = useState([]);
            const [loading, setLoading] = useState(true);

            useEffect(() => {
                loadUsers();
            }, []);

            const loadUsers = async () => {
                setLoading(true);
                const result = await apiCall('users.php');
                if (result.success) {
                    setUsers(result.users);
                }
                setLoading(false);
            };

            const updateUser = async (userId, updates) => {
                const result = await apiCall('users.php', 'PUT', {
                    user_id: userId,
                    ...updates
                });

                if (result.success) {
                    loadUsers();
                }
            };

            const deleteUser = async (userId) => {
                if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
                    const result = await apiCall('users.php', 'DELETE', { user_id: userId });
                    if (result.success) {
                        loadUsers();
                    }
                }
            };

            if (loading) {
                return React.createElement('div', { className: "flex items-center justify-center h-64" },
                    React.createElement('div', { className: "text-lg" }, "Chargement...")
                );
            }

            return React.createElement('div', { className: "bg-white rounded-lg p-6", style: {boxShadow: 'var(--shadow-md)'} },
                React.createElement('h3', { className: "text-lg font-bold mb-6" }, "Gestion des Utilisateurs"),

                React.createElement('div', { className: "overflow-x-auto" },
                    React.createElement('table', { className: "w-full text-sm text-left" },
                        React.createElement('thead', { className: "text-xs text-gray-700 uppercase bg-gray-50" },
                            React.createElement('tr', null,
                                React.createElement('th', { className: "px-6 py-3" }, "Utilisateur"),
                                React.createElement('th', { className: "px-6 py-3" }, "Email"),
                                React.createElement('th', { className: "px-6 py-3" }, "Rôle"),
                                React.createElement('th', { className: "px-6 py-3" }, "Note"),
                                React.createElement('th', { className: "px-6 py-3" }, "Statistiques"),
                                React.createElement('th', { className: "px-6 py-3" }, "Actions")
                            )
                        ),
                        React.createElement('tbody', null,
                            users.map(user =>
                                React.createElement('tr', { key: user.id, className: "bg-white border-b hover:bg-gray-50" },
                                    React.createElement('td', { className: "px-6 py-4 flex items-center space-x-3" },
                                        user.avatar ?
                                            React.createElement('img', {
                                                src: user.avatar,
                                                alt: user.name,
                                                className: "w-10 h-10 rounded-full object-cover",
                                                onError: (e) => {
                                                    const parent = e.target.parentNode;
                                                    e.target.style.display = 'none';
                                                    const svgDiv = document.createElement('div');
                                                    parent.appendChild(svgDiv);
                                                    ReactDOM.render(React.createElement(AnonymousAvatar, { size: 40 }), svgDiv);
                                                }
                                            }) :
                                            React.createElement(AnonymousAvatar, { size: 40 }),
                                        React.createElement('div', null,
                                            React.createElement('div', { className: "font-medium" }, user.name),
                                            React.createElement('div', { className: "text-gray-500 text-xs" },
                                                `Inscrit le ${new Date(user.created_at).toLocaleDateString()}`
                                            )
                                        )
                                    ),
                                    React.createElement('td', { className: "px-6 py-4" }, user.email),
                                    React.createElement('td', { className: "px-6 py-4" },
                                        React.createElement('select', {
                                            value: user.role,
                                            onChange: (e) => updateUser(user.id, { role: e.target.value }),
                                            className: "text-sm border rounded px-2 py-1"
                                        },
                                            React.createElement('option', { value: "user" }, "Utilisateur"),
                                            React.createElement('option', { value: "admin" }, "Admin")
                                        )
                                    ),
                                    React.createElement('td', { className: "px-6 py-4" },
                                        React.createElement('input', {
                                            type: "number",
                                            min: "0",
                                            max: "5",
                                            step: "0.1",
                                            value: user.rating || 0,
                                            onChange: (e) => updateUser(user.id, { rating: parseFloat(e.target.value) }),
                                            className: "w-16 text-sm border rounded px-2 py-1"
                                        })
                                    ),
                                    React.createElement('td', { className: "px-6 py-4 text-xs text-gray-600" },
                                        React.createElement('div', null, `Ventes: ${user.sales_count || 0}`),
                                        React.createElement('div', null, `Achats: ${user.purchases_count || 0}`),
                                        React.createElement('div', null, `Revenus: ${user.total_earnings || 0}€`)
                                    ),
                                    React.createElement('td', { className: "px-6 py-4" },
                                        user.id !== userData.id && React.createElement('button', {
                                            onClick: () => deleteUser(user.id),
                                            className: "text-red-600 hover:text-red-800 text-sm"
                                        }, "Supprimer")
                                    )
                                )
                            )
                        )
                    )
                )
            );
        };

        // Admin Support Tab
        const AdminSupportTab = ({ data, loadData }) => {
            const [tickets, setTickets] = useState([]);
            const [loading, setLoading] = useState(true);
            const [selectedTicket, setSelectedTicket] = useState(null);
            const [response, setResponse] = useState('');

            useEffect(() => {
                loadTickets();
            }, []);

            const loadTickets = async () => {
                setLoading(true);
                const result = await apiCall('support.php');
                if (result.success) {
                    setTickets(result.tickets);
                }
                setLoading(false);
            };

            const updateTicket = async (ticketId, updates) => {
                const result = await apiCall('support.php', 'PUT', {
                    ticket_id: ticketId,
                    ...updates
                });

                if (result.success) {
                    loadTickets();
                    if (selectedTicket && selectedTicket.id === ticketId) {
                        setSelectedTicket({ ...selectedTicket, ...updates });
                    }
                }
            };

            const respondToTicket = async () => {
                if (!response.trim() || !selectedTicket) return;

                await updateTicket(selectedTicket.id, {
                    admin_response: response,
                    status: 'resolved'
                });

                setResponse('');
                setSelectedTicket(null);
            };

            const formatStatus = (status) => {
                const statusLabels = {
                    open: 'Ouvert',
                    in_progress: 'En cours',
                    resolved: 'Résolu',
                    closed: 'Fermé'
                };
                return statusLabels[status] || status;
            };

            if (loading) {
                return React.createElement('div', { className: "flex items-center justify-center h-64" },
                    React.createElement('div', { className: "text-lg" }, "Chargement...")
                );
            }

            if (selectedTicket) {
                return React.createElement('div', { className: "bg-white rounded-lg p-6", style: {boxShadow: 'var(--shadow-md)'} },
                    React.createElement('div', { className: "flex items-center justify-between mb-6" },
                        React.createElement('button', {
                            onClick: () => setSelectedTicket(null),
                            className: "text-gray-500 hover:text-gray-700"
                        }, "← Retour"),
                        React.createElement('h3', { className: "text-lg font-bold" }, "Ticket Support")
                    ),

                    React.createElement('div', { className: "space-y-4" },
                        React.createElement('div', { className: "flex items-center space-x-3" },
                            selectedTicket.user_avatar ?
                                React.createElement('img', {
                                    src: selectedTicket.user_avatar,
                                    alt: selectedTicket.user_name,
                                    className: "w-10 h-10 rounded-full object-cover",
                                    onError: (e) => {
                                        const parent = e.target.parentNode;
                                        e.target.style.display = 'none';
                                        const svgDiv = document.createElement('div');
                                        parent.appendChild(svgDiv);
                                        ReactDOM.render(React.createElement(AnonymousAvatar, { size: 40 }), svgDiv);
                                    }
                                }) :
                                React.createElement(AnonymousAvatar, { size: 40 }),
                            React.createElement('div', null,
                                React.createElement('p', { className: "font-medium" }, selectedTicket.user_name),
                                React.createElement('p', { className: "text-sm text-gray-600" }, selectedTicket.user_email)
                            )
                        ),

                        React.createElement('div', null,
                            React.createElement('h4', { className: "font-medium mb-2" }, selectedTicket.subject),
                            React.createElement('p', { className: "text-gray-700 bg-gray-50 p-3 rounded" }, selectedTicket.message)
                        ),

                        React.createElement('div', { className: "flex items-center space-x-2 text-sm text-gray-600" },
                            React.createElement('span', null, `Statut: ${formatStatus(selectedTicket.status)}`),
                            React.createElement('span', null, `-`),
                            React.createElement('span', null, `Créé le ${new Date(selectedTicket.created_at).toLocaleDateString()}`)
                        ),

                        selectedTicket.admin_response && React.createElement('div', null,
                            React.createElement('h5', { className: "font-medium mb-2" }, "Réponse admin:"),
                            React.createElement('p', { className: "text-gray-700 bg-blue-50 p-3 rounded" }, selectedTicket.admin_response)
                        ),

                        React.createElement('div', null,
                            React.createElement('h5', { className: "font-medium mb-2" }, "Répondre au ticket:"),
                            React.createElement('textarea', {
                                value: response,
                                onChange: (e) => setResponse(e.target.value),
                                placeholder: "Votre réponse...",
                                className: "w-full p-3 border rounded-lg",
                                rows: 4
                            }),
                            React.createElement('button', {
                                onClick: respondToTicket,
                                className: "btn-primary px-4 py-2 rounded-lg mt-2"
                            }, "Envoyer la réponse")
                        )
                    )
                );
            }

            return React.createElement('div', { className: "bg-white rounded-lg p-6", style: {boxShadow: 'var(--shadow-md)'} },
                React.createElement('h3', { className: "text-lg font-bold mb-6" }, "Support Tickets"),

                tickets.length === 0
                    ? React.createElement('div', { className: "text-center py-8 text-gray-500" }, "Aucun ticket")
                    : React.createElement('div', { className: "space-y-4" },
                        tickets.map(ticket =>
                            React.createElement('div', {
                                key: ticket.id,
                                onClick: () => setSelectedTicket(ticket),
                                className: "border rounded-lg p-4 cursor-pointer hover:bg-gray-50 transition-colors"
                            },
                                React.createElement('div', { className: "flex items-center justify-between mb-2" },
                                    React.createElement('h4', { className: "font-medium" }, ticket.subject),
                                    React.createElement('span', {
                                        className: `px-2 py-1 text-xs rounded-full status-${ticket.status}`
                                    }, formatStatus(ticket.status))
                                ),
                                React.createElement('div', { className: "flex items-center space-x-3 text-sm text-gray-600" },
                                    React.createElement('span', null, `Par: ${ticket.user_name}`),
                                    React.createElement('span', null, `"`),
                                    React.createElement('span', null, new Date(ticket.created_at).toLocaleDateString())
                                ),
                                React.createElement('p', { className: "text-sm text-gray-700 mt-2 truncate" }, ticket.message)
                            )
                        )
                    )
            );
        };

        // Category Modal Component
        const CategoryModal = ({ isOpen, onClose, editingCategory, formData, setFormData, onSubmit }) => {
            if (!isOpen) return null;

            return React.createElement('div', { className: "fixed inset-0 z-50 flex items-center justify-center modal-overlay" },
                React.createElement('div', { className: "bg-white rounded-lg p-6 w-full max-w-md mx-4" },
                    React.createElement('h3', { className: "text-lg font-bold mb-4" },
                        editingCategory ? 'Modifier la catégorie' : 'Nouvelle catégorie'
                    ),
                    React.createElement('form', { onSubmit: onSubmit, className: "space-y-4" },
                        React.createElement('input', {
                            type: "text",
                            value: formData.name,
                            onChange: (e) => setFormData({...formData, name: e.target.value}),
                            placeholder: "Nom de la catégorie",
                            className: "w-full p-3 border rounded-lg",
                            required: true
                        }),
                        React.createElement('input', {
                            type: "text",
                            value: formData.icon,
                            onChange: (e) => setFormData({...formData, icon: e.target.value}),
                            placeholder: "Icône (emoji ou symbole)",
                            className: "w-full p-3 border rounded-lg",
                            maxLength: 10
                        }),
                        React.createElement('div', { className: "text-xs text-gray-500" },
                            "Exemples d'icônes : 💻 🎨 ✍️ 📈 🌍 📱 🎬 🎵"
                        ),
                        React.createElement('div', { className: "flex space-x-2" },
                            React.createElement('button', {
                                type: "submit",
                                className: "btn-primary px-4 py-2 rounded-lg flex-1"
                            }, editingCategory ? 'Modifier' : 'Créer'),
                            React.createElement('button', {
                                type: "button",
                                onClick: onClose,
                                className: "btn-secondary px-4 py-2 rounded-lg flex-1"
                            }, "Annuler")
                        )
                    )
                )
            );
        };

        // Admin Categories Tab
        const AdminCategoriesTab = ({ data, loadData }) => {
            const [categories, setCategories] = useState(data.categories || []);
            const [showModal, setShowModal] = useState(false);
            const [editingCategory, setEditingCategory] = useState(null);
            const [formData, setFormData] = useState({
                name: '',
                icon: ''
            });
            const [loading, setLoading] = useState(false);

            const loadCategories = async () => {
                setLoading(true);
                const result = await apiCall('categories.php');
                if (result.success) {
                    setCategories(result.categories);
                }
                setLoading(false);
            };

            const handleSubmit = async (e) => {
                e.preventDefault();

                const method = editingCategory ? 'PUT' : 'POST';
                const payload = editingCategory
                    ? { ...formData, id: editingCategory.id }
                    : formData;

                const result = await apiCall('categories.php', method, payload);

                if (result.success) {
                    handleCloseModal();
                    loadCategories();
                    loadData(); // Recharger les données du dashboard
                } else {
                    alert(result.error || 'Erreur lors de la sauvegarde');
                }
            };

            const handleCloseModal = () => {
                setShowModal(false);
                setEditingCategory(null);
                setFormData({ name: '', icon: '' });
            };

            const handleEdit = (category) => {
                setEditingCategory(category);
                setFormData({
                    name: category.name,
                    icon: category.icon || ''
                });
                setShowModal(true);
            };

            const handleDelete = async (categoryId) => {
                if (confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')) {
                    const result = await apiCall('categories.php', 'DELETE', { id: categoryId });
                    if (result.success) {
                        loadCategories();
                        loadData();
                    } else {
                        alert(result.error || 'Erreur lors de la suppression');
                    }
                }
            };

            if (loading && categories.length === 0) {
                return React.createElement('div', { className: "flex items-center justify-center h-64" },
                    React.createElement('div', { className: "text-lg" }, "Chargement...")
                );
            }

            return React.createElement('div', { className: "space-y-6" },
                React.createElement('div', { className: "bg-white rounded-lg p-6", style: {boxShadow: 'var(--shadow-md)'} },
                    React.createElement('div', { className: "flex items-center justify-between mb-6" },
                        React.createElement('h3', { className: "text-lg font-bold" }, "Gestion des Catégories"),
                        React.createElement('button', {
                            onClick: () => setShowModal(true),
                            className: "btn-primary px-4 py-2 rounded-lg"
                        }, "Nouvelle catégorie")
                    ),

                    categories.length === 0
                        ? React.createElement('div', { className: "text-center py-8 text-gray-500" }, "Aucune catégorie")
                        : React.createElement('div', { className: "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" },
                            categories.map(category =>
                                React.createElement('div', { key: category.id, className: "border rounded-lg p-4 hover:shadow-lg transition-shadow" },
                                    React.createElement('div', { className: "flex items-center mb-3" },
                                        category.icon && React.createElement('span', { className: "text-2xl mr-3" }, category.icon),
                                        React.createElement('h4', { className: "font-medium" }, category.name)
                                    ),
                                    React.createElement('div', { className: "text-xs text-gray-500 mb-3" },
                                        `Slug: ${category.slug}`
                                    ),
                                    React.createElement('div', { className: "text-xs text-gray-500 mb-4" },
                                        `Créée le ${new Date(category.created_at).toLocaleDateString()}`
                                    ),
                                    React.createElement('div', { className: "flex space-x-2" },
                                        React.createElement('button', {
                                            onClick: () => handleEdit(category),
                                            className: "btn-secondary px-3 py-1 text-sm rounded flex-1"
                                        }, "Modifier"),
                                        React.createElement('button', {
                                            onClick: () => handleDelete(category.id),
                                            className: "px-3 py-1 text-sm rounded flex-1 bg-red-100 text-red-800 hover:bg-red-200"
                                        }, "Supprimer")
                                    )
                                )
                            )
                        )
                ),

                React.createElement(CategoryModal, {
                    isOpen: showModal,
                    onClose: handleCloseModal,
                    editingCategory,
                    formData,
                    setFormData,
                    onSubmit: handleSubmit
                })
            );
        };

        // Render Dashboard
        const root = ReactDOM.createRoot(document.getElementById('dashboard-root'));
        root.render(React.createElement(Dashboard));
    </script>
    <?php include __DIR__ . '/../../includes/Footer.php';?>
</body>
</html>