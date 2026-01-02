<?php

declare(strict_types=1);

namespace Mini\Controllers;

use Mini\Core\Controller;
use Mini\Models\Order;
use Mini\Models\Cart;

final class OrderController extends Controller
{
    /**
     * Affiche toutes les commandes d'un utilisateur
     */
    public function listByUser(): void
    {
        // Vérifie et crée la colonne user_id si nécessaire
        \Mini\Core\MigrationHelper::ensureCommandeUserIdColumnExists();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Priorité : session > GET > 1
        $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : (isset($_GET['user_id']) ? (int)$_GET['user_id'] : 1);
        
        $orders = Order::getByUserId($user_id);
        
        $this->render('order/list', params: [
            'title' => 'Mes commandes',
            'orders' => $orders,
            'user_id' => $user_id
        ]);
    }

    /**
     * Affiche toutes les commandes validées
     */
    public function listValidated(): void
    {
        $orders = Order::getValidatedOrders();
        
        $this->render('order/validated', params: [
            'title' => 'Commandes validées',
            'orders' => $orders
        ]);
    }

    /**
     * Affiche les détails d'une commande
     */
    public function show(): void
    {
        // Vérifie et crée la colonne user_id si nécessaire
        \Mini\Core\MigrationHelper::ensureCommandeUserIdColumnExists();
        
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Le paramètre id est requis.'], JSON_PRETTY_PRINT);
            return;
        }
        
        $order = Order::findByIdWithProducts($id);
        
        $message = null;
        $messageType = null;
        
        if (isset($_GET['success']) && $_GET['success'] === 'created') {
            $message = 'Commande créée avec succès !';
            $messageType = 'success';
        }
        
        if (!$order) {
            $this->render('order/not-found', params: [
                'title' => 'Commande introuvable'
            ]);
            return;
        }
        
        $this->render('order/show', params: [
            'title' => 'Détails de la commande #' . $id,
            'order' => $order,
            'message' => $message,
            'messageType' => $messageType
        ]);
    }

    /**
     * Crée une commande à partir du panier
     */
    public function create(): void
    {
        // Vérifie et crée les colonnes/tables nécessaires
        \Mini\Core\MigrationHelper::ensureCommandeUserIdColumnExists();
        \Mini\Core\MigrationHelper::ensureCartTableExists();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /cart?user_id=' . ($_GET['user_id'] ?? 1));
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input === null) {
            $input = $_POST;
        }
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Priorité : POST > session > GET > 1
        $user_id = isset($input['user_id']) ? (int)$input['user_id'] : (isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : (isset($_GET['user_id']) ? (int)$_GET['user_id'] : 1));
        
        // Vérifie que le panier n'est pas vide
        $cartItems = Cart::getByUserId($user_id);
        if (empty($cartItems)) {
            header('Location: /cart?user_id=' . $user_id . '&error=empty_cart');
            return;
        }
        
        // Crée la commande
        try {
            $result = Order::createFromCart($user_id);
            
            if ($result['success'] && $result['order_id']) {
                // Redirige vers la liste des commandes avec le user_id de la session
                $redirect_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $user_id;
                // Utilise exit() pour s'assurer que la redirection fonctionne
                header('Location: /orders?user_id=' . $redirect_user_id . '&success=created');
                exit();
            } else {
                // Affiche l'erreur retournée par createFromCart
                $errorMsg = $result['error'] ?? 'Erreur inconnue lors de la création de la commande';
                error_log("Échec de la création de la commande pour user_id $user_id: $errorMsg");
                header('Location: /cart?user_id=' . $user_id . '&error=create_failed&msg=' . urlencode($errorMsg));
                exit();
            }
        } catch (\Exception $e) {
            error_log("Exception lors de la création de la commande: " . $e->getMessage());
            header('Location: /cart?user_id=' . $user_id . '&error=create_failed&msg=' . urlencode($e->getMessage()));
            exit();
        }
    }

    /**
     * Met à jour le statut d'une commande
     */
    public function updateStatus(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée.'], JSON_PRETTY_PRINT);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input === null) {
            $input = $_POST;
        }
        
        if (empty($input['order_id']) || empty($input['statut'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Les champs "order_id" et "statut" sont requis.'], JSON_PRETTY_PRINT);
            return;
        }
        
        $validStatuses = ['en_attente', 'validee', 'annulee'];
        if (!in_array($input['statut'], $validStatuses)) {
            http_response_code(400);
            echo json_encode(['error' => 'Statut invalide. Valeurs acceptées: ' . implode(', ', $validStatuses)], JSON_PRETTY_PRINT);
            return;
        }
        
        $order = new Order();
        $order->setId($input['order_id']);
        $order->setStatut($input['statut']);
        
        // Récupère le total existant
        $pdo = \Mini\Core\Database::getPDO();
        $stmt = $pdo->prepare("SELECT total FROM commande WHERE id = ?");
        $stmt->execute([$input['order_id']]);
        $existing = $stmt->fetch(\PDO::FETCH_ASSOC);
        $order->setTotal($existing['total'] ?? 0);
        
        if ($order->update()) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Statut de la commande mis à jour avec succès.'
            ], JSON_PRETTY_PRINT);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la mise à jour.'], JSON_PRETTY_PRINT);
        }
    }
}

