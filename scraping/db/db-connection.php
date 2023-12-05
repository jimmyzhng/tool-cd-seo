<?php
require_once "config.php";
require_once 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$host = $_ENV['RDS_DB_HOST'];
$dbname = $_ENV['RDS_DB_NAME'];
$user = $_ENV['RDS_DB_USER'];
$password = $_ENV['RDS_DB_PASS'];

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $password, $options);
} catch (PDOException $e) {

        die("Connection failed: " . $e->getCode() . " - " . $e->getMessage());
    
}