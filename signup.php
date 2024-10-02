<?php
// signup.php

// Database connection parameters
$servername = "localhost"; // Change if necessary
$username = "root"; // Change if necessary
$password = ""; // Change if necessary
$dbname = "clinic_management"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle the POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the request is JSON
    if (strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
        // Get the raw POST data for JSON requests
        $input = json_decode(file_get_contents('php://input'), true);
        $username = $input['username'] ?? null;
        $password = $input['password'] ?? null;
        $email = $input['email'] ?? null;
    } else {
        // Handle form submission for website requests
        $username = $_POST['username'] ?? null;
        $password = $_POST['password'] ?? null;
        $email = $_POST['email'] ?? null;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO patients_login (username, password, email) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $hashed_password, $email);

    // Execute the statement
    if ($stmt->execute()) {
        // Respond with a success message for API requests
        if (isset($input['source']) && $input['source'] === 'api') {
            echo json_encode(["message" => "Sign up successful! You can now login."]);
        } else {
            // For web form submissions, redirect or show a success message
            echo "Sign up successful! You can now login.";
        }
    } else {
        // Respond with an error for API requests
        if (isset($input['source']) && $input['source'] === 'api') {
            echo json_encode(["error" => $stmt->error]);
        } else {
            echo "Error: " . $stmt->error; // Display error for web form
        }
    }

    // Close the statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>
