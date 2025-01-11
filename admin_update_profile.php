<?php
$conn = new mysqli('localhost', 'root', '', 'car_rental'); // Kontrollo emrin e bazës së të dhënave

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $emriPlote = htmlspecialchars($_POST['emriplote']);
    $adresa = htmlspecialchars($_POST['adresa']);
    $nrTeli = htmlspecialchars($_POST['nrtelefoni']);
    $role_id = htmlspecialchars($_POST['role_id']);
    $is_verified = htmlspecialchars($_POST['is_verified']);
    $userId =$_POST['id']; // Fetch dynamic user ID

    $password_hashed = null;
    if (!empty($_POST['password'])) {
        $password_hashed = password_hash($_POST['password'], PASSWORD_BCRYPT);
    }

    $profile_image = isset($_FILES['profile_picture']['name']) ? $_FILES['profile_picture']['name'] : null;
    $target_file = null;

    if ($profile_image) {
        if (!isset($_FILES['profile_picture']['tmp_name']) || !is_uploaded_file($_FILES['profile_picture']['tmp_name'])) {
            echo "No valid file uploaded.";
            exit;
        }

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = mime_content_type($_FILES["profile_picture"]["tmp_name"]);
        $file_size = $_FILES["profile_picture"]["size"];

        if (in_array($file_type, $allowed_types) && $file_size <= 2 * 1024 * 1024) { // Max size: 2MB
            $target_dir = "images/";
            $target_file = $target_dir . $userId . '.' . pathinfo($profile_image, PATHINFO_EXTENSION); // Përdor userId për emrin e skedarit
            if (!move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                echo "Error uploading the profile picture.";
                exit;
            }
        } else {
            echo "Invalid file type or size.";
            exit;
        }
    }

    $sql = "UPDATE users SET username = ?, email = ?,full_name = ?,  address = ?, phone_number=?, role_id = ?, is_verified = ?";
    $params = [$username, $email,$emriPlote,$adresa,$nrTeli ,$role_id, $is_verified];

    if ($password_hashed) {
        $sql .= ", password = ?";
        $params[] = $password_hashed;
    }
    if ($target_file) {
        $sql .= ", profile_image = ?"; // Sigurohu që emri i kolonës është i saktë
        $params[] = $target_file;
    }
    $sql .= " WHERE id = ?";
    $params[] = $userId;

    $stmt = $conn->prepare($sql);
    $types = str_repeat('s', count($params) - 1) . 'i'; // 's' for strings, 'i' for integers
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        echo "Profile updated successfully.";
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>