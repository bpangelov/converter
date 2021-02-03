<?php

class FileUtil {
    public static function read($filePath) {
        $myfile = fopen($filePath, "r") or die("Unable to open file!");
        $content = fread($myfile, filesize($filePath));
        fclose($myfile);
        return $content;
    }

    public static function write($filePath, $content) {
        $file = fopen($filePath , "w") or die("Unable to open file!");
        fwrite($file, $content);
        fclose($file);
    }

    public static function overwrite($filePath, $content) {
        $file = fopen($filePath , "w+") or die("Unable to open file!");
        fwrite($file, $content);
        fclose($file);
    }
}

?>