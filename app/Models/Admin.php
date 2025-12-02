<?php
require_once 'Database.php';

class Admin {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM administrateurs");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
