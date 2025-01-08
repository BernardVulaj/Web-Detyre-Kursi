<?php
// Start the session to access session variables
session_start();

// Set headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
// header("Access-Control-Allow-Headers: Content-Type");

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'car_rental';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Retrieve the user id from the session
$idx = isset($_SESSION['id']) ? intval($_SESSION['id']) : null;

if ($idx === null) {
    echo json_encode(["error" => "User not logged in or session expired"]);
    $conn->close();
    exit;
}

$sql = "SELECT id, username, email, role_id FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(["error" => "Failed to prepare the SQL statement"]);
    $conn->close();
    exit;
}

$stmt->bind_param("i", $idx);
$stmt->execute();
$result = $stmt->get_result();


if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo json_encode(["success" => true, "id" => $user['id'], "role_id" => $user['role_id']]);
} else {
    echo json_encode(["success" => false, "message" => "No user found with the given ID"]);
}



// $clients = [];
// if ($result->num_rows > 0) {
//     while ($row = $result->fetch_assoc()) {
//         $clients[] = $row;
//     }
// }

// echo json_encode($clients);

$stmt->close();
$conn->close();
?>

