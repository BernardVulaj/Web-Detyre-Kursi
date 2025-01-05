<?php
$conn = new mysqli('localhost', 'root', '', 'car_rental');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $role_id = htmlspecialchars($_POST['role_id']);
    $is_verified = htmlspecialchars($_POST['is_verified']);
    $profile_image = '';

    // Insert user into database to get the user ID
    $sql = "INSERT INTO users (name, email, password, role_id, is_verified) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $name, $email, $password, $role_id, $is_verified);

    if ($stmt->execute()) {
        $userId = $stmt->insert_id; // Get the inserted user ID

        // Handle file upload
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
            $fileName = $_FILES['profile_picture']['name'];
            $fileSize = $_FILES['profile_picture']['size'];
            $fileType = $_FILES['profile_picture']['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            // Sanitize file name
            $newFileName = $userId . '.' . $fileExtension; // Use userId for the file name

            // Check if file type is allowed
            $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');
            if (in_array($fileExtension, $allowedfileExtensions)) {
                // Directory in which the uploaded file will be moved
                $uploadFileDir = 'images/';
                $dest_path = $uploadFileDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $profile_image = $dest_path;

                    // Update user with profile image path
                    $sql = "UPDATE users SET profile_image = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $profile_image, $userId);

                    if ($stmt->execute()) {
                        echo 'User added successfully with profile image!';
                    } else {
                        echo 'Error updating profile image: ' . $stmt->error;
                    }
                } else {
                    echo 'There was some error moving the file to upload directory. Please make sure the upload directory is writable by web server.';
                }
            } else {
                echo 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions);
            }
        } else {
            echo 'User added successfully!';
        }
    } else {
        echo 'Error: ' . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
