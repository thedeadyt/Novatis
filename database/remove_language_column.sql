-- Script pour supprimer le système de langue de Novatis
-- Exécuter ce script pour supprimer la colonne language de la table user_preferences

USE alex2pro_movatis;

-- Supprimer la colonne language de la table user_preferences
ALTER TABLE user_preferences DROP COLUMN IF EXISTS language;

-- Fin du script
