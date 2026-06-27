-- Suppression de la table si elle existe déjà
DROP TABLE IF EXISTS predefined_services;

-- Création de la table des services prédéfinis
CREATE TABLE predefined_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion des services prédéfinis
-- Note: Ajustez les category_id selon vos catégories existantes dans la table categories
INSERT INTO predefined_services (name, description, category_id) VALUES
-- Services Web (category_id à ajuster selon votre table)
('Site Vitrine', 'Création d''un site web élégant et professionnel pour présenter votre entreprise, vos services et vos coordonnées. Design responsive et optimisé SEO.', 1),
('Site E-commerce', 'Boutique en ligne complète avec gestion des produits, panier d''achat, paiement sécurisé et tableau de bord administrateur.', 1),
('Application Web Sur Mesure', 'Développement d''une application web personnalisée selon vos besoins spécifiques avec architecture moderne et scalable.', 1),
('Plateforme SaaS', 'Création d''une plateforme Software as a Service multi-tenant avec gestion des abonnements et facturation automatique.', 1),
('Portfolio Professionnel', 'Site portfolio moderne et interactif pour présenter vos projets, compétences et expériences de manière attractive.', 1),
('Blog & Site de Contenu', 'Plateforme de publication avec système de gestion de contenu, commentaires, catégories et optimisation SEO avancée.', 1),
('Dashboard Analytique', 'Interface d''administration avec visualisation de données, graphiques interactifs et rapports en temps réel.', 1),
('Intranet d''Entreprise', 'Réseau interne sécurisé pour la collaboration d''équipe, partage de documents et communication interne.', 1),

-- Design & UX/UI (category_id à ajuster)
('Maquette UI/UX Complète', 'Conception complète de l''interface utilisateur avec wireframes, prototypes interactifs et guide de style visuel.', 2),
('Refonte Design', 'Modernisation du design de votre site ou application existante pour améliorer l''expérience utilisateur et l''esthétique.', 2),
('Design System', 'Création d''un système de design complet avec composants réutilisables, guidelines et documentation pour assurer la cohérence.', 2),
('Branding & Identité Visuelle', 'Développement de votre identité de marque incluant logo, charte graphique, couleurs et typographies.', 2),
('Design Mobile First', 'Conception spécialement optimisée pour les appareils mobiles avec navigation intuitive et performance maximale.', 2),
('Animation & Micro-interactions', 'Ajout d''animations fluides et micro-interactions pour améliorer l''engagement et l''expérience utilisateur.', 2),

-- Applications Mobile (category_id à ajuster)
('Application iOS Native', 'Développement d''application iOS native en Swift avec intégration complète des fonctionnalités Apple.', 3),
('Application Android Native', 'Création d''application Android native en Kotlin avec Material Design et optimisation des performances.', 3),
('Application Cross-Platform', 'Application mobile multi-plateforme avec React Native ou Flutter pour iOS et Android à partir d''un seul code.', 3),
('Application Progressive Web (PWA)', 'Application web progressive installable offrant une expérience native avec fonctionnement hors ligne.', 3),
('Application de Livraison', 'Solution complète de livraison avec suivi GPS en temps réel, notifications push et système de paiement intégré.', 3),
('Application de Réservation', 'Système de réservation mobile avec gestion des disponibilités, calendrier et confirmations automatiques.', 3),

-- SEO & Marketing (category_id à ajuster)
('Audit SEO Complet', 'Analyse approfondie de votre site web avec recommandations détaillées pour améliorer votre référencement naturel.', 4),
('Optimisation SEO On-Page', 'Optimisation technique du contenu, balises meta, structure des URLs et performances pour un meilleur classement.', 4),
('Stratégie de Contenu SEO', 'Développement d''une stratégie de contenu optimisée avec recherche de mots-clés et planning éditorial.', 4),
('SEO Local', 'Optimisation pour la recherche locale avec Google My Business, citations locales et stratégie géolocalisée.', 4),
('Campagne Google Ads', 'Configuration et gestion de campagnes publicitaires Google Ads avec optimisation du ROI et reporting.', 4),
('Social Media Marketing', 'Stratégie et gestion des réseaux sociaux avec création de contenu, planification et analyse des performances.', 4),

-- API & Intégrations (category_id à ajuster)
('API RESTful', 'Développement d''API REST sécurisée et documentée pour l''intégration avec des applications tierces.', 5),
('API GraphQL', 'Création d''API GraphQL flexible et performante avec requêtes optimisées et typage fort.', 5),
('Intégration Stripe', 'Intégration complète de la plateforme de paiement Stripe avec gestion des abonnements et webhooks.', 5),
('Intégration PayPal', 'Configuration du système de paiement PayPal avec checkout express et gestion des transactions.', 5),
('Intégration CRM', 'Connexion avec votre CRM (Salesforce, HubSpot) pour synchroniser les données clients et leads.', 5),
('Intégration ERP', 'Intégration avec votre système ERP pour synchroniser stocks, commandes et données financières.', 5),
('Webhooks Personnalisés', 'Configuration de webhooks pour la communication en temps réel entre vos différents systèmes.', 5),

-- Maintenance & Support (category_id à ajuster)
('Maintenance Mensuelle', 'Maintenance régulière incluant mises à jour de sécurité, sauvegardes et monitoring de performance.', 6),
('Support Technique 24/7', 'Assistance technique disponible en continu pour résoudre rapidement tout problème critique.', 6),
('Migration de Site', 'Migration complète de votre site vers un nouvel hébergeur avec zéro temps d''arrêt et tests approfondis.', 6),
('Optimisation Performance', 'Amélioration des temps de chargement avec compression, mise en cache et optimisation de la base de données.', 6),
('Sécurité & Audit', 'Audit de sécurité complet avec tests de pénétration, correction des vulnérabilités et mise en place de protections.', 6),
('Backup & Restauration', 'Système de sauvegarde automatique avec restauration rapide et stockage sécurisé multi-sites.', 6),

-- E-commerce (category_id à ajuster)
('Boutique Shopify', 'Configuration complète d''une boutique Shopify avec thème personnalisé et intégrations essentielles.', 7),
('Boutique WooCommerce', 'Création d''une boutique WooCommerce sur WordPress avec plugins optimisés et design sur mesure.', 7),
('Marketplace Multi-vendeurs', 'Plateforme marketplace permettant à plusieurs vendeurs de commercialiser leurs produits avec commissions automatiques.', 7),
('Système de Dropshipping', 'Solution de dropshipping automatisée avec synchronisation des stocks et gestion des fournisseurs.', 7),
('Configurateur de Produits', 'Outil de personnalisation permettant aux clients de configurer leurs produits avant achat avec aperçu en temps réel.', 7),
('Programme de Fidélité', 'Système de points de fidélité avec récompenses, niveaux VIP et promotions personnalisées.', 7),

-- Cloud & DevOps (category_id à ajuster)
('Déploiement Cloud AWS', 'Configuration et déploiement de votre infrastructure sur Amazon Web Services avec auto-scaling et haute disponibilité.', 8),
('Déploiement Cloud Azure', 'Migration et hébergement sur Microsoft Azure avec intégration des services cloud natifs.', 8),
('CI/CD Pipeline', 'Mise en place de pipelines d''intégration et déploiement continus pour automatiser vos releases.', 8),
('Containerisation Docker', 'Containerisation de vos applications avec Docker pour un déploiement portable et scalable.', 8),
('Orchestration Kubernetes', 'Configuration de clusters Kubernetes pour la gestion automatisée de vos containers en production.', 8),
('Monitoring & Alerting', 'Système de surveillance avec alertes en temps réel, logs centralisés et dashboards de performance.', 8),

-- Intelligence Artificielle (category_id à ajuster)
('Chatbot IA', 'Développement d''un assistant conversationnel intelligent avec traitement du langage naturel et apprentissage automatique.', 9),
('Recommandation Personnalisée', 'Système de recommandation basé sur l''IA pour suggérer des produits ou contenus pertinents à vos utilisateurs.', 9),
('Analyse de Sentiment', 'Outil d''analyse de sentiment pour comprendre les émotions et opinions de vos clients à partir de leurs retours.', 9),
('Reconnaissance d''Images', 'Intégration de reconnaissance d''images par IA pour classification, détection d''objets ou modération de contenu.', 9),
('Traduction Automatique', 'Service de traduction multilingue automatique pour internationaliser votre contenu en temps réel.', 9),

-- Blockchain & Web3 (category_id à ajuster)
('Smart Contract', 'Développement de smart contracts sécurisés sur Ethereum, Polygon ou Binance Smart Chain.', 10),
('NFT Marketplace', 'Création d''une plateforme de marketplace pour l''achat, vente et échange de NFTs avec wallet intégré.', 10),
('Application DeFi', 'Application de finance décentralisée avec staking, yield farming et gestion de portefeuille crypto.', 10),
('Wallet Crypto', 'Développement d''un portefeuille de cryptomonnaies sécurisé multi-blockchain avec gestion des clés privées.', 10);
