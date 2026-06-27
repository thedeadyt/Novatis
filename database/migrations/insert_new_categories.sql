-- Ajout de nouvelles catégories dans la table categories
-- Compatible avec la structure existante de alex2pro_movatis

-- Insertion des nouvelles catégories (IDs 6-15)
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `created_at`) VALUES
(6, 'Applications Mobile', 'apps-mobile', '📱', NOW()),
(7, 'SEO & Marketing Digital', 'seo-marketing', '🚀', NOW()),
(8, 'API & Intégrations', 'api-integrations', '🔌', NOW()),
(9, 'Maintenance & Support', 'maintenance-support', '🛠️', NOW()),
(10, 'E-commerce', 'ecommerce', '🛒', NOW()),
(11, 'Cloud & DevOps', 'cloud-devops', '☁️', NOW()),
(12, 'Intelligence Artificielle', 'ia-ai', '🤖', NOW()),
(13, 'Blockchain & Web3', 'blockchain-web3', '⛓️', NOW()),
(14, 'Vidéo & Animation', 'video-animation', '🎬', NOW()),
(15, 'Consulting IT', 'consulting-it', '💼', NOW());
