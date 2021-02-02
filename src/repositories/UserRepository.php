<?php

class UserRepository {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function saveUser($username, $password) {
        $query = "INSERT INTO users (username, password) VALUES (?, ?)";
         
        try {
            $stmt = $this->connection->prepare($query);
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);  
            $stmt->execute([$username, $hashedPassword]);
        } catch (PDOException $e) {
            exit($e->getMessage());
        } 
    }

    public function getUser($username) {
        $query = "SELECT id, username, password FROM users WHERE username = ?";
        
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute([$username]);

            $row = $stmt->fetch();
            return $row;
        } catch (PDOException $e) {
            exit($e->getMessage());
        } 
    }
}

?>