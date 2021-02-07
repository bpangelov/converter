<?php

require_once "./src/dtos/Config.php";
require_once "./src/db.php";
require_once "./src/repositories/ConfigRepository.php";
require_once "./src/repositories/TransformationRepository.php";

class ConfigController {
    private $requestMethod;
    private $name;
    private $configRepo;
    private $transformationRepo;

    public function __construct($requestMethod, $name) {
        $this->requestMethod = $requestMethod;
        $this->name = $name;
        $db = new DB();
        $this->configRepo = new ConfigRepository($db->getConnection());
        $this->transformationRepo = new TransformationRepository($db->getConnection());
    }

    public function handleRequest() {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->name) {
                    $response = $this->getSingle($this->name);
                } else {
                    http_response_code(404);
                    exit();
                };
                break;
            case 'DELETE':
                $response = $this->delete($this->name);
                break;
            default:
                http_response_code(404);
                exit();
        }
        if (array_key_exists('body', $response)) {
            echo $response['body'];
        }
    }

    public function getSingle($name) {
        $userID = "";
        session_start();
        if (isset($_SESSION["id"])) {
            $userID = $_SESSION["id"];
        } else {
            http_response_code(401);
            exit("user is not logged in");
        }
        $config = $this->configRepo->getIfOwned($name, $userID);

        if ($config == null) {
            $config = $this->configRepo->getSharedWithUser($name, $userID);
            if ($config == null) {
                http_response_code(404);
                exit("Config doesn't exist for user");
            }
        }
        $response['body'] = json_encode($config->getJson());
        return $response;
    }

    public function delete($name) {
        $userID = "";
        session_start();
        if (isset($_SESSION["id"])) {
            $userID = $_SESSION["id"];
        } else {
            http_response_code(401);
            exit("user is not logged in");
        }

        $config = $this->configRepo->getIfOwned($name, $userID);
        if ($config == null) {
            http_response_code(204);
            return array();
        }

        $allTr = $this->transformationRepo->getByConfigId($config);
        if ($allTr == null) {
            $this->configRepo->delete($config->getId());
            return array();
        } else {
            http_response_code(409);
            exit("Config sitll in use");
        }
    }
}

?>