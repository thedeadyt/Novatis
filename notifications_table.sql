-- =========================
-- TABLE NOTIFICATIONS
-- =========================
CREATE TABLE notifications (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    user_id         INT NOT NULL,
    type            ENUM('order','message','system','payment','service','review') DEFAULT 'system',
    title           VARCHAR(200) NOT NULL,
    message         TEXT NOT NULL,
    action_url      VARCHAR(500),
    metadata        JSON,
    is_read         TINYINT(1) DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_notifications (user_id, is_read, created_at),
    INDEX idx_created_at (created_at)
);

-- =========================
-- EXEMPLES DE NOTIFICATIONS
-- =========================
INSERT INTO notifications (user_id, type, title, message, action_url) VALUES
(1, 'order', 'Nouvelle commande', 'Vous avez reçu une nouvelle commande pour "Développement Web"', '/dashboard?tab=orders'),
(1, 'message', 'Nouveau message', 'Vous avez reçu un nouveau message de Jean Dupont', '/dashboard?tab=messages'),
(1, 'system', 'Bienvenue !', 'Bienvenue sur Novatis ! Complétez votre profil pour commencer.', '/profile'),
(2, 'payment', 'Paiement reçu', 'Vous avez reçu un paiement de 50€ pour votre service', '/dashboard?tab=orders'),
(2, 'service', 'Service approuvé', 'Votre service "Design Logo" a été approuvé et est maintenant visible', '/dashboard?tab=services'),
(3, 'review', 'Nouvelle évaluation', 'Vous avez reçu une nouvelle évaluation 5 étoiles !', '/dashboard?tab=reviews');