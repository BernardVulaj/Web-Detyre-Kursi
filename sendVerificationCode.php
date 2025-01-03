<?php
require_once "functions.php"; // Make sure this file includes the sendEmail function

session_start();
header('Content-Type: application/json'); // Add this line at the top of your PHP file

// Check if email is stored in the session
if (!isset($_SESSION['email'])) {
    echo json_encode(["success" => false, "message" => "Nuk eshte gjetur emaili ne session."]);
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
    echo json_encode(["success" => false, "message" => "Gabim ne lidhje me databazen: " . $conn->connect_error]);
    exit();
}


// Query the database to check if the email exists
$stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ? ");

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Nuk ka perdorues te rregjistruar me kete email"]);
    exit();
}

// Generate a new verification code
$newVerificationCode = rand(100000, 999999);

// Fetch user's name
$user = $result->fetch_assoc();
$username = $user['username'];

// Store the new verification code in the session
$_SESSION['verification_code'] = $newVerificationCode;

// Send the new verification code to the user's email
$message = "Pershendetje $username, kodi juaj i verifikimit eshte: $newVerificationCode. Ju lutem vendoseni kete kod ne faqen e verifikimit qe te verifikoni emailin tuaj";

$result = sendEmail($message, $email); // Use the existing sendEmail function

// Return response based on whether email sending was successful
if ($result === true) {
    echo json_encode([
        'success' => true,
        'message' => 'Nje kod verifikimi eshte derguar ne emailin tuaj.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gabim ne dergimin e kodit ne email'
    ]);
}

// Close the MySQLi connection
$stmt->close();
$conn->close();
?>
