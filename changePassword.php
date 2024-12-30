<?php
session_start();

// Database connection details
$host = 'localhost';
$dbname = 'car_rental';
$username = 'root';
$password = '';

// Create a MySQLi connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Check if the user is logged in and email is in session
if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

// Retrieve the email from the session
$email = $_SESSION['email'];

// Get the JSON input from JavaScript
$data = json_decode(file_get_contents('php://input'), true);

// Get the new password from the request body
$newPassword = $data['changedPassword'] ?? '';

// Validate the new password (basic validation)
if (empty($newPassword) || strlen($newPassword) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long.']);
    exit;
}

// Hash the new password
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// Prepare SQL query to update the user's password
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->bind_param('ss', $hashedPassword, $email);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Password updated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating password.']);
}

// Close the database connection
$stmt->close();
$conn->close();
?>
