<?php

namespace Mini\Core;

use PDO;

/**
 * Classe utilitaire pour gérer les migrations automatiques
 */
class MigrationHelper
{
    /**
     * Vérifie et ajoute la colonne image_url à la table produit si elle n'existe pas
     */
    public static function ensureImageUrlColumnExists(): void
    {
        try {
            $pdo = Database::getPDO();
            
            // Vérifie si la colonne existe
            $stmt = $pdo->query("SHOW COLUMNS FROM produit LIKE 'image_url'");
            
            if ($stmt->rowCount() == 0) {
                // La colonne n'existe pas, on l'ajoute
                $pdo->exec("ALTER TABLE produit ADD COLUMN image_url VARCHAR(500) NULL");
            }
        } catch (\PDOException $e) {
            // Ignore les erreurs si la colonne existe déjà ou si la table n'existe pas encore
            // L'erreur sera gérée lors de la création de la table
        }
    }

    /**
     * Vérifie et modifie la colonne categorie_id pour accepter NULL si nécessaire
     */
    public static function ensureCategorieIdAllowsNull(): void
    {
        try {
            $pdo = Database::getPDO();
            
            // Vérifie la définition actuelle de la colonne
            $stmt = $pdo->query("SHOW COLUMNS FROM produit WHERE Field = 'categorie_id'");
            $column = $stmt->fetch();
            
            if ($column && strtoupper($column['Null']) === 'NO') {
                // La colonne n'accepte pas NULL, on la modifie pour accepter NULL
                $pdo->exec("ALTER TABLE produit MODIFY COLUMN categorie_id INT NULL");
            }
        } catch (\PDOException $e) {
            // Ignore les erreurs si la colonne n'existe pas encore
        }
    }
    
    /**
     * Vérifie et crée la table panier si elle n'existe pas
     */
    public static function ensureCartTableExists(): void
    {
        try {
            $pdo = Database::getPDO();
            
            // Vérifie si la table existe
            $stmt = $pdo->query("SHOW TABLES LIKE 'panier'");
            
            if ($stmt->rowCount() == 0) {
                // Crée d'abord la table sans clés étrangères pour éviter les erreurs
                $pdo->exec("CREATE TABLE IF NOT EXISTS panier (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    product_id INT NOT NULL,
                    quantite INT NOT NULL DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_user_product (user_id, product_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                
                // Vérifie si les tables référencées existent avant d'ajouter les clés étrangères
                try {
                    $stmtUser = $pdo->query("SHOW TABLES LIKE 'user'");
                    $stmtProduit = $pdo->query("SHOW TABLES LIKE 'produit'");
                    
                    if ($stmtUser->rowCount() > 0 && $stmtProduit->rowCount() > 0) {
                        // Vérifie si les colonnes id existent dans les tables référencées
                        $stmtUserCols = $pdo->query("SHOW COLUMNS FROM user WHERE Field = 'id'");
                        $stmtProduitCols = $pdo->query("SHOW COLUMNS FROM produit WHERE Field = 'id'");
                        
                        if ($stmtUserCols->rowCount() > 0 && $stmtProduitCols->rowCount() > 0) {
                            // Ajoute les clés étrangères si possible
                            try {
                                $pdo->exec("ALTER TABLE panier 
                                    ADD CONSTRAINT fk_panier_user 
                                    FOREIGN KEY (user_id) 
                                    REFERENCES user(id) 
                                    ON DELETE CASCADE 
                                    ON UPDATE CASCADE");
                            } catch (\PDOException $e) {
                                // Ignore si la contrainte existe déjà
                            }
                            
                            try {
                                $pdo->exec("ALTER TABLE panier 
                                    ADD CONSTRAINT fk_panier_produit 
                                    FOREIGN KEY (product_id) 
                                    REFERENCES produit(id) 
                                    ON DELETE CASCADE 
                                    ON UPDATE CASCADE");
                            } catch (\PDOException $e) {
                                // Ignore si la contrainte existe déjà
                            }
                        }
                    }
                } catch (\PDOException $e) {
                    // Continue sans clés étrangères si les tables n'existent pas
                }
            }
        } catch (\PDOException $e) {
            // Ignore les erreurs si la table existe déjà
        }
    }
    
    /**
     * Vérifie et crée/ajoute les colonnes nécessaires à la table commande
     */
    public static function ensureCommandeUserIdColumnExists(): void
    {
        try {
            $pdo = Database::getPDO();
            
            // Vérifie si la table commande existe
            $stmt = $pdo->query("SHOW TABLES LIKE 'commande'");
            
            if ($stmt->rowCount() > 0) {
                // La table existe, vérifie les contraintes de clé étrangère
                try {
                    // Supprime les anciennes contraintes si elles existent
                    $fkStmt = $pdo->query("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.TABLE_CONSTRAINTS 
                        WHERE TABLE_SCHEMA = DATABASE() 
                        AND TABLE_NAME = 'commande' 
                        AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                    ");
                    $constraints = $fkStmt->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($constraints as $constraint) {
                        try {
                            $pdo->exec("ALTER TABLE commande DROP FOREIGN KEY `$constraint`");
                        } catch (\PDOException $e) {
                            // Ignore si la contrainte n'existe pas
                        }
                    }
                } catch (\PDOException $e) {
                    // Ignore les erreurs
                }
                
                // Vérifie les colonnes existantes
                $columns = $pdo->query("SHOW COLUMNS FROM commande")->fetchAll(PDO::FETCH_COLUMN);
                
                // Si utilisateur_id existe, on le renomme en user_id
                if (in_array('utilisateur_id', $columns) && !in_array('user_id', $columns)) {
                    try {
                        $pdo->exec("ALTER TABLE commande CHANGE utilisateur_id user_id INT NOT NULL");
                    } catch (\PDOException $e) {
                        // Si ça échoue, on essaie de supprimer et recréer
                        try {
                            $pdo->exec("ALTER TABLE commande DROP COLUMN utilisateur_id");
                            $pdo->exec("ALTER TABLE commande ADD COLUMN user_id INT NOT NULL AFTER id");
                        } catch (\PDOException $e2) {
                            // Ignore
                        }
                    }
                }
                
                if (!in_array('user_id', $columns) && !in_array('utilisateur_id', $columns)) {
                    $pdo->exec("ALTER TABLE commande ADD COLUMN user_id INT NOT NULL AFTER id");
                }
                
                if (!in_array('created_at', $columns)) {
                    $pdo->exec("ALTER TABLE commande ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
                }
                
                if (!in_array('updated_at', $columns)) {
                    $pdo->exec("ALTER TABLE commande ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
                }
                
                if (!in_array('statut', $columns)) {
                    $pdo->exec("ALTER TABLE commande ADD COLUMN statut ENUM('en_attente', 'validee', 'annulee') DEFAULT 'en_attente'");
                }
                
                if (!in_array('total', $columns)) {
                    $pdo->exec("ALTER TABLE commande ADD COLUMN total DECIMAL(10,2) NOT NULL DEFAULT 0.00");
                }
            } else {
                // La table n'existe pas, on la crée complète
                $pdo->exec("CREATE TABLE IF NOT EXISTS commande (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    statut ENUM('en_attente', 'validee', 'annulee') DEFAULT 'en_attente',
                    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            }
            
            // Vérifie et crée la table commande_produit si nécessaire
            $stmt = $pdo->query("SHOW TABLES LIKE 'commande_produit'");
            if ($stmt->rowCount() == 0) {
                try {
                    $pdo->exec("CREATE TABLE IF NOT EXISTS commande_produit (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        commande_id INT NOT NULL,
                        product_id INT NOT NULL,
                        quantite INT NOT NULL DEFAULT 1,
                        prix_unitaire DECIMAL(10,2) NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                } catch (\PDOException $e) {
                    // Si la table existe déjà, on ignore l'erreur
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        error_log("Erreur lors de la création de la table commande_produit: " . $e->getMessage());
                    }
                }
            }
        } catch (\PDOException $e) {
            // Ignore les erreurs si les colonnes/tables existent déjà
        }
    }
    
    /**
     * Vérifie et crée la table user si elle n'existe pas
     */
    public static function ensureUserTableExists(): void
    {
        try {
            $pdo = Database::getPDO();
            
            // Vérifie si la table existe
            $stmt = $pdo->query("SHOW TABLES LIKE 'user'");
            
            if ($stmt->rowCount() == 0) {
                // La table n'existe pas, on la crée
                $pdo->exec("CREATE TABLE IF NOT EXISTS user (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nom VARCHAR(150) NOT NULL,
                    email VARCHAR(255) NOT NULL UNIQUE,
                    password VARCHAR(255) NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            } else {
                // La table existe, vérifie si la colonne password existe
                $stmtCol = $pdo->query("SHOW COLUMNS FROM user LIKE 'password'");
                if ($stmtCol->rowCount() == 0) {
                    // Ajoute la colonne password si elle n'existe pas
                    $pdo->exec("ALTER TABLE user ADD COLUMN password VARCHAR(255) NULL");
                }
            }
        } catch (\PDOException $e) {
            // Ignore les erreurs si la table/colonne existe déjà
        }
    }
}

