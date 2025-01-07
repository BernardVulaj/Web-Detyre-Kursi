<?php
require_once "functions.php"; // Make sure this file includes the sendEmail function

$data = json_decode(file_get_contents("php://input"), true);

$actionCalled = $data['action'];
// Database connection
$host = 'localhost'; // Change to your database host
$dbname = 'car_rental'; // Change to your database name
$username = 'root'; // Your database username
$password = ''; // Your database password
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Gabim ne lidhje me databaze: " . $conn->connect_error]));

}

if($actionCalled == "register"){
    session_start(); // Start session to store email for verification
    // header('Content-Type: application/json');
    
    // Get the raw POST data and decode the JSON
    $data = json_decode(file_get_contents("php://input"), true); // Decode JSON to an associative array

    // Check if the necessary fields are provided
    if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
        echo json_encode(["success" => false, "message" => "Nuk keni plotesuar te dhenat e nevojitura."]);
        exit();
        
    }
    $username = $data['username'];
    $email = $data['email'];
    $password = $data['password'];

    $emailRegex = "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/";
    $passwordRegex = "/^(?=.*[A-Za-z])(?=.*[-._!#$%&?])[A-Za-z\d!#$%&?]{8,255}$/";
    $usernameRegex = "/^[A-Za-z][A-Za-z0-9._!#$%&?-]{2,20}$/";


    if(!preg_match($emailRegex, $email)){
        echo json_encode(["success" => false, "message" => "Ju lutem jepni nje email valid."]);
        exit();
    }
    if(!preg_match($passwordRegex, $password)){
        echo json_encode(["success" => false, "message" => "Fjalekalimi duhet te kete se pakti 8 karaktere, nje shkronje dhe nje karakter special."]);
        exit();
    }
    if(!preg_match($usernameRegex, $username)){
        echo json_encode(["success" => false, "message" => "Ju lutem jepni nje username valid."]);
        exit();
    }

    // Check if the email already exists in the database using MySQLi
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email); // "s" for string
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Emaili eshte i rregjistruar."]);
        exit;
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);


    // Insert user data into the database using MySQLi
    try {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role_id, is_verified) VALUES (?, ?, ?, 2, 0)");
        $stmt->bind_param("sss", $username, $email, $hashedPassword); // "sss" for three string parameters
        $stmt->execute();

        $_SESSION['email'] = $email;

        echo json_encode([
            'success' => true,
            'message' => 'Perdoruesi u rregjistrua me sukses!'
        ]);
    
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
}
else if($actionCalled == "login"){
    header('Content-Type: application/json');
    session_start();

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

    $loginAttemptStmt->close();
}
else if($actionCalled == "rememberMe"){

    $data = json_decode(file_get_contents("php://input"), true);

    // Check if rememberToken is provided
    if (empty($data['rememberToken'])) {
        echo json_encode(["success" => false, "message" => "Tokeni per me kujto nuk eshte derguar."]);
        exit();
    }

    $rememberToken = $data['rememberToken'];

    // Prepare SQL to fetch user by remember_token
    $stmt = $conn->prepare("SELECT email, username FROM users WHERE remember_token = ? LIMIT 1");
    $stmt->bind_param("s", $rememberToken);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo json_encode(["success" => true, "email" => $user['email'], "username" => $user['username']]);
    } else {
        echo json_encode(["success" => false, "message" => "Tokeni i pasakte."]);
    }
}
else if($actionCalled == "changePassword"){
    session_start();

    if (!isset($_SESSION['email'])) {
        echo json_encode(['success' => false, 'message' => 'Emaili nuk eshte ruajtur ne session']);
        exit;
    }

    // Retrieve the email from the session
    $email = $_SESSION['email'];

    // Get the JSON input from JavaScript
    $data = json_decode(file_get_contents('php://input'), true);

    // Get the new password from the request body
    $newPassword = $data['changedPassword'] ?? '';

    $passwordRegex = "/^(?=.*[A-Za-z])(?=.*[-._!#$%&?])[A-Za-z\d!#$%&?]{8,255}$/";

    if(!preg_match($passwordRegex, $newPassword)){
        echo json_encode(["success" => false, "message" => "Fjalekalimi duhet te kete se pakti 8 karaktere, nje shkronje dhe nje karakter special."]);
        exit();
    }

    // Retrieve the current password (hashed) from the database
    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();


    // Check if the user exists
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Emaili nuk eshte rregjisturar.']);
        exit;
    }

    // Fetch user data
    $user = $result->fetch_assoc();
    $currentPassword = $user['password']; // Current password (hashed)

    // Check if the new password is the same as the old password
    if (password_verify($newPassword, $currentPassword)) {
        echo json_encode(['success' => false, 'message' => 'Fjalekalimi i ri nuk mund te jete i njejte si fjalekalimi aktual.']);
        exit;
    }

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Prepare SQL query to update the user's password
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param('ss', $hashedPassword, $email);

    // Execute the update query
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Fjalekalimi u ndryshua me sukses.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gabim ne ndryshimin e emailit.']);
    }

}
else if($actionCalled == "saveEmailInSession"){
    session_start();

    // Get the JSON payload from the POST request
    $data = json_decode(file_get_contents("php://input"), true);

    // Check if email is provided in the request
    if (!isset($data['email'])) {
        echo json_encode(["success" => false, "message" => "Nuk keni dhene emailin"]);
        exit();
    }

    $email = $data['email'];

    // Validate the email format
    // if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    //     echo json_encode(["success" => false, "message" => "Invalid email format"]);
    //     exit();
    // }

    // ose

    $emailRegex = "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/";

    if(!preg_match($emailRegex, $email)){
        echo json_encode(["success" => false, "message" => "Ju lutem jepni nje email valid."]);
        exit();
    }


    // Query to check if the email exists in the database and retrieve block status
    $stmt = $conn->prepare("SELECT id, username, email, blocked_until FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the email is found in the database
    if ($result->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Nuk ka perdorues te rregjistruar me kete email."]);
        exit();
    }

    // Fetch user data
    $user = $result->fetch_assoc();

    // Check if the user is blocked
    if ($user['blocked_until'] && new DateTime() < new DateTime($user['blocked_until'])) {
        echo json_encode(["success" => false, "message" => "Accounti juaj eshte i bllokuar perkohesisht. Ju lutem provoni me vone."]);
        exit();
    }

    // Store the email and username in the session
    $_SESSION['email'] = $email;

    // Return success response
    echo json_encode(["success" => true, "message" => "Emaili u verifikua dhe eshte ruajtur ne session"]);
}
else if($actionCalled == "sendVerificationCode"){

    session_start();
    header('Content-Type: application/json'); // Add this line at the top of your PHP file

    // Check if email is stored in the session
    if (!isset($_SESSION['email'])) {
        echo json_encode(["success" => false, "message" => "Nuk eshte gjetur emaili ne session."]);
        exit();
    }

    // Get the email from session
    $email = $_SESSION['email'];

    // Query the database to check if the email exists
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ? ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Nuk ka perdorues te rregjistruar me kete email"]);
        exit();
    }

    // Generate a new verification code
    $newVerificationCode = rand(100000, 999999);

    // Fetch user's name
    $user = $result->fetch_assoc();
    $username = $user['username'];

    // Store the new verification code in the session
    $_SESSION['verification_code'] = $newVerificationCode;

    // Send the new verification code to the user's email
    $message = "Pershendetje $username, kodi juaj i verifikimit eshte: $newVerificationCode. Ju lutem vendoseni kete kod ne faqen e verifikimit qe te verifikoni emailin tuaj";

    $result = sendEmail($message, $email); // Use the existing sendEmail function

    // Return response based on whether email sending was successful
    if ($result === true) {
        echo json_encode([
            'success' => true,
            'message' => 'Nje kod verifikimi eshte derguar ne emailin tuaj.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gabim ne dergimin e kodit ne email'
        ]);
    }
}


// Close the MySQLi connection
$stmt->close();
$conn->close();


?>