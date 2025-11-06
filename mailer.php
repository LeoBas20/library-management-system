<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


require __DIR__ . "/vendor/autoload.php";

$mail = new PHPMailer(true);

$mail->isSMTP();
$mail->SMTPAuth = true;

//$mail->SMTPDebug = SMTP::DEBUG_SERVER; 

$mail->Host = "smtp.gmail.com";
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
$mail->Username = "your_username";
$mail->Password = "your_password";

$mail->isHTML(true);

return $mail;

?>