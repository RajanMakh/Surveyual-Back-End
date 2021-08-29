<?php

/**
 * Config allows classes to be auto loaded
 *
 * @author Rajan Makh
 *
 * @param $e
 */

function autoloadClasses($className) {
    $filename = "classes\\" . strtolower($className) . ".class.php";
    $filename = str_replace('\\', DIRECTORY_SEPARATOR, $filename);
    if (is_readable($filename)) {
        include_once $filename;
    } else {
        exit("File not found: " . $className . " (" . $filename . ")");
    }

}
spl_autoload_register("autoloadClasses");

function exceptionHandler($e) {
    $msg = array("status" => "500", "message" => $e->getMessage(), "file" => $e->getFile(), "line" => $e->getLine());
    $usr_msg = array("status" => "500", "message" => "Sorry! Internal server error");
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET, POST");
    echo json_encode($msg);
    logError($msg);
}
set_exception_handler('exceptionHandler');


$ini['main'] = parse_ini_file("config.ini",true);
$ini['routes'] = parse_ini_file("routes.ini",true);

define('BASEPATH', $ini['main']['paths']['basepath']);
define('CSSPATH', $ini['main']['paths']['css']);
define('JWTKEY', $ini['main']['jwtkey']['key']);
?>

<?php
///**
// * Config allows classes to be auto loaded
// *
// * @author Rajan Makh
// *
// * @param $e
// */
//function exceptionHandler($e)
//{
//    $msg = array("status" => "500", "message" => $e->getMessage(), "file" => $e->getFile(), "line" => $e->getLine());
//    $usr_msg = array("status" => "500", "message" => "Sorry! Internal server error");
//    header("Access-Control-Allow-Origin: *");
//    header("Content-Type: application/json; charset=UTF-8");
//    header("Access-Control-Allow-Methods: GET, POST");
//    echo json_encode($usr_msg);
//    //echo json_encode($msg);
//    logError($msg);
//}
//
//set_exception_handler('exceptionHandler');
//
//error_reporting(0);
//
///**
// * Error handling when an error is detected
// *
// * @param $errno error number
// * @param $errstr error string
// * @param $errfile error file
// * @param $errline error line
// */
//function errorHandler($errno, $errstr, $errfile, $errline)
//{
//    $msg = array("status" => "500", "message" => "Error Detected: [$errno] $errstr line: $errline");
//    header("Access-Control-Allow-Origin: *");
//    header("Content-Type: application/json; charset=UTF-8");
//    header("Access-Control-Allow-Methods: GET, POST");
//    echo json_encode($msg);
//}
//
//set_error_handler('errorHandler');
//
///**
// * Auto load classes
// *
// * @param string $className name of the class that is being loaded
// *
// * @throws exception If a class is not found.
// */
//function autoloadClasses($className)
//{
//    $filename = "classes\\" . strtolower($className) . ".class.php";
//    $filename = str_replace('\\', DIRECTORY_SEPARATOR, $filename);
//    if (is_readable($filename)) {
//        include_once $filename;
//    } else {
//        throw new exception("File not found: " . $className . " (" . $filename . ")");
//    }
//
//}
//
//spl_autoload_register("autoloadClasses");
//
//$ini['main'] = parse_ini_file("config.ini", true);
//$ini['routes'] = parse_ini_file("routes.ini", true);
//
//define('BASEPATH', $ini['main']['paths']['basepath']);
//define('CSSPATH', $ini['main']['paths']['css']);
//define('JWTKEY', $ini['main']['jwtkey']['key']);
//?>

