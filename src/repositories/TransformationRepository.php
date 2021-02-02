<?php

require_once "./src/repositories/ConfigRepository.php";

class TransformationRepository {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function save($config, $fileName, $outputFileName, $inputFileName) {
        $statement = "
            INSERT INTO transformations 
                (config_id, file_name, output_file_name, input_file_name)
            VALUES
                (:config_id, :file_name, :output_file_name, :input_file_name);
        ";

        try {
            $statement = $this->connection->prepare($statement);
            $statement->execute(array(
                'config_id' => $config->getId(),
                'file_name' => $fileName,
                'output_file_name' => $outputFileName,
                'input_file_name' => $inputFileName
            ));
            return $statement->rowCount();
        } catch (PDOException $e) {
            exit($e->getMessage());
        }  
    }

    public function getAll() {
        $statement = "
            SELECT * FROM transformations;
        ";

        $configRepo = new ConfigRepository($this->connection);
        try {
            $fetch = $this->connection->prepare($statement);
            $fetch->execute();
            $result = $fetch->fetchAll(PDO::FETCH_ASSOC);
            $arr = array();

            foreach ($result as $id => $row) {
                $configName = $configRepo->getSingle($row["config_id"])["name"];
                array_push($arr, array("id" => $row["id"], "configId"=> $row["config_id"], 
                    "fileName" => $row["file_name"], "inputFileName" => $row["input_file_name"], 
                    "outputFileName" => $row["output_file_name"], "configName" => $configName));
            }
            return $arr;
        } catch (PDOException $e) {
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
            return array("id" => $row["id"], "configId"=> $row["config_id"], 
                "fileName" => $row["file_name"], "inputFileName" => $row["input_file_name"], 
                "outputFileName" => $row["output_file_name"]);
        } catch (PDOException $e) {
            exit($e->getMessage());
        } 
    }
}

?>