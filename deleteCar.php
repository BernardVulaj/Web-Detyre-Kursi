<?php
// Database connection
$host = 'localhost'; // Change to your database host
$dbname = 'car_rental'; // Change to your database name
$username = 'root'; // Your database username
$password = ''; // Your database password

// Create a new mysqli instance
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the car ID from the request body (assuming it's a POST request)
$data = json_decode(file_get_contents("php://input"));
if (isset($data->car_id)) {
    $carId = $data->car_id;

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Delete images associated with the car
        $deleteImagesQuery = "DELETE FROM car_images WHERE car_id = ?";
        $stmt = $conn->prepare($deleteImagesQuery);
        $stmt->bind_param('i', $carId);
        $stmt->execute();

        // Delete bookings associated with the car
        $deleteBookingsQuery = "DELETE FROM bookings WHERE car_id = ?";
        $stmt = $conn->prepare($deleteBookingsQuery);
        $stmt->bind_param('i', $carId);
        $stmt->execute();

        // Delete the car record
        $deleteCarQuery = "DELETE FROM cars WHERE id = ?";
        $stmt = $conn->prepare($deleteCarQuery);
        $stmt->bind_param('i', $carId);
        $stmt->execute();

        // Commit the transaction
        $conn->commit();

        // Return success message
        echo json_encode(["message" => "Car and associated records deleted successfully."]);

    } catch (Exception $e) {
        // Rollback the transaction if any error occurs
        $conn->rollback();
        echo json_encode(["error" => "Error deleting car: " . $e->getMessage()]);
    }

} else {
    // If car_id is not provided
    echo json_encode(["error" => "No car ID provided."]);
}

// Close the connection
$conn->close();

?>
