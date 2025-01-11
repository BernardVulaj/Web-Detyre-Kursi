<?php
$conn = new mysqli('localhost', 'root', '', 'car_rental');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $price = htmlspecialchars($_POST['price_per_day']);
    $fuel = htmlspecialchars($_POST['fuel']);
    $seats = htmlspecialchars($_POST['seating_capacity']);
    $engine = htmlspecialchars($_POST['engine']);
    $transmission = htmlspecialchars($_POST['transmission']);
    $year = htmlspecialchars($_POST['year']);
    $bluetooth = htmlspecialchars($_POST['bluetooth']);
    $gps = htmlspecialchars($_POST['gps']);
    $color = htmlspecialchars($_POST['color']);
    $type = htmlspecialchars($_POST['type']);

    // Insert car into database to get the car ID
    $sql = "INSERT INTO cars (name, price_per_day, fuel, seating_capacity, engine, transmission, year, bluetooth, gps, color, type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisissiiiss", $name, $price, $fuel, $seats, $engine, $transmission, $year, $bluetooth, $gps, $color, $type);

    if ($stmt->execute()) {
        $carId = $stmt->insert_id; // Get the inserted car ID

        // Handle file uploads
        if (isset($_FILES['profile_pictures']) && count($_FILES['profile_pictures']['name']) > 0) {
            $uploadFileDir = 'images/cars/';
            $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');

            for ($i = 0; $i < count($_FILES['profile_pictures']['name']); $i++) {
                $fileTmpPath = $_FILES['profile_pictures']['tmp_name'][$i];
                $fileName = $_FILES['profile_pictures']['name'][$i];
                $fileSize = $_FILES['profile_pictures']['size'][$i];
                $fileType = $_FILES['profile_pictures']['type'][$i];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));

                // Sanitize file name
                $newFileName = $carId . '_' . $i . '.' . $fileExtension; // Use carId and index for the file name

                if (in_array($fileExtension, $allowedfileExtensions)) {
                    $dest_path = $uploadFileDir . $newFileName;

                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        // Insert only the file name into car_images table
                        $sql = "INSERT INTO car_images (car_id, image_path, image_order) VALUES (?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $image_order = $i + 1; // Image order starts from 1
                        $stmt->bind_param("isi", $carId, $newFileName, $image_order);

                        if (!$stmt->execute()) {
                            echo 'Error inserting car image: ' . $stmt->error;
                        }
                    } else {
                        echo 'There was some error moving the file to upload directory. Please make sure the upload directory is writable by web server.';
                    }
                } else {
                    echo 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions);
                }
            }
            echo 'Car added successfully with profile images!';
        } else {
            echo 'Car added successfully!';
        }
    } else {
        echo 'Error: ' . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
