<?php

function camelToSnake($camel) {
    if (strpos($camel, "_") || strpos($camel, "-")) {
        http_response_code(400);
        exit("Invalid camel case property");
    }

    $snake = preg_replace('/[A-Z]/', '_$0', $camel);
    $snake = strtolower($snake);
    $snake = ltrim($snake, '_');
    return $snake;
}

function snakeToCamel($snake) {
    $snake = preg_replace_callback('/_[a-z0-9]/', function ($match){
        return strtoupper($match[0][1]);
    }, $snake);
    return $snake;
}

abstract class Converter {
    protected $config;

    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * Convert file from input type to target type, applying transformations
     */
    public function convert($input) {
        //var_dump($this->config->getJson());
        $caseConverted = $this->changePropertyCase($input);
        $encoded = $this->encode($caseConverted);
        $tabFormatted = $this->formatString($encoded);
        return $tabFormatted;
    }

    /**
     * Add tabulation and other formats to converted file.
     */
    abstract protected function formatString($str);

    /**
     * Convert from associative array to encoded string.
     */
    abstract protected function encode($map);

    /**
     * Change the case of properties.
     */
    private function changePropertyCase($map) {
        switch ($this->config->getPropertyCase()) {
            case "snake":
                return $this->changeCase($map, 'camelToSnake');
            case "camel":
                return $this->changeCase($map, 'snakeToCamel');
            case "none":
                return $map;
            default:
                http_response_code(400);
                exit("Unknown property case format");
        }
    }

    private function changeCase($map, $mapper) {
        $res = array();
        foreach ($map as $key=>$val) {
            if (is_array($val)) {
                $val = $this->changeCase($val, $mapper);
            }
            $res[$mapper($key)] = $val;
        }
        return $res;
    }
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

    protected function encode($input) {
        return json_encode($input);
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

            // Handle yaml array
            if (preg_match('/- [\w]+/', $trimmed)) {
                $temp = ($tabulationCount + 1) * $this->config->getTabulation() - 2;
                if ($temp > 0) {
                    $result .= str_repeat(" ", $temp) . $trimmed . "\n";
                } else {
                    $result .= $trimmed . "\n";
                }
            } else {
                $result .= str_repeat($tabulation, $tabulationCount) . $trimmed . "\n";
            }
            
            $tabulationPrev = $currentTabs;
        }
        return $result;
    }

    public function encode($input) {
        return yaml_emit($input);
    }
}

class XMLConverter extends Converter {
    public function __construct($config) {
        parent::__construct($config);
    }

    protected function encode($input) {
        $tabulation = str_repeat(" ", $this->config->getTabulation());
        return $this->arrayToXML($input, $tabulation, 0);
    }

    protected function formatString($str) {
        return $str;
    }

    private function arrayToXML($array, $tab, $tabCount, $root = true) {
        $result = "";
        if($root) {
            $result = "<root>\n";
        }
        foreach($array as $key => $value) {
            if(is_array($value)) {
                $result .= str_repeat($tab, $tabCount) . "<$key>\n" . 
                            $this->arrayToXML($value, $tab, $tabCount + 1, false) . 
                            str_repeat($tab, $tabCount) . "</$key>\n";
            } else {
                $result .= str_repeat($tab, $tabCount) . "<$key>$value</$key>\n";
            }
        }
        if($root) {
            $result .= "</root>";
        }
        return $result;
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
            case 'xml':
                return new XMLConverter($config);
            default:
                http_response_code(400);
                exit("Unknown output format");
        }
    }
}

?>