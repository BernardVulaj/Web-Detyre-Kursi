<?php
// Database connection settings
$host = 'localhost';
$dbname = 'car_rental';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}

$data = json_decode(file_get_contents("php://input"), true);

// Check if rememberToken is provided
if (empty($data['rememberToken'])) {
    echo json_encode(["success" => false, "message" => "Token is required."]);
    exit();
}

$rememberToken = $data['rememberToken'];

// Prepare SQL to fetch user by remember_token
$stmt = $conn->prepare("SELECT email, name FROM users WHERE remember_token = ? LIMIT 1");
$stmt->bind_param("s", $rememberToken);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo json_encode(["success" => true, "email" => $user['email'], "username" => $user['name']]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid token."]);
}

$stmt->close();
$conn->close();
?>
