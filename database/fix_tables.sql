-- Script de correction pour créer les tables manquantes
-- Exécutez ce script dans votre base de données MySQL/MariaDB

-- 1. Création de la table panier (si elle n'existe pas)
CREATE TABLE IF NOT EXISTS panier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantite INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_panier_user 
        FOREIGN KEY (user_id) 
        REFERENCES user(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT fk_panier_produit 
        FOREIGN KEY (product_id) 
        REFERENCES produit(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Création de la table commande (si elle n'existe pas)
CREATE TABLE IF NOT EXISTS commande (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    statut ENUM('en_attente', 'validee', 'annulee') DEFAULT 'en_attente',
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_commande_user 
        FOREIGN KEY (user_id) 
        REFERENCES user(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Création de la table commande_produit (si elle n'existe pas)
CREATE TABLE IF NOT EXISTS commande_produit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL,
    product_id INT NOT NULL,
    quantite INT NOT NULL DEFAULT 1,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_commande_produit_commande 
        FOREIGN KEY (commande_id) 
        REFERENCES commande(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT fk_commande_produit_produit 
        FOREIGN KEY (product_id) 
        REFERENCES produit(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Insertion des catégories de produits de beauté
INSERT INTO categorie (nom, description) VALUES
('Essences', 'Essences hydratantes et régénérantes pour la peau'),
('Sérums', 'Sérums concentrés pour des soins ciblés'),
('Nettoyants', 'Produits de nettoyage et démaquillants'),
('Toners', 'Toniques et lotions pour équilibrer le pH de la peau'),
('Protection solaire', 'Crèmes et sprays de protection solaire'),
('Masques', 'Masques visage pour des soins intensifs'),
('Exfoliants', 'Produits exfoliants pour éliminer les cellules mortes'),
('Soins yeux', 'Crèmes et sérums spécialisés pour le contour des yeux'),
('Soins lèvres', 'Baumes et soins pour les lèvres')
ON DUPLICATE KEY UPDATE nom=nom;

-- 5. Vérification et correction de la table commande si user_id n'existe pas
-- (Cette partie ne sera exécutée que si nécessaire)
-- Si vous obtenez une erreur sur user_id, exécutez manuellement :
-- ALTER TABLE commande ADD COLUMN user_id INT NOT NULL AFTER id;
-- ALTER TABLE commande ADD CONSTRAINT fk_commande_user FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE ON UPDATE CASCADE;

