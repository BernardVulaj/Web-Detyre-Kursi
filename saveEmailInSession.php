<?php
session_start();

// Get the JSON payload from the POST request
$data = json_decode(file_get_contents("php://input"), true);

// Check if email is provided in the request
if (!isset($data['email'])) {
    echo json_encode(["success" => false, "message" => "Email not provided"]);
    exit();
}

$email = $data['email'];

// Validate the email format
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Invalid email format"]);
    exit();
}

// Database connection details
$host = 'localhost';
$dbname = 'car_rental';
$username = 'root';
$password = '';

// Create a MySQLi connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check if the connection is successful
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// Query to check if the email exists in the database (no need to check is_verified)
$stmt = $conn->prepare("SELECT id, name, email FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Check if the email is found in the database
if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "No user found with this email."]);
    exit();
}

// Fetch user data
$user = $result->fetch_assoc();

// Store the email in the session
$_SESSION['email'] = $email;
$_SESSION['user_name'] = $user['name'];  // Optionally store the name as well

// Return success response
echo json_encode(["success" => true, "message" => "Email verified and saved to session"]);

// Close the database connection
$stmt->close();
$conn->close();
?>
