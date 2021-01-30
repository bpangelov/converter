<?php

abstract class Parser {
    public function __construct() {
    }

    abstract public function parse($inputFile);
}

class JsonParser extends Parser {

    public function parse($inputFile) {
        $jsonDecoded = json_decode($inputFile);
        if ($jsonDecoded == null) {
            header("HTTP/1.1 400 Bad Input");
            exit();
        }

        return "This is JsonParser |" . $inputFile;
    }
}

class YamlParser extends Parser {

    public function parse($inputFile) {
        return "This is YamlParser |" . $inputFile;
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
                header("HTTP/1.1 400 Bad Input");
                exit();
        }
    }
}

?>