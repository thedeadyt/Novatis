/**
 * Toast Notification System for Novatis
 * Système de notifications toast avec support i18n
 */

(function() {
    'use strict';

    /**
     * Affiche une notification toast
     * @param {string} messageKey - Clé de traduction i18n (ex: "auth.messages.loginSuccess")
     * @param {string} type - Type de notification: success, error, warning, info
     * @param {string} namespace - Namespace i18n (par défaut: "common")
     * @param {string} fallbackMessage - Message de secours si i18n n'est pas disponible
     */
    function showToast(messageKey, type = 'info', namespace = 'common', fallbackMessage = '') {
        // Créer le container s'il n'existe pas
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed top-20 left-1/2 transform -translate-x-1/2 z-[9999] space-y-2 pointer-events-none';
            container.style.width = 'calc(100% - 2rem)';
            container.style.maxWidth = '28rem';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');

        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            warning: 'bg-yellow-500',
            info: 'bg-blue-500'
        };

        const icons = {
            success: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>',
            error: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>',
            warning: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>',
            info: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
        };

        // Traduire le message
        let message = fallbackMessage;
        if (typeof window.t === 'function') {
            try {
                message = window.t(messageKey, namespace) || fallbackMessage || messageKey;
            } catch (e) {
                console.warn('Translation failed for:', messageKey);
            }
        }

        toast.className = `${colors[type] || colors.info} text-white px-6 py-4 rounded-lg shadow-2xl flex items-center space-x-3 pointer-events-auto transform transition-all duration-300 ease-out`;
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-20px)';

        toast.innerHTML = `
            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                ${icons[type] || icons.info}
            </svg>
            <span class="flex-1 font-medium">${message}</span>
            <button class="text-white hover:text-gray-200 transition-colors focus:outline-none toast-close-btn">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;

        container.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        });

        // Close button handler
        const closeBtn = toast.querySelector('.toast-close-btn');
        closeBtn.addEventListener('click', () => removeToast(toast));

        // Auto-remove after 5 seconds
        const timeout = setTimeout(() => removeToast(toast), 5000);

        // Pause on hover
        toast.addEventListener('mouseenter', () => clearTimeout(timeout));
        toast.addEventListener('mouseleave', () => setTimeout(() => removeToast(toast), 2000));
    }

    function removeToast(toast) {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-20px)';
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 300);
    }

    /**
     * Raccourcis pour les types courants
     */
    window.toast = {
        success: (messageKey, namespace = 'common', fallback = '') =>
            showToast(messageKey, 'success', namespace, fallback),
        error: (messageKey, namespace = 'common', fallback = '') =>
            showToast(messageKey, 'error', namespace, fallback),
        warning: (messageKey, namespace = 'common', fallback = '') =>
            showToast(messageKey, 'warning', namespace, fallback),
        info: (messageKey, namespace = 'common', fallback = '') =>
            showToast(messageKey, 'info', namespace, fallback),
        show: showToast
    };

    // Exposer également showToast directement
    window.showToast = showToast;

    console.log('✅ Toast notification system loaded');
})();
