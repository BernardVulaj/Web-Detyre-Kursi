<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'car_rental';

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// Get 'id' parameter from request
$idx = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($idx === null) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "Missing or invalid 'id' parameter"]);
    $conn->close();
    exit;
}

// Prepare statement
$sql = "SELECT id, 
               username, 
               email, 
               profile_image, 
               role_id, 
               is_verified, 
               created_at, 
               updated_at 
        FROM users 
        WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["error" => "Failed to prepare SQL statement"]);
    $conn->close();
    exit;
}

$stmt->bind_param("i", $idx);
$stmt->execute();
$result = $stmt->get_result();

// Fetch only the first row
$user = $result->fetch_assoc();

if (!$user) {
    http_response_code(404); // Not Found
    echo json_encode(["error" => "No user found with the given ID"]);
} else {
    http_response_code(200); // OK
    // Return just the single object
    echo json_encode($user);
}

$stmt->close();
$conn->close();
?>