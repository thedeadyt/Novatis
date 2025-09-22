-- =========================
-- BASE DE DONNÉES ULTRA-SIMPLE
-- Plateforme Freelance Étudiante
-- =========================

-- =========================
-- USERS (Étudiants polyvalents)
-- =========================
CREATE TABLE users (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    name            VARCHAR(100) NOT NULL,
    email           VARCHAR(100) UNIQUE NOT NULL,
    password        VARCHAR(255) NOT NULL,
    role            ENUM('user','admin') DEFAULT 'user',
    avatar          VARCHAR(255),
    bio             TEXT,
    phone           VARCHAR(20),
    rating          DECIMAL(2,1) DEFAULT 0.0,
    is_verified     TINYINT(1) DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- CATEGORIES
-- =========================
CREATE TABLE categories (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    name            VARCHAR(100) NOT NULL,
    slug            VARCHAR(100) UNIQUE NOT NULL,
    icon            VARCHAR(50),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- SERVICES (Ce que proposent les étudiants)
-- =========================
CREATE TABLE services (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    user_id         INT NOT NULL,
    category_id     INT,
    title           VARCHAR(200) NOT NULL,
    description     TEXT NOT NULL,
    price           DECIMAL(8,2) NOT NULL,
    delivery_days   INT DEFAULT 3,
    image           VARCHAR(255),
    status          ENUM('active','paused','draft') DEFAULT 'draft',
    views           INT DEFAULT 0,
    orders_count    INT DEFAULT 0,
    rating          DECIMAL(2,1) DEFAULT 0.0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- =========================
-- ORDERS (Commandes entre étudiants)
-- =========================
CREATE TABLE orders (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    service_id      INT NOT NULL,
    buyer_id        INT NOT NULL,     -- L'étudiant qui achète
    seller_id       INT NOT NULL,     -- L'étudiant qui vend
    title           VARCHAR(200) NOT NULL,
    description     TEXT,
    price           DECIMAL(8,2) NOT NULL,
    status          ENUM('pending','in_progress','delivered','completed','cancelled') DEFAULT 'pending',
    deadline        DATE,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =========================
-- MESSAGES (Communication)
-- =========================
CREATE TABLE messages (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    order_id        INT NOT NULL,
    sender_id       INT NOT NULL,
    content         TEXT NOT NULL,
    is_read         TINYINT(1) DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =========================
-- REVIEWS (Avis mutuels)
-- =========================
CREATE TABLE reviews (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    order_id        INT NOT NULL,
    reviewer_id     INT NOT NULL,     -- Qui donne l'avis
    reviewee_id     INT NOT NULL,     -- Qui reçoit l'avis
    rating          INT CHECK (rating BETWEEN 1 AND 5),
    comment         TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewee_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =========================
-- PORTFOLIO (Projets des étudiants)
-- =========================
CREATE TABLE portfolio (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    user_id         INT NOT NULL,
    title           VARCHAR(200) NOT NULL,
    description     TEXT,
    image           VARCHAR(255),
    category_id     INT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- =========================
-- SUPPORT (Tickets pour les admins)
-- =========================
CREATE TABLE support_tickets (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    user_id         INT NOT NULL,
    subject         VARCHAR(200) NOT NULL,
    message         TEXT NOT NULL,
    status          ENUM('open','in_progress','resolved','closed') DEFAULT 'open',
    admin_response  TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =========================
-- DONNÉES D'EXEMPLE
-- =========================

-- Catégories
INSERT INTO categories (name, slug, icon) VALUES
('Développement Web', 'dev-web', '💻'),
('Design Graphique', 'design', '🎨'),
('Rédaction', 'redaction', '✍️'),
('Marketing', 'marketing', '📈'),
('Traduction', 'traduction', '🌍');

-- Utilisateurs (étudiants + admin)
INSERT INTO users (name, email, password, role, bio, rating, is_verified) VALUES
('Marie Dubois', 'marie@student.com', '$2y$10$hash1', 'user', 'Étudiante en informatique, spécialisée en développement web', 4.8, 1),
('Jean Martin', 'jean@student.com', '$2y$10$hash2', 'user', 'Étudiant en commerce, j\'ai besoin de services créatifs', 4.2, 1),
('Sophie Chen', 'sophie@student.com', '$2y$10$hash3', 'user', 'Étudiante en design graphique, créative et passionnée', 4.9, 1),
('Thomas Roy', 'thomas@student.com', '$2y$10$hash4', 'user', 'Étudiant en marketing digital, expert réseaux sociaux', 4.5, 1),
('Admin Platform', 'admin@novatis.com', '$2y$10$hash5', 'admin', 'Équipe Novatis - Support et gestion', 0, 1);

-- Services proposés par les étudiants
INSERT INTO services (user_id, category_id, title, description, price, delivery_days, status, rating) VALUES
(1, 1, 'Site web étudiant responsive', 'Je crée ton site web professionnel pour tes projets étudiants', 80, 5, 'active', 4.8),
(3, 2, 'Logo + identité visuelle', 'Design de logo moderne + charte graphique pour ton projet', 50, 3, 'active', 4.9),
(1, 1, 'App mobile simple', 'Développement d\'une app mobile basique avec React Native', 200, 10, 'active', 4.7),
(4, 4, 'Gestion réseaux sociaux', 'Je gère tes réseaux sociaux pendant 1 mois (posts + engagement)', 60, 1, 'active', 4.5),
(3, 2, 'Flyers et affiches', 'Création de supports de communication pour tes événements', 25, 2, 'active', 4.8);

-- Commandes entre étudiants
INSERT INTO orders (service_id, buyer_id, seller_id, title, description, price, status, deadline) VALUES
(1, 2, 1, 'Site pour association étudiante', 'Site web pour notre BDE avec events et news', 80, 'in_progress', '2025-01-20'),
(2, 4, 3, 'Logo pour ma startup étudiante', 'Logo pour mon projet de fin d\'études', 50, 'completed', '2025-01-12'),
(4, 1, 4, 'Promo de mon app', 'Promotion de mon app sur les réseaux sociaux', 60, 'delivered', '2025-01-25');

-- Messages entre étudiants
INSERT INTO messages (order_id, sender_id, content, is_read) VALUES
(1, 2, 'Salut Marie ! Peux-tu ajouter une page "événements" au site ?', 0),
(1, 1, 'Bien sûr Jean ! Je l\'ajoute aujourd\'hui 👍', 1),
(2, 3, 'Voici 3 propositions de logo, laquelle tu préfères ?', 1),
(2, 4, 'J\'adore la version n°2 ! Elle est parfaite 🔥', 0),
(3, 4, 'Campaign lancée ! +50 followers en 2 jours 📈', 1),
(3, 1, 'Génial Thomas ! Merci beaucoup 🚀', 0);

-- Reviews entre étudiants
INSERT INTO reviews (order_id, reviewer_id, reviewee_id, rating, comment) VALUES
(2, 4, 3, 5, 'Sophie est super créative ! Logo parfait pour mon projet 🎨'),
(2, 3, 4, 5, 'Thomas était très clair sur ses besoins, top client ! 😊'),
(3, 1, 4, 5, 'Excellent travail sur les réseaux sociaux, très pro ! 📱'),
(3, 4, 1, 4, 'Marie est réactive et sympa, je recommande ! 👍');

-- Portfolio des étudiants
INSERT INTO portfolio (user_id, title, description, category_id) VALUES
(1, 'Site E-learning Étudiants', 'Plateforme d\'apprentissage pour cours en ligne', 1),
(1, 'App de Covoiturage Campus', 'App mobile pour covoiturage entre étudiants', 1),
(3, 'Identité BDE Sciences Po', 'Logo et charte graphique complète', 2),
(3, 'Affiches Festival Étudiant', 'Communication visuelle pour festival de musique', 2),
(4, 'Campagne Instagram +2K', 'Croissance Instagram pour startup étudiante', 4);

-- Tickets de support
INSERT INTO support_tickets (user_id, subject, message, status) VALUES
(2, 'Problème de paiement', 'Je n\'arrive pas à payer ma commande, la carte est refusée', 'open'),
(1, 'Modification de profil', 'Comment changer ma photo de profil ?', 'resolved'),
(4, 'Question sur les commissions', 'Quel pourcentage prend la plateforme ?', 'in_progress');

-- =========================
-- INDEX POUR PERFORMANCES
-- =========================
CREATE INDEX idx_services_user ON services(user_id);
CREATE INDEX idx_services_category ON services(category_id);
CREATE INDEX idx_services_status ON services(status);
CREATE INDEX idx_orders_buyer ON orders(buyer_id);
CREATE INDEX idx_orders_seller ON orders(seller_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_messages_order ON messages(order_id);
CREATE INDEX idx_messages_read ON messages(is_read);
CREATE INDEX idx_support_status ON support_tickets(status);