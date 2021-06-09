<?php

require "./vendor/autoload.php";
require_once "./src/FileUtil.php";
require_once "./src/AWSUtil.php";
require_once "./config.php";

use Aws\S3\S3Client;

use Aws\Exception\AwsException;

// This is a script to test S3 Bucket connectivity. Change key, secret, token with your credentials when testing
$baseConfig = [
    'region' => 'us-east-1',
    'version' => 'latest'
];

$sharedConfig = [
    'region' => 'us-east-1',
    'version' => 'latest',
    'credentials' => array(
        'key' => 'key',
        'secret'  => 'secret',
        'token' => 'token'
      )
];

$bucketName = ServerConfig::$S3_BUCKET;
$filePath = './example_data/example_json.json';
$fileName = 'example_json.json';

// Set up
$s3Endpoint = new S3Endpoint($sharedConfig, $bucketName);
$s3Endpoint->initClient();

// Upload
$content = FileUtil::read($filePath);
$s3Endpoint->upload($fileName, $content);

$result = $s3Endpoint->download($fileName);

// Print the body of the result by indexing into the result object.
echo $result;

?>