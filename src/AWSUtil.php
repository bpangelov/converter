<?php

require "./vendor/autoload.php";

use Aws\S3\S3Client;

use Aws\Exception\AwsException;

// Class that encapsulates connection with a single bucket
class S3Endpoint {
    private $config;
    private $client;
    private $bucketName;

    public function __construct($config, $bucketName) {
        $this->config = $config;
        $this->bucketName = $bucketName;
    }

    public function initClient() {
        try {
            // Create an SDK class used to share configuration across clients.
            $sdk = new Aws\Sdk($this->config);

            // Create an Amazon S3 client using the shared configuration data.
            $this->client = $sdk->createS3();
        } catch (AwsException $error) {
            http_response_code(500);
            exit($error->getMessage());
        }
    }

    public function upload($fileName, $content) {
        try {
            if (!isset($this->client)) {
                $this->initClient();
            }

            $result = $this->client->putObject([
                'Bucket' => $this->bucketName,
                'Key' => $fileName,
                'Body' => $content
            ]);
        } catch (AwsException $error) {
            http_response_code(500);
            exit($error->getMessage());
        }
    }

    public function downLoadObject($fileName) {
        // Download the contents of the object.
        $result = $this->client->getObject([
            'Bucket' => $this->bucketName,
            'Key' => $fileName
        ]);
        return $result['Body'];
    }
}

?>