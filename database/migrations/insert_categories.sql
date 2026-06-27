-- Insertion de nouvelles catégories dans la table categories
-- Vérifier d'abord les catégories existantes pour éviter les doublons

INSERT INTO categories (name, description, created_at) VALUES
('Développement Web', 'Création de sites web, applications web et solutions digitales sur mesure', NOW()),
('Design & UX/UI', 'Conception d''interfaces utilisateur, design graphique et expérience utilisateur', NOW()),
('Applications Mobile', 'Développement d''applications mobiles iOS, Android et cross-platform', NOW()),
('SEO & Marketing Digital', 'Référencement naturel, publicité en ligne et stratégie marketing digital', NOW()),
('API & Intégrations', 'Développement d''APIs, intégrations de services tiers et webhooks', NOW()),
('Maintenance & Support', 'Maintenance technique, support client et optimisation de performances', NOW()),
('E-commerce', 'Solutions de vente en ligne, boutiques et marketplaces', NOW()),
('Cloud & DevOps', 'Déploiement cloud, containerisation et automatisation CI/CD', NOW()),
('Intelligence Artificielle', 'Solutions IA, machine learning, chatbots et analyse de données', NOW()),
('Blockchain & Web3', 'Smart contracts, NFT, DeFi et applications décentralisées', NOW())
ON DUPLICATE KEY UPDATE
    description = VALUES(description);
