<?php

class ServerConfig {
    public static $DB_HOST = "localhost";
    public static $DB_PORT = 3306;
    public static $DB_NAME = "converter";
    public static $DB_USER = "root";
    public static $DB_PASS = "";

    public static $FILE_PATH = "./files/";

    public static $IS_AWS_DELPOYMENT = false;
    public static $S3_BUCKET = "<s3_bucket_name>";
    public static $REGION = "us-east-1";
}

?>