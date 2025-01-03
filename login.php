<?php

header('Content-Type: application/json');
session_start();


// Database connection settings
$host = 'localhost';  // or your database server
$dbname = 'car_rental';
$username = 'root';
$password = '';  // empty by default, change if you have a password set for MySQL

// Create connection using MySQLi
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Gabim ne lidhje me databazen: " . $conn->connect_error]));
}



// Get the raw POST data (JSON)
$data = json_decode(file_get_contents("php://input"), true);

// Check if email and password are provided
if (empty($data['email']) || empty($data['password'])) {
    echo json_encode(["success" => false, "message" => "Nuk keni plotesuar te gjitha fushat"]);
    exit();
}

// Extract user input
$email = $data['email'];
$password = $data['password'];
$rememberMe = $data['rememberMe'];

$emailRegex = "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/";
$passwordRegex = "/^(?=.*[A-Za-z])(?=.*[-._!#$%&?])[A-Za-z\d!#$%&?]{8,255}$/";


if(!preg_match($emailRegex, $email)){
    echo json_encode(["success" => false, "message" => "Ju lutem jepni nje email valid."]);
    exit();
}
if(!preg_match($passwordRegex, $password)){
    echo json_encode(["success" => false, "message" => "Fjalekalimi duhet te kete se pakti 8 karaktere, nje shkronje dhe nje karakter special."]);
    exit();
}

// Prepare SQL to fetch user by email
$stmt = $conn->prepare("SELECT id, password, username, is_verified, blocked_until, role_id FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Check if the user exists
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $user_id = $user['id']; // Get the user_id for the login attempt logging


    if($user['blocked_until'] && new DateTime() < new DateTime($user['blocked_until'])) {
        echo json_encode(["success" => false, "message" => "Accounti juaj eshte perkohesisht i bllokuar. Ju lutem provoni me vone."]);
        exit();
    }

    // Fetch the most recent successful login attempt time
    $recentLoginStmt = $conn->prepare("SELECT attempt_time FROM login_attempts WHERE user_id = ? AND successful = 1 ORDER BY attempt_time DESC LIMIT 1");
    $recentLoginStmt->bind_param("i", $user_id);
    $recentLoginStmt->execute();
    $recentLoginResult = $recentLoginStmt->get_result();

    
    if($recentLoginResult->num_rows > 0){
        $recentLoginTime = $recentLoginResult->fetch_assoc();
        $recentLoginTime = $recentLoginTime['attempt_time'];  // Store the time of the most recent successful login
        $recentLoginTime = new DateTime($recentLoginTime);
    }
    else{
        $recentLoginTime = new DateTime('1970-01-01 00:00:00');
        // $recentLoginTime = $recentLoginTime->format('Y-m-d H:i:s');

    }

    // Fetch blocked_until and recent login time
    $blockedUntil = $user['blocked_until'] ? new DateTime($user['blocked_until']) : null;
    // $recentLoginTime = $recentLoginTime ? new DateTime($recentLoginTime) : null;


    if($blockedUntil){
        if ($blockedUntil > $recentLoginTime) {
            $startTimeForFaiiledLoginCheck = $blockedUntil;
        } else {
            $startTimeForFaiiledLoginCheck = $recentLoginTime;
        }
    }
    else{
        $startTimeForFaiiledLoginCheck = $recentLoginTime;
    }

    // Convert $mostRecentLoginTime to the format suitable for the database
    $startTimeForFaiiledLoginCheck = $startTimeForFaiiledLoginCheck->format('Y-m-d H:i:s');

    // If there's a recent successful login, check the number of failed attempts after it else get all the failed attempts
    $failedAttemptsStmt = $conn->prepare("SELECT COUNT(*) AS failed_attempts FROM login_attempts WHERE user_id = ? AND successful = 0 AND attempt_time > ?");
    $failedAttemptsStmt->bind_param("is", $user_id, $startTimeForFaiiledLoginCheck);
    $failedAttemptsStmt->execute();
    $failedAttemptsResult = $failedAttemptsStmt->get_result();
    $failedAttemptsData = $failedAttemptsResult->fetch_assoc();
    $failedAttempts = $failedAttemptsData['failed_attempts'];

                
    // If there are 7 or more failed attempts after the successful login
    if ($failedAttempts >= 7) {
        // Block the user for 30 minutes
        $blockTime = new DateTime();
        $blockTime->add(new DateInterval('PT30M')); // Adds 30 minutes to current time
        $blockedUntil = $blockTime->format('Y-m-d H:i:s');

        // Update the blocked_until field for the user
        $updateBlockStmt = $conn->prepare("UPDATE users SET blocked_until = ? WHERE id = ?");
        $updateBlockStmt->bind_param("si", $blockedUntil, $user_id);
        // $updateBlockStmt->execute();
        if (!$updateBlockStmt->execute()) {
            echo json_encode(["success" => false, "message" => "Nuk ishte e mundur te bllokohet useri."]);
            exit();
        }

        echo json_encode(["success" => false, "message" => "Teper shume tentime te gabuara. Accounti juaj eshte i bllokuar per 30 minuta."]);
        exit();
    }

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
            }
            // Set a cookie to remember the user (30 days)
            setcookie('remember_token', $rememberToken, time() + 3600 * 24 * 30, '/', false, true);


            // Log successful login attempt
            $loginAttemptStmt = $conn->prepare("INSERT INTO login_attempts (user_id, successful) VALUES (?, ?)");
            $successful = true; // Since login is successful
            $loginAttemptStmt->bind_param("ii", $user_id, $successful);
            $loginAttemptStmt->execute();

            $_SESSION['id'] = $user['id'];  //kush te beje kerkesen qe nese perdoruesi nuk eshte i loguar te ridrejtohet 
                                            //te faqja e logimit, kontrollon nese gjendet 'id' ne session. 'email' do te 
                                            //gjendet ne session edhe nese useri nuk eshte i loguam se e kam perdor per 
                                            //verifikim te emailit dhe ndryshim te passwordit
            $_SESSION['email'] = $email;
            $_SESSION['username'] = $user['username'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['is_verified'] = $user['is_verified'];

            // Successful login

            $redirectTo = "userProfile.html";
            if($user['role_id'] == 1){
                $redirectTo = "adminProfile.html";
            }
            echo json_encode(["success" => true, "verified" => true, "message" => "Login me sukses.", "redirectTo" => $redirectTo]);

            $recentLoginStmt->close();
            $failedAttemptsStmt->close();
        } else {
            // Account is not verified
            // Log unsuccessful login attempt
            $loginAttemptStmt = $conn->prepare("INSERT INTO login_attempts (user_id, successful) VALUES (?, ?)");
            $successful = false; // Account not verified is an unsuccessful attempt
            $loginAttemptStmt->bind_param("ii", $user_id, $successful);
            $loginAttemptStmt->execute();

            echo json_encode(["success" => true, "verified" => false, "message" => "Ju lutem verifikoni emailin tuaj."]);
        }
    } else {
        // Incorrect password
        // Log unsuccessful login attempt
        $loginAttemptStmt = $conn->prepare("INSERT INTO login_attempts (user_id, successful) VALUES (?, ?)");
        $successful = false; // Incorrect password is an unsuccessful attempt
        $loginAttemptStmt->bind_param("ii", $user_id, $successful);
        $loginAttemptStmt->execute();

        echo json_encode(["success" => false, "message" => "Fjalekalimi qe dhate nuk perputhet me emailin."]);
    }
} else {
    // No user found with this email
    // Log unsuccessful login attempt
    $loginAttemptStmt = $conn->prepare("INSERT INTO login_attempts (user_id, successful) VALUES (?, ?)");
    $successful = false; // No user found is an unsuccessful attempt
    $user_id = null; // User not found, no valid user_id, but still log the attempt
    $loginAttemptStmt->bind_param("ii", $user_id, $successful);
    $loginAttemptStmt->execute();

    echo json_encode(["success" => false, "message" => "Nuk ka perdorues te rregjistruar me kete email"]);
}

// Close connection
$stmt->close();
$loginAttemptStmt->close();

$conn->close();






?>










