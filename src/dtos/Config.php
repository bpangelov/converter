<?php

class Config {
    private $inputFormat;
    private $outputFormat;

    public function __construct($in, $out) {
        $this->inputFormat = $in;
        $this->outputFormat = $out;
    }

    public function getInputFormat() {
        return $this->inputFormat;
    }
    
    public function getOutputFormat() {
        return $this->outputFormat;
    }

    public static function fromJson($cnf) {
        if (property_exists($cnf, 'inputFormat')) {
            $in = $cnf->inputFormat;
        } else {
            header("HTTP/1.1 400 Bad Input");
            exit();
        }

        if (property_exists($cnf, 'outputFormat')) {
            $out = $cnf->outputFormat;
        } else {
            header("HTTP/1.1 400 Bad Input");
            exit();
        }

        return new Config($in, $out);
    }
}

?>