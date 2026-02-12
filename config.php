<?php
// Database Configuration
$host = "localhost";
$user = "root";
$password = "";
$database = "vehicle_records";

// Create connection
$connect = mysqli_connect($host, $user, $password, $database);

if (!$connect) {
    die("❌ Database Connection Failed: " . mysqli_connect_error());
}

// Set character set
mysqli_set_charset($connect, "utf8mb4");

// Define currency only if not already defined
if (!defined('CURRENCY')) {
    define('CURRENCY', 'TZS');
}

if (!defined('CURRENCY_SYMBOL')) {
    define('CURRENCY_SYMBOL', 'Tsh');
}

?>