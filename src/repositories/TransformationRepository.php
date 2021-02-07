<?php

require_once "./src/repositories/ConfigRepository.php";
require_once "./src/dtos/Config.php";

class TransformationRepository {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function save($userID, $config, $fileName, $outputFileName, $inputFileName) {
        $statement = "
            INSERT INTO transformations 
                (user_id, config_id, file_name, output_file_name, input_file_name)
            VALUES
                (:user_id, :config_id, :file_name, :output_file_name, :input_file_name);
        ";

        try {
            $statement = $this->connection->prepare($statement);
            $statement->execute(array(
                'user_id' => $userID,
                'config_id' => $config->getId(),
                'file_name' => $fileName,
                'output_file_name' => $outputFileName,
                'input_file_name' => $inputFileName
            ));
            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            http_response_code(500);
            exit($e->getMessage());
        }  
    }

    public function getForUser($userID) {
        $statement = "
            SELECT * FROM transformations WHERE user_id = ?;
        ";

        $configRepo = new ConfigRepository($this->connection);
        try {
            $fetch = $this->connection->prepare($statement);
            $fetch->execute([$userID]);
            $result = $fetch->fetchAll(PDO::FETCH_ASSOC);
            $arr = array();

            foreach ($result as $id => $row) {
                $configName = $configRepo->getSingle($row["config_id"])->getName();
                array_push($arr, array("id" => $row["id"], "configId"=> $row["config_id"], 
                    "fileName" => $row["file_name"], "inputFileName" => $row["input_file_name"], 
                    "outputFileName" => $row["output_file_name"], "configName" => $configName));
            }
            return $arr;
        } catch (PDOException $e) {
            http_response_code(500);
            exit($e->getMessage());
        }
    }

    public function getByConfigAndFile($config, $fileName) {
        $statement = "
            SELECT * FROM transformations WHERE config_id = :id AND file_name = :file;
        ";

        try {
            $fetch = $this->connection->prepare($statement);
            $fetch->execute(array(
                "id" => $config->getId(),
                "file" => $fileName
            ));
            $row = $fetch->fetch();
            if (!$row || $row == "") {
                return null;
            }
            return array("id" => $row["id"], "configId"=> $row["config_id"], 
                "fileName" => $row["file_name"], "inputFileName" => $row["input_file_name"], 
                "outputFileName" => $row["output_file_name"], "userId" => $row["user_id"]);;
        } catch (PDOException $e) {
            http_response_code(500);
            exit($e->getMessage());
        }
    }

    public function getByConfigId($config) {
        $statement = "
            SELECT * FROM transformations WHERE config_id = :id";

        try {
            $fetch = $this->connection->prepare($statement);
            $fetch->execute(array(
                "id" => $config->getId(),
            ));
            $rows = $fetch->fetchAll();
            if (!$rows || $rows == "") {
                return null;
            }
            return $rows;
        } catch (PDOException $e) {
            http_response_code(500);
            exit($e->getMessage());
        }
    }

    public function getSingle($id) {
        $statement = "
            SELECT * FROM transformations WHERE id = ?;
        ";

        try {
            $fetch = $this->connection->prepare($statement);
            $fetch->execute(array($id));
            $row = $fetch->fetch();
            if (!$row || $row == "") {
                return null;
            }
            return array("id" => $row["id"], "configId"=> $row["config_id"], "userId" => $row["user_id"],
                "fileName" => $row["file_name"], "inputFileName" => $row["input_file_name"], 
                "outputFileName" => $row["output_file_name"]);
        } catch (PDOException $e) {
            http_response_code(500);
            exit($e->getMessage());
        } 
    }

    public function delete($id) {
        $statement = "
            DELETE FROM transformations WHERE id = ?;
        ";

        try {
            $fetch = $this->connection->prepare($statement);
            $fetch->execute(array($id));
        } catch (PDOException $e) {
            http_response_code(500);
            exit($e->getMessage());
        } 
    }
}

?>