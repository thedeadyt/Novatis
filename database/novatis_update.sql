-- ============================================
-- MISE À JOUR NOVATIS - AJOUT TABLES AVANCÉES
-- À exécuter sur une BDD existante
-- Date: 2025-09-30
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ============================================
-- NOUVELLES TABLES AVANCÉES
-- ============================================

-- Table: user_sessions (tracking détaillé des sessions)
CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `device_info` varchar(500) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `browser` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_activity` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_session_token` (`session_token`),
  KEY `idx_last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: user_display (préférences d'affichage avancées)
CREATE TABLE IF NOT EXISTS `user_display` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `theme` varchar(20) DEFAULT 'light',
  `sidebar_collapsed` tinyint(1) DEFAULT 0,
  `dashboard_layout` varchar(20) DEFAULT 'grid',
  `items_per_page` int(11) DEFAULT 10,
  `show_tutorials` tinyint(1) DEFAULT 1,
  `custom_css` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: activity_logs (historique détaillé des actions)
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `section` varchar(50) NOT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: notification_settings (configuration des notifications par type)
CREATE TABLE IF NOT EXISTS `notification_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `notification_type` varchar(50) NOT NULL,
  `enabled` tinyint(1) DEFAULT 1,
  `frequency` varchar(20) DEFAULT 'immediate',
  `channels` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT '["email"]' CHECK (json_valid(`channels`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_type` (`user_id`,`notification_type`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: user_integrations (intégrations avec services externes)
CREATE TABLE IF NOT EXISTS `user_integrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `service_name` varchar(50) NOT NULL,
  `is_connected` tinyint(1) DEFAULT 0,
  `access_token` text DEFAULT NULL,
  `refresh_token` text DEFAULT NULL,
  `token_expires_at` timestamp NULL DEFAULT NULL,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_service` (`user_id`,`service_name`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: login_history (pour le trigger et la procédure stockée)
CREATE TABLE IF NOT EXISTS `login_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `login_time` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_login_time` (`login_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- MISE À JOUR DE LA PROCÉDURE STOCKÉE
-- ============================================

DROP PROCEDURE IF EXISTS `CleanExpiredSessions`;

DELIMITER $$
CREATE PROCEDURE `CleanExpiredSessions` ()
BEGIN
    DELETE FROM user_sessions
    WHERE last_activity < NOW() - INTERVAL 30 DAY;

    DELETE FROM login_history
    WHERE login_time < NOW() - INTERVAL 6 MONTH;
END$$
DELIMITER ;

-- ============================================
-- AJOUT DE security_questions À user_security
-- ============================================

-- Vérifier si la colonne existe déjà avant de l'ajouter
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'user_security'
    AND COLUMN_NAME = 'security_questions'
);

SET @query = IF(@column_exists = 0,
    'ALTER TABLE `user_security` ADD COLUMN `security_questions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`security_questions`)) AFTER `locked_until`',
    'SELECT "La colonne security_questions existe déjà"'
);

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- CRÉATION DE LA VUE (avec remplacement)
-- ============================================

DROP VIEW IF EXISTS `user_settings_summary`;

CREATE VIEW `user_settings_summary` AS
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

-- ============================================
-- MISE À JOUR DE L'ÉVÉNEMENT PLANIFIÉ
-- ============================================

DROP EVENT IF EXISTS `ev_clean_expired_sessions`;

DELIMITER $$
CREATE EVENT `ev_clean_expired_sessions`
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
ON COMPLETION PRESERVE
ENABLE
DO CALL CleanExpiredSessions()$$
DELIMITER ;

-- ============================================
-- SUCCÈS
-- ============================================

SELECT 'Base de données mise à jour avec succès!' AS message;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- ============================================
-- FIN DU SCRIPT
-- ============================================
