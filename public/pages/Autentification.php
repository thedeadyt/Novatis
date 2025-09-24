<?php
require_once __DIR__ . '/../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si l'utilisateur est déjà connecté, rediriger vers le dashboard
if (isset($_SESSION['user'])) {
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
                name: '',
                pseudo: '',
                email: '',
                password: '',
                confirmPassword: ''
            });
            const [errors, setErrors] = useState({});
            const [loading, setLoading] = useState(false);

            const switchMode = () => {
                setIsTransitioning(true);

                // Reset form immediately
                setFormData({
                    name: '',
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
                    if (!formData.name.trim()) newErrors.name = 'Le nom est requis';
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
                    const endpoint = isLogin ? 'login.php' : 'register.php';
                    const response = await fetch(`<?= BASE_URL ?>/api/${endpoint}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(formData)
                    });

                    const result = await response.json();

                    if (result.success) {
                        window.location.href = '<?= BASE_URL ?>/pages/Dashboard.php';
                    } else {
                        setErrors({ general: result.error || 'Une erreur est survenue' });
                    }
                } catch (error) {
                    setErrors({ general: 'Erreur de connexion au serveur' });
                } finally {
                    setLoading(false);
                }
            };

            return React.createElement('div', {
                className: "w-full max-w-6xl mx-auto"
            },
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
                                    src: "<?= BASE_URL ?>/asset/img/logo.png",
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
                                    // Nom
                                    React.createElement('div', null,
                                        React.createElement('input', {
                                            type: "text",
                                            name: "name",
                                            value: formData.name,
                                            onChange: handleInputChange,
                                            placeholder: "Nom complet",
                                            className: `w-full px-4 py-3 rounded-lg input-field focus:outline-none ${errors.name ? 'border-red-500' : ''}`
                                        }),
                                        errors.name && React.createElement('p', {
                                            className: "text-red-500 text-sm mt-1"
                                        }, errors.name)
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
                                }, loading ? (isLogin ? "Connexion..." : "Inscription...") : (isLogin ? "Se connecter" : "S'inscrire"))
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