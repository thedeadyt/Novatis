/**
 * Système de gestion du thème global
 * Gère le passage entre mode clair et mode sombre
 */

const ThemeManager = {
    // Clé de stockage localStorage
    STORAGE_KEY: 'novatis_theme',

    /**
     * Initialise le gestionnaire de thème
     */
    init() {
        // Appliquer le thème sauvegardé au chargement
        this.applyStoredTheme();

        // Écouter les changements de thème dans d'autres onglets
        window.addEventListener('storage', (e) => {
            if (e.key === this.STORAGE_KEY) {
                this.applyTheme(e.newValue === 'dark');
            }
        });

        // Détecter la préférence système SEULEMENT si aucun thème n'est défini ET qu'on n'a jamais défini de thème
        // Ne pas réinitialiser après le premier chargement
        if (!localStorage.getItem(this.STORAGE_KEY) && !sessionStorage.getItem('theme_initialized')) {
            this.detectSystemPreference();
            sessionStorage.setItem('theme_initialized', 'true');
        }
    },

    /**
     * Applique le thème sauvegardé dans localStorage
     */
    applyStoredTheme() {
        const theme = localStorage.getItem(this.STORAGE_KEY);
        const isDark = theme === 'dark';
        this.applyTheme(isDark);
    },

    /**
     * Applique le thème (clair ou sombre)
     * @param {boolean} isDark - true pour le mode sombre, false pour le mode clair
     */
    applyTheme(isDark) {
        const html = document.documentElement;

        if (isDark) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }

        // Mettre à jour tous les toggles de thème sur la page
        this.updateAllToggles(isDark);

        // Émettre un événement personnalisé
        const event = new CustomEvent('themeChanged', { detail: { isDark } });
        window.dispatchEvent(event);
    },

    /**
     * Bascule entre mode clair et sombre
     */
    toggle() {
        const isDark = !this.isDark();
        this.setTheme(isDark ? 'dark' : 'light');
    },

    /**
     * Définit le thème
     * @param {string} theme - 'dark' ou 'light'
     */
    setTheme(theme) {
        localStorage.setItem(this.STORAGE_KEY, theme);
        this.applyTheme(theme === 'dark');
    },

    /**
     * Vérifie si le mode sombre est actif
     * @returns {boolean}
     */
    isDark() {
        return document.documentElement.classList.contains('dark');
    },

    /**
     * Récupère le thème actuel
     * @returns {string} 'dark' ou 'light'
     */
    getCurrentTheme() {
        return this.isDark() ? 'dark' : 'light';
    },

    /**
     * Détecte la préférence système de l'utilisateur
     */
    detectSystemPreference() {
        // Ne détecter que si aucun thème n'a jamais été défini
        if (localStorage.getItem(this.STORAGE_KEY)) {
            return; // Déjà défini, ne pas changer
        }

        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            this.setTheme('dark');
        } else {
            this.setTheme('light');
        }

        // Écouter les changements de préférence système
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            if (!localStorage.getItem(this.STORAGE_KEY)) {
                this.setTheme(e.matches ? 'dark' : 'light');
            }
        });
    },

    /**
     * Met à jour tous les toggles de thème sur la page
     * @param {boolean} isDark
     */
    updateAllToggles(isDark) {
        // Mettre à jour les checkboxes
        const toggles = document.querySelectorAll('#darkModeToggle, .theme-toggle-checkbox');
        toggles.forEach(toggle => {
            if (toggle) {
                toggle.checked = isDark;
            }
        });

        // Mettre à jour les boutons
        const buttons = document.querySelectorAll('[data-theme-toggle]');
        buttons.forEach(button => {
            const icon = button.querySelector('i');
            if (icon) {
                if (isDark) {
                    icon.className = 'fas fa-sun';
                } else {
                    icon.className = 'fas fa-moon';
                }
            }
        });
    },

    /**
     * Crée un bouton de toggle pour le thème
     * @param {string} containerSelector - Sélecteur du conteneur où ajouter le bouton
     * @param {object} options - Options de configuration
     */
    createToggleButton(containerSelector, options = {}) {
        const container = document.querySelector(containerSelector);
        if (!container) {
            console.warn(`Container ${containerSelector} not found`);
            return;
        }

        const defaults = {
            className: 'theme-toggle-btn',
            showLabel: false,
            position: 'append' // 'append' ou 'prepend'
        };

        const config = { ...defaults, ...options };
        const isDark = this.isDark();

        const button = document.createElement('button');
        button.className = config.className;
        button.setAttribute('data-theme-toggle', 'true');
        button.setAttribute('aria-label', 'Changer le thème');
        button.innerHTML = `
            <i class="fas ${isDark ? 'fa-sun' : 'fa-moon'}"></i>
            ${config.showLabel ? `<span class="ml-2">${isDark ? 'Mode clair' : 'Mode sombre'}</span>` : ''}
        `;

        button.addEventListener('click', () => {
            this.toggle();
        });

        if (config.position === 'prepend') {
            container.prepend(button);
        } else {
            container.appendChild(button);
        }

        return button;
    },

    /**
     * Initialise les toggles existants sur la page
     */
    initializeToggles() {
        // Toggles checkbox
        const checkboxToggles = document.querySelectorAll('#darkModeToggle, .theme-toggle-checkbox');
        checkboxToggles.forEach(toggle => {
            toggle.checked = this.isDark();
            toggle.addEventListener('change', (e) => {
                this.setTheme(e.target.checked ? 'dark' : 'light');
            });
        });

        // Toggles button
        const buttonToggles = document.querySelectorAll('[data-theme-toggle]');
        buttonToggles.forEach(button => {
            button.addEventListener('click', () => {
                this.toggle();
            });
        });

        // Boutons de sauvegarde de thème (comme dans Parametres.php)
        const saveButtons = document.querySelectorAll('#saveThemeBtn');
        saveButtons.forEach(button => {
            button.addEventListener('click', () => {
                const toggle = document.querySelector('#darkModeToggle');
                if (toggle) {
                    this.setTheme(toggle.checked ? 'dark' : 'light');
                    if (typeof window.toast !== 'undefined') {
                        window.toast.success('messages.saved', 'common', 'Préférences de thème enregistrées !');
                    }
                }
            });
        });
    }
};

// Auto-initialisation au chargement du DOM
document.addEventListener('DOMContentLoaded', () => {
    ThemeManager.init();
    ThemeManager.initializeToggles();
});

// Export pour utilisation dans d'autres scripts
window.ThemeManager = ThemeManager;
