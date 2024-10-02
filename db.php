<?php
// db.php - Central database connection

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinic_management";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
