<?php

class Config {
    private $id;
    private $name;
    private $inputFormat;
    private $outputFormat;
    private $tabulation;

    public function __construct($in, $out, $name = "", $tabulation = 3) {
        $this->id = uniqid();
        $this->inputFormat = $in;
        $this->outputFormat = $out;
        $this->tabulation = $tabulation;
        $this->name = $name;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getInputFormat() {
        return $this->inputFormat;
    }
    
    public function getOutputFormat() {
        return $this->outputFormat;
    }

    public function getTabulation() {
        return $this->tabulation;
    }

    public static function fromJson($cnf) {
        if (property_exists($cnf, 'name')) {
            $name = $cnf->name;
        }

        if (property_exists($cnf, 'tabulation')) {
            $tabulation = $cnf->tabulation;
        }

        if (property_exists($cnf, 'inputFormat')) {
            $in = $cnf->inputFormat;
        } else {
            http_response_code(400);
            exit();
        }

        if (property_exists($cnf, 'outputFormat')) {
            $out = $cnf->outputFormat;
        } else {
            http_response_code(400);
            exit();
        }

        return new Config($in, $out, $name, $tabulation);
    }
}

?>