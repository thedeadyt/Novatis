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
        }

        .sidebar-mobile.open {
            transform: translateX(0);
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
            avatar: <?= json_encode($user['avatar'] ?? 'https://images.unsplash.com/photo-1494790108755-2616b612b194?w=150&h=150&fit=crop&crop=face') ?>,
            rating: <?= json_encode($user['rating'] ?? 0) ?>,
            baseUrl: <?= json_encode(BASE_URL) ?>
        };

        const isAdmin = <?= json_encode($isAdmin) ?>;

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

        // Dashboard Component
        const Dashboard = () => {
            const [activeTab, setActiveTab] = useState('overview');
            const [sidebarOpen, setSidebarOpen] = useState(false);
            const [data, setData] = useState({
                stats: {},
                services: [],
                orders: [],
                messages: [],
                users: [],
                conversations: [],
                portfolio: [],
                formerClients: [],
                categories: [],
                supportTickets: []
            });
            const [loading, setLoading] = useState(true);

            // Load data on mount
            useEffect(() => {
                loadDashboardData();
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
                    case 'messages': return React.createElement(MessagesTab, { data, setData });
                    case 'services': return React.createElement(ServicesTab, { data, loadData: loadDashboardData });
                    case 'orders': return React.createElement(OrdersTab, { data });
                    case 'portfolio': return React.createElement(PortfolioTab, { data, loadData: loadDashboardData });
                    case 'former-clients': return React.createElement(FormerClientsTab, { data });
                    case 'admin-users': return React.createElement(AdminUsersTab, { data, loadData: loadDashboardData });
                    case 'admin-support': return React.createElement(AdminSupportTab, { data, loadData: loadDashboardData });
                    default: return React.createElement(OverviewTab, { data, loadData: loadDashboardData });
                }
            };

            const getPageTitle = () => {
                switch(activeTab) {
                    case 'overview': return 'Vue d\'ensemble';
                    case 'messages': return 'Messages';
                    case 'services': return isAdmin ? 'Tous les Services' : 'Mes Services';
                    case 'orders': return 'Commandes';
                    case 'portfolio': return 'Portfolio';
                    case 'former-clients': return 'Anciens Clients';
                    case 'admin-users': return 'Gestion Utilisateurs';
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
                    handleLogout
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
                            React.createElement('div', { className: "flex items-center" },
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
        const Sidebar = ({ activeTab, setActiveTab, isOpen, onClose, data, handleLogout }) => {
            const userMenuItems = [
                { id: 'overview', icon: 'fas fa-chart-line', label: 'Vue d\'ensemble' },
                { id: 'messages', icon: 'fas fa-envelope', label: 'Messages', badge: data.unreadMessages || 0 },
                { id: 'services', icon: 'fas fa-cog', label: 'Mes Services' },
                { id: 'orders', icon: 'fas fa-briefcase', label: 'Commandes' },
                { id: 'portfolio', icon: 'fas fa-images', label: 'Portfolio' },
                { id: 'former-clients', icon: 'fas fa-users', label: 'Anciens Clients' }
            ];

            const adminMenuItems = [
                { id: 'overview', icon: 'fas fa-chart-line', label: 'Vue d\'ensemble' },
                { id: 'admin-users', icon: 'fas fa-users', label: 'Utilisateurs' },
                { id: 'services', icon: 'fas fa-cog', label: 'Tous les Services' },
                { id: 'orders', icon: 'fas fa-briefcase', label: 'Toutes les Commandes' },
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
            const StatsCard = ({ title, value, icon, color }) =>
                React.createElement('div', { className: "bg-white rounded-lg p-6", style: {boxShadow: 'var(--shadow-md)'} },
                    React.createElement('div', { className: "flex items-center justify-between" },
                        React.createElement('div', null,
                            React.createElement('p', { className: "text-sm opacity-70" }, title),
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
                        { title: "Note", value: `${data.stats.rating || 0}/5`, icon: "fas fa-star", color: "bg-yellow-100" }
                    ];
                }
            };

            return React.createElement('div', { className: "space-y-6" },
                // Stats
                React.createElement('div', { className: "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6" },
                    ...getStatsData().map(stat =>
                        React.createElement(StatsCard, { key: stat.title, ...stat })
                    )
                ),

                // Recent Activity
                React.createElement('div', { className: "grid grid-cols-1 lg:grid-cols-2 gap-6" },
                    // Services/Commandes
                    React.createElement('div', { className: "bg-white rounded-lg p-6", style: {boxShadow: 'var(--shadow-md)'} },
                        React.createElement('h3', { className: "text-lg font-bold mb-4" }, isAdmin ? "Services Récents" : "Mes Services"),
                        React.createElement('div', { className: "space-y-3" },
                            data.services.slice(0, 3).map(service =>
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
                            data.services.length === 0 && React.createElement('p', { className: "text-gray-500 text-center py-4" }, "Aucun service")
                        )
                    ),

                    // Commandes
                    React.createElement('div', { className: "bg-white rounded-lg p-6", style: {boxShadow: 'var(--shadow-md)'} },
                        React.createElement('h3', { className: "text-lg font-bold mb-4" }, "Commandes Récentes"),
                        React.createElement('div', { className: "space-y-3" },
                            data.orders.slice(0, 3).map(order =>
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
                            data.orders.length === 0 && React.createElement('p', { className: "text-gray-500 text-center py-4" }, "Aucune commande")
                        )
                    )
                )
            );
        };

        // Messages Tab
        const MessagesTab = ({ data, setData }) => {
            const [conversations, setConversations] = useState([]);
            const [selectedConversation, setSelectedConversation] = useState(null);
            const [messages, setMessages] = useState([]);
            const [newMessage, setNewMessage] = useState('');
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
                if (!newMessage.trim() || !selectedConversation) return;

                const result = await apiCall('messages.php', 'POST', {
                    order_id: selectedConversation.id,
                    content: newMessage
                });

                if (result.success) {
                    setNewMessage('');
                    loadMessages(selectedConversation.id);
                    loadConversations();
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
                                    onKeyPress: (e) => e.key === 'Enter' && sendMessage(),
                                    placeholder: "Tapez votre message...",
                                    className: "flex-1 message-input"
                                }),
                                React.createElement('button', {
                                    onClick: sendMessage,
                                    className: "btn-primary px-4 py-2 rounded-lg"
                                }, "Envoyer")
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
                                    React.createElement('img', {
                                        src: conv.contact_avatar || 'https://images.unsplash.com/photo-1494790108755-2616b612b194?w=40&h=40&fit=crop&crop=face',
                                        alt: conv.contact_name,
                                        className: "w-12 h-12 rounded-full object-cover"
                                    }),
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
                }
            };

            const handleCloseModal = () => {
                setShowServiceModal(false);
                setEditingService(null);
                setServiceFormData({ title: '', description: '', category_id: '', price: '', delivery_days: '', image: '' });
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

                data.services.length === 0
                    ? React.createElement('div', { className: "text-center py-8 text-gray-500" }, "Aucun service")
                    : React.createElement('div', { className: "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" },
                        data.services.map(service =>
                            React.createElement('div', { key: service.id, className: "border rounded-lg p-4 hover:shadow-lg transition-shadow" },
                                service.image && React.createElement('img', {
                                    src: service.image,
                                    alt: service.title,
                                    className: "w-full h-32 object-cover rounded-lg mb-3"
                                }),
                                React.createElement('h4', { className: "font-medium mb-2" }, service.title),
                                React.createElement('p', { className: "text-sm text-gray-600 mb-3 line-clamp-2" }, service.description),
                                React.createElement('div', { className: "flex items-center justify-between" },
                                    React.createElement('span', { className: "font-bold text-lg", style: {color: 'var(--color-red)'} }, `${service.price}€`),
                                    React.createElement('span', {
                                        className: `px-2 py-1 text-xs rounded-full ${service.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}`
                                    }, service.status)
                                ),
                                isAdmin && React.createElement('p', { className: "text-xs text-gray-500 mt-2" }, `par ${service.user_name}`)
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
        const OrdersTab = ({ data }) => {
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

                    data.portfolio.length === 0
                        ? React.createElement('div', { className: "text-center py-8 text-gray-500" }, "Aucun projet dans votre portfolio")
                        : React.createElement('div', { className: "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" },
                            data.portfolio.map(project =>
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

                data.formerClients.length === 0
                    ? React.createElement('div', { className: "text-center py-8 text-gray-500" }, "Aucun client pour le moment")
                    : React.createElement('div', { className: "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" },
                        data.formerClients.map(client =>
                            React.createElement('div', { key: client.id, className: "border rounded-lg p-4 hover:shadow-lg transition-shadow" },
                                React.createElement('div', { className: "flex items-center space-x-3 mb-3" },
                                    React.createElement('img', {
                                        src: client.avatar || 'https://images.unsplash.com/photo-1494790108755-2616b612b194?w=50&h=50&fit=crop&crop=face',
                                        alt: client.name,
                                        className: "w-12 h-12 rounded-full object-cover"
                                    }),
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
                                        React.createElement('img', {
                                            src: user.avatar || 'https://images.unsplash.com/photo-1494790108755-2616b612b194?w=40&h=40&fit=crop&crop=face',
                                            alt: user.name,
                                            className: "w-10 h-10 rounded-full object-cover"
                                        }),
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
                            React.createElement('img', {
                                src: selectedTicket.user_avatar || 'https://images.unsplash.com/photo-1494790108755-2616b612b194?w=40&h=40&fit=crop&crop=face',
                                alt: selectedTicket.user_name,
                                className: "w-10 h-10 rounded-full object-cover"
                            }),
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

        // Render Dashboard
        const root = ReactDOM.createRoot(document.getElementById('dashboard-root'));
        root.render(React.createElement(Dashboard));
    </script>
    <?php include __DIR__ . '/../../includes/Footer.php';?>
</body>
</html>