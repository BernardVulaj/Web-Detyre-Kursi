<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'car_rental';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        "status" => "error", 
        "message" => "Database connection failed."
    ]);
    exit;
}



// Example query
$sql = "SELECT id, username, email 
        FROM users
        ORDER BY username";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    http_response_code(500);
    echo json_encode([
        "status" => "error", 
        "message" => "Query failed."
    ]);
    $conn->close();
    exit;
}

$clients = [];
while ($row = $result->fetch_assoc()) {
    $clients[] = $row;
}

http_response_code(200);
echo json_encode([
    "status" => "success", 
    "data" => $clients
]);

$conn->close();
?>
