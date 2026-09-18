<?php
require_once __DIR__ . '/../config/env.php';

$host = $_ENV['DB_HOST'];
$user = $_ENV['DB_USER'];
$pw = $_ENV['DB_PASS'];
$database = $_ENV['DB_NAME'];

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$connection = new mysqli($host, $user, $pw, $database);
$connection->set_charset("utf8mb4"); 

if($connection ->connect_error){
    die("Connection error: " . $connection->connect_error);
}



?>