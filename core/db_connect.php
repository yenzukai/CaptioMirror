<?php

require '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$servername = "localhost";
$username = "root";
$dbPassword = $_ENV['DATABASE_PASSWORD'];
$dbname = "CaptioMirror";

// Create a new MySQLi connection
$conn = new mysqli($servername, $username, $dbPassword, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optionally, set the charset to utf8mb4 (recommended for modern applications)
$conn->set_charset("utf8mb4");
?>
