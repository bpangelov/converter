<?php

$mapping = [
   
    // app classes
    'ConverterController' => './src/ConverterController.php',
 ];

//----------------------------------------------------------------------------------------------------------------------
spl_autoload_register(function ($class) use ($mapping) {
    if (isset($mapping[$class])) {
        require_once $mapping[$class];
    }
}, true);

?>