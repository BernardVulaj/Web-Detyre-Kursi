<?php
// Get the data from the POST request
$data = json_decode(file_get_contents("php://input"), true);

// Check if the action is defined
// $actionCalled = $data['action'];

// Process the request if the action is to get car details
if (true) {
    // Database connection
    $host = 'localhost';  // Database host
    $dbname = 'car_rental'; // Database name
    $username = 'root'; // Database username
    $password = ''; // Database password

    // Create a MySQLi instance
    $conn = new mysqli($host, $username, $password, $dbname);

    // Check if the connection was successful
    if ($conn->connect_error) {
        die(json_encode(["success" => false, "message" => "Connection error: " . $conn->connect_error]));
    }

    // SQL to get the car details
    $sql = "SELECT id, name, price_per_day, engine,fuel, transmission, year, type FROM cars";
    $result = $conn->query($sql);

    // Check if any cars are found
    if ($result->num_rows > 0) {
        // Prepare the response array
        $cars_data = [];

        // Fetch car details and images
        while ($car = $result->fetch_assoc()) {
            $carId = $car['id'];

            // Get the image path for the car where image_order is 1
            $image_sql = "SELECT image_path FROM car_images WHERE car_id = ? AND image_order = 1";
            $image_stmt = $conn->prepare($image_sql);
            $image_stmt->bind_param("i", $carId); // Bind the carId parameter
            $image_stmt->execute();
            $image_stmt->bind_result($image_path);
            
            $image = null;
            if ($image_stmt->fetch()) {
                $image = $image_path;  // Set the image path
            }
            $image_stmt->close();

            // Add the image to the car data
            $car['image_path'] = $image;

            // Append the car data to the response array
            $cars_data[] = $car;
        }

        // Return the data as JSON
        echo json_encode(["success" => true, "cars" => $cars_data]);
    } else {
        // No cars found in the database
        echo json_encode(['success' => false, 'message' => 'No cars found']);
    }

    // Close the connection
    $conn->close();
}
?>
