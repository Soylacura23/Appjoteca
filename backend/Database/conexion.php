<?php
require_once __DIR__ . '/../config/env.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


function db_connect(bool $withdb = true): mysqli
{
    if ($withdb) {
        $connection = new mysqli(
            $_ENV['DB_HOST'],
            $_ENV['DB_USER'],
            $_ENV['DB_PASS'],
            $_ENV['DB_NAME']
        );
    } else {
        $connection = new mysqli(
            $_ENV['DB_HOST'],
            $_ENV['DB_USER'],
            $_ENV['DB_PASS']
        );
    }

    $connection->set_charset('utf8mb4');

    return $connection;
}

/** @var mysqli|null $connection */
$connection = null;

try {
    $connection = db_connect(true);
} catch (Throwable $e) {
    $connection = null;
}
?>