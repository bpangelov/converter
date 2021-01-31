<?php

abstract class Parser {
    public function __construct() {
    }

    abstract public function parse($inputFile);
}

class JsonParser extends Parser {

    public function parse($inputFile) {
        $jsonDecoded = json_decode($inputFile, true);
        if ($jsonDecoded == null) {
            http_response_code(400);
            exit();
        }

        return $jsonDecoded;
    }
}

class YamlParser extends Parser {

    public function parse($inputFile) {
        $yamlDecoded = yaml_parse($inputFile);
        if ($yamlDecoded == null) {
            http_response_code(400);
            exit();
        }

        return $yamlDecoded;
    }
}

class ParserFactory {
    public function __construct() {
    }

    public function createParser($config) {
        switch ($config->getInputFormat()) {
            case 'json':
                return new JsonParser();
            case 'yaml':
                return new YamlParser();
            default:
                http_response_code(400);
                exit();
        }
    }
}

?>