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
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));

}

// Get the raw POST data and decode the JSON
$data = json_decode(file_get_contents("php://input"), true); // Decode JSON to an associative array

// Check if the necessary fields are provided
if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
    echo json_encode(["success" => false, "message" => "Missing required fields."]);
    exit();
    
}
$username = $data['username'];
$email = $data['email'];
$password = $data['password'];

// Check if the email already exists in the database using MySQLi
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email); // "s" for string
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Email already exists."]);
    exit;
}

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);


// Insert user data into the database using MySQLi
try {
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role_id, is_verified) VALUES (?, ?, ?, 2, 0)");
    $stmt->bind_param("sss", $username, $email, $hashedPassword); // "sss" for four string parameters
    $stmt->execute();

    $_SESSION['email'] = $email;

    echo json_encode([
        'success' => true,
        'message' => 'User registered successfully!'
    ]);
   
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}


// Close the MySQLi connection
$stmt->close();
$conn->close();


?>
