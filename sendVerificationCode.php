<?php
require_once "functions.php"; // Make sure this file includes the sendEmail function

session_start();
header('Content-Type: application/json'); // Add this line at the top of your PHP file

// Check if email is stored in the session
if (!isset($_SESSION['email'])) {
    echo json_encode(["success" => false, "message" => "No email found in session."]);
    exit();
}

// Get the email from session
$email = $_SESSION['email'];

// Verify if the email exists in the database (to make sure it's a valid user)
$host = 'localhost';
$dbname = 'car_rental';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

// Check if connection is successful
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// Query the database to check if the email exists and is not yet verified
$stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ? ");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "No unverified user found with this email."]);
    exit();
}

// Generate a new verification code
$newVerificationCode = rand(100000, 999999);

// Fetch user's name
$user = $result->fetch_assoc();
$username = $user['name'];

// Store the new verification code in the session
$_SESSION['verification_code'] = $newVerificationCode;

// Send the new verification code to the user's email
$message = "Hello $username, your new verification code is: $newVerificationCode. Please enter this code on the verification page to complete your registration.";

$result = sendEmail($message, $email); // Use the existing sendEmail function

// Return response based on whether email sending was successful
if ($result === true) {
    echo json_encode([
        'success' => true,
        'message' => 'A new verification code has been sent to your email.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error sending verification email.'
    ]);
}

// Close the MySQLi connection
$stmt->close();
$conn->close();
?>
