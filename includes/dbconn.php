<?php

require __DIR__ . 'vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$dbHost = $_ENV['DB_HOST']; # The hostname of the database server
$dbName = $_ENV['DB_NAME']; # The name of the database being connected to
$dbUser = $_ENV['DB_USERNAME']; # The username used to connect to the database
$dbPassword = $_ENV['DB_PASSWORD']; # The password used to connect to the database
$conn; # The database connection

try {
    $conn = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUsername, $dbPassword);
} catch (PDOException) {
    echo $e->getMessage();
}

?>