<?php
//require the php mailer
require_once('php_mailer/class.phpmailer.php');

$mail             = new PHPMailer();
$body="
asdfasdf
";
$body             = eregi_replace("[\]",'',$body);
$mail->IsSMTP(); // telling the class to use SMTP
$mail->Host       = "localhost"; // SMTP server
$mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
$mail->SMTPAuth   = true;                  // enable SMTP authentication
$mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
$mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
$mail->Port       = 465;                   // set the SMTP port for the GMAIL server
$mail->Username   = "online.door.data@gmail.com";  // GMAIL username
$mail->Password   = "onlinedoordata";            // GMAIL password
$mail->SetFrom("hal.kelton@doordatasolutions.com");
//$mail->AddReplyTo("");
$mail->Subject    = "Your Online DOORDATA account";
$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!";
$mail->MsgHTML($body);
//$mail->AddAddress($fetch['EMAIL']);
$mail->AddAddress("purshottam.sandbox@hotmail.com");
echo $mail->Send();


?>