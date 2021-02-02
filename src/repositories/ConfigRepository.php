<?php

require_once "./src/dtos/Config.php";

class ConfigRepository {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function save($config, $userId) {
        $statement = "
            INSERT INTO configs 
                (id, name, input_format, output_format, tabulation)
            VALUES
                (:id, :name, :inputFormat, :outputFormat, :tabulation);
        ";

        $existing = $this->checkIfExistsForUser($config->getName(), $userId);
        if ($existing != null) {
            return Config::fromMap($existing);
        }
        try {
            $statement = $this->connection->prepare($statement);
            $statement->execute(array(
                'id' => $config->getId(),
                'name' => $config->getName(),
                'inputFormat'  => $config->getInputFormat(),
                'outputFormat' => $config->getOutputFormat(),
                'tabulation' => $config->getTabulation(),
            ));
            return $config;
        } catch (PDOException $e) {
            http_response_code(500);
            exit($e->getMessage());
        }  
    }

    private function checkIfExistsForUser($configName, $userId) {
        $statement = "
            SELECT configs.id, name, input_format, output_format, tabulation
            FROM configs JOIN transformations ON configs.id = transformations.config_id
            WHERE configs.name = :configName AND transformations.user_id = :userId;
        ";
        try {
            $fetch = $this->connection->prepare($statement);
            $fetch->execute(array("userId" => $userId, "configName" => $configName));
            $result = $fetch->fetch();
            
            if (!$result || $result == "") {
                error_log("Not found", 3 , "./err_log.log");
                return null;
            }
            return array("id" => $result["id"], "name" => $result["name"], "inputFormat" => $result["input_format"],
                "outputFormat" => $result["output_format"], "tabulation" => $result["tabulation"]);
        } catch (PDOException $e) {
            http_response_code(500);
            exit($e->getMessage());
        }
    }

    public function getSingle($id) {
        $statement = "
            SELECT * FROM configs WHERE id = ?;
        ";

        try {
            $fetch = $this->connection->prepare($statement);
            $fetch->execute(array($id));
            $result = $fetch->fetch();
            return array("id" => $result["id"], "name" => $result["name"], "inputFormat" => $result["input_format"],
                "outputFormat" => $result["output_format"], "tabulation" => $result["tabulation"]);
        } catch (PDOException $e) {
            http_response_code(500);
            exit($e->getMessage());
        } 
    }
}

?>