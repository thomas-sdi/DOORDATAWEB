<?php
ini_set("display_errors",1);
include("PHPMailer.php"); 


/*$to = "purshottam.sandbox@hotmail.com";
$subject = "Test mail";
$message = "Hello! This is a simple email message.";
$from = "someonelse@example.com";
$headers = "From:" . $from;
 
*/

$mail = new phpmailer();
$mail->ContentType="text/html";
$mail->FromName ="testing";
$mail->From ="someonelse@example.com";
$mail->Subject = "asdddddddddddd asd";
//$actual_m_body = implode(" ",file("registrationmail.html"));
//die($actual_m_body);
//$actual_m_body = str_replace("##NAME##",ucfirst($strFirstName)." ".ucfirst($strLastName),$actual_m_body);
$mail->Body = "asdasldjkajkd askldasdkjasdka assdjkasdjks";
$mail->AddAddress("purshottam.sandbox@hotmail.com");
if($mail->Send()) 
{
echo "sucess";
// for sending email
}
				
?> 