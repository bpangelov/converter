<?php

$mapping = [
    // app classes
    'ConverterController' => './src/ConverterController.php',
    'ConfigController' => './src/ConfigController.php',
    'SharesController' => './src/SharesController.php'
 ];

//----------------------------------------------------------------------------------------------------------------------
spl_autoload_register(function ($class) use ($mapping) {
    if (isset($mapping[$class])) {
        require_once $mapping[$class];
    }
}, true);

?>