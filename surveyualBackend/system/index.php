<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * View layer returns data page
 *
 * @author Rajan Makh
 */
include 'config/config.php';

$recordset = new JSONRecordSet($ini['main']['database']['dbname']);

$page = new Router($recordset);
new View($page);
?>


