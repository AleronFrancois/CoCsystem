<?php
//phpinfo();
require dirname(__DIR__) . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$dbHost = $_ENV['DB_HOST']; # The hostname of the database server
$dbName = $_ENV['DB_NAME']; # The name of the database being connected to
$dbUser = $_ENV['DB_USERNAME']; # The username used to connect to the database
$dbPassword = $_ENV['DB_PASSWORD']; # The password used to connect to the database
$conn; # The database connection

try {
    $conn = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword, [
        PDO::MYSQL_ATTR_SSL_CA => __DIR__ . "/DigiCertGlobalRootG2.crt.pem"
    ]);
    //    $conn = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);
    echo "<script>console.log('Success');</script>";
} catch (PDOException $e) {
    echo $e->getMessage();
    echo "<script>console.log('Error');</script>";
}

?>