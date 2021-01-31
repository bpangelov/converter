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

    private function formatString($str) {
        $result = "";
        $tabulation = "   ";
        $tabulationCount = 0;
        for ($i = 0; $i < strlen($str); $i++) {
            if ($str[$i] == '}') {
                $tabulationCount--;
                $result .= "\n" . str_repeat($tabulation, $tabulationCount) . '}';
            } else {
                $result .= $str[$i];
                if ($str[$i] == '{') {
                    $tabulationCount++;
                    $result .= "\n" . str_repeat($tabulation, $tabulationCount);
                } else if ($str[$i] == ',' && $str[$i + 1] == '"') {
                    $result .= "\n" . str_repeat($tabulation, $tabulationCount);
                }
            }
        }
        return $result;
    }

    public function convert($input) {
        $jsonString = json_encode($input);
        $formated = $this->formatString($jsonString);
        //echo $formated;
        return  $formated;
    } 
}

class YamlConverter extends Converter {
    public function __construct($config) {
        parent::__construct($config);
    }

    public function convert($input) {
        $yamlString = yaml_emit($input);
        return $yamlString;
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
                http_response_code(400);
                exit();
        }
    }
}

?>