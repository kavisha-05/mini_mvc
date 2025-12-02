<?php
require_once 'Database.php';

class Order {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM commandes");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByClient($clientId) {
        $stmt = $this->pdo->prepare("SELECT * FROM commandes WHERE id_client = :id");
        $stmt->execute(['id' => $clientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
