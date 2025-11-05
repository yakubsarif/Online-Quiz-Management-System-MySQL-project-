<?php
// Set timezone globally for the project
if (!ini_get('date.timezone')) {
    date_default_timezone_set('Asia/Dhaka');
}
// Database connection configuration
$host = 'localhost';
$dbname = 'quiz_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // Set MySQL session timezone to +06:00 (Bangladesh)
    $pdo->exec("SET time_zone = '+06:00'");
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}
?> 