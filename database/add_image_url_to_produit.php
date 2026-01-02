<?php
/**
 * Script pour ajouter automatiquement la colonne image_url à la table produit
 * Exécutez ce script une fois pour ajouter la colonne manquante
 * 
 * Usage: php database/add_image_url_to_produit.php
 * Ou accédez-y via le navigateur si votre serveur web le permet
 */

// Charge la configuration
$config = parse_ini_file(__DIR__ . '/../app/config.ini');

$db_name = $config['DB_NAME'] ?? 'mini_mvc';
$db_host = $config['DB_HOST'] ?? '127.0.0.1';
$db_username = $config['DB_USERNAME'] ?? 'root';
$db_password = $config['DB_PASSWORD'] ?? '';

try {
    // Connexion à MySQL
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_username,
        $db_password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✅ Connexion à la base de données réussie\n";
    echo "📦 Base de données: $db_name\n\n";
    
    // Vérifie que la table produit existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'produit'");
    if ($stmt->rowCount() == 0) {
        echo "❌ La table 'produit' n'existe pas. Veuillez d'abord créer cette table.\n";
        exit(1);
    }
    
    // Vérifie si la colonne image_url existe déjà
    echo "🔍 Vérification de la colonne 'image_url'...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM produit LIKE 'image_url'");
    
    if ($stmt->rowCount() > 0) {
        echo "ℹ️  La colonne 'image_url' existe déjà dans la table 'produit'.\n";
        echo "✅ Aucune action nécessaire.\n";
    } else {
        echo "🔨 Ajout de la colonne 'image_url' à la table 'produit'...\n";
        
        try {
            $pdo->exec("ALTER TABLE produit ADD COLUMN image_url VARCHAR(500) NULL");
            echo "✅ Colonne 'image_url' ajoutée avec succès !\n";
        } catch (PDOException $e) {
            if ($e->getCode() == '42S21') {
                echo "ℹ️  La colonne existe déjà (erreur de cache).\n";
            } else {
                throw $e;
            }
        }
    }
    
    // Vérification finale
    echo "\n🔍 Vérification de la structure de la table 'produit'...\n";
    $stmt = $pdo->query("DESCRIBE produit");
    $columns = $stmt->fetchAll();
    
    echo "\n📋 Colonnes de la table 'produit' :\n";
    foreach ($columns as $column) {
        $marker = ($column['Field'] === 'image_url') ? ' ✅' : '';
        echo "   • {$column['Field']} ({$column['Type']})$marker\n";
    }
    
    echo "\n🎉 Migration terminée avec succès !\n";
    echo "Vous pouvez maintenant utiliser le champ image_url lors de la création de produits.\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur lors de la migration :\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
    echo "\n";
    echo "💡 Vérifiez que :\n";
    echo "   - La base de données '$db_name' existe\n";
    echo "   - La table 'produit' existe\n";
    echo "   - Vos identifiants de connexion sont corrects dans app/config.ini\n";
    exit(1);
}

