<?php
/**
 * Script d'insertion des catégories de produits de beauté
 * Exécutez ce script une fois pour ajouter les catégories dans la base de données
 * 
 * Usage: php database/insert_categories_beaute.php
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
    
    // Vérifie que la table categorie existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'categorie'");
    if ($stmt->rowCount() == 0) {
        echo "❌ La table 'categorie' n'existe pas. Veuillez d'abord exécuter database/migrations.sql\n";
        exit(1);
    }
    
    echo "🔨 Insertion des catégories de beauté...\n\n";
    
    // Liste des catégories à insérer
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
    
    $inserted = 0;
    $updated = 0;
    
    foreach ($categories as $categorie) {
        try {
            $stmt->execute([$categorie[0], $categorie[1]]);
            
            // Vérifie si c'était une insertion ou une mise à jour
            $checkStmt = $pdo->prepare("SELECT id FROM categorie WHERE nom = ?");
            $checkStmt->execute([$categorie[0]]);
            $existing = $checkStmt->fetch();
            
            if ($existing) {
                echo "✅ Catégorie '{$categorie[0]}' ajoutée/mise à jour\n";
                $inserted++;
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate key
                echo "ℹ️  Catégorie '{$categorie[0]}' existe déjà\n";
                $updated++;
            } else {
                echo "❌ Erreur pour '{$categorie[0]}': " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n📊 Résumé :\n";
    echo "   - Catégories ajoutées/mises à jour : $inserted\n";
    
    // Affiche toutes les catégories
    echo "\n📋 Liste des catégories disponibles :\n";
    $stmt = $pdo->query("SELECT id, nom, description FROM categorie ORDER BY nom ASC");
    $allCategories = $stmt->fetchAll();
    
    foreach ($allCategories as $cat) {
        echo "   • {$cat['nom']}";
        if (!empty($cat['description'])) {
            echo " - {$cat['description']}";
        }
        echo "\n";
    }
    
    echo "\n🎉 Catégories de beauté ajoutées avec succès !\n";
    echo "Vous pouvez maintenant les utiliser lors de la création de produits.\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur lors de l'insertion des catégories :\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
    echo "\n";
    echo "💡 Vérifiez que :\n";
    echo "   - La base de données '$db_name' existe\n";
    echo "   - La table 'categorie' existe (exécutez database/migrations.sql si nécessaire)\n";
    echo "   - Vos identifiants de connexion sont corrects dans app/config.ini\n";
    exit(1);
}

