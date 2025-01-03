<?php
session_start();

// Get the JSON payload from the POST request
$data = json_decode(file_get_contents("php://input"), true);

// Check if email is provided in the request
if (!isset($data['email'])) {
    echo json_encode(["success" => false, "message" => "Nuk keni dhene emailin"]);

    exit();
}

$email = $data['email'];


// if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
//     echo json_encode(["success" => false, "message" => "Invalid email format"]);
//     exit();
// }

// ose

$emailRegex = "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/";

if(!preg_match($emailRegex, $email)){
    echo json_encode(["success" => false, "message" => "Ju lutem jepni nje email valid."]);

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
    echo json_encode(["success" => false, "message" => "Gabim ne lidhje me databaze: " . $conn->connect_error]);
    exit();
}

// Query to check if the email exists in the database and retrieve block status
$stmt = $conn->prepare("SELECT id, username, email, blocked_until FROM users WHERE email = ?");

>>>>>>> d2384f6767bd0c3d0a03ae771918d9df94d2255c
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Check if the email is found in the database
if ($result->num_rows === 0) {

    echo json_encode(["success" => false, "message" => "Nuk ka perdorues te rregjistruar me kete email."]);

    exit();
}

// Fetch user data
$user = $result->fetch_assoc();


// Check if the user is blocked
if ($user['blocked_until'] && new DateTime() < new DateTime($user['blocked_until'])) {
    echo json_encode(["success" => false, "message" => "Accounti juaj eshte i bllokuar perkohesisht. Ju lutem provoni me vone."]);
    exit();
}

// Store the email and username in the session
$_SESSION['email'] = $email;

// Return success response
echo json_encode(["success" => true, "message" => "Emaili u verifikua dhe eshte ruajtur ne session"]);


// Close the database connection
$stmt->close();
$conn->close();
?>
