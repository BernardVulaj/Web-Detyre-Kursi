<?php
session_start();

if (!isset($_SESSION['lastActivity'])) {
    $_SESSION['lastActivity'] = time();
}

$data = json_decode(file_get_contents('php://input'), true);
$updateActivity = $data['updateActivity'];


if($updateActivity){
    $_SESSION['lastActivity'] = time();
}

if ((time() - $_SESSION['lastActivity']) > 900) { // 900 sekonda = 15 minuta
    // Nëse ka kaluar më shumë se 15 minuta, ç'aktivizo sesionin dhe ridrejto në faqen e login-it
    session_unset();     // Fshi të gjitha të dhënat e sesionit
    session_destroy();   // Zhbëj sesionin

    echo json_encode(["success" => true]);  // Dërgo një mesazh për ridrejtim
    exit();
}

echo json_encode(["active" => true]); // Përdoruesi është ende aktiv

?>