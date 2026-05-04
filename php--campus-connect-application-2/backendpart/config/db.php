<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "campus_connect";

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8");

// Uncomment to test connection (remove after testing)
// echo "Connected successfully to database: " . $dbname;
?>