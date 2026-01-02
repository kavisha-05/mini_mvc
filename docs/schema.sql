-- Script SQL pour créer toutes les tables de la base de données mini_mvc
-- Ce script peut être exécuté dans phpMyAdmin ou via la ligne de commande MySQL

-- Suppression des tables si elles existent (ATTENTION: supprime toutes les données)
-- DROP TABLE IF EXISTS commande_produit;
-- DROP TABLE IF EXISTS commande;
-- DROP TABLE IF EXISTS panier;
-- DROP TABLE IF EXISTS produit;
-- DROP TABLE IF EXISTS categorie;
-- DROP TABLE IF EXISTS user;

-- Table: user
-- Stocke les informations des utilisateurs (clients)
CREATE TABLE IF NOT EXISTS user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: categorie
-- Stocke les catégories de produits (ex: Essences, Sérums, Nettoyants, etc.)
CREATE TABLE IF NOT EXISTS categorie (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: produit
-- Stocke les informations des produits
CREATE TABLE IF NOT EXISTS produit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    description TEXT,
    prix DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    image_url VARCHAR(500) NULL,
    categorie_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categorie_id) REFERENCES categorie(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: panier
-- Stocke les articles dans le panier d'un utilisateur
CREATE TABLE IF NOT EXISTS panier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantite INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_product (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES produit(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: commande
-- Stocke les commandes validées par les utilisateurs
CREATE TABLE IF NOT EXISTS commande (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    statut ENUM('en_attente', 'validee', 'annulee') DEFAULT 'en_attente',
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: commande_produit
-- Table de liaison entre les commandes et les produits (many-to-many)
-- Stocke les détails de chaque produit dans une commande (quantité, prix au moment de la commande)
CREATE TABLE IF NOT EXISTS commande_produit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL,
    product_id INT NOT NULL,
    quantite INT NOT NULL DEFAULT 1,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (commande_id) REFERENCES commande(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES produit(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertion des catégories par défaut (beauté)
INSERT IGNORE INTO categorie (nom) VALUES
('Essences'),
('Sérums'),
('Nettoyants'),
('Toners'),
('Protection solaire'),
('Masques'),
('Exfoliants'),
('Soins yeux'),
('Soins lèvres');

