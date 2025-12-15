<?php
// db.php - Petty Cash System - Database Class

class Database {
    private $host = 'localhost';
    private $db_name = 'pettycashsystem';
    private $username = 'root';
    private $password = '';
    public $conn;
    

    public function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                $this->username, 
                $this->password
            );
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
    }

    
    public function getPdo() {
        return $this->conn;
    }

    
    public function executeQuery($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    
    public function getSingle($sql, $params = []) {
        $stmt = $this->executeQuery($sql, $params);
        if ($stmt) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    
    public function getMultiple($sql, $params = []) {
        $stmt = $this->executeQuery($sql, $params);
        if ($stmt) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }

    
    public function getCount($sql, $params = []) {
        $stmt = $this->executeQuery($sql, $params);
        if ($stmt) {
            return $stmt->rowCount();
        }
        return 0;
    }

    
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
}
?>