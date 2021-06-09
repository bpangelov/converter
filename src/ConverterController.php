<?php

require_once "./src/Parser.php";
require_once "./src/Converter.php";
require_once "./src/dtos/Config.php";
require_once "./src/db.php";
require_once "./src/FileUtil.php";
require_once "./src/AWSUtil.php";
require_once "./src/repositories/UserRepository.php";
require_once "./src/repositories/ConfigRepository.php";
require_once "./src/repositories/TransformationRepository.php";
require_once "./src/repositories/SharesRepository.php";
require_once "./config.php";

define("FILE_PATH", ServerConfig::$FILE_PATH);

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
            exit("Input file required");
        }

        if (property_exists($data, 'save')) {
            $this->save = $data->save;
        } else {
            $this->save = false;
        }

        if (property_exists($data, 'fileName')) {
            $this->fileName = $data->fileName;
        }

        if (property_exists($data, 'config')) {
            $cnf = $data->config;
            $this->config = Config::fromJson($cnf);
        } else {
            http_response_code(400);
            exit("Config is required");
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
        if (!is_dir(FILE_PATH)) {
            mkdir(FILE_PATH);
        }

        switch ($this->requestMethod) {
            case 'GET':
                if ($this->id) {
                    $response = $this->getSingle($this->id);
                } else {
                    $response = $this->getForUser();
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
        if (array_key_exists('body', $response)) {
            echo $response['body'];
        }
    }

    private function getSingle($transformationId) {
        session_start();
        if (isset($_SESSION["id"])) {
            $userID = $_SESSION["id"];
        } else {
            http_response_code(401);
            exit("user is not logged in");
        }

        $db = new DB();
        // Get transformation
        $transformationRepo = new TransformationRepository($db->getConnection());
        $historyEntry = $transformationRepo->getSingle($transformationId);

        if (empty($historyEntry)) {
            http_response_code(404);
            exit("transformation doesn't exist");
        }

        // Get config
        $configRepo = new ConfigRepository($db->getConnection());
        $config = $configRepo->getSingle($historyEntry["configId"]);

        // Read original and converted files
        $originalContent = $this->readFile($historyEntry["inputFileName"]);
        $convertedContent = $this->readFile($historyEntry["outputFileName"]);

        http_response_code(200);
        header('Content-Type: application/json');
        $response['body'] = json_encode(array("config" => $config->getJson(), "originalFile" => $originalContent, 
            "convertedFile" => $convertedContent, "fileName" => $historyEntry["fileName"]
        ));
        return $response;
    }

    private function getForUser() {
        session_start();
        if (isset($_SESSION["id"])) {
            $userID = $_SESSION["id"];
        } else {
            http_response_code(401);
            exit("user is not logged in");
        }

        $db = new DB();
        $transformationRepo = new TransformationRepository($db->getConnection());
        $historyEntries = $transformationRepo->getForUser($userID);

        $sharesRepo = new SharesRepository($db->getConnection());
        $sharedEntries = $sharesRepo->getSharedTransformations($userID);

        $configRepo = new ConfigRepository($db->getConnection());
        $configs = $configRepo->getAllOwned($userID);
        $sharedConfigs = $configRepo->getAllSharedWithUser($userID);

        http_response_code(200);
        header('Content-Type: application/json');
        $response['body'] = json_encode(array("historyEntries" => $historyEntries, "historyConfigs" => ($configs == null) ? array() : $configs,
            "sharedEntries" => $sharedEntries, "sharedConfigs" => ($sharedConfigs == null) ? array() : $sharedConfigs));
        return $response;
    }

    private function convert() {
        $input = json_decode(file_get_contents('php://input'));
        $requestDto = new ApiRequest($input);

        $resultBody = $this->parseAndConvert($requestDto->getConfig(), $requestDto->getInputFileContent());

        if ($requestDto->saveTransformation()) {
            session_start();
            $userID = "";
            if (isset($_SESSION["id"])) {
                $userID = $_SESSION["id"];
            } else {
                http_response_code(401);
                exit("user is not logged in");
            }

            // Save config in db if it doesn't exist
            $db = new DB();
            $configRepo = new ConfigRepository($db->getConnection());
            $config = $configRepo->getIfOwned($requestDto->getConfig()->getName(), $userID);

            if ($config == null) {
                $config = $configRepo->save($requestDto->getConfig(), $userID);
            } else if (!$config->isSameAs($requestDto->getConfig())) {
                http_response_code(405);
                exit("Modifying config is not allowed with this method");
            }

            $transformationRepo = new TransformationRepository($db->getConnection());
            $transformation = $transformationRepo->getByConfigAndFile($config, $requestDto->getFileName());

            if ($transformation == null) {
                // Save to file, keep original file
                $inputFileName = $requestDto->getFileName() . "_" . 
                                    $config->getId() . "_original" . "." . 
                                    $config->getInputFormat();
                $outputFileName = $requestDto->getFileName() . "_" . 
                                    $config->getId() . "." . 
                                    $config->getOutputFormat();

                $id = $transformationRepo->save($userID, $config, $requestDto->getFileName(), $outputFileName, $inputFileName);

                $this->writeFile($outputFileName, $resultBody);
                $this->writeFile($inputFileName, $requestDto->getInputFileContent());
            } else {
                http_response_code(405);
                exit("Modifying transformation is not allowed with this method");
            }
        }
        
        http_response_code(201);
        header('Content-Type: application/json');
        $result = array("convertedFile" => $resultBody);
        if (isset($id)) {
            $result["id"] = $id;
        }

        $response['body'] = json_encode($result);
        return $response;
    }

    private function edit() {
        $userID = "";
        session_start();
        if (isset($_SESSION["id"])) {
            $userID = $_SESSION["id"];
        } else {
            http_response_code(401);
            exit("user is not logged in");
        }

        $input = json_decode(file_get_contents('php://input'));
        $requestDto = new ApiRequest($input);

        $db = new DB();
        $configRepo = new ConfigRepository($db->getConnection());
        $transformationRepo = new TransformationRepository($db->getConnection());
        $config = $configRepo->getIfOwned($requestDto->getConfig()->getName(), $userID);

        if ($config == null) {
            $config = $configRepo->getSharedWithUser($requestDto->getConfig()->getName(), $userID);
            if ($config == null) {
                http_response_code(404);
                exit("Config doesn't exist for user");
            }
        }

        $transformation = $transformationRepo->getByConfigAndFile($config, $requestDto->getFileName());
        if ($transformation == null) {
            http_response_code(404);
            exit("Transformation with this file doesn't exist");
        }

        // Don't change formats
        if ($config->getInputFormat() !== $requestDto->getConfig()->getInputFormat() ||
            $config->getOutputFormat() !== $requestDto->getConfig()->getOutputFormat()) {

            http_response_code(405);
            exit("Changing formats is not allowed with this method");
        }

        $config = $configRepo->update($config->getId(), $requestDto->getConfig());
        $resultConverted = $this->parseAndConvert($config, $requestDto->getInputFileContent());

        $inputFileName = $requestDto->getFileName() . "_" . 
                            $config->getId() . "_original" . "." . 
                            $config->getInputFormat();
        $outputFileName = $requestDto->getFileName() . "_" . 
                            $config->getId() . "." . 
                            $config->getOutputFormat();

        $this->writeFile($outputFileName, $resultConverted);
        $this->writeFile($inputFileName, $requestDto->getInputFileContent(), true);

        http_response_code(200);
        header('Content-Type: application/json');
        $response['body'] = json_encode(array(
            "convertedFile" => $resultConverted,
            "id" => $transformation["id"]
        ));
        return $response;
    }

    private function delete($id) {
        $userID = "";
        session_start();
        if (isset($_SESSION["id"])) {
            $userID = $_SESSION["id"];
        } else {
            http_response_code(401);
            exit("user is not logged in");
        }

        $db = new DB();
        $transformationRepo = new TransformationRepository($db->getConnection());
        $sharesRepo = new SharesRepository($db->getConnection());

        $transformation = $transformationRepo->getSingle($id);
        if ($transformation == null) {
            http_response_code(204);
            return array();
        }

        if ($transformation["userId"] != $userID) {
            http_response_code(401);
            exit("user doesn't own transformation");
        }

        $fileOriginal = $transformation["inputFileName"];
        $fileConverted = $transformation["outputFileName"];

        $sharesRepo->deleteShares($id);
        $transformationRepo->delete($id);

        // Delete files
        unlink(FILE_PATH . $fileOriginal);
        unlink(FILE_PATH . $fileConverted);

        http_response_code(204);
        return array();
    }

    private function parseAndConvert($config, $fileContent) {
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->createParser($config);
        $result = $parser->parse($fileContent);

        $conveterFactory = new ConverterFactory();
        $converter = $conveterFactory->createConverter($config);
        return $converter->convert($result);
    }

    private function writeFile($name, $content, $overWrite = false) {
        if (ServerConfig::$IS_AWS_DELPOYMENT) {
            $s3Endpoint = new S3Endpoint([
                'region' => ServerConfig::$REGION,
                'version' => 'latest'
            ], ServerConfig::$S3_BUCKET);

            $s3Endpoint->upload($name, $content);
        } else {
            $path = FILE_PATH . $name;
            if ($overWrite) {
                FileUtil::overwrite($path, $content);
            } else {
                FileUtil::write($path, $content);
            }
        }
    }

    private function readFile($name) {
        if (ServerConfig::$IS_AWS_DELPOYMENT) {
            $s3Endpoint = new S3Endpoint([
                'region' => ServerConfig::$REGION,
                'version' => 'latest'
            ], ServerConfig::$S3_BUCKET);

            return $s3Endpoint->download($name);
        } else {
            $path = FILE_PATH . $name;
            return FileUtil::read($path);
        }
    }
}

?>