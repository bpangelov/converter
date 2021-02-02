<?php

abstract class Converter {
    protected $config;

    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * Convert file from input type to target type
     */
    abstract public function convert($input);

    /**
     * Add tabulation and other formats to converted file.
     */
    abstract protected function formatString($str);
}

class JsonConverter extends Converter {
    public function __construct($config) {
        parent::__construct($config);
    }

    protected function formatString($str) {
        $result = "";
        $tabulation = str_repeat(" ", $this->config->getTabulation());
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
        return  $formated;
    } 
}

class YamlConverter extends Converter {
    public function __construct($config) {
        parent::__construct($config);
    }

    protected function formatString($str) {
        $result = "";
        $rows = explode("\n", $str);
        $tabulation = str_repeat(" ", $this->config->getTabulation());
        $tabulationCount = 0;
        $tabulationPrev = 0;
        $rowCount = count($rows);

        for ($i = 0; $i < $rowCount; $i++) {
            $row = $rows[$i];
            $trimmed = ltrim($row);
            $currentTabs = strlen($row) - strlen($trimmed);
            
            if ($row == "...") {
                // End of file
                $result .= $trimmed;
                $tabulationPrev = 0;
                $tabulationCount = 0;
                continue;
            } else if (strpos($row, "---")) {
                // Document separator
                $result .= $trimmed . "\n";
                $tabulationPrev = 0;
                $tabulationCount = 0;
                continue;
            }

            if ($tabulationPrev < $currentTabs) {
                $tabulationCount++;
            } else if ($tabulationPrev > $currentTabs) {
                $tabulationCount--;
            }
            $result .= str_repeat($tabulation, $tabulationCount) . $trimmed . "\n";
            $tabulationPrev = $currentTabs;
        }
        return $result;
    }

    public function convert($input) {
        $yamlString = yaml_emit($input);
        return $this->formatString($yamlString);
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