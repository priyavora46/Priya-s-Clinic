<?php
require 'db.php'; // Include the database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $username = $input['username'] ?? null;
    $password = $input['password'] ?? null;

    if ($username && $password) {
        $stmt = $conn->prepare("SELECT password FROM patients_login WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($hashed_password);
        
        if ($stmt->num_rows > 0) {
            $stmt->fetch();
            if (password_verify($password, $hashed_password)) {
                echo json_encode(["message" => "Login successful!"]);
            } else {
                echo json_encode(["error" => "Invalid password."]);
            }
        } else {
            echo json_encode(["error" => "User not found."]);
        }

        $stmt->close();
    } else {
        echo json_encode(["error" => "Missing username or password."]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}

$conn->close();
?>
