-- Base de données séparée pour les paramètres Novatis
-- Création de la base de données
CREATE DATABASE IF NOT EXISTS novatis_settings CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE novatis_settings;

-- Table des préférences utilisateur
CREATE TABLE user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email_notifications BOOLEAN DEFAULT TRUE,
    push_notifications BOOLEAN DEFAULT FALSE,
    sms_notifications BOOLEAN DEFAULT FALSE,
    dark_mode BOOLEAN DEFAULT FALSE,
    language VARCHAR(10) DEFAULT 'fr',
    timezone VARCHAR(50) DEFAULT 'Europe/Paris',
    currency VARCHAR(3) DEFAULT 'EUR',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user (user_id),
    INDEX idx_user_id (user_id)
);

-- Table de configuration de sécurité
CREATE TABLE user_security (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    two_factor_secret VARCHAR(255) DEFAULT NULL,
    backup_codes JSON DEFAULT NULL,
    last_password_change TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    password_expires_at TIMESTAMP NULL,
    login_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    security_questions JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id)
);

-- Table des sessions de sécurité
CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    device_info VARCHAR(500),
    ip_address VARCHAR(45),
    location VARCHAR(100),
    browser VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_session_token (session_token),
    INDEX idx_last_activity (last_activity)
);

-- Table de configuration d'affichage
CREATE TABLE user_display (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    theme VARCHAR(20) DEFAULT 'light',
    sidebar_collapsed BOOLEAN DEFAULT FALSE,
    dashboard_layout VARCHAR(20) DEFAULT 'grid',
    items_per_page INT DEFAULT 10,
    show_tutorials BOOLEAN DEFAULT TRUE,
    custom_css TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id)
);

-- Table de configuration de confidentialité
CREATE TABLE user_privacy (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    profile_visibility VARCHAR(20) DEFAULT 'public',
    show_email BOOLEAN DEFAULT FALSE,
    show_phone BOOLEAN DEFAULT FALSE,
    allow_search_engines BOOLEAN DEFAULT TRUE,
    data_sharing BOOLEAN DEFAULT FALSE,
    analytics_tracking BOOLEAN DEFAULT TRUE,
    marketing_emails BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id)
);

-- Table des logs d'activité
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    section VARCHAR(50) NOT NULL,
    details JSON DEFAULT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- Table des notifications personnalisées
CREATE TABLE notification_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    notification_type VARCHAR(50) NOT NULL,
    enabled BOOLEAN DEFAULT TRUE,
    frequency VARCHAR(20) DEFAULT 'immediate',
    channels JSON DEFAULT '["email"]',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_type (user_id, notification_type),
    INDEX idx_user_id (user_id)
);

-- Table des intégrations externes
CREATE TABLE user_integrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_name VARCHAR(50) NOT NULL,
    is_connected BOOLEAN DEFAULT FALSE,
    access_token TEXT DEFAULT NULL,
    refresh_token TEXT DEFAULT NULL,
    token_expires_at TIMESTAMP NULL,
    settings JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_service (user_id, service_name),
    INDEX idx_user_id (user_id)
);

-- Insertion de données par défaut pour les types de notifications
INSERT INTO notification_settings (user_id, notification_type, enabled, frequency, channels) VALUES
-- Ces données seront créées dynamiquement pour chaque utilisateur
-- Exemple pour l'utilisateur ID 1 (à adapter selon vos besoins)
(1, 'new_order', TRUE, 'immediate', '["email", "push"]'),
(1, 'order_update', TRUE, 'immediate', '["email"]'),
(1, 'payment_confirmation', TRUE, 'immediate', '["email", "sms"]'),
(1, 'weekly_summary', TRUE, 'weekly', '["email"]'),
(1, 'security_alerts', TRUE, 'immediate', '["email", "sms", "push"]'),
(1, 'promotional_offers', FALSE, 'weekly', '["email"]');

-- Vues pour faciliter les requêtes
CREATE VIEW user_settings_summary AS
SELECT
    up.user_id,
    up.email_notifications,
    up.push_notifications,
    up.sms_notifications,
    up.dark_mode,
    up.language,
    up.timezone,
    us.two_factor_enabled,
    us.last_password_change,
    ud.theme,
    ud.sidebar_collapsed,
    upr.profile_visibility,
    upr.data_sharing
FROM user_preferences up
LEFT JOIN user_security us ON up.user_id = us.user_id
LEFT JOIN user_display ud ON up.user_id = ud.user_id
LEFT JOIN user_privacy upr ON up.user_id = upr.user_id;