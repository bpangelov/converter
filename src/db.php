<?php

require_once "./config.php";

class DB {
    private $connection;

    public function __construct() {
        $dbhost = ServerConfig::$DB_HOST;
        $dbName = ServerConfig::$DB_NAME;
        $userName = ServerConfig::$DB_USER;
        $userPassword = ServerConfig::$DB_PASS;

        $this->connection = new PDO("mysql:host=$dbhost;dbname=$dbName", $userName, $userPassword,
            [
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
    }

    public function getConnection() {
        return $this->connection;
    }
}
?>