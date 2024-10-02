<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinic_management";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle GET request - Fetch appointment details by username
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['username'])) {
        $username = $_GET['username'];

        // Fetch user login details
        $stmtLogin = $conn->prepare("SELECT username, email FROM patients_login WHERE username = ?");
        $stmtLogin->bind_param("s", $username);
        $stmtLogin->execute();
        $resultLogin = $stmtLogin->get_result();

        if ($resultLogin->num_rows > 0) {
            $userDetails = $resultLogin->fetch_assoc();
            
            // Fetch appointments for the user
            $stmtAppointment = $conn->prepare("SELECT patient_name, contactno, datetime FROM patients_appointment WHERE username = ?");
            $stmtAppointment->bind_param("s", $username);
            $stmtAppointment->execute();
            $resultAppointment = $stmtAppointment->get_result();

            $appointments = [];
            if ($resultAppointment->num_rows > 0) {
                while ($row = $resultAppointment->fetch_assoc()) {
                    $appointments[] = $row;
                }
            }

            echo json_encode([
                "userDetails" => $userDetails,
                "appointments" => $appointments
            ]);
        } else {
            echo json_encode(["message" => "No user found with that username."]);
        }

        // Close statements
        $stmtLogin->close();
        $stmtAppointment->close();
    } else {
        echo json_encode(["message" => "Username not provided."]);
    }
}

// Handle POST request - Sign up new user and Book new appointment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Parse the input
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? null; // Define action type
    $username = $input['username'] ?? null;
    $password = $input['password'] ?? null; // Only needed for signup
    $email = $input['email'] ?? null; // Only needed for signup
    $contactNumber = $input['contactNumber'] ?? null; // Needed for booking
    $patientName = $input['patientName'] ?? null; // Needed for booking
    $datetime = $input['datetime'] ?? null; // Needed for booking

    // Sign up new user
    if ($action === 'signup') {
        // Validate required fields
        if ($username && $password && $email) {
            // Check if the username already exists
            $stmtCheckUser = $conn->prepare("SELECT username FROM patients_login WHERE username = ?");
            $stmtCheckUser->bind_param("s", $username);
            $stmtCheckUser->execute();
            $resultCheckUser = $stmtCheckUser->get_result();

            if ($resultCheckUser->num_rows === 0) {
                // Prepare the SQL statement for user registration
                $stmt = $conn->prepare("INSERT INTO patients_login (username, password, email) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $password, $email);

                if ($stmt->execute()) {
                    echo json_encode(["message" => "Signup successful!"]);
                } else {
                    echo json_encode(["error" => "Failed to sign up: " . $stmt->error]);
                }

                // Close the statement
                $stmt->close();
            } else {
                echo json_encode(["error" => "Username already exists."]);
            }

            // Close the check user statement
            $stmtCheckUser->close();
        } else {
            echo json_encode(["error" => "Missing required fields for signup."]);
        }
    }

    // Book new appointment
    if ($action === 'book-appointment') {
        // Validate required fields
        if ($username && $contactNumber && $patientName && $datetime) {
            // Check if the username exists in patients_login
            $stmtCheckUser = $conn->prepare("SELECT username FROM patients_login WHERE username = ?");
            $stmtCheckUser->bind_param("s", $username);
            $stmtCheckUser->execute();
            $resultCheckUser = $stmtCheckUser->get_result();

            if ($resultCheckUser->num_rows > 0) {
                // Prepare the SQL statement for booking
                $stmt = $conn->prepare("INSERT INTO patients_appointment (username, contactno, datetime, patient_name) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $username, $contactNumber, $datetime, $patientName);

                if ($stmt->execute()) {
                    echo json_encode(["message" => "Appointment booked successfully!"]);
                } else {
                    echo json_encode(["error" => "Failed to book appointment: " . $stmt->error]);
                }

                // Close the statement
                $stmt->close();
            } else {
                echo json_encode(["error" => "Username does not exist in patients_login."]);
            }

            // Close the check user statement
            $stmtCheckUser->close();
        } else {
            echo json_encode(["error" => "Missing required fields for booking."]);
        }
    }
}

// Close the connection
$conn->close();
?>
