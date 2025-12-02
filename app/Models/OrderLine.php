<?php
require_once 'Database.php';

class OrderLine {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function getByOrder($orderId) {
        $stmt = $this->pdo->prepare("SELECT * FROM lignes_commande WHERE id_commande = :id");
        $stmt->execute(['id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
