<?php

namespace Mini\Models;

use Mini\Core\Database;
use Mini\Models\Cart;
use Mini\Models\Product;
use PDO;

class Order
{
    private $id;
    private $user_id;
    private $statut;
    private $total;
    private $created_at;
    private $updated_at;

    // =====================
    // Getters / Setters
    // =====================

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    public function getStatut()
    {
        return $this->statut;
    }

    public function setStatut($statut)
    {
        $this->statut = $statut;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function setTotal($total)
    {
        $this->total = $total;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
    }

    // =====================
    // Méthodes CRUD
    // =====================

    /**
     * Récupère toutes les commandes d'un utilisateur avec leurs produits
     * @param int $user_id
     * @return array
     */
    public static function getByUserId($user_id)
    {
        // Vérifie et crée les colonnes/tables nécessaires
        \Mini\Core\MigrationHelper::ensureCommandeUserIdColumnExists();
        
        $pdo = Database::getPDO();
        
        // Convertit user_id en entier pour éviter les problèmes de type
        $user_id = (int)$user_id;
        
        // Récupère les commandes
        try {
            $stmt = $pdo->prepare("
                SELECT * FROM commande 
                WHERE user_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$user_id]);
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Si created_at n'existe pas, on trie par id
            try {
                $stmt = $pdo->prepare("
                    SELECT * FROM commande 
                    WHERE user_id = ? 
                    ORDER BY id DESC
                ");
                $stmt->execute([$user_id]);
                $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\PDOException $e2) {
                // Si même ça échoue, retourne un tableau vide
                error_log("Erreur lors de la récupération des commandes pour user_id $user_id: " . $e2->getMessage());
                return [];
            }
        }
        
        // Pour chaque commande, récupère ses produits
        foreach ($orders as &$order) {
            try {
                $stmt = $pdo->prepare("
                    SELECT cp.*, p.nom as product_nom, p.image_url, p.description, cat.nom as categorie_nom
                    FROM commande_produit cp
                    INNER JOIN produit p ON cp.product_id = p.id
                    LEFT JOIN categorie cat ON p.categorie_id = cat.id
                    WHERE cp.commande_id = ?
                ");
                $stmt->execute([$order['id']]);
                $order['products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                error_log("Erreur lors de la récupération des produits pour la commande " . $order['id'] . ": " . $e->getMessage());
                $order['products'] = [];
            }
        }
        
        return $orders;
    }

    /**
     * Récupère toutes les commandes validées
     * @return array
     */
    public static function getValidatedOrders()
    {
        $pdo = Database::getPDO();
        $stmt = $pdo->query("
            SELECT c.*, u.nom as user_nom, u.email as user_email
            FROM commande c
            INNER JOIN user u ON c.user_id = u.id
            WHERE c.statut = 'validee'
            ORDER BY c.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère une commande par son ID avec ses produits
     * @param int $id
     * @return array|null
     */
    public static function findByIdWithProducts($id)
    {
        $pdo = Database::getPDO();
        
        // Récupère la commande
        $stmt = $pdo->prepare("
            SELECT c.*, u.nom as user_nom, u.email as user_email
            FROM commande c
            INNER JOIN user u ON c.user_id = u.id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            return null;
        }
        
        // Récupère les produits de la commande
        $stmt = $pdo->prepare("
            SELECT cp.*, p.nom as product_nom, p.image_url, cat.nom as categorie_nom
            FROM commande_produit cp
            INNER JOIN produit p ON cp.product_id = p.id
            LEFT JOIN categorie cat ON p.categorie_id = cat.id
            WHERE cp.commande_id = ?
        ");
        $stmt->execute([$id]);
        $order['products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $order;
    }

    /**
     * Crée une nouvelle commande à partir du panier
     * @param int $user_id
     * @return array ['success' => bool, 'order_id' => int|null, 'error' => string|null]
     */
    public static function createFromCart($user_id)
    {
        // Vérifie et crée les colonnes/tables nécessaires
        \Mini\Core\MigrationHelper::ensureCommandeUserIdColumnExists();
        \Mini\Core\MigrationHelper::ensureCartTableExists();
        
        $pdo = Database::getPDO();
        
        // Convertit user_id en entier
        $user_id = (int)$user_id;
        
        // Récupère les articles du panier
        $cartItems = Cart::getByUserId($user_id);
        
        if (empty($cartItems)) {
            $error = "Panier vide pour user_id: $user_id";
            error_log($error);
            return ['success' => false, 'order_id' => null, 'error' => 'Votre panier est vide.'];
        }
        
        // Calcule le total
        $total = Cart::getTotalByUserId($user_id);
        
        if ($total <= 0) {
            $error = "Total invalide pour user_id: $user_id, total: $total";
            error_log($error);
            return ['success' => false, 'order_id' => null, 'error' => 'Le total de la commande est invalide.'];
        }
        
        try {
            $pdo->beginTransaction();
            
            // Vérifie que la table commande existe et a les bonnes colonnes
            $stmtCheck = $pdo->query("SHOW COLUMNS FROM commande");
            $columns = $stmtCheck->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('user_id', $columns) || !in_array('statut', $columns) || !in_array('total', $columns)) {
                throw new \Exception("La table commande n'a pas les colonnes nécessaires");
            }
            
            // Crée la commande avec user_id, statut et total
            $stmt = $pdo->prepare("INSERT INTO commande (user_id, statut, total) VALUES (?, 'validee', ?)");
            $result = $stmt->execute([$user_id, $total]);
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                throw new \Exception("Erreur SQL lors de l'insertion de la commande: " . ($errorInfo[2] ?? 'Erreur inconnue'));
            }
            
            $orderId = $pdo->lastInsertId();
            
            if (!$orderId || $orderId == 0) {
                throw new \Exception("Impossible de récupérer l'ID de la commande créée");
            }
            
            error_log("Commande créée avec succès: order_id=$orderId, user_id=$user_id, total=$total");
            
            // Vérifie que la table commande_produit existe, sinon on la crée
            $stmtCheck = $pdo->query("SHOW TABLES LIKE 'commande_produit'");
            if ($stmtCheck->rowCount() == 0) {
                // Crée la table commande_produit si elle n'existe pas
                $pdo->exec("CREATE TABLE IF NOT EXISTS commande_produit (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    commande_id INT NOT NULL,
                    product_id INT NOT NULL,
                    quantite INT NOT NULL DEFAULT 1,
                    prix_unitaire DECIMAL(10,2) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            }
            
            // Ajoute les produits à la commande
            $stmt = $pdo->prepare("INSERT INTO commande_produit (commande_id, product_id, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
            
            foreach ($cartItems as $item) {
                // L'id retourné par Cart::getByUserId() est l'id du produit (p.*)
                $product_id = $item['id'];
                $product = Product::findById($product_id);
                if ($product) {
                    $result = $stmt->execute([
                        $orderId,
                        $product_id,
                        $item['quantite'],
                        $product['prix']
                    ]);
                    
                    if (!$result) {
                        $errorInfo = $stmt->errorInfo();
                        throw new \Exception("Erreur SQL lors de l'insertion du produit dans la commande: " . ($errorInfo[2] ?? 'Erreur inconnue'));
                    }
                    
                    // Met à jour le stock
                    $newStock = $product['stock'] - $item['quantite'];
                    $updateStmt = $pdo->prepare("UPDATE produit SET stock = ? WHERE id = ?");
                    $updateResult = $updateStmt->execute([$newStock, $item['id']]);
                    if (!$updateResult) {
                        error_log("Erreur lors de la mise à jour du stock pour le produit $product_id");
                        // On continue quand même
                    }
                } else {
                    error_log("Produit introuvable: product_id=$product_id");
                    throw new \Exception("Produit introuvable: ID $product_id");
                }
            }
            
            // Vide le panier
            $clearResult = Cart::clearByUserId($user_id);
            if (!$clearResult) {
                error_log("Erreur lors du vidage du panier pour user_id: $user_id");
                // On continue quand même car la commande est créée
            }
            
            $pdo->commit();
            error_log("Commande créée et panier vidé avec succès: order_id=$orderId, user_id=$user_id");
            return ['success' => true, 'order_id' => $orderId, 'error' => null];
            
        } catch (\PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errorMsg = "Erreur de base de données: " . $e->getMessage();
            if ($e->getCode() == 23000) {
                $errorMsg = "Erreur de contrainte (clé étrangère): Vérifiez que l'utilisateur et les produits existent.";
            }
            error_log("Erreur PDO lors de la création de la commande pour user_id $user_id: " . $e->getMessage());
            error_log("Code d'erreur: " . $e->getCode());
            error_log("Stack trace: " . $e->getTraceAsString());
            return ['success' => false, 'order_id' => null, 'error' => $errorMsg];
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errorMsg = "Erreur: " . $e->getMessage();
            error_log("Erreur lors de la création de la commande pour user_id $user_id: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return ['success' => false, 'order_id' => null, 'error' => $errorMsg];
        }
    }

    /**
     * Met à jour le statut d'une commande
     * @return bool
     */
    public function update()
    {
        $pdo = Database::getPDO();
        $stmt = $pdo->prepare("UPDATE commande SET statut = ?, total = ? WHERE id = ?");
        return $stmt->execute([$this->statut, $this->total, $this->id]);
    }

    /**
     * Supprime une commande
     * @return bool
     */
    public function delete()
    {
        $pdo = Database::getPDO();
        $stmt = $pdo->prepare("DELETE FROM commande WHERE id = ?");
        return $stmt->execute([$this->id]);
    }
}

