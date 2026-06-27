-- Ajouter les colonnes manquantes à la table users pour compléter le profil
-- À exécuter dans phpMyAdmin

ALTER TABLE users
ADD COLUMN location VARCHAR(100) DEFAULT NULL COMMENT 'Localisation de l\'utilisateur' AFTER bio,
ADD COLUMN website VARCHAR(255) DEFAULT NULL COMMENT 'Site web de l\'utilisateur' AFTER location;
