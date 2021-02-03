<?php

class SharesRepository {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function shareTransformation($userID, $transformationID) {
        $query = "INSERT INTO shares(user_id, transformation_id) VALUES (?, ?)";

        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute([$userID, $transformationID]);
        } catch (PDOException $e) {
            exit($e->getMessage());
        } 
    }

    public function getSharedTransformations($userID) {
        $query = "SELECT s.transformation_id as transformationID, t.user_id as ownerID, 
            t.config_id as configID, t.file_name as fileName, t.input_file_name as inputFileName,
            t.output_file_name as outputFileName, c.name as configName
            FROM shares s
            JOIN transformations t ON s.transformation_id = t.id
            JOIN configs c ON t.config_id = c.id
            WHERE s.user_id = ?";

        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute([$userID]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            exit($e->getMessage());
        } 
    }

    public function deleteShares($transformationID) {
        $query = "DELETE FROM shares WHERE transformation_id = ?;";

        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute([$transformationID]);
        } catch (PDOException $e) {
            exit($e->getMessage());
        } 
    }
}

?>