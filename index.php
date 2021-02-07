<?php

require_once ('./src/Autoloader.php');

$url = $_SERVER['QUERY_STRING'];
$uri = explode( '/', $url );

if ($uri[0] !== 'converter') {
    http_response_code(404);
    exit();
}

$requestMethod = $_SERVER["REQUEST_METHOD"];

// pass the request method to Controller
if ($uri[1] == 'api') {
    if ($uri[2] == 'transformations') {
        $id = null;
        if (isset($uri[3])) {
            $id = (int) $uri[3];
        }

        $controller = new ConverterController($requestMethod, $id);
        $controller->handleRequest();
        exit();
    } else if ($uri[2] == 'configs') {
        $configName = null;
        if (isset($uri[3])) {
            $configName = $uri[3];
        }

        $controller = new ConfigController($requestMethod, $configName);
        $controller->handleRequest();
        exit();
    } else if ($uri[2] == 'share') {
        $controller = new SharesController($requestMethod);
        $controller->handleRequest();
        exit();
    } else {
        http_response_code(404); 
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}

http_response_code(404);

?>