<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';


function sendEmail($text, $emailDestination){
//Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->SMTPDebug = false;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host = 'smtp.gmail.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth = true;                                   //Enable SMTP authentication
        $mail->Username = 'rentmakina@gmail.com';                     //SMTP username
        $mail->Password = 'zahn jkma ryxn eknv';                               //SMTP password
        $mail->SMTPSecure = 'tsl';            //Enable implicit TLS encryption
        $mail->Port = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        //Recipients
        $mail->setFrom('rentmakina@gmail.com', 'Rent Makina');
        // $mail->addAddress('benivulaj@gmail.com');     //Add a recipient
        $mail->addAddress($emailDestination);     //Add a recipient

        // $mail->addBCC('benivulaj@gmail.com');
        //Attachments
//    $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
//    $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name
        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'Verification Code';
        $mail->Body = $text;

        if ($mail->send()) {
            return true; // Email sent successfully
        } else {
            return false; // Email sending failed
        }
    } catch (Exception $e) {
        // Catch PHPMailer's exception and return the error message
        error_log("Mailer Error: {$mail->ErrorInfo}"); // Log the error
        return false;
    }
}