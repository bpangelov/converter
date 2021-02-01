<?php

require_once "Parser.php";
require_once "Converter.php";
require_once "./src/dtos/Config.php";
require_once "./src/db.php";
require_once "./src/repositories/ConfigRepository.php";

define("FILE_PATH", "./files/");

class ApiRequest {
    private $config;
    private $inputFile;

    public function __construct($data) {
        $this->fromJson($data);
    }

    private function fromJson($data) {
        if (property_exists($data, 'inputFile')) {
            $this->inputFile = $data->inputFile;
        }

        if (property_exists($data, 'config')) {
            $cnf = $data->config;
            $this->config = Config::fromJson($cnf);
        }
    }

    public function getConfig() {
        return $this->config;
    }
    
    public function getInputFile() {
        return $this->inputFile;
    }
}

class ConverterController {
    private $requestMethod;
    private $id;

    public function __construct($requestMethod, $id) {
        $this->requestMethod = $requestMethod;
        $this->id = $id;
    }

    public function handleRequest() {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->id) {
                    $response = $this->getSingle($this->id);
                } else {
                    $response = $this->getAll();
                };
                break;
            case 'POST':
                $response = $this->convert();
                break;
            case 'PUT':
                $response = $this->edit();
                break;
            case 'DELETE':
                $response = $this->delete($this->id);
                break;
            default:
                http_response_code(404);
                exit();
        }
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getSingle($id) {
        http_response_code(200);
        $response['body'] = json_encode(array("GET id" => $id));
        return $response;
    }

    private function getAll() {
        http_response_code(200);
        $response['body'] = json_encode(array("GET All" => "get all called"));
        return $response;
    }

    private function convert() {
        $input = json_decode(file_get_contents('php://input'));
        $requestDto = new ApiRequest($input);

        $parserFactory = new ParserFactory();
        $parser = $parserFactory->createParser($requestDto->getConfig());
        $result = $parser->parse($requestDto->getInputFile());

        $conveterFactory = new ConverterFactory();
        $converter = $conveterFactory->createConverter($requestDto->getConfig());
        $resultBody = $converter->convert($result);

        if (!is_dir(FILE_PATH)) {
            mkdir(FILE_PATH);
        }

        // Save to file
        $file = fopen(FILE_PATH . $requestDto->getConfig()->getName() . "_" . 
            $requestDto->getConfig()->getId() . "." . 
            $requestDto->getConfig()->getOutputFormat(), "w") or die("Unable to open file!");
        fwrite($file, $resultBody);
        fclose($file);

        // Save config in db
        $db = new DB();
        $configRepo = new ConfigRepository($db->getConnection());
        $configRepo->save($requestDto->getConfig());

        http_response_code(201);
        $response['body'] = json_encode(array("convertedFile" => $resultBody));
        return $response;
    }

    private function edit() {
        http_response_code(200);
        $response['body'] = json_encode(array("PUT" => "put called"));
        return $response;
    }

    private function delete() {
        http_response_code(200);
        $response['body'] = json_encode(array("DELETE" => "delete called"));
        return $response;
    }
}

?>