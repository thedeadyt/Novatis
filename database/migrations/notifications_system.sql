-- Table des notifications utilisateur
CREATE TABLE IF NOT EXISTS user_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL COMMENT 'Type: order, message, security, payment, service_update',
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255) DEFAULT NULL COMMENT 'Lien vers la ressource concernée',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_unread (user_id, is_read),
    INDEX idx_created (created_at),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des événements de notification (pour l'historique)
CREATE TABLE IF NOT EXISTS notification_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notification_id INT NOT NULL,
    event_type VARCHAR(50) NOT NULL COMMENT 'created, sent_email, sent_push, read, deleted',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    metadata JSON DEFAULT NULL,
    FOREIGN KEY (notification_id) REFERENCES user_notifications(id) ON DELETE CASCADE,
    INDEX idx_notification (notification_id),
    INDEX idx_event_type (event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
