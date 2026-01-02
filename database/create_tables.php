<?php
/**
 * Script de création automatique des tables manquantes
 * Exécutez ce script une fois pour créer les tables panier, commande et commande_produit
 * 
 * Usage: php database/create_tables.php
 * Ou accédez-y via le navigateur si votre serveur web le permet
 */

// Charge la configuration
$config = parse_ini_file(__DIR__ . '/../app/config.ini');

$db_name = $config['DB_NAME'] ?? 'mini_mvc';
$db_host = $config['DB_HOST'] ?? '127.0.0.1';
$db_username = $config['DB_USERNAME'] ?? 'root';
$db_password = $config['DB_PASSWORD'] ?? '';

try {
    // Connexion à MySQL (sans sélectionner la base de données)
    $pdo = new PDO(
        "mysql:host=$db_host;charset=utf8mb4",
        $db_username,
        $db_password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Sélectionne la base de données
    $pdo->exec("USE `$db_name`");
    
    echo "✅ Connexion à la base de données réussie\n";
    echo "📦 Base de données: $db_name\n\n";
    
    // 0. Création de la table categorie si elle n'existe pas
    echo "🔨 Vérification/Création de la table 'categorie'...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS categorie (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(150) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✅ Table 'categorie' prête\n\n";
    
    // 1. Création de la table panier
    echo "🔨 Création de la table 'panier'...\n";
    $pdo->exec("
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✅ Table 'panier' créée avec succès\n\n";
    
    // 2. Création de la table commande
    echo "🔨 Création de la table 'commande'...\n";
    $pdo->exec("
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✅ Table 'commande' créée avec succès\n\n";
    
    // 3. Création de la table commande_produit
    echo "🔨 Création de la table 'commande_produit'...\n";
    $pdo->exec("
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✅ Table 'commande_produit' créée avec succès\n\n";
    
    // 4. Insertion des catégories de beauté
    echo "🔨 Insertion des catégories de produits de beauté...\n";
    $categories = [
        ['Essences', 'Essences hydratantes et régénérantes pour la peau'],
        ['Sérums', 'Sérums concentrés pour des soins ciblés'],
        ['Nettoyants', 'Produits de nettoyage et démaquillants'],
        ['Toners', 'Toniques et lotions pour équilibrer le pH de la peau'],
        ['Protection solaire', 'Crèmes et sprays de protection solaire'],
        ['Masques', 'Masques visage pour des soins intensifs'],
        ['Exfoliants', 'Produits exfoliants pour éliminer les cellules mortes'],
        ['Soins yeux', 'Crèmes et sérums spécialisés pour le contour des yeux'],
        ['Soins lèvres', 'Baumes et soins pour les lèvres']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO categorie (nom, description) VALUES (?, ?) ON DUPLICATE KEY UPDATE nom=nom");
    foreach ($categories as $cat) {
        $stmt->execute([$cat[0], $cat[1]]);
        echo "✅ Catégorie '{$cat[0]}' ajoutée\n";
    }
    echo "\n";
    
    // Vérification
    echo "🔍 Vérification des tables créées...\n";
    $tables = ['panier', 'commande', 'commande_produit'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Table '$table' existe\n";
        } else {
            echo "❌ Table '$table' n'existe pas\n";
        }
    }
    
    echo "\n🎉 Migration terminée avec succès !\n";
    echo "Vous pouvez maintenant utiliser le panier et les commandes.\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur lors de la migration :\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
    echo "\n";
    echo "💡 Vérifiez que :\n";
    echo "   - La base de données '$db_name' existe\n";
    echo "   - Les tables 'user' et 'produit' existent\n";
    echo "   - Vos identifiants de connexion sont corrects dans app/config.ini\n";
    exit(1);
}

