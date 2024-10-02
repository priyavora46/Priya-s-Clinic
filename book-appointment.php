<?php
// appointment.php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'db.php'; // Include your database connection

// Handle the POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $username = $input['username'] ?? null;
    $contactNumber = $input['contactNumber'] ?? null;
    $patientName = $input['patientName'] ?? null;
    $datetime = $input['datetime'] ?? null;

    // Prepare and bind the SQL statement
    $stmt = $conn->prepare("INSERT INTO patients_appointment (username, contactno, datetime, patient_name) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $contactNumber, $datetime, $patientName);

    // Execute the statement and respond
    if ($stmt->execute()) {
        echo json_encode(["message" => "Appointment booked successfully!"]);
    } else {
        echo json_encode(["error" => $stmt->error]);
    }

    // Close the statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>
