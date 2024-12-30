<?php

// Database connection settings
$host = 'localhost';  // or your database server
$dbname = 'car_rental';
$username = 'root';
$password = '';  // empty by default, change if you have a password set for MySQL

// Create connection using MySQLi
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}



// Get the raw POST data (JSON)
$data = json_decode(file_get_contents("php://input"), true);

// Check if email and password are provided
if (empty($data['email']) || empty($data['password'])) {
    echo json_encode(["success" => false, "message" => "Both fields are required."]);
    exit();
}

// Extract user input
$email = $data['email'];
$password = $data['password'];
$rememberMe = $data['rememberMe'];

// Prepare SQL to fetch user by email
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Check if the user exists
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Verify if the password matches the hashed password in the database
    if (password_verify($password, $user['password'])) {
        // Password is correct, check if the account is verified
        if ($user['is_verified'] == 1) {
            // Optionally, create a remember me token
            $rememberToken = null;

            if ($rememberMe) {
                $rememberToken = bin2hex(random_bytes(32));
                // Store this token in the database for the user
                $updateStmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE email = ?");
                $updateStmt->bind_param("ss", $rememberToken, $email);
                $updateStmt->execute();

                // Set a cookie to remember the user (30 days)
                setcookie('remember_token', $rememberToken, time() + 3600 * 24 * 30, '/', false, true);
            }

            // Successful login
            echo json_encode(["success" => true, "verified" => true, "message" => "Login successful."]);
        } else {
            // Account is not verified
            echo json_encode(["success" => true, "verified" => false, "message" => "Please verify your email address."]);
        }
    } else {
        // Incorrect password
        echo json_encode(["success" => false, "message" => "Incorrect email or password."]);
    }
} else {
    // No user found with this email
    echo json_encode(["success" => false, "message" => "No user found with this email address."]);
}

// Close connection
$stmt->close();
$conn->close();



?>










