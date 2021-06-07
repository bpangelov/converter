<?php

require_once ('./src/Autoloader.php');

// $uri[0] = www
if (!isset($_REQUEST['target'])) {
    http_response_code(404);
    exit();
}

$target = $_REQUEST['target'];

$requestMethod = $_SERVER["REQUEST_METHOD"];

// pass the request method to Controller

if ($target == 'transformations') {
    $id = null;
    if (isset($_REQUEST['id'])) {
        $id = (int) $_REQUEST['id'];
    }

    $controller = new ConverterController($requestMethod, $id);
    $controller->handleRequest();
    exit();
} else if ($target == 'configs') {
    $configName = null;
    if (isset($_REQUEST['configName'])) {
        $configName = $_REQUEST['configName'];
    }

    $controller = new ConfigController($requestMethod, $configName);
    $controller->handleRequest();
    exit();
} else if ($target == 'share') {
    $controller = new SharesController($requestMethod);
    $controller->handleRequest();
    exit();
} else {
    http_response_code(404);
    exit();
}

http_response_code(404);

?>