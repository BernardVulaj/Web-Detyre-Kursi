<?php
session_start(); // Start session to access stored email and verification code
 header('Content-Type: application/json');

// Get the raw POST data and decode the JSON
$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['verificationCode'])) {
    echo json_encode(["success" => false, "message" => "Kodi i verifikimit eshte i nevojitur."]);
    exit();
}

$enteredCode = $data['verificationCode'];
$verifyInDatabase = $data['verify'];

$codeRegex = "/^\d{6}$/";
if(!preg_match($codeRegex, $enteredCode)){
    echo json_encode(["success" => false, "message" => "Kodi duhet te kete 6 shifra"]);
    exit();
}


// Check if the verification code matches the one stored in the session
if (isset($_SESSION['verification_code']) && $_SESSION['verification_code'] == $enteredCode) {
    // Mark the user as verified in the database
    if($verifyInDatabase){
        $email = $_SESSION['email'];

        // Database connection
        $host = 'localhost';
        $dbname = 'car_rental';
        $username = 'root';
        $password = '';
        
        $conn = new mysqli($host, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            die(json_encode(["success" => false, "message" => "Gabim ne lidhje me databazen: " . $conn->connect_error]));

        }
        
        // Update the user's verification status
        $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $stmt->close();
        $conn->close();
    }
    

    // Clear session data
    // unset($_SESSION['verification_email']);
    // unset($_SESSION['verification_code']);

    echo json_encode(["success" => true, "message" => "Sukses!"]);

    
} else {
    echo json_encode(["success" => false, "message" => "Kodi i verifikimit i pasakte."]);
}










// // Database connection settings
// $host = 'localhost';
// $dbname = 'car_rental';
// $username = 'root';
// $password = '';

// // Create a MySQLi instance
// $conn = new mysqli($host, $username, $password, $dbname);

// // Check the connection
// if ($conn->connect_error) {
//     die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
// }

// // Get the raw POST data and decode the JSON
// $data = json_decode(file_get_contents("php://input"), true);

// // Check if email and verification code are provided
// if (empty($data['email']) || empty($data['verificationCode'])) {
//     echo json_encode(["success" => false, "message" => "Email and verification code are required."]);
//     exit();
// }

// $email = $data['email'];
// $verificationCode = $data['verificationCode'];

// // Fetch the user's stored verification code from the database
// $stmt = $conn->prepare("SELECT verification_code FROM users WHERE email = ? LIMIT 1");
// $stmt->bind_param("s", $email);
// $stmt->execute();
// $result = $stmt->get_result();

// if ($result->num_rows > 0) {
//     $user = $result->fetch_assoc();
//     // Compare the input verification code with the one in the database
//     if ($user['verification_code'] == $verificationCode) {
//         // Update the is_verified field to 1
//         $updateStmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE email = ?");
//         $updateStmt->bind_param("s", $email);
//         $updateStmt->execute();
        
//         echo json_encode(["success" => true, "message" => "Email successfully verified!"]);
//     } else {
//         echo json_encode(["success" => false, "message" => "Incorrect verification code."]);
//     }
// } else {
//     echo json_encode(["success" => false, "message" => "Email not found."]);
// }

// // Close the connection
// $stmt->close();
// $conn->close();
?>
