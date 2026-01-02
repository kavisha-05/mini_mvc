-- Migration pour ajouter la colonne image_url à la table produit
-- Exécutez ce script dans votre base de données MySQL/MariaDB

-- Vérifie si la colonne existe déjà avant de l'ajouter
-- Si la colonne existe déjà, cette commande échouera - c'est normal, vous pouvez l'ignorer

ALTER TABLE produit 
ADD COLUMN image_url VARCHAR(500) NULL;

-- Note: Si vous obtenez une erreur "Duplicate column name", cela signifie que la colonne existe déjà
-- Dans ce cas, vous pouvez ignorer l'erreur

