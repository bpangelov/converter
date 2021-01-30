<?php

abstract class Converter {
    private $config;

    public function __construct($config) {
        $this->config = $config;
    }

    abstract public function convert($input);
}

class JsonConverter extends Converter {
    public function __construct($config) {
        parent::__construct($config);
    }

    public function convert($input) {
        return $input . "| This is JsonConverter"; 
    }
}

class YamlConverter extends Converter {
    public function __construct($config) {
        parent::__construct($config);
    }

    public function convert($input) {
        return $input . "| This is YamlConverter";
    }
}

class ConverterFactory {
    public function __construct() {
    }

    public function createConverter($config) {
        switch ($config->getOutputFormat()) {
            case 'json':
                return new JsonConverter($config);
            case 'yaml':
                return new YamlConverter($config);
            default:
                header("HTTP/1.1 400 Bad Input");
                exit();
        }
    }
}

?>