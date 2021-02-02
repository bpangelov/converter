<?php

require_once "Parser.php";
require_once "Converter.php";
require_once "./src/dtos/Config.php";
require_once "./src/db.php";
require_once "./src/FileUtil.php";
require_once "./src/repositories/ConfigRepository.php";
require_once "./src/repositories/TransformationRepository.php";

define("FILE_PATH", "./files/");

class ApiRequest {
    private $config;
    private $fileName;
    private $inputFileContent;
    private $save;

    public function __construct($data) {
        $this->fromJson($data);
    }

    private function fromJson($data) {
        if (property_exists($data, 'inputFileContent')) {
            $this->inputFileContent = $data->inputFileContent;
        } else {
            http_response_code(400);
            exit();
        }

        if (property_exists($data, 'save')) {
            $this->save = $data->save;
        } else {
            echo "hee";
            http_response_code(400);
            exit();
        }

        if (property_exists($data, 'fileName')) {
            $this->fileName = $data->fileName;
        }

        if (property_exists($data, 'config')) {
            $cnf = $data->config;
            $this->config = Config::fromJson($cnf);
        }
    }

    public function getConfig() {
        return $this->config;
    }
    
    public function getFileName() {
        return $this->fileName;
    }

    public function getInputFileContent() {
        return $this->inputFileContent;
    }

    public function saveTransformation() {
        return $this->save;
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

    private function getSingle($transformationId) {
        $db = new DB();
        // Get transformation
        $transformationRepo = new TransformationRepository($db->getConnection());
        $historyEntry = $transformationRepo->getSingle($transformationId);

        // Get config
        $configRepo = new ConfigRepository($db->getConnection());
        $config = $configRepo->getSingle($historyEntry["configId"]);

        // Read original and converted files
        $originalContent = FileUtil::read(FILE_PATH . $historyEntry["inputFileName"]);
        $convertedContent = FileUtil::read(FILE_PATH . $historyEntry["outputFileName"]);

        http_response_code(200);
        header('Content-Type: application/json');
        $response['body'] = json_encode(array("config" => $config, "originalFile" => $originalContent, 
            "convertedFile" => $convertedContent, "fileName" => $historyEntry["fileName"]
        ));
        return $response;
    }

    private function getAll() {
        $db = new DB();
        $transformationRepo = new TransformationRepository($db->getConnection());
        $historyEntries = $transformationRepo->getAll();


        http_response_code(200);
        header('Content-Type: application/json');
        $response['body'] = json_encode(array("historyEntries" => $historyEntries));
        return $response;
    }

    private function convert() {
        $input = json_decode(file_get_contents('php://input'));
        $requestDto = new ApiRequest($input);

        $parserFactory = new ParserFactory();
        $parser = $parserFactory->createParser($requestDto->getConfig());
        $result = $parser->parse($requestDto->getInputFileContent());

        $conveterFactory = new ConverterFactory();
        $converter = $conveterFactory->createConverter($requestDto->getConfig());
        $resultBody = $converter->convert($result);

        if (!is_dir(FILE_PATH)) {
            mkdir(FILE_PATH);
        }

        if ($requestDto->saveTransformation()) {
            // Save to file, keep original file
            $inputFileName = $requestDto->getFileName() . "_" . 
                                $requestDto->getConfig()->getId() . "_original" . "." . 
                                $requestDto->getConfig()->getInputFormat();
            $outputFileName = $requestDto->getFileName() . "_" . 
                        $requestDto->getConfig()->getId() . "." . 
                        $requestDto->getConfig()->getOutputFormat();
            $filePath = FILE_PATH . $outputFileName;
            $filePathOriginal = FILE_PATH . $inputFileName;

            FileUtil::write($filePath, $resultBody);
            FileUtil::write($filePathOriginal, $requestDto->getInputFileContent());

            // Save config in db
            $db = new DB();
            $configRepo = new ConfigRepository($db->getConnection());
            $configRepo->save($requestDto->getConfig());

            $transformationRepo = new TransformationRepository($db->getConnection());
            $transformationRepo->save($requestDto->getConfig(), $requestDto->getFileName(), $outputFileName, $inputFileName);
        }

        http_response_code(201);
        header('Content-Type: application/json');
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