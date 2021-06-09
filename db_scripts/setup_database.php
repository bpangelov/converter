<?php

require_once "./config.php";
require_once "./src/db.php";

$db = new DB(false);
$connection = $db->getConnection();

$query = file_get_contents("./db_scripts/migrations.sql");

$stmt = $connection->prepare($query);

if ($stmt->execute()){
    echo "Success";
} else { 
    echo "Fail";
}

?>