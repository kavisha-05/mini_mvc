-- Migration pour ajouter le champ password à la table user
-- Exécutez ce script dans votre base de données MySQL/MariaDB
-- 
-- NOTE: Si la colonne existe déjà, cette commande échouera.
-- Dans ce cas, vous pouvez ignorer l'erreur ou vérifier d'abord avec:
-- SHOW COLUMNS FROM user LIKE 'password';

ALTER TABLE user 
ADD COLUMN password VARCHAR(255) NULL;

