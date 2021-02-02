<?php

class TransformationRepository {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function save($config, $fileName) {
        $statement = "
            INSERT INTO transformations 
                (config_id, file_name)
            VALUES
                (:config_id, :file_name);
        ";

        try {
            $statement = $this->connection->prepare($statement);
            $statement->execute(array(
                'config_id' => $config->getId(),
                'file_name' => $fileName,
            ));
            return $statement->rowCount();
        } catch (PDOException $e) {
            exit($e->getMessage());
        }  
    }
}

?>