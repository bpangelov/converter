<?php

class Config {
    private $json;

    public function __construct($json) {
        $this->json = $json;
        if (!array_key_exists("id", $json)) {
            $this->json["id"] = uniqid();
        }
    }

    public function getJson() {
        return $this->json;
    }

    public function getId() {
        return $this->json["id"];
    }

    public function getName() {
        return $this->json["name"];
    }

    public function getInputFormat() {
        return $this->json["inputFormat"];
    }
    
    public function getOutputFormat() {
        return $this->json["outputFormat"];
    }

    public function getTabulation() {
        return $this->json["tabulation"];
    }

    public function getPropertyCase() {
        return $this->json["propertyCase"];
    }

    public static function fromJson($cnf) {
        $json = array();
        if (property_exists($cnf, 'name')) {
            $json["name"] = $cnf->name;
        } else {
            $json["name"] = "";
        }

        if (property_exists($cnf, 'tabulation')) {
            $json["tabulation"] = $cnf->tabulation;
        } else {
            http_response_code(400);
            exit("Tabulation not set");
        }

        if (property_exists($cnf, 'propertyCase')) {
            $json["propertyCase"] = $cnf->propertyCase;
        } else {
            http_response_code(400);
            exit("Property case is not set");
        }

        if (property_exists($cnf, 'inputFormat')) {
            $json["inputFormat"] = $cnf->inputFormat;
        } else {
            http_response_code(400);
            exit("Input format is not set");
        }

        if (property_exists($cnf, 'outputFormat')) {
            $json["outputFormat"] = $cnf->outputFormat;
        } else {
            http_response_code(400);
            exit("Output format is not sets");
        }

        return new Config($json);
    }

    public function isSameAs($other) {
        return $other->getOutputFormat() == $this->getOutputFormat() && $other->getInputFormat() == $this->getInputFormat()
            &&  $other->getTabulation() == $this->getTabulation() && $other->getName() == $this->getName()
            && $other->getPropertyCase() == $this->getPropertyCase();
    }

    public static function fromDatabaseEntry($map) {
        return new Config(array("id" => $map["id"], "name" => $map["name"], "inputFormat" => $map["input_format"],
            "outputFormat" => $map["output_format"], "tabulation" => $map["tabulation"], "propertyCase" => $map["property_case"]));
    }
}

?>