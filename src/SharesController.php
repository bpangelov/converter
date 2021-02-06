<?php

class ApiRequest {
    private $userID;
    private $transformationID;

    public function __construct($data) {
        $this->fromJson($data);
    }

    private function fromJson($data) {
        if (property_exists($data, 'userID')) {
            $this->userID = $data->userID;
        } else {
            http_response_code(400);
            exit("User ID required");
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
    
    public function getUserID() {
        return $this->userID;
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
            $ownerID = $_SESSION["id"];
        } else {
            http_response_code(401);
            exit();
        }

        $db = new DB();

        // Check if transformation is owned by the current user.
        $transformationRepo = new TransformationRepository($db->getConnection());
        $transformation = $transformationRepo->getSingle($request->getTransformationID());
        if ($transformation["user_id"] != $ownerID) {
            http_response_code(401);
            exit();
        }

        $sharesRepo = new SharesRepository($db->getConnection());
        $sharesRepo->shareTransformation($request->getUserID(), $request->getTransformationID());

        http_response_code(200);
    }
}

?>