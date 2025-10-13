<?php
require_once __DIR__ . '/../../config/config.php';

// Si l'utilisateur est déjà connecté, rediriger vers le dashboard
if (isUserLoggedIn()) {
    header('Location: ' . BASE_URL . '/pages/Dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | Inscription - Novatis</title>

    <!-- Variables CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/variables.css">

    <!-- Font Awesome pour les icônes OAuth -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- React & Babel -->
    <script src="https://unpkg.com/react@18/umd/react.development.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'custom-bg': '#e8e8e8',
                        'custom-white': '#ffffff',
                        'custom-black': '#1f2020',
                        'custom-red': '#B41200',
                        'accent-1': '#1f2020',
                        'accent-2': '#7F0D00',
                        'hover-1': '#464646',
                        'hover-2': '#E04830'
                    },
                    animation: {
                        'slide-in-left': 'slideInLeft 0.6s ease-out',
                        'slide-in-right': 'slideInRight 0.6s ease-out',
                        'fade-in': 'fadeIn 0.4s ease-out',
                        'shake': 'shake 0.5s ease-in-out'
                    },
                    keyframes: {
                        slideInLeft: {
                            '0%': { transform: 'translateX(-100%)', opacity: '0' },
                            '100%': { transform: 'translateX(0)', opacity: '1' }
                        },
                        slideInRight: {
                            '0%': { transform: 'translateX(100%)', opacity: '0' },
                            '100%': { transform: 'translateX(0)', opacity: '1' }
                        },
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' }
                        },
                        shake: {
                            '0%, 100%': { transform: 'translateX(0)' },
                            '10%, 30%, 50%, 70%, 90%': { transform: 'translateX(-10px)' },
                            '20%, 40%, 60%, 80%': { transform: 'translateX(10px)' }
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: var(--font-tinos);
            background-color: var(--color-bg);
            min-height: 100vh;
        }

        .auth-container {
            box-shadow: var(--shadow-lg);
            border-radius: 12px;
            overflow: hidden;
            background: var(--color-white);
            transition: all 0.3s ease;
        }

        .switch-panel {
            background: var(--color-red);
            color: var(--color-white);
            transition: all 0.5s ease-in-out;
        }

        .form-panel {
            background: var(--color-white);
            transition: all 0.5s ease-in-out;
        }

        .input-field {
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
            background: var(--color-white);
            color: var(--color-black);
        }

        .input-field:focus {
            border-color: var(--color-red);
            box-shadow: 0 0 0 3px rgba(180, 18, 0, 0.1);
            transform: translateY(-1px);
        }

        .btn-primary {
            background: var(--color-red);
            color: var(--color-white);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--color-hover-2);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-switch {
            background: rgba(255, 255, 255, 0.2);
            color: var(--color-white);
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .btn-switch:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            transform: scale(1.05);
        }

        .panel-transitioning {
            opacity: 0.8;
        }

        .panel-transitioning .switch-panel,
        .panel-transitioning .form-panel {
            transform: translateY(-5px);
        }

        .content-animate {
            transition: all 0.4s ease;
        }

        .content-transitioning .content-animate {
            opacity: 0.6;
            transform: scale(0.98);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        .logo-glow {
            filter: drop-shadow(0 4px 8px rgba(180, 18, 0, 0.3));
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center p-4">
    <div id="auth-root"></div>

    <script type="text/babel">
        const { useState, useEffect } = React;

        const AuthPage = () => {
            const [isLogin, setIsLogin] = useState(true);
            const [isTransitioning, setIsTransitioning] = useState(false);
            const [formData, setFormData] = useState({
                firstname: '',
                lastname: '',
                pseudo: '',
                email: '',
                password: '',
                confirmPassword: ''
            });
            const [errors, setErrors] = useState({});
            const [loading, setLoading] = useState(false);
            const [show2FA, setShow2FA] = useState(false);
            const [twoFactorCode, setTwoFactorCode] = useState('');

            // Vérifier l'URL pour ouvrir la bonne section
            useEffect(() => {
                const urlParams = new URLSearchParams(window.location.search);
                const mode = urlParams.get('mode');

                if (mode === 'register') {
                    setIsLogin(false);
                } else if (mode === 'login') {
                    setIsLogin(true);
                }
            }, []);

            const switchMode = () => {
                setIsTransitioning(true);

                // Reset form immediately
                setFormData({
                    firstname: '',
                    lastname: '',
                    pseudo: '',
                    email: '',
                    password: '',
                    confirmPassword: ''
                });
                setErrors({});

                setTimeout(() => {
                    setIsLogin(!isLogin);
                }, 150);

                setTimeout(() => {
                    setIsTransitioning(false);
                }, 600);
            };

            const handleInputChange = (e) => {
                const { name, value } = e.target;
                setFormData(prev => ({ ...prev, [name]: value }));
                if (errors[name]) {
                    setErrors(prev => ({ ...prev, [name]: '' }));
                }
            };

            const validateForm = () => {
                const newErrors = {};

                if (!isLogin) {
                    if (!formData.firstname.trim()) newErrors.firstname = 'Le prénom est requis';
                    if (!formData.lastname.trim()) newErrors.lastname = 'Le nom est requis';
                    if (!formData.pseudo.trim()) newErrors.pseudo = 'Le pseudo est requis';
                    if (formData.pseudo.length < 3) newErrors.pseudo = 'Le pseudo doit faire au moins 3 caractères';
                    if (formData.password !== formData.confirmPassword) {
                        newErrors.confirmPassword = 'Les mots de passe ne correspondent pas';
                    }
                }

                if (!formData.email.trim()) newErrors.email = 'Email ou pseudo requis';
                if (!formData.password.trim()) newErrors.password = 'Le mot de passe est requis';
                if (!isLogin && formData.password.length < 6) {
                    newErrors.password = 'Le mot de passe doit faire au moins 6 caractères';
                }

                setErrors(newErrors);
                return Object.keys(newErrors).length === 0;
            };

            const handleSubmit = async (e) => {
                e.preventDefault();

                if (!validateForm()) return;

                setLoading(true);

                try {
                    const endpoint = isLogin ? 'auth/login.php' : 'auth/register.php';

                    // Préparer les données selon le mode
                    const requestData = isLogin
                        ? { email: formData.email, password: formData.password }
                        : formData;

                    const response = await fetch(`<?= BASE_URL ?>/api/${endpoint}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(requestData)
                    });

                    const result = await response.json();

                    if (result.success) {
                        window.location.href = '<?= BASE_URL ?>/Dashboard';
                    } else if (result.require_2fa) {
                        // A2F requis - afficher le modal
                        setShow2FA(true);
                        setLoading(false);
                    } else {
                        setErrors({ general: result.error || result.message || 'Une erreur est survenue' });
                    }
                } catch (error) {
                    setErrors({ general: 'Erreur de connexion au serveur' });
                } finally {
                    setLoading(false);
                }
            };

            const handleVerify2FA = async (e) => {
                e.preventDefault();

                if (twoFactorCode.length !== 6) {
                    setErrors({ twoFactor: 'Le code doit contenir 6 chiffres' });
                    return;
                }

                setLoading(true);
                try {
                    const twoFAResponse = await fetch(`<?= BASE_URL ?>/api/auth/login.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            ...formData,
                            two_factor_code: twoFactorCode
                        })
                    });

                    const twoFAResult = await twoFAResponse.json();

                    if (twoFAResult.success) {
                        window.location.href = '<?= BASE_URL ?>/Dashboard';
                    } else {
                        setErrors({ twoFactor: twoFAResult.error || 'Code incorrect' });
                        setTwoFactorCode('');
                    }
                } catch (error) {
                    setErrors({ twoFactor: 'Erreur de connexion au serveur' });
                } finally {
                    setLoading(false);
                }
            };

            const handleOAuthLogin = (provider) => {
                const width = 600;
                const height = 700;
                const left = (screen.width - width) / 2;
                const top = (screen.height - height) / 2;

                const popup = window.open(
                    `<?= BASE_URL ?>/api/oauth/authorize.php?provider=${provider}`,
                    `oauth_${provider}`,
                    `width=${width},height=${height},left=${left},top=${top},scrollbars=yes`
                );

                // Écouter les messages du popup
                const messageHandler = (event) => {
                    if (event.origin !== window.location.origin) return;

                    if (event.data.type === 'oauth_success') {
                        popup.close();
                        window.location.href = '<?= BASE_URL ?>/Dashboard';
                    } else if (event.data.type === 'oauth_error') {
                        popup.close();
                        setErrors({ general: event.data.message || 'Erreur lors de la connexion OAuth' });
                    }
                };

                window.addEventListener('message', messageHandler);

                // Nettoyer l'écouteur si le popup est fermé manuellement
                const checkPopup = setInterval(() => {
                    if (popup.closed) {
                        clearInterval(checkPopup);
                        window.removeEventListener('message', messageHandler);
                    }
                }, 500);
            };

            return React.createElement('div', {
                className: "w-full max-w-6xl mx-auto"
            },
                // Modal A2F
                show2FA && React.createElement('div', {
                    className: "fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4",
                    onClick: (e) => {
                        if (e.target === e.currentTarget) {
                            setShow2FA(false);
                            setTwoFactorCode('');
                            setErrors({});
                        }
                    }
                },
                    React.createElement('div', {
                        className: "bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 animate-fade-in"
                    },
                        // Header du modal
                        React.createElement('div', {
                            className: "text-center mb-6"
                        },
                            React.createElement('div', {
                                className: "w-16 h-16 bg-gradient-to-br from-red-500 to-red-700 rounded-full flex items-center justify-center mx-auto mb-4"
                            },
                                React.createElement('svg', {
                                    className: "w-8 h-8 text-white",
                                    fill: "none",
                                    stroke: "currentColor",
                                    viewBox: "0 0 24 24"
                                },
                                    React.createElement('path', {
                                        strokeLinecap: "round",
                                        strokeLinejoin: "round",
                                        strokeWidth: "2",
                                        d: "M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                    })
                                )
                            ),
                            React.createElement('h3', {
                                className: "text-2xl font-bold text-gray-900 mb-2"
                            }, "Authentification à deux facteurs"),
                            React.createElement('p', {
                                className: "text-gray-600"
                            }, "Entrez le code à 6 chiffres généré par votre application")
                        ),

                        // Formulaire A2F
                        React.createElement('form', {
                            onSubmit: handleVerify2FA,
                            className: "space-y-6"
                        },
                            // Erreur
                            errors.twoFactor && React.createElement('div', {
                                className: "bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm text-center"
                            }, errors.twoFactor),

                            // Input code
                            React.createElement('div', null,
                                React.createElement('input', {
                                    type: "text",
                                    value: twoFactorCode,
                                    onChange: (e) => {
                                        const value = e.target.value.replace(/\D/g, '').slice(0, 6);
                                        setTwoFactorCode(value);
                                        if (errors.twoFactor) setErrors({});
                                    },
                                    placeholder: "000000",
                                    maxLength: 6,
                                    autoFocus: true,
                                    className: "w-full px-6 py-4 text-center text-2xl font-mono tracking-widest rounded-lg border-2 border-gray-300 focus:border-red-500 focus:ring-4 focus:ring-red-100 outline-none transition-all"
                                })
                            ),

                            // Boutons
                            React.createElement('div', {
                                className: "flex gap-3"
                            },
                                React.createElement('button', {
                                    type: "button",
                                    onClick: () => {
                                        setShow2FA(false);
                                        setTwoFactorCode('');
                                        setErrors({});
                                    },
                                    className: "flex-1 px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-all"
                                }, "Annuler"),
                                React.createElement('button', {
                                    type: "submit",
                                    disabled: loading || twoFactorCode.length !== 6,
                                    className: "flex-1 px-6 py-3 bg-gradient-to-r from-red-500 to-red-700 text-white rounded-lg font-semibold hover:from-red-600 hover:to-red-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg"
                                }, loading ? "Vérification..." : "Vérifier")
                            )
                        )
                    )
                ),

                React.createElement('div', {
                    className: `auth-container grid grid-cols-1 lg:grid-cols-2 min-h-[600px] lg:h-[600px] relative overflow-hidden ${isTransitioning ? 'panel-transitioning content-transitioning' : ''}`
                },
                    // Panel de switch (côté rouge avec bouton)
                    React.createElement('div', {
                        className: `switch-panel flex items-center justify-center p-6 lg:p-12 relative z-10 ${
                            isLogin ? 'lg:order-2' : 'lg:order-1'
                        }`
                    },
                        React.createElement('div', {
                            className: "text-center content-animate"
                        },
                            // Logo
                            React.createElement('div', {
                                className: "mb-6 lg:mb-8"
                            },
                                React.createElement('img', {
                                    src: "<?= BASE_URL ?>/assets/img/logo.png",
                                    alt: "Novatis Logo",
                                    className: "w-16 h-16 lg:w-24 lg:h-24 mx-auto rounded-full logo-glow"
                                })
                            ),

                            // Titre principal
                            React.createElement('h1', {
                                className: "text-2xl lg:text-4xl font-bold mb-3 lg:mb-4"
                            }, "Novatis"),

                            // Sous-titre et description
                            React.createElement('div', {
                                className: "mb-6 lg:mb-8"
                            },
                                React.createElement('h2', {
                                    className: "text-lg lg:text-2xl font-semibold mb-2 lg:mb-3"
                                }, isLogin ? "Nouveau sur Novatis ?" : "Bon retour parmi nous !"),
                                React.createElement('p', {
                                    className: "text-sm lg:text-lg opacity-90 leading-relaxed px-2 lg:px-0"
                                },
                                    isLogin
                                        ? "Rejoignez notre communauté de freelances étudiants et commencez à proposer vos services dès maintenant."
                                        : "Connectez-vous pour accéder à votre espace personnel et gérer vos services."
                                )
                            ),

                            // Gros bouton de switch
                            React.createElement('button', {
                                onClick: switchMode,
                                disabled: isTransitioning,
                                className: "btn-switch px-6 py-3 lg:px-8 lg:py-4 text-lg lg:text-xl font-bold rounded-full hover:scale-105 transition-all duration-300 disabled:opacity-50"
                            }, isLogin ? "S'inscrire" : "Se connecter")
                        )
                    ),

                    // Panel de formulaire
                    React.createElement('div', {
                        className: `form-panel flex items-center justify-center p-6 lg:p-12 ${
                            isLogin ? 'lg:order-1' : 'lg:order-2'
                        }`
                    },
                        React.createElement('div', {
                            className: "w-full max-w-md content-animate"
                        },
                            // Titre du formulaire
                            React.createElement('div', {
                                className: "text-center mb-6 lg:mb-8"
                            },
                                React.createElement('h2', {
                                    className: "text-2xl lg:text-3xl font-bold mb-2",
                                    style: { color: 'var(--color-black)' }
                                }, isLogin ? "Connexion" : "Inscription"),
                                React.createElement('p', {
                                    className: "text-gray-600 text-sm lg:text-base"
                                }, isLogin ? "Accédez à votre espace" : "Créez votre compte")
                            ),

                            // Formulaire
                            React.createElement('form', {
                                onSubmit: handleSubmit,
                                className: "space-y-4 lg:space-y-6"
                            },
                                // Erreur générale
                                errors.general && React.createElement('div', {
                                    className: "bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm"
                                }, errors.general),

                                // Champs d'inscription seulement
                                !isLogin && React.createElement('div', {
                                    className: "space-y-4"
                                },
                                    // Prénom et Nom (côte à côte)
                                    React.createElement('div', {
                                        className: "grid grid-cols-2 gap-3"
                                    },
                                        // Prénom
                                        React.createElement('div', null,
                                            React.createElement('input', {
                                                type: "text",
                                                name: "firstname",
                                                value: formData.firstname,
                                                onChange: handleInputChange,
                                                placeholder: "Prénom",
                                                className: `w-full px-4 py-3 rounded-lg input-field focus:outline-none ${errors.firstname ? 'border-red-500' : ''}`
                                            }),
                                            errors.firstname && React.createElement('p', {
                                                className: "text-red-500 text-xs mt-1"
                                            }, errors.firstname)
                                        ),
                                        // Nom
                                        React.createElement('div', null,
                                            React.createElement('input', {
                                                type: "text",
                                                name: "lastname",
                                                value: formData.lastname,
                                                onChange: handleInputChange,
                                                placeholder: "Nom",
                                                className: `w-full px-4 py-3 rounded-lg input-field focus:outline-none ${errors.lastname ? 'border-red-500' : ''}`
                                            }),
                                            errors.lastname && React.createElement('p', {
                                                className: "text-red-500 text-xs mt-1"
                                            }, errors.lastname)
                                        )
                                    ),
                                    // Pseudo
                                    React.createElement('div', null,
                                        React.createElement('input', {
                                            type: "text",
                                            name: "pseudo",
                                            value: formData.pseudo,
                                            onChange: handleInputChange,
                                            placeholder: "Pseudo (unique)",
                                            className: `w-full px-4 py-3 rounded-lg input-field focus:outline-none ${errors.pseudo ? 'border-red-500' : ''}`
                                        }),
                                        errors.pseudo && React.createElement('p', {
                                            className: "text-red-500 text-sm mt-1"
                                        }, errors.pseudo)
                                    )
                                ),

                                // Email
                                React.createElement('div', null,
                                    React.createElement('input', {
                                        type: "text",
                                        name: "email",
                                        value: formData.email,
                                        onChange: handleInputChange,
                                        placeholder: isLogin ? "Email ou pseudo" : "Adresse email",
                                        className: `w-full px-4 py-3 rounded-lg input-field focus:outline-none ${errors.email ? 'border-red-500' : ''}`
                                    }),
                                    errors.email && React.createElement('p', {
                                        className: "text-red-500 text-sm mt-1"
                                    }, errors.email)
                                ),

                                // Mot de passe
                                React.createElement('div', null,
                                    React.createElement('input', {
                                        type: "password",
                                        name: "password",
                                        value: formData.password,
                                        onChange: handleInputChange,
                                        placeholder: "Mot de passe",
                                        className: `w-full px-4 py-3 rounded-lg input-field focus:outline-none ${errors.password ? 'border-red-500' : ''}`
                                    }),
                                    errors.password && React.createElement('p', {
                                        className: "text-red-500 text-sm mt-1"
                                    }, errors.password)
                                ),

                                // Confirmation mot de passe (inscription)
                                !isLogin && React.createElement('div', null,
                                    React.createElement('input', {
                                        type: "password",
                                        name: "confirmPassword",
                                        value: formData.confirmPassword,
                                        onChange: handleInputChange,
                                        placeholder: "Confirmer le mot de passe",
                                        className: `w-full px-4 py-3 rounded-lg input-field focus:outline-none ${errors.confirmPassword ? 'border-red-500' : ''}`
                                    }),
                                    errors.confirmPassword && React.createElement('p', {
                                        className: "text-red-500 text-sm mt-1"
                                    }, errors.confirmPassword)
                                ),

                                // Bouton submit
                                React.createElement('button', {
                                    type: "submit",
                                    disabled: loading,
                                    className: "w-full btn-primary font-semibold py-3 px-6 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
                                }, loading ? (isLogin ? "Connexion..." : "Inscription...") : (isLogin ? "Se connecter" : "S'inscrire")),

                                // Séparateur OU
                                React.createElement('div', {
                                    className: "relative my-6"
                                },
                                    React.createElement('div', {
                                        className: "absolute inset-0 flex items-center"
                                    },
                                        React.createElement('div', {
                                            className: "w-full border-t border-gray-300"
                                        })
                                    ),
                                    React.createElement('div', {
                                        className: "relative flex justify-center text-sm"
                                    },
                                        React.createElement('span', {
                                            className: "px-4 bg-white text-gray-500 font-medium"
                                        }, "Ou continuer avec")
                                    )
                                ),

                                // Boutons OAuth
                                React.createElement('div', {
                                    className: "grid grid-cols-3 gap-3"
                                },
                                    // Google
                                    React.createElement('button', {
                                        type: "button",
                                        onClick: () => handleOAuthLogin('google'),
                                        disabled: loading,
                                        className: "flex items-center justify-center px-4 py-3 border-2 border-gray-300 rounded-lg hover:border-red-400 hover:bg-red-50 transition-all disabled:opacity-50 disabled:cursor-not-allowed group"
                                    },
                                        React.createElement('i', {
                                            className: "fab fa-google text-xl text-red-500 group-hover:scale-110 transition-transform"
                                        })
                                    ),
                                    // Microsoft
                                    React.createElement('button', {
                                        type: "button",
                                        onClick: () => handleOAuthLogin('microsoft'),
                                        disabled: loading,
                                        className: "flex items-center justify-center px-4 py-3 border-2 border-gray-300 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-all disabled:opacity-50 disabled:cursor-not-allowed group"
                                    },
                                        React.createElement('i', {
                                            className: "fab fa-microsoft text-xl text-blue-500 group-hover:scale-110 transition-transform"
                                        })
                                    ),
                                    // GitHub
                                    React.createElement('button', {
                                        type: "button",
                                        onClick: () => handleOAuthLogin('github'),
                                        disabled: loading,
                                        className: "flex items-center justify-center px-4 py-3 border-2 border-gray-300 rounded-lg hover:border-gray-700 hover:bg-gray-50 transition-all disabled:opacity-50 disabled:cursor-not-allowed group"
                                    },
                                        React.createElement('i', {
                                            className: "fab fa-github text-xl text-gray-800 group-hover:scale-110 transition-transform"
                                        })
                                    )
                                )
                            )
                        )
                    )
                )
            );
        };

        // Render
        ReactDOM.createRoot(document.getElementById('auth-root')).render(
            React.createElement(AuthPage)
        );
    </script>
</body>
</html>