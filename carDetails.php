<?php
$data = json_decode(file_get_contents("php://input"), true);

$actionCalled = $data['action'];
// Database connection
$host = 'localhost'; // Change to your database host
$dbname = 'car_rental'; // Change to your database name
$username = 'root'; // Your database username
$password = ''; // Your database password
$conn = new mysqli($host, $username, $password, $dbname);


if($actionCalled == "getCarDetails"){
    

    // Create a MySQLi instance

    
    if (empty($data['carId'])) {
        echo json_encode(["success" => false, "message" => "Nuk eshte marr carId ne php"]);
        exit();
        
    }
    $carId = $data['carId'];

    $stmt = $conn->prepare("SELECT * FROM cars WHERE id = ?");
    $stmt->bind_param("i", $carId); 
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) {
        $car = $result->fetch_assoc();

        
    }
    else{
        echo json_encode(['success' => false, 'message' => 'Nuk eshte gjetur makina me kete id']);
        exit;
    }
    // $sql = "
    //     SELECT image_path 
    //     FROM car_images
    //     WHERE car_id = ? 
    //     ORDER BY image_order;
    // ";
    $stmt = $conn->prepare("SELECT image_path FROM car_images WHERE car_id = ? ORDER BY image_order");
    $stmt->bind_param("i", $carId);  // "i" means integer
    $stmt->execute();
    // Bind the result to a variable
    $stmt->bind_result($image_path);
    $images = [];
    while ($stmt->fetch()) {
        $images[] = $image_path;  // Store image_path in the images array
    }


    echo json_encode(["success" => true, 
                            "name" => $car['name'], 
                            "pricePerDay" => $car['price_per_day'], 
                            "fuel" => $car['fuel'], 
                            "seatingCapacity" => $car['seating_capacity'], 
                            "engine" => $car['engine'],
                            "transmission" => $car['transmission'], 
                            "year" => $car['year'], 
                            "bluetooth" => $car['bluetooth'], 
                            "gps" => $car['gps'], 
                            "color" => $car['color'], 
                            "type" => $car['type'],
                            'images' => $images]);
    exit;
    
}
else if($actionCalled == "addPendingBook"){
    session_start();


    if (!isset($_SESSION['id'])) {
        echo json_encode(["success" => false, "message" => "Perdoruesi nuk eshte i loguar"]);
        exit();
    }
    $data = json_decode(file_get_contents("php://input"), true);
    if (empty($data['carId']) || empty($data['startDate']) || empty($data['endDate']) || empty($data['totalDays']) || empty($data['pricePerDay'])) {
        echo json_encode(["success" => false, "message" => "Nuk jane marre te gjitha informacionet e nevojshme"]);
        exit();
    }
    $totalPrice = $data['totalDays'] * $data['pricePerDay'];

    $stmt = $conn->prepare("INSERT INTO bookings (car_id, user_id, start_date, end_date, total_price, status) VALUES (?, ?, ?, ?, ?, 'pending')");

    // Bind parameters to the prepared statement
    $stmt->bind_param("iissi", $data['carId'], $_SESSION['id'], $data['startDate'], $data['endDate'], $totalPrice);

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "U shtua booking si pending"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
    }
    
}

$stmt->close();
    $conn->close();

?>
