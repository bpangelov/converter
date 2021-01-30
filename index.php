<?php

require_once ('./src/Autoloader.php');

$url = $_SERVER['QUERY_STRING'];
$uri = explode( '/', $url );

if ($uri[0] !== 'converter') {
    header("HTTP/1.1 404 Not Found");
    exit();
}

$requestMethod = $_SERVER["REQUEST_METHOD"];

// pass the request method to Controller
if ($uri[1] == 'api') {
    $id = null;
    if (isset($uri[2])) {
        $id = (int) $uri[2];
    }

    $controller = new ConverterController($requestMethod, $id);
    $controller->handleRequest();
    exit();
} else {
    echo "<script>location.href='login.html';</script>";
    exit();
}

header("HTTP/1.1 404 Not Found");

?>