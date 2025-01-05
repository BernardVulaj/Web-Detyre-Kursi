<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'car_rental';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

$idx = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($idx === null) {
    echo json_encode(["error" => "Missing or invalid 'id' parameter"]);
    $conn->close();
    exit;
}

$sql = "SELECT id, name, email, role_id FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(["error" => "Failed to prepare the SQL statement"]);
    $conn->close();
    exit;
}

$stmt->bind_param("i", $idx);
$stmt->execute();
$result = $stmt->get_result();

$clients = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $clients[] = $row;
    }
}

echo json_encode($clients);

$stmt->close();
$conn->close();
?>


