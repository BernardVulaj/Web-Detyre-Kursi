<?php
session_start(); // Start session to store email for verification
// header('Content-Type: application/json');

// Database connection
$host = 'localhost'; // Change to your database host
$dbname = 'car_rental'; // Change to your database name
$username = 'root'; // Your database username
$password = ''; // Your database password

// Create a MySQLi instance
$conn = new mysqli($host, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Gabim ne lidhje me databaze: " . $conn->connect_error]));

}

// Get the raw POST data and decode the JSON
$data = json_decode(file_get_contents("php://input"), true); // Decode JSON to an associative array

// Check if the necessary fields are provided
if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
    echo json_encode(["success" => false, "message" => "Nuk keni plotesuar te dhenat e nevojitura."]);
    exit();
    
}
$username = $data['username'];
$email = $data['email'];
$password = $data['password'];

$emailRegex = "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/";
$passwordRegex = "/^(?=.*[A-Za-z])(?=.*[-._!#$%&?])[A-Za-z\d!#$%&?]{8,255}$/";
$usernameRegex = "/^[A-Za-z][A-Za-z0-9._!#$%&?-]{2,20}$/";


if(!preg_match($emailRegex, $email)){
    echo json_encode(["success" => false, "message" => "Ju lutem jepni nje email valid."]);
    exit();
}
if(!preg_match($passwordRegex, $password)){
    echo json_encode(["success" => false, "message" => "Fjalekalimi duhet te kete se pakti 8 karaktere, nje shkronje dhe nje karakter special."]);
    exit();
}
if(!preg_match($usernameRegex, $username)){
    echo json_encode(["success" => false, "message" => "Ju lutem jepni nje username valid."]);
    exit();
}

// Check if the email already exists in the database using MySQLi
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email); // "s" for string
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Emaili eshte i rregjistruar."]);
    exit;
}

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);


// Insert user data into the database using MySQLi
try {
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role_id, is_verified) VALUES (?, ?, ?, 2, 0)");

    $stmt->bind_param("sss", $username, $email, $hashedPassword); // "sss" for three string parameters
    $stmt->execute();

    $_SESSION['email'] = $email;

    echo json_encode([
        'success' => true,
        'message' => 'Perdoruesi u rregjistrua me sukses!'
    ]);
   
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}


// Close the MySQLi connection
$stmt->close();
$conn->close();


?>
