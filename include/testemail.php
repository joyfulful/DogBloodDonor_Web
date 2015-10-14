<?php

require_once('../vendor/phpmailer/PHPMailerAutoload.php');
$mail = new PHPMailer();
$mail->IsSMTP(); // telling the class to use SMTP
$mail->SMTPDebug = 0;                     // enables SMTP debug information (for testing)
// 1 = errors and messages
// 2 = messages only
$mail->SMTPAuth = true;                  // enable SMTP authentication
$mail->SMTPSecure = "tls";                 // sets the prefix to the servier
$mail->Host = "smtp.gmail.com";      // sets GMAIL as the SMTP server
$mail->Port = 587;                   // set the SMTP port for the GMAIL server
$mail->Username = "system@chakree.me";  // GMAIL username
$mail->Password = "IT5510IT5510";            // GMAIL password

$mail->SetFrom('system@dogblooddonor.in.th', 'DogBloodDonor Admin');

$mail->SetFrom('system@dogblooddonor.in.th', 'DogBloodDonor Admin');

$mail->Subject = "Welcome To Dog Blood Donor Application : Your Activate Code";

//$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test



$mailbody = file_get_contents("../include/email/activatetemplate.html");
$bg = "https://dogblooddonor.in.th/include/email/emailbg.jpg";

$mail->MsgHTML($mailbody);

$address = "chakree30584@gmail.com";
$mail->AddAddress($address);
//$mail->AddEmbeddedImage('../include/email/emailbg.jpg', 'bg');

if (!$mail->Send()) {
    echo 0;
} else {
    echo 1;
}