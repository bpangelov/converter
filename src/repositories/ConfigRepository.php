<?php

require_once "./src/dtos/Config.php";

class ConfigRepository {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function save($config) {
        $statement = "
            INSERT INTO configs 
                (id, name, input_format, output_format, tabulation)
            VALUES
                (:id, :name, :inputFormat, :outputFormat, :tabulation);
        ";

        try {
            $statement = $this->connection->prepare($statement);
            $statement->execute(array(
                'id' => $config->getId(),
                'name' => $config->getName(),
                'inputFormat'  => $config->getInputFormat(),
                'outputFormat' => $config->getOutputFormat(),
                'tabulation' => $config->getTabulation(),
            ));
            return $statement->rowCount();
        } catch (PDOException $e) {
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
            exit($e->getMessage());
        } 
    }
}

?>