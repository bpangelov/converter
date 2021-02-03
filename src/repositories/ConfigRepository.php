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
            return $config;
        } catch (PDOException $e) {
            http_response_code(500);
            exit($e->getMessage());
        }  
    }

    public function getIfOwnedOrSharedWithUser($configName, $userId) {
        $statement = "
            SELECT configs.id, name, input_format, output_format, tabulation FROM configs 
            JOIN transformations ON configs.id = transformations.config_id
            LEFT JOIN shares ON shares.transformation_id = transformations.id
            WHERE configs.name = :configName AND (transformations.user_id = :userId OR shares.user_id = :userId);
        ";
        try {
            $fetch = $this->connection->prepare($statement);
            $fetch->execute(array("userId" => $userId, "configName" => $configName));
            $result = $fetch->fetch();
            
            if (!$result || $result == "") {
                return null;
            }
            return Config::fromDatabaseEntry($result);
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
            return Config::fromDatabaseEntry($result);;
        } catch (PDOException $e) {
            http_response_code(500);
            exit($e->getMessage());
        }
    }

    public function update($id, $config) {
        $statement = "
            UPDATE configs
            SET input_format = :inputFormat, output_format = :outputFormat, tabulation = :tabulation
            WHERE id = :id;
        ";

        try {
            $fetch = $this->connection->prepare($statement);
            $fetch->execute(array(
                'id' => $id,
                'inputFormat'  => $config->getInputFormat(),
                'outputFormat' => $config->getOutputFormat(),
                'tabulation' => $config->getTabulation(),
            ));
            $result = $this->getSingle($id);
            return $result;
        } catch (PDOException $e) {
            http_response_code(500);
            exit($e->getMessage());
        }
    }

    public function delete($id) {
        $statement = "
            DELETE FROM configs WHERE id = ?;
        ";

        try {
            $op = $this->connection->prepare($statement);
            $op->execute(array($id));
        } catch (PDOException $e) {
            http_response_code(500);
            exit($e->getMessage());
        } 
    }
}

?>