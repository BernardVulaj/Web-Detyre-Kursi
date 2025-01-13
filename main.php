<?php
// Get the data from the POST request
$data = json_decode(file_get_contents("php://input"), true);

// Database connection
$host = 'localhost';
$dbname = 'car_rental';
$username = 'root';
$password = '';
$conn = new mysqli($host, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection error: " . $conn->connect_error]));
}

// Start building the SQL query for fetching the cars
$sql = "SELECT id, name, price_per_day, engine, fuel, transmission, year, type FROM cars WHERE 1=1";

// Apply filters to the SQL query for fetching the cars
if (!empty($data['fuelType'])) {
    $fuelFilter = implode("','", $data['fuelType']);
    $sql .= " AND fuel IN ('$fuelFilter')";
}

if (!empty($data['price'])) {
    $priceFilter = $data['price'];
    $sql .= " AND price_per_day <= $priceFilter";
}

if (!empty($data['transmission'])) {
    $transmissionFilter = $data['transmission'];
    $sql .= " AND transmission = '$transmissionFilter'";
}

// Check if search is provided, and modify the query accordingly
if (!empty($data['search'])) {
    $searchTerm = $data['search'];
    $sql .= " AND name LIKE '%$searchTerm%'";
}

// Get pagination details
$page = isset($data['page']) ? $data['page'] : 1;
$carsPerPage = isset($data['carsPerPage']) ? $data['carsPerPage'] : 3;
$offset = ($page - 1) * $carsPerPage;  // Offset to get the right set of cars

// Apply pagination to the query
$sql .= " LIMIT $offset, $carsPerPage";

// Execute the SQL query to get the cars
$result = $conn->query($sql);

// Check if any cars are found
$cars_data = [];
if ($result->num_rows > 0) {
    while ($car = $result->fetch_assoc()) {
        $carId = $car['id'];
        $image_sql = "SELECT image_path FROM car_images WHERE car_id = ? AND image_order = 1";
        $image_stmt = $conn->prepare($image_sql);
        $image_stmt->bind_param("i", $carId);
        $image_stmt->execute();
        $image_stmt->bind_result($image_path);
        
        $image = null;
        if ($image_stmt->fetch()) {
            $image = $image_path;
        }
        $image_stmt->close();
        $car['image_path'] = $image;
        $cars_data[] = $car;
    }
}

// Start building the SQL query for calculating the total number of cars (with filters and search if needed)
$totalSql = "SELECT COUNT(*) AS total FROM cars WHERE 1=1";

// Apply the same filters to the total count query
if (!empty($data['fuelType'])) {
    $fuelFilter = implode("','", $data['fuelType']);
    $totalSql .= " AND fuel IN ('$fuelFilter')";
}

if (!empty($data['price'])) {
    $priceFilter = $data['price'];
    $totalSql .= " AND price_per_day <= $priceFilter";
}

if (!empty($data['transmission'])) {
    $transmissionFilter = $data['transmission'];
    $totalSql .= " AND transmission = '$transmissionFilter'";
}

// Apply the search filter to the total count query if search is provided
if (!empty($data['search'])) {
    $searchTerm = $data['search'];
    $totalSql .= " AND name LIKE '%$searchTerm%'";
}

// Execute the total count query
$totalCarsResult = $conn->query($totalSql);
$totalCars = $totalCarsResult->fetch_assoc()['total'];

// Calculate the total number of pages
$totalPages = ceil($totalCars / $carsPerPage);

// Return the data as JSON
echo json_encode([
    "success" => true,
    "cars" => $cars_data,
    "totalPages" => $totalPages
]);

// Close the connection
$conn->close();

?>
