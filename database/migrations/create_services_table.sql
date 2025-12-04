-- Création de la table des services prédéfinis
CREATE TABLE IF NOT EXISTS predefined_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion des services de développement web
INSERT INTO predefined_services (name, description, category) VALUES
-- Développement Web
('Site Vitrine', 'Création d''un site web élégant et professionnel pour présenter votre entreprise, vos services et vos coordonnées. Design responsive et optimisé SEO.', 'Développement Web'),
('Site E-commerce', 'Boutique en ligne complète avec gestion des produits, panier d''achat, paiement sécurisé et tableau de bord administrateur.', 'Développement Web'),
('Application Web Sur Mesure', 'Développement d''une application web personnalisée selon vos besoins spécifiques avec architecture moderne et scalable.', 'Développement Web'),
('Plateforme SaaS', 'Création d''une plateforme Software as a Service multi-tenant avec gestion des abonnements et facturation automatique.', 'Développement Web'),
('Portfolio Professionnel', 'Site portfolio moderne et interactif pour présenter vos projets, compétences et expériences de manière attractive.', 'Développement Web'),
('Blog & Site de Contenu', 'Plateforme de publication avec système de gestion de contenu, commentaires, catégories et optimisation SEO avancée.', 'Développement Web'),
('Dashboard Analytique', 'Interface d''administration avec visualisation de données, graphiques interactifs et rapports en temps réel.', 'Développement Web'),
('Intranet d''Entreprise', 'Réseau interne sécurisé pour la collaboration d''équipe, partage de documents et communication interne.', 'Développement Web'),

-- Design & UX/UI
('Maquette UI/UX Complète', 'Conception complète de l''interface utilisateur avec wireframes, prototypes interactifs et guide de style visuel.', 'Design & UX/UI'),
('Refonte Design', 'Modernisation du design de votre site ou application existante pour améliorer l''expérience utilisateur et l''esthétique.', 'Design & UX/UI'),
('Design System', 'Création d''un système de design complet avec composants réutilisables, guidelines et documentation pour assurer la cohérence.', 'Design & UX/UI'),
('Branding & Identité Visuelle', 'Développement de votre identité de marque incluant logo, charte graphique, couleurs et typographies.', 'Design & UX/UI'),
('Design Mobile First', 'Conception spécialement optimisée pour les appareils mobiles avec navigation intuitive et performance maximale.', 'Design & UX/UI'),
('Animation & Micro-interactions', 'Ajout d''animations fluides et micro-interactions pour améliorer l''engagement et l''expérience utilisateur.', 'Design & UX/UI'),

-- Applications Mobile
('Application iOS Native', 'Développement d''application iOS native en Swift avec intégration complète des fonctionnalités Apple.', 'Applications Mobile'),
('Application Android Native', 'Création d''application Android native en Kotlin avec Material Design et optimisation des performances.', 'Applications Mobile'),
('Application Cross-Platform', 'Application mobile multi-plateforme avec React Native ou Flutter pour iOS et Android à partir d''un seul code.', 'Applications Mobile'),
('Application Progressive Web (PWA)', 'Application web progressive installable offrant une expérience native avec fonctionnement hors ligne.', 'Applications Mobile'),
('Application de Livraison', 'Solution complète de livraison avec suivi GPS en temps réel, notifications push et système de paiement intégré.', 'Applications Mobile'),
('Application de Réservation', 'Système de réservation mobile avec gestion des disponibilités, calendrier et confirmations automatiques.', 'Applications Mobile'),

-- SEO & Marketing Digital
('Audit SEO Complet', 'Analyse approfondie de votre site web avec recommandations détaillées pour améliorer votre référencement naturel.', 'SEO & Marketing'),
('Optimisation SEO On-Page', 'Optimisation technique du contenu, balises meta, structure des URLs et performances pour un meilleur classement.', 'SEO & Marketing'),
('Stratégie de Contenu SEO', 'Développement d''une stratégie de contenu optimisée avec recherche de mots-clés et planning éditorial.', 'SEO & Marketing'),
('SEO Local', 'Optimisation pour la recherche locale avec Google My Business, citations locales et stratégie géolocalisée.', 'SEO & Marketing'),
('Campagne Google Ads', 'Configuration et gestion de campagnes publicitaires Google Ads avec optimisation du ROI et reporting.', 'SEO & Marketing'),
('Social Media Marketing', 'Stratégie et gestion des réseaux sociaux avec création de contenu, planification et analyse des performances.', 'SEO & Marketing'),

-- API & Intégrations
('API RESTful', 'Développement d''API REST sécurisée et documentée pour l''intégration avec des applications tierces.', 'API & Intégrations'),
('API GraphQL', 'Création d''API GraphQL flexible et performante avec requêtes optimisées et typage fort.', 'API & Intégrations'),
('Intégration Stripe', 'Intégration complète de la plateforme de paiement Stripe avec gestion des abonnements et webhooks.', 'API & Intégrations'),
('Intégration PayPal', 'Configuration du système de paiement PayPal avec checkout express et gestion des transactions.', 'API & Intégrations'),
('Intégration CRM', 'Connexion avec votre CRM (Salesforce, HubSpot) pour synchroniser les données clients et leads.', 'API & Intégrations'),
('Intégration ERP', 'Intégration avec votre système ERP pour synchroniser stocks, commandes et données financières.', 'API & Intégrations'),
('Webhooks Personnalisés', 'Configuration de webhooks pour la communication en temps réel entre vos différents systèmes.', 'API & Intégrations'),

-- Maintenance & Support
('Maintenance Mensuelle', 'Maintenance régulière incluant mises à jour de sécurité, sauvegardes et monitoring de performance.', 'Maintenance & Support'),
('Support Technique 24/7', 'Assistance technique disponible en continu pour résoudre rapidement tout problème critique.', 'Maintenance & Support'),
('Migration de Site', 'Migration complète de votre site vers un nouvel hébergeur avec zéro temps d''arrêt et tests approfondis.', 'Maintenance & Support'),
('Optimisation Performance', 'Amélioration des temps de chargement avec compression, mise en cache et optimisation de la base de données.', 'Maintenance & Support'),
('Sécurité & Audit', 'Audit de sécurité complet avec tests de pénétration, correction des vulnérabilités et mise en place de protections.', 'Maintenance & Support'),
('Backup & Restauration', 'Système de sauvegarde automatique avec restauration rapide et stockage sécurisé multi-sites.', 'Maintenance & Support'),

-- E-commerce
('Boutique Shopify', 'Configuration complète d''une boutique Shopify avec thème personnalisé et intégrations essentielles.', 'E-commerce'),
('Boutique WooCommerce', 'Création d''une boutique WooCommerce sur WordPress avec plugins optimisés et design sur mesure.', 'E-commerce'),
('Marketplace Multi-vendeurs', 'Plateforme marketplace permettant à plusieurs vendeurs de commercialiser leurs produits avec commissions automatiques.', 'E-commerce'),
('Système de Dropshipping', 'Solution de dropshipping automatisée avec synchronisation des stocks et gestion des fournisseurs.', 'E-commerce'),
('Configurateur de Produits', 'Outil de personnalisation permettant aux clients de configurer leurs produits avant achat avec aperçu en temps réel.', 'E-commerce'),
('Programme de Fidélité', 'Système de points de fidélité avec récompenses, niveaux VIP et promotions personnalisées.', 'E-commerce'),

-- Cloud & DevOps
('Déploiement Cloud AWS', 'Configuration et déploiement de votre infrastructure sur Amazon Web Services avec auto-scaling et haute disponibilité.', 'Cloud & DevOps'),
('Déploiement Cloud Azure', 'Migration et hébergement sur Microsoft Azure avec intégration des services cloud natifs.', 'Cloud & DevOps'),
('CI/CD Pipeline', 'Mise en place de pipelines d''intégration et déploiement continus pour automatiser vos releases.', 'Cloud & DevOps'),
('Containerisation Docker', 'Containerisation de vos applications avec Docker pour un déploiement portable et scalable.', 'Cloud & DevOps'),
('Orchestration Kubernetes', 'Configuration de clusters Kubernetes pour la gestion automatisée de vos containers en production.', 'Cloud & DevOps'),
('Monitoring & Alerting', 'Système de surveillance avec alertes en temps réel, logs centralisés et dashboards de performance.', 'Cloud & DevOps'),

-- Intelligence Artificielle
('Chatbot IA', 'Développement d''un assistant conversationnel intelligent avec traitement du langage naturel et apprentissage automatique.', 'Intelligence Artificielle'),
('Recommandation Personnalisée', 'Système de recommandation basé sur l''IA pour suggérer des produits ou contenus pertinents à vos utilisateurs.', 'Intelligence Artificielle'),
('Analyse de Sentiment', 'Outil d''analyse de sentiment pour comprendre les émotions et opinions de vos clients à partir de leurs retours.', 'Intelligence Artificielle'),
('Reconnaissance d''Images', 'Intégration de reconnaissance d''images par IA pour classification, détection d''objets ou modération de contenu.', 'Intelligence Artificielle'),
('Traduction Automatique', 'Service de traduction multilingue automatique pour internationaliser votre contenu en temps réel.', 'Intelligence Artificielle'),

-- Blockchain & Web3
('Smart Contract', 'Développement de smart contracts sécurisés sur Ethereum, Polygon ou Binance Smart Chain.', 'Blockchain & Web3'),
('NFT Marketplace', 'Création d''une plateforme de marketplace pour l''achat, vente et échange de NFTs avec wallet intégré.', 'Blockchain & Web3'),
('Application DeFi', 'Application de finance décentralisée avec staking, yield farming et gestion de portefeuille crypto.', 'Blockchain & Web3'),
('Wallet Crypto', 'Développement d''un portefeuille de cryptomonnaies sécurisé multi-blockchain avec gestion des clés privées.', 'Blockchain & Web3');
