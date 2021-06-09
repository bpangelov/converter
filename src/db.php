<?php

require_once "./config.php";

class DB {
    private $connection;

    public function __construct($useDatabase = true) {
        $dbhost = ServerConfig::$DB_HOST;
        $dbport = ServerConfig::$DB_PORT;
        $dbName = ServerConfig::$DB_NAME;
        $userName = ServerConfig::$DB_USER;
        $userPassword = ServerConfig::$DB_PASS;

        $args = "mysql:host=$dbhost;port=$dbport";
        if ($useDatabase) {
            $args .= ";dbname=$dbName";
        }
        $this->connection = new PDO($args, $userName, $userPassword,
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