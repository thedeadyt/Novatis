/**
 * Système A2F complet pour Novatis
 * Gestion de l'activation, désactivation et codes de sauvegarde
 */

const TwoFactorAuth = {
    baseUrl: '',

    /**
     * Initialise le module avec l'URL de base
     */
    init(baseUrl) {
        this.baseUrl = baseUrl;
    },

    /**
     * Affiche un toast de notification
     */
    showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white font-semibold z-[9999]`;
        toast.style.background = type === 'success' ? '#10b981' : '#ef4444';
        toast.style.animation = 'slideInRight 0.3s ease-out';
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }, 2500);
    },

    /**
     * Affiche le modal d'activation A2F
     */
    showEnableModal() {
        const modal = document.createElement('div');
        modal.id = '2fa-modal';
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
        modal.onclick = (e) => { if (e.target === modal) this.closeModal(); };

        modal.innerHTML = `
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full relative overflow-hidden" style="animation: slideUp 0.3s ease-out;">
                <button onclick="TwoFactorAuth.closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 z-10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <!-- Étape 1: Information -->
                <div id="2fa-step-1" class="p-8">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-red-700 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Activer l'A2F</h3>
                        <p class="text-gray-600">Protégez votre compte avec une double authentification</p>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <p class="text-sm font-medium text-blue-900 mb-2">📱 Installez une application d'authentification :</p>
                        <div class="space-y-2">
                            <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank" class="block text-sm text-blue-600 hover:underline">• Google Authenticator (Android)</a>
                            <a href="https://apps.apple.com/app/google-authenticator/id388497605" target="_blank" class="block text-sm text-blue-600 hover:underline">• Google Authenticator (iOS)</a>
                            <a href="https://authy.com/download/" target="_blank" class="block text-sm text-blue-600 hover:underline">• Authy (Multi-plateforme)</a>
                        </div>
                    </div>

                    <button onclick="TwoFactorAuth.loadSetup()" class="w-full px-6 py-3 bg-gradient-to-r from-red-500 to-red-700 text-white rounded-lg font-semibold hover:from-red-600 hover:to-red-800 transition-all shadow-lg">
                        Continuer
                    </button>
                </div>

                <!-- Étape 2: Configuration -->
                <div id="2fa-step-2" style="display: none;" class="p-8">
                    <div class="text-center mb-6">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Scannez le QR Code</h3>
                        <p class="text-gray-600 text-sm">Utilisez votre application pour scanner ce code</p>
                    </div>

                    <div id="qr-code-container" class="flex justify-center mb-6 bg-gray-50 p-6 rounded-xl border-2 border-gray-200">
                        <div class="text-center text-gray-500">Chargement...</div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <p class="text-xs text-gray-600 mb-2 font-medium">Ou entrez cette clé manuellement :</p>
                        <div id="secret-key" class="bg-white p-3 rounded font-mono text-sm text-center border border-gray-200"></div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Entrez le code à 6 chiffres :</label>
                        <input type="text" id="2fa-code-input" maxlength="6" pattern="[0-9]{6}"
                               class="w-full px-4 py-3 text-center text-2xl font-mono tracking-widest border-2 border-gray-300 rounded-lg focus:border-red-500 focus:ring-4 focus:ring-red-100 outline-none"
                               placeholder="000000">
                    </div>

                    <div class="flex gap-3">
                        <button onclick="TwoFactorAuth.closeModal()" class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold transition-all">
                            Annuler
                        </button>
                        <button onclick="TwoFactorAuth.verifyCode()" class="flex-1 px-6 py-3 bg-gradient-to-r from-red-500 to-red-700 text-white rounded-lg font-semibold hover:from-red-600 hover:to-red-800 transition-all shadow-lg">
                            Vérifier
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
    },

    /**
     * Charge la configuration A2F (génère le QR code)
     */
    async loadSetup() {
        try {
            const response = await fetch(`${this.baseUrl}/api/parametres/2fa-setup.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'generate_secret' })
            });

            if (!response.ok) throw new Error('Erreur réseau');

            const data = await response.json();

            if (data.success) {
                document.getElementById('2fa-step-1').style.display = 'none';
                document.getElementById('2fa-step-2').style.display = 'block';
                document.getElementById('qr-code-container').innerHTML = `<img src="${data.qr_code_url}" alt="QR Code" class="max-w-full rounded-lg">`;
                document.getElementById('secret-key').textContent = data.secret;
                window.temp2FASecret = data.secret;

                // Focus sur l'input
                setTimeout(() => document.getElementById('2fa-code-input').focus(), 100);
            } else {
                this.showToast(data.message || 'Erreur lors de la génération', 'error');
            }
        } catch (error) {
            console.error('Erreur:', error);
            this.showToast('Erreur de connexion au serveur', 'error');
        }
    },

    /**
     * Vérifie le code et active l'A2F
     */
    async verifyCode() {
        const code = document.getElementById('2fa-code-input').value.trim();

        if (code.length !== 6 || !/^\d{6}$/.test(code)) {
            this.showToast('Le code doit contenir 6 chiffres', 'error');
            return;
        }

        try {
            const response = await fetch(`${this.baseUrl}/api/parametres/2fa-setup.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'enable_2fa',
                    secret: window.temp2FASecret,
                    code: code
                })
            });

            if (!response.ok) throw new Error('Erreur réseau');

            const data = await response.json();

            if (data.success) {
                this.closeModal();
                this.showBackupCodesAfterActivation(data.backup_codes);
            } else {
                this.showToast(data.message || 'Code incorrect', 'error');
                document.getElementById('2fa-code-input').value = '';
                document.getElementById('2fa-code-input').focus();
            }
        } catch (error) {
            console.error('Erreur:', error);
            this.showToast('Erreur de connexion au serveur', 'error');
        }
    },

    /**
     * Affiche les codes de sauvegarde après activation
     */
    showBackupCodesAfterActivation(codes) {
        const modal = document.createElement('div');
        modal.id = 'backup-codes-activation-modal';
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';

        modal.innerHTML = `
            <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full relative overflow-hidden" style="animation: slideUp 0.3s ease-out;">
                <div class="bg-gradient-to-r from-green-500 to-green-700 px-8 py-6">
                    <div class="flex items-center">
                        <div class="w-14 h-14 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-white mb-1">A2F activée avec succès !</h3>
                            <p class="text-white text-opacity-90 text-sm">Sauvegardez ces codes de secours</p>
                        </div>
                    </div>
                </div>

                <div class="p-8">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                        <p class="text-sm font-bold text-red-900 mb-2">⚠️ IMPORTANT - Conservez ces codes !</p>
                        <p class="text-sm text-red-800">
                            Ces codes ne seront affichés qu'une seule fois. Conservez-les dans un endroit sûr (gestionnaire de mots de passe, coffre-fort numérique).
                        </p>
                    </div>

                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 mb-6 border border-gray-200">
                        <div class="grid grid-cols-2 gap-4">
                            ${codes.map((code, index) => `
                                <div class="bg-white rounded-lg p-4 border-2 border-gray-200">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-gradient-to-br from-red-500 to-red-600 rounded-full flex items-center justify-center mr-3">
                                            <span class="text-white text-xs font-bold">${(index + 1).toString().padStart(2, '0')}</span>
                                        </div>
                                        <code class="font-mono text-base font-bold text-gray-800">${code}</code>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button onclick="TwoFactorAuth.downloadBackupCodes(${JSON.stringify(codes)})" class="flex-1 px-6 py-3 bg-gradient-to-r from-red-500 to-red-700 text-white rounded-lg font-semibold hover:from-red-600 hover:to-red-800 transition-all shadow-lg">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Télécharger
                        </button>
                        <button onclick="TwoFactorAuth.copyAllCodes(${JSON.stringify(codes)})" class="flex-1 px-6 py-3 bg-gray-600 text-white rounded-lg font-semibold hover:bg-gray-700 transition-all">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            Copier
                        </button>
                        <button onclick="TwoFactorAuth.closeActivationModal()" class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-all">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
    },

    /**
     * Ferme le modal d'activation des codes
     */
    closeActivationModal() {
        const modal = document.getElementById('backup-codes-activation-modal');
        if (modal) modal.remove();
        location.reload();
    },

    /**
     * Affiche le modal de désactivation A2F
     */
    showDisableModal() {
        const modal = document.createElement('div');
        modal.id = 'disable-2fa-modal';
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
        modal.onclick = (e) => { if (e.target === modal) this.closeDisableModal(); };

        modal.innerHTML = `
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full relative overflow-hidden" style="animation: slideUp 0.3s ease-out;">
                <button onclick="TwoFactorAuth.closeDisableModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 z-10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <!-- Header avec avertissement -->
                <div class="bg-gradient-to-r from-orange-500 to-red-600 px-8 py-6">
                    <div class="flex items-center">
                        <div class="w-14 h-14 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-white mb-1">Désactiver l'A2F</h3>
                            <p class="text-white text-opacity-90 text-sm">Action irréversible</p>
                        </div>
                    </div>
                </div>

                <!-- Contenu -->
                <div class="p-8">
                    <!-- Avertissement de sécurité -->
                    <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4 mb-6">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-red-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-bold text-red-900 mb-2">⚠️ Avertissement de sécurité</p>
                                <p class="text-sm text-red-800 leading-relaxed mb-2">
                                    En désactivant l'authentification à deux facteurs, vous :
                                </p>
                                <ul class="text-sm text-red-800 space-y-1 ml-4">
                                    <li>• Réduisez la sécurité de votre compte</li>
                                    <li>• Perdez tous vos codes de sauvegarde</li>
                                    <li>• Facilitez l'accès non autorisé à votre compte</li>
                                    <li>• Risquez la perte de vos données personnelles</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Informations -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <p class="text-sm text-blue-900">
                            <span class="font-semibold">💡 Conseil :</span> Si vous rencontrez des problèmes avec votre application d'authentification, utilisez plutôt un code de sauvegarde au lieu de désactiver l'A2F.
                        </p>
                    </div>

                    <!-- Checkbox de confirmation -->
                    <div class="mb-6">
                        <label class="flex items-start cursor-pointer group">
                            <input type="checkbox" id="confirm-disable-checkbox" class="mt-1 h-5 w-5 text-red-600 border-gray-300 rounded focus:ring-red-500 cursor-pointer">
                            <span class="ml-3 text-sm text-gray-700 group-hover:text-gray-900">
                                Je comprends les risques et souhaite désactiver l'authentification à deux facteurs
                            </span>
                        </label>
                    </div>

                    <!-- Input mot de passe -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Entrez votre mot de passe pour confirmer :
                        </label>
                        <div class="relative">
                            <input type="password" id="disable-password-input"
                                   class="w-full px-4 py-3 pr-12 border-2 border-gray-300 rounded-lg focus:border-red-500 focus:ring-4 focus:ring-red-100 outline-none transition-all"
                                   placeholder="••••••••">
                            <button type="button" onclick="TwoFactorAuth.togglePasswordVisibility('disable-password-input', this)"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Boutons -->
                    <div class="flex gap-3">
                        <button onclick="TwoFactorAuth.closeDisableModal()"
                                class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold transition-all">
                            Annuler
                        </button>
                        <button onclick="TwoFactorAuth.confirmDisable2FA()"
                                class="flex-1 px-6 py-3 bg-gradient-to-r from-red-500 to-red-700 text-white rounded-lg font-semibold hover:from-red-600 hover:to-red-800 transition-all shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Désactiver l'A2F
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Focus sur le champ mot de passe
        setTimeout(() => document.getElementById('disable-password-input').focus(), 100);
    },

    /**
     * Toggle visibility du mot de passe
     */
    togglePasswordVisibility(inputId, button) {
        const input = document.getElementById(inputId);
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';

        // Changer l'icône
        button.innerHTML = isPassword
            ? `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
               </svg>`
            : `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
               </svg>`;
    },

    /**
     * Confirme et désactive l'A2F
     */
    async confirmDisable2FA() {
        const checkbox = document.getElementById('confirm-disable-checkbox');
        const password = document.getElementById('disable-password-input').value.trim();

        // Vérifier la checkbox
        if (!checkbox.checked) {
            this.showToast('Vous devez confirmer que vous comprenez les risques', 'error');
            checkbox.focus();
            return;
        }

        // Vérifier le mot de passe
        if (!password) {
            this.showToast('Veuillez entrer votre mot de passe', 'error');
            document.getElementById('disable-password-input').focus();
            return;
        }

        try {
            const response = await fetch(`${this.baseUrl}/api/parametres/2fa-setup.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'disable_2fa',
                    password: password
                })
            });

            if (!response.ok) throw new Error('Erreur réseau');

            const data = await response.json();

            if (data.success) {
                this.closeDisableModal();
                this.showSuccessDisableModal();
            } else {
                this.showToast(data.message || 'Erreur lors de la désactivation', 'error');
                // Effacer le mot de passe en cas d'erreur
                document.getElementById('disable-password-input').value = '';
                document.getElementById('disable-password-input').focus();
            }
        } catch (error) {
            console.error('Erreur:', error);
            this.showToast('Erreur de connexion au serveur', 'error');
        }
    },

    /**
     * Affiche le modal de confirmation de désactivation réussie
     */
    showSuccessDisableModal() {
        const modal = document.createElement('div');
        modal.id = 'success-disable-modal';
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';

        modal.innerHTML = `
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full relative overflow-hidden" style="animation: slideUp 0.3s ease-out;">
                <div class="bg-gradient-to-r from-green-500 to-green-700 px-8 py-6">
                    <div class="flex items-center">
                        <div class="w-14 h-14 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-white mb-1">A2F désactivée</h3>
                            <p class="text-white text-opacity-90 text-sm">Modification effectuée</p>
                        </div>
                    </div>
                </div>

                <div class="p-8">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <p class="text-sm text-yellow-900">
                            <span class="font-semibold">⚠️ Important :</span> Votre authentification à deux facteurs a été désactivée. Vos codes de sauvegarde ont été supprimés. Vous pouvez la réactiver à tout moment dans vos paramètres.
                        </p>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <p class="text-sm text-blue-900">
                            <span class="font-semibold">💡 Recommandation :</span> Pour maintenir la sécurité de votre compte, nous vous recommandons de :
                        </p>
                        <ul class="text-sm text-blue-800 mt-2 space-y-1 ml-4">
                            <li>• Utiliser un mot de passe fort et unique</li>
                            <li>• Activer les notifications de connexion</li>
                            <li>• Vérifier régulièrement l'activité de votre compte</li>
                        </ul>
                    </div>

                    <button onclick="location.reload()"
                            class="w-full px-6 py-3 bg-gradient-to-r from-red-500 to-red-700 text-white rounded-lg font-semibold hover:from-red-600 hover:to-red-800 transition-all shadow-lg">
                        Continuer
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
    },

    /**
     * Ferme le modal de désactivation
     */
    closeDisableModal() {
        const modal = document.getElementById('disable-2fa-modal');
        if (modal) modal.remove();
    },

    /**
     * Affiche les codes de sauvegarde
     */
    async showBackupCodes() {
        try {
            const response = await fetch(`${this.baseUrl}/api/parametres/2fa-setup.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get_backup_codes' })
            });

            if (!response.ok) throw new Error('Erreur réseau');

            const data = await response.json();

            if (data.success) {
                this.showBackupCodesModal(data.backup_codes);
            } else {
                this.showToast(data.message || 'Erreur lors de la récupération des codes', 'error');
            }
        } catch (error) {
            console.error('Erreur:', error);
            this.showToast('Impossible de récupérer les codes de sauvegarde', 'error');
        }
    },

    /**
     * Affiche le modal des codes de sauvegarde
     */
    showBackupCodesModal(codes) {
        const modal = document.createElement('div');
        modal.id = 'backup-codes-modal';
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 animate-fade-in';
        modal.onclick = (e) => { if (e.target === modal) this.closeBackupCodesModal(); };

        modal.innerHTML = `
            <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full relative overflow-hidden" style="animation: slideUp 0.3s ease-out;">
                <div class="bg-gradient-to-r from-red-500 to-red-700 px-8 py-6 relative">
                    <button onclick="TwoFactorAuth.closeBackupCodesModal()" class="absolute top-4 right-4 text-white hover:text-gray-200 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>

                    <div class="flex items-center">
                        <div class="w-14 h-14 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-white mb-1">Codes de sauvegarde</h3>
                            <p class="text-white text-opacity-90 text-sm">${codes.length} codes disponibles</p>
                        </div>
                    </div>
                </div>

                <div class="p-8">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <p class="text-sm font-medium text-blue-900 mb-1">Comment utiliser ces codes ?</p>
                        <p class="text-sm text-blue-700">
                            Utilisez ces codes si vous perdez l'accès à votre application d'authentification. Chaque code ne peut être utilisé qu'une seule fois.
                        </p>
                    </div>

                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 mb-6 border border-gray-200">
                        <div class="grid grid-cols-2 gap-4">
                            ${codes.map((code, index) => `
                                <div class="group bg-white rounded-lg p-4 border-2 border-gray-200 hover:border-red-300 transition-all duration-200 hover:shadow-md">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center flex-1">
                                            <div class="w-8 h-8 bg-gradient-to-br from-red-500 to-red-600 rounded-full flex items-center justify-center mr-3">
                                                <span class="text-white text-xs font-bold">${(index + 1).toString().padStart(2, '0')}</span>
                                            </div>
                                            <code class="font-mono text-base font-bold text-gray-800 tracking-wide">${code}</code>
                                        </div>
                                        <button onclick="TwoFactorAuth.copySingleCode('${code}')" class="ml-2 p-2 text-gray-400 hover:text-red-500 transition opacity-0 group-hover:opacity-100" title="Copier">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border-l-4 border-yellow-400 rounded-lg p-4 mb-6">
                        <p class="text-sm font-bold text-yellow-900 mb-1">⚠️ Important - Sécurité</p>
                        <p class="text-sm text-yellow-800">
                            Conservez ces codes dans un endroit sûr (gestionnaire de mots de passe, coffre-fort numérique). Ne les partagez <strong>jamais</strong> avec personne.
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <button onclick="TwoFactorAuth.downloadBackupCodes(${JSON.stringify(codes)})" class="flex-1 px-6 py-3 bg-gradient-to-r from-red-500 to-red-700 text-white rounded-lg hover:from-red-600 hover:to-red-800 transition-all shadow-lg font-semibold">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Télécharger
                        </button>
                        <button onclick="TwoFactorAuth.copyAllCodes(${JSON.stringify(codes)})" class="flex-1 px-6 py-3 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-lg hover:from-gray-700 hover:to-gray-800 transition-all shadow-md font-semibold">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            Copier tout
                        </button>
                        <button onclick="TwoFactorAuth.closeBackupCodesModal()" class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-all font-semibold">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
    },

    /**
     * Télécharge les codes de sauvegarde
     */
    downloadBackupCodes(codes) {
        const content = 'CODES DE SAUVEGARDE NOVATIS\n' +
                       '============================\n\n' +
                       'Conservez ces codes en lieu sûr.\n' +
                       'Chaque code ne peut être utilisé qu\'une seule fois.\n\n' +
                       codes.map((code, i) => `${(i + 1).toString().padStart(2, '0')}. ${code}`).join('\n') +
                       '\n\n' +
                       'Généré le: ' + new Date().toLocaleString('fr-FR');

        const blob = new Blob([content], { type: 'text/plain' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'novatis-backup-codes.txt';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);

        this.showToast('Codes téléchargés', 'success');
    },

    /**
     * Copie tous les codes de sauvegarde
     */
    copyAllCodes(codes) {
        const text = codes.join('\n');
        navigator.clipboard.writeText(text).then(() => {
            this.showToast('Tous les codes copiés !', 'success');
        }).catch(() => {
            this.showToast('Erreur lors de la copie', 'error');
        });
    },

    /**
     * Copie un seul code
     */
    copySingleCode(code) {
        navigator.clipboard.writeText(code).then(() => {
            this.showToast('Code copié !', 'success');
        }).catch(() => {
            this.showToast('Erreur lors de la copie', 'error');
        });
    },

    /**
     * Ferme le modal principal
     */
    closeModal() {
        const modal = document.getElementById('2fa-modal');
        if (modal) modal.remove();
    },

    /**
     * Ferme le modal des codes de sauvegarde
     */
    closeBackupCodesModal() {
        const modal = document.getElementById('backup-codes-modal');
        if (modal) modal.remove();
    }
};
