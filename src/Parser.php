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
            exit("Input is not valid json");
        }

        return $jsonDecoded;
    }
}

class YamlParser extends Parser {

    public function parse($inputFile) {
        $yamlDecoded = yaml_parse($inputFile);
        if ($yamlDecoded == null) {
            http_response_code(400);
            exit("Input is not valid yaml");
        }

        return $yamlDecoded;
    }
}

class XMLParser extends Parser {

    public function parse($inputFile) {
        try {
            $xmlDecoded = new SimpleXMLElement($inputFile);
        } catch (Exception $e) {
            http_response_code(400);
            exit("Input is not valid xml");
        }

        return $this->populateArray($xmlDecoded);
    }

    private function populateArray(SimpleXMLElement $xmlDecoded) {
        $array = []; 
        foreach ($xmlDecoded->children() as $node) {
            $attributes = [];

            if ($node->attributes()) {
                foreach ($node->attributes() as $name => $value) {
                    $attributes[$name] = (string)$value;
                }
            }

            if ($node->children()->count() > 0) {
                $data = array_merge($attributes, $this->populateArray($node));

                if (isset($array[$node->getName()])) {
                    if (!isset($array[$node->getName()][0])) {
                        $entry = $array[$node->getName()];
                        $array[$node->getName()] = [];
                        $array[$node->getName()][] = $entry;
                    }

                    $array[$node->getName()][] = $data;
                } else {
                    $array[$node->getName()] = $data;
                }
            } else {
                $array[$node->getName()] = (string)$node;
            }
        }
        return $array;
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
            case 'xml':
                return new XMLParser();
            default:
                http_response_code(400);
                exit("Unknown format");
        }
    }
}

?>