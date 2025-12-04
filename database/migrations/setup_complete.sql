-- ============================================================================
-- SCRIPT COMPLET POUR NOVATIS
-- Ajout de catégories et services prédéfinis
-- Compatible avec la structure alex2pro_movatis
-- ============================================================================

-- --------------------------------------------------------
-- ÉTAPE 1: Ajout des nouvelles catégories
-- --------------------------------------------------------

-- Insertion des nouvelles catégories (IDs 6-15)
-- Catégories existantes: 1=Développement Web, 2=Design Graphique, 3=Rédaction, 4=Marketing, 5=Traduction
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
(15, 'Consulting IT', 'consulting-it', '💼', NOW())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- --------------------------------------------------------
-- ÉTAPE 2: Création de la table des services prédéfinis
-- --------------------------------------------------------

DROP TABLE IF EXISTS `predefined_services`;

CREATE TABLE `predefined_services` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `category_id` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- ÉTAPE 3: Insertion des services prédéfinis
-- --------------------------------------------------------

INSERT INTO `predefined_services` (`name`, `description`, `category_id`) VALUES

-- ===== DÉVELOPPEMENT WEB (ID: 1) =====
('Site Vitrine', 'Création d''un site web élégant et professionnel pour présenter votre entreprise, vos services et vos coordonnées. Design responsive et optimisé SEO.', 1),
('Site E-commerce', 'Boutique en ligne complète avec gestion des produits, panier d''achat, paiement sécurisé et tableau de bord administrateur.', 1),
('Application Web Sur Mesure', 'Développement d''une application web personnalisée selon vos besoins spécifiques avec architecture moderne et scalable.', 1),
('Plateforme SaaS', 'Création d''une plateforme Software as a Service multi-tenant avec gestion des abonnements et facturation automatique.', 1),
('Portfolio Professionnel', 'Site portfolio moderne et interactif pour présenter vos projets, compétences et expériences de manière attractive.', 1),
('Blog & Site de Contenu', 'Plateforme de publication avec système de gestion de contenu, commentaires, catégories et optimisation SEO avancée.', 1),
('Dashboard Analytique', 'Interface d''administration avec visualisation de données, graphiques interactifs et rapports en temps réel.', 1),
('Intranet d''Entreprise', 'Réseau interne sécurisé pour la collaboration d''équipe, partage de documents et communication interne.', 1),
('Landing Page Conversion', 'Page d''atterrissage optimisée pour la conversion avec formulaires, CTA et design percutant.', 1),
('Refonte de Site Web', 'Modernisation complète de votre site existant avec nouvelle architecture, design et fonctionnalités.', 1),

-- ===== DESIGN GRAPHIQUE (ID: 2) =====
('Maquette UI/UX Complète', 'Conception complète de l''interface utilisateur avec wireframes, prototypes interactifs et guide de style visuel.', 2),
('Refonte Design', 'Modernisation du design de votre site ou application existante pour améliorer l''expérience utilisateur et l''esthétique.', 2),
('Design System', 'Création d''un système de design complet avec composants réutilisables, guidelines et documentation pour assurer la cohérence.', 2),
('Branding & Identité Visuelle', 'Développement de votre identité de marque incluant logo, charte graphique, couleurs et typographies.', 2),
('Design Mobile First', 'Conception spécialement optimisée pour les appareils mobiles avec navigation intuitive et performance maximale.', 2),
('Animation & Micro-interactions', 'Ajout d''animations fluides et micro-interactions pour améliorer l''engagement et l''expérience utilisateur.', 2),
('Infographie Professionnelle', 'Création d''infographies visuelles pour présenter vos données et informations de manière attractive et claire.', 2),
('Design de Présentation', 'Création de présentations PowerPoint ou Keynote professionnelles et impactantes pour vos pitch et réunions.', 2),
('Illustration Personnalisée', 'Création d''illustrations sur mesure pour votre marque, site web ou supports de communication.', 2),
('Packaging Produit', 'Design de packaging attractif et fonctionnel pour mettre en valeur vos produits.', 2),

-- ===== RÉDACTION (ID: 3) =====
('Rédaction Articles de Blog', 'Rédaction d''articles de blog optimisés SEO, engageants et informatifs pour votre audience cible.', 3),
('Rédaction Fiches Produits', 'Création de descriptions de produits persuasives et détaillées pour booster vos ventes en ligne.', 3),
('Rédaction Web SEO', 'Rédaction de contenus web optimisés pour les moteurs de recherche tout en restant naturels et engageants.', 3),
('Copywriting Commercial', 'Rédaction de textes de vente percutants pour pages de vente, emails marketing et publicités.', 3),
('Rédaction de Newsletters', 'Création de newsletters engageantes pour maintenir le contact avec votre audience et promouvoir vos offres.', 3),
('Livre Blanc & E-book', 'Rédaction de livres blancs et e-books pour établir votre expertise et générer des leads qualifiés.', 3),
('Storytelling de Marque', 'Création de récits captivants autour de votre marque pour créer une connexion émotionnelle avec votre audience.', 3),
('Scripts Vidéo', 'Rédaction de scripts pour vidéos marketing, tutoriels ou présentations corporatives.', 3),

-- ===== MARKETING (ID: 4) =====
('Stratégie Marketing Digitale', 'Élaboration d''une stratégie marketing digital complète adaptée à vos objectifs et votre audience.', 4),
('Gestion Réseaux Sociaux', 'Gestion complète de vos réseaux sociaux avec création de contenu, planification et engagement communautaire.', 4),
('Email Marketing', 'Création et gestion de campagnes email marketing avec segmentation, automation et analyse des performances.', 4),
('Publicité Facebook & Instagram', 'Création et gestion de campagnes publicitaires sur Facebook et Instagram pour maximiser votre ROI.', 4),
('Marketing d''Influence', 'Stratégie et gestion de campagnes avec influenceurs pour augmenter votre visibilité et crédibilité.', 4),
('Growth Hacking', 'Stratégies de croissance rapide et créatives pour développer votre audience et vos revenus.', 4),
('Analyse & Reporting', 'Analyse approfondie de vos performances marketing avec rapports détaillés et recommandations.', 4),
('Marketing Automation', 'Mise en place de workflows automatisés pour nurturing, onboarding et conversion de prospects.', 4),

-- ===== TRADUCTION (ID: 5) =====
('Traduction Site Web', 'Traduction professionnelle de votre site web en plusieurs langues pour étendre votre portée internationale.', 5),
('Traduction Marketing', 'Traduction et adaptation de vos contenus marketing en respectant les nuances culturelles locales.', 5),
('Traduction Technique', 'Traduction de documentation technique, manuels et spécifications avec terminologie précise.', 5),
('Localisation d''Application', 'Adaptation complète de votre application mobile ou web pour différents marchés et cultures.', 5),
('Sous-titrage Vidéo', 'Création de sous-titres multilingues pour vos vidéos et contenus audiovisuels.', 5),
('Relecture & Correction', 'Relecture professionnelle et correction de vos textes pour garantir une qualité irréprochable.', 5),

-- ===== APPLICATIONS MOBILE (ID: 6) =====
('Application iOS Native', 'Développement d''application iOS native en Swift avec intégration complète des fonctionnalités Apple.', 6),
('Application Android Native', 'Création d''application Android native en Kotlin avec Material Design et optimisation des performances.', 6),
('Application Cross-Platform', 'Application mobile multi-plateforme avec React Native ou Flutter pour iOS et Android à partir d''un seul code.', 6),
('Application Progressive Web (PWA)', 'Application web progressive installable offrant une expérience native avec fonctionnement hors ligne.', 6),
('Application de Livraison', 'Solution complète de livraison avec suivi GPS en temps réel, notifications push et système de paiement intégré.', 6),
('Application de Réservation', 'Système de réservation mobile avec gestion des disponibilités, calendrier et confirmations automatiques.', 6),
('Application de Fitness', 'Application de suivi fitness avec exercices, nutrition, statistiques et coaching personnalisé.', 6),
('Application de E-learning', 'Plateforme d''apprentissage mobile avec cours, quiz, suivi de progression et certificats.', 6),

-- ===== SEO & MARKETING DIGITAL (ID: 7) =====
('Audit SEO Complet', 'Analyse approfondie de votre site web avec recommandations détaillées pour améliorer votre référencement naturel.', 7),
('Optimisation SEO On-Page', 'Optimisation technique du contenu, balises meta, structure des URLs et performances pour un meilleur classement.', 7),
('Stratégie de Contenu SEO', 'Développement d''une stratégie de contenu optimisée avec recherche de mots-clés et planning éditorial.', 7),
('SEO Local', 'Optimisation pour la recherche locale avec Google My Business, citations locales et stratégie géolocalisée.', 7),
('Campagne Google Ads', 'Configuration et gestion de campagnes publicitaires Google Ads avec optimisation du ROI et reporting.', 7),
('Link Building', 'Stratégie de création de backlinks de qualité pour améliorer votre autorité de domaine.', 7),
('Optimisation Taux de Conversion', 'Analyse et optimisation de votre tunnel de conversion pour maximiser les ventes.', 7),

-- ===== API & INTÉGRATIONS (ID: 8) =====
('API RESTful', 'Développement d''API REST sécurisée et documentée pour l''intégration avec des applications tierces.', 8),
('API GraphQL', 'Création d''API GraphQL flexible et performante avec requêtes optimisées et typage fort.', 8),
('Intégration Stripe', 'Intégration complète de la plateforme de paiement Stripe avec gestion des abonnements et webhooks.', 8),
('Intégration PayPal', 'Configuration du système de paiement PayPal avec checkout express et gestion des transactions.', 8),
('Intégration CRM', 'Connexion avec votre CRM (Salesforce, HubSpot) pour synchroniser les données clients et leads.', 8),
('Intégration ERP', 'Intégration avec votre système ERP pour synchroniser stocks, commandes et données financières.', 8),
('Webhooks Personnalisés', 'Configuration de webhooks pour la communication en temps réel entre vos différents systèmes.', 8),
('Intégration Marketplace', 'Synchronisation avec marketplaces (Amazon, eBay) pour gérer vos produits et commandes.', 8),

-- ===== MAINTENANCE & SUPPORT (ID: 9) =====
('Maintenance Mensuelle', 'Maintenance régulière incluant mises à jour de sécurité, sauvegardes et monitoring de performance.', 9),
('Support Technique 24/7', 'Assistance technique disponible en continu pour résoudre rapidement tout problème critique.', 9),
('Migration de Site', 'Migration complète de votre site vers un nouvel hébergeur avec zéro temps d''arrêt et tests approfondis.', 9),
('Optimisation Performance', 'Amélioration des temps de chargement avec compression, mise en cache et optimisation de la base de données.', 9),
('Sécurité & Audit', 'Audit de sécurité complet avec tests de pénétration, correction des vulnérabilités et mise en place de protections.', 9),
('Backup & Restauration', 'Système de sauvegarde automatique avec restauration rapide et stockage sécurisé multi-sites.', 9),
('Monitoring & Alertes', 'Surveillance continue de votre infrastructure avec alertes en cas d''incident.', 9),

-- ===== E-COMMERCE (ID: 10) =====
('Boutique Shopify', 'Configuration complète d''une boutique Shopify avec thème personnalisé et intégrations essentielles.', 10),
('Boutique WooCommerce', 'Création d''une boutique WooCommerce sur WordPress avec plugins optimisés et design sur mesure.', 10),
('Marketplace Multi-vendeurs', 'Plateforme marketplace permettant à plusieurs vendeurs de commercialiser leurs produits avec commissions automatiques.', 10),
('Système de Dropshipping', 'Solution de dropshipping automatisée avec synchronisation des stocks et gestion des fournisseurs.', 10),
('Configurateur de Produits', 'Outil de personnalisation permettant aux clients de configurer leurs produits avant achat avec aperçu en temps réel.', 10),
('Programme de Fidélité', 'Système de points de fidélité avec récompenses, niveaux VIP et promotions personnalisées.', 10),
('Passerelle de Paiement', 'Intégration multi-passerelles de paiement pour accepter cartes, PayPal, crypto et plus.', 10),

-- ===== CLOUD & DEVOPS (ID: 11) =====
('Déploiement Cloud AWS', 'Configuration et déploiement de votre infrastructure sur Amazon Web Services avec auto-scaling et haute disponibilité.', 11),
('Déploiement Cloud Azure', 'Migration et hébergement sur Microsoft Azure avec intégration des services cloud natifs.', 11),
('CI/CD Pipeline', 'Mise en place de pipelines d''intégration et déploiement continus pour automatiser vos releases.', 11),
('Containerisation Docker', 'Containerisation de vos applications avec Docker pour un déploiement portable et scalable.', 11),
('Orchestration Kubernetes', 'Configuration de clusters Kubernetes pour la gestion automatisée de vos containers en production.', 11),
('Infrastructure as Code', 'Automatisation de votre infrastructure avec Terraform ou CloudFormation pour une gestion versionnée.', 11),
('Serverless Architecture', 'Architecture serverless avec AWS Lambda ou Azure Functions pour réduire les coûts et améliorer la scalabilité.', 11),

-- ===== INTELLIGENCE ARTIFICIELLE (ID: 12) =====
('Chatbot IA', 'Développement d''un assistant conversationnel intelligent avec traitement du langage naturel et apprentissage automatique.', 12),
('Recommandation Personnalisée', 'Système de recommandation basé sur l''IA pour suggérer des produits ou contenus pertinents à vos utilisateurs.', 12),
('Analyse de Sentiment', 'Outil d''analyse de sentiment pour comprendre les émotions et opinions de vos clients à partir de leurs retours.', 12),
('Reconnaissance d''Images', 'Intégration de reconnaissance d''images par IA pour classification, détection d''objets ou modération de contenu.', 12),
('Traduction Automatique IA', 'Service de traduction multilingue automatique basé sur l''IA pour internationaliser votre contenu en temps réel.', 12),
('Prédiction & Analytics', 'Modèles prédictifs pour anticiper les tendances, comportements clients et optimiser vos décisions.', 12),
('Génération de Contenu IA', 'Génération automatique de textes, images ou vidéos assistée par intelligence artificielle.', 12),

-- ===== BLOCKCHAIN & WEB3 (ID: 13) =====
('Smart Contract', 'Développement de smart contracts sécurisés sur Ethereum, Polygon ou Binance Smart Chain.', 13),
('NFT Marketplace', 'Création d''une plateforme de marketplace pour l''achat, vente et échange de NFTs avec wallet intégré.', 13),
('Application DeFi', 'Application de finance décentralisée avec staking, yield farming et gestion de portefeuille crypto.', 13),
('Wallet Crypto', 'Développement d''un portefeuille de cryptomonnaies sécurisé multi-blockchain avec gestion des clés privées.', 13),
('DAO Governance', 'Mise en place d''une organisation autonome décentralisée avec système de vote et gouvernance.', 13),
('Token Creation', 'Création et déploiement de tokens (ERC-20, BEP-20) pour votre projet blockchain.', 13),

-- ===== VIDÉO & ANIMATION (ID: 14) =====
('Vidéo Promotionnelle', 'Création de vidéos promotionnelles professionnelles pour présenter vos produits ou services.', 14),
('Animation Motion Design', 'Animations graphiques dynamiques pour présenter vos concepts de manière visuelle et attractive.', 14),
('Montage Vidéo', 'Montage professionnel de vos vidéos avec effets, transitions et post-production.', 14),
('Vidéo Explicative', 'Création de vidéos explicatives animées pour simplifier vos messages complexes.', 14),
('Publicité Vidéo', 'Production de publicités vidéo pour réseaux sociaux, YouTube et télévision.', 14),

-- ===== CONSULTING IT (ID: 15) =====
('Audit Technique', 'Audit complet de votre infrastructure technique avec recommandations d''amélioration.', 15),
('Consulting Architecture', 'Conseil en architecture logicielle pour concevoir des systèmes robustes et scalables.', 15),
('Conseil en Cybersécurité', 'Conseil et stratégie de cybersécurité pour protéger vos actifs numériques.', 15),
('Formation Technique', 'Formation personnalisée pour vos équipes sur les technologies et bonnes pratiques.', 15),
('Stratégie IT', 'Élaboration de votre stratégie IT alignée avec vos objectifs business.', 15);

-- ============================================================================
-- FIN DU SCRIPT
-- ============================================================================
