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
    // echo phpinfo();
    $id = null;
    if (isset($uri[2])) {
        $id = (int) $uri[2];
    }

    $controller = new ConverterController($requestMethod, $id);
    $controller->handleRequest();
    exit();
} else {
    header("Location: register.php");
    exit();
}

http_response_code(404);

?>