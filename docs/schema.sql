CREATE TABLE categories (
    id_categorie SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE produits (
    id_produit SERIAL PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    description TEXT,
    prix NUMERIC(10,2) NOT NULL CHECK (prix >= 0),
    stock INT NOT NULL CHECK (stock >= 0),
    image VARCHAR(255),
    actif BOOLEAN DEFAULT TRUE,
    id_categorie INT NOT NULL REFERENCES categories(id_categorie) ON DELETE RESTRICT ON UPDATE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE clients (
    id_client SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    adresse VARCHAR(255),
    ville VARCHAR(100),
    code_postal VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE commandes (
    id_commande SERIAL PRIMARY KEY,
    numero_commande VARCHAR(50) NOT NULL UNIQUE,
    id_client INT NOT NULL REFERENCES clients(id_client) ON DELETE CASCADE ON UPDATE CASCADE,
    date_commande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    statut VARCHAR(20) DEFAULT 'en_attente' CHECK (statut IN ('en_attente', 'payee', 'expediee', 'livree', 'annulee')),
    montant_total NUMERIC(10,2) NOT NULL CHECK (montant_total >= 0),
    adresse_livraison VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE lignes_commande (
    id_ligne SERIAL PRIMARY KEY,
    id_commande INT NOT NULL REFERENCES commandes(id_commande) ON DELETE CASCADE ON UPDATE CASCADE,
    id_produit INT NOT NULL REFERENCES produits(id_produit) ON DELETE RESTRICT ON UPDATE CASCADE,
    quantite INT NOT NULL CHECK (quantite > 0),
    prix_unitaire NUMERIC(10,2) NOT NULL CHECK (prix_unitaire >= 0),
    sous_total NUMERIC(10,2) GENERATED ALWAYS AS (quantite * prix_unitaire) STORED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE administrateurs (
    id_admin SERIAL PRIMARY KEY,
    nom_utilisateur VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'admin' CHECK (role IN ('admin','super_admin')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



-- =====================================

-- Catégories
INSERT INTO categories (nom, description) VALUES
('Informatique', 'Ordinateurs, périphériques et accessoires'),
('Livres', 'Romans, guides et manuels'),
('Mode', 'Vêtements et accessoires'),
('Maison', 'Décoration et ustensiles'),
('Jeux', 'Jeux de société et jouets');

-- Produits (25 produits répartis dans les catégories)
INSERT INTO produits (nom, description, prix, stock, id_categorie) VALUES
('Ordinateur portable', 'Portable 16GB RAM', 1200.00, 10, 1),
('Clavier mécanique', 'Clavier RGB', 80.00, 50, 1),
('Souris gaming', 'Souris sans fil', 50.00, 70, 1),
('Écran 27 pouces', 'Full HD', 200.00, 30, 1),
('Casque audio', 'Casque Bluetooth', 100.00, 40, 1),
('Roman SF', 'Livre science-fiction', 15.00, 30, 2),
('Guide pratique', 'Guide pour débutants', 20.00, 25, 2),
('Roman policier', 'Thriller haletant', 18.00, 20, 2),
('BD aventure', 'Bande dessinée', 12.00, 35, 2),
('Livre cuisine', 'Recettes faciles', 22.00, 15, 2),
('T-shirt coton', 'T-shirt taille M', 20.00, 100, 3),
('Jean slim', 'Jean taille 32', 50.00, 80, 3),
('Veste hiver', 'Veste chaude', 120.00, 25, 3),
('Robe été', 'Robe légère', 60.00, 30, 3),
('Chaussures sport', 'Chaussures running', 90.00, 50, 3),
('Bougie parfumée', 'Bougie parfumée vanille', 15.00, 40, 4),
('Tasse céramique', 'Tasse design', 8.00, 60, 4),
('Oreiller confort', 'Oreiller doux', 25.00, 30, 4),
('Plaid chaud', 'Plaid laine', 40.00, 20, 4),
('Lampe bureau', 'Lampe LED', 35.00, 25, 4),
('Jeu de société', 'Jeu stratégique', 30.00, 15, 5),
('Puzzle 1000 pièces', 'Puzzle complexe', 20.00, 20, 5),
('Peluche ours', 'Peluche douce', 15.00, 50, 5),
('Figurine héro', 'Figurine articulée', 25.00, 30, 5),
('Dés de jeu', 'Lot de dés colorés', 10.00, 40, 5);

-- Clients (5 clients)
INSERT INTO clients (nom, prenom, email, mot_de_passe, adresse, ville, code_postal) VALUES
('Joseph', 'Marie', 'marie.joseph@email.com', 'motdepasse1', '12 rue A', 'Paris', '75001'),
('Martin', 'Claire', 'claire.martin@email.com', 'motdepasse2', '34 rue B', 'Lyon', '69000'),
('Durand', 'Paul', 'paul.durand@email.com', 'motdepasse3', '56 rue C', 'Marseille', '13000'),
('Leroy', 'Sophie', 'sophie.leroy@email.com', 'motdepasse4', '78 rue D', 'Toulouse', '31000'),
('Moreau', 'Luc', 'luc.moreau@email.com', 'motdepasse5', '90 rue E', 'Nice', '06000');

-- Administrateurs (2)
INSERT INTO administrateurs (nom_utilisateur, email, mot_de_passe, role) VALUES
('admin1', 'admin1@email.com', 'motdepasseAdmin', 'admin'),
('superadmin', 'superadmin@email.com', 'motdepasseSuper', 'super_admin');

-- =====================================
-- 3️⃣ Insertion des commandes et lignes de commande
-- =====================================

-- Commandes
INSERT INTO commandes (numero_commande, id_client, statut, montant_total, adresse_livraison) VALUES
('CMD-0001', 1, 'en_attente', 1350.00, '12 rue A, Paris'),
('CMD-0002', 2, 'payee', 100.00, '34 rue B, Lyon'),
('CMD-0003', 3, 'expediee', 75.00, '56 rue C, Marseille'),
('CMD-0004', 4, 'livree', 200.00, '78 rue D, Toulouse'),
('CMD-0005', 5, 'annulee', 60.00, '90 rue E, Nice'),
('CMD-0006', 1, 'payee', 90.00, '12 rue A, Paris'),
('CMD-0007', 2, 'en_attente', 250.00, '34 rue B, Lyon'),
('CMD-0008', 3, 'en_attente', 120.00, '56 rue C, Marseille'),
('CMD-0009', 4, 'livree', 45.00, '78 rue D, Toulouse'),
('CMD-0010', 5, 'payee', 300.00, '90 rue E, Nice');

-- Lignes de commande
INSERT INTO lignes_commande (id_commande, id_produit, quantite, prix_unitaire) VALUES
-- CMD-0001 (Marie Joseph)
(1, 1, 1, 1200.00),
(1, 2, 1, 150.00),

-- CMD-0002 (Claire Martin)
(2, 6, 2, 15.00),
(2, 7, 1, 20.00),

-- CMD-0003 (Paul Durand)
(3, 11, 1, 20.00),
(3, 12, 1, 50.00),

-- CMD-0004 (Sophie Leroy)
(4, 13, 1, 120.00),
(4, 14, 1, 60.00),

-- CMD-0005 (Luc Moreau)
(5, 15, 1, 90.00),

-- CMD-0006 (Jean Dupont)
(6, 16, 2, 8.00),
(6, 17, 1, 25.00),

-- CMD-0007 (Claire Martin)
(7, 3, 2, 50.00),
(7, 4, 1, 200.00),

-- CMD-0008 (Paul Durand)
(8, 18, 1, 30.00),
(8, 19, 2, 40.00),

-- CMD-0009 (Sophie Leroy)
(9, 20, 1, 30.00),
(9, 21, 1, 20.00),

-- CMD-0010 (Luc Moreau)
(10, 22, 1, 15.00),
(10, 23, 1, 25.00),
(10, 24, 1, 25.00),
(10, 25, 1, 10.00);


