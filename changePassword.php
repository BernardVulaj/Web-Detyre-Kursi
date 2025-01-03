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
    echo json_encode(['success' => false, 'message' => 'Lidhja me databaze nuk pati sukses: ' . $conn->connect_error]);
    exit;
}

if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'Emaili nuk eshte ruajtur ne session']);
    exit;
}

// Retrieve the email from the session
$email = $_SESSION['email'];

// Get the JSON input from JavaScript
$data = json_decode(file_get_contents('php://input'), true);

// Get the new password from the request body
$newPassword = $data['changedPassword'] ?? '';

$passwordRegex = "/^(?=.*[A-Za-z])(?=.*[-._!#$%&?])[A-Za-z\d!#$%&?]{8,255}$/";

if(!preg_match($passwordRegex, $newPassword)){
    echo json_encode(["success" => false, "message" => "Fjalekalimi duhet te kete se pakti 8 karaktere, nje shkronje dhe nje karakter special."]);
    exit();
}

// Retrieve the current password (hashed) from the database
$stmt = $conn->prepare("SELECT password FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();


// Check if the user exists
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Emaili nuk eshte rregjisturar.']);
    exit;
}

// Fetch user data
$user = $result->fetch_assoc();
$currentPassword = $user['password']; // Current password (hashed)

// Check if the new password is the same as the old password
if (password_verify($newPassword, $currentPassword)) {
    echo json_encode(['success' => false, 'message' => 'Fjalekalimi i ri nuk mund te jete i njejte si fjalekalimi aktual.']);
    exit;
}

// Hash the new password
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// Prepare SQL query to update the user's password
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->bind_param('ss', $hashedPassword, $email);

// Execute the update query
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Fjalekalimi u ndryshua me sukses.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gabim ne ndryshimin e emailit.']);
}

// Close the database connection
$stmt->close();
$conn->close();
?>
