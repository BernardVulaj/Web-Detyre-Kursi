<?php
$conn = new mysqli('localhost', 'root', '', 'car_rental');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = $_POST['password']; // Will hash later
    $profile_picture = $_FILES['profile_picture']['name'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
        exit;
    }

    // Hash the password
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    } else {
        $hashed_password = null;
    }

    $target_file = null;
    if ($profile_picture) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = mime_content_type($_FILES["profile_picture"]["tmp_name"]);
        $file_size = $_FILES["profile_picture"]["size"];
        $max_file_size = 2 * 1024 * 1024; // 2MB

        if (in_array($file_type, $allowed_types) && $file_size <= $max_file_size) {
            $target_dir = "images/";
            $target_file = $target_dir . basename($profile_picture);
            if (!move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                echo "Error uploading profile picture.";
                exit;
            }
        } else {
            echo "Invalid file type or size. Only JPG, PNG, and GIF files under 2MB are allowed.";
            exit;
        }
    }

    $sql = "UPDATE users SET name = ?, email = ?";
    $params = [$name, $email];
    $types = "ss";

    if ($hashed_password) {
        $sql .= ", password = ?";
        $params[] = $hashed_password;
        $types .= "s";
    }

    if ($target_file) {
        $sql .= ", profile_picture = ?";
        $params[] = $target_file;
        $types .= "s";
    }

    $sql .= " WHERE id = ?";
    $params[] = 1; // Replace with dynamic user ID if needed
    $types .= "i";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        echo "Profile updated successfully.";
        header("Location: index.php");
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
