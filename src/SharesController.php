<?php

require_once "./src/db.php";
require_once "./src/repositories/TransformationRepository.php";
require_once "./src/repositories/SharesRepository.php";
require_once "./src/repositories/UserRepository.php";

class ApiRequest {
    private $username;
    private $transformationID;

    public function __construct($data) {
        $this->fromJson($data);
    }

    private function fromJson($data) {
        if (property_exists($data, 'username')) {
            $this->username = $data->username;
        } else {
            http_response_code(400);
            exit("Username required");
        }

        if (property_exists($data, 'transformationID')) {
            $this->transformationID = $data->transformationID;
        } else {
            http_response_code(400);
            exit("Tranformation ID required");
        }

    }

    public function getTransformationID() {
        return $this->transformationID;
    }
    
    public function getUsername() {
        return $this->username;
    }
}

class SharesController {
    private $requestMethod;

    public function __construct($requestMethod) {
        $this->requestMethod = $requestMethod;
    }

    public function handleRequest() {
        switch ($this->requestMethod) {
            case 'POST':
                $this->shareTransformation();
                break;
            default:
                http_response_code(404);
                exit();
        }
    }

    private function shareTransformation() {
        $input = json_decode(file_get_contents('php://input'));
        $request = new ApiRequest($input);

        session_start();
        if (isset($_SESSION["id"])) {
            $userID = $_SESSION["id"];
        } else {
            http_response_code(401);
            exit("user must be logged in");
        }
        $db = new DB();
        // Check if transformation is owned by the current user.
        $transformationRepo = new TransformationRepository($db->getConnection());
        $transformation = $transformationRepo->getSingle($request->getTransformationID());
        if ($transformation["userId"] != $userID) {
            http_response_code(401);
            exit("the current user doesn't own this transformation");
        }

        $userRepo = new UserRepository($db->getConnection());
        $user = $userRepo->getUser($request->getUsername());
        if (empty($user)) {
            http_response_code(404);
            exit("user doesn't exist");
        }

        $sharesRepo = new SharesRepository($db->getConnection());
        if ($transformation["userId"] !== $user["id"]) {
            $sharesRepo->shareTransformation($user["id"], $request->getTransformationID());
        } else {
            http_response_code(400);
            exit("invalid user");
        }

        http_response_code(200);
    }
}

?>