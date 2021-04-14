<?php
class M_forgetPassword
{
	public static function is_login_name_exist($LOGIN_NAME)
	{
		global $DB;
		$sql="
			 	SELECT * 
				FROM  `user` 
				WHERE  `LOGIN` =  '".$LOGIN_NAME."'
			 ";	
		$result=$DB->query($sql);
		if($DB->num_rows($result)==1)
		{
			
			$fetch=$DB->fetch_assoc($result);
			
			$records=$fetch;			
			
			return $records;

		}
		else
		{
			return false;	
		}
	}
	
	public static function setPassword($EMPLOYEE_ID)
	{
		global $DB;
		
		$sql="
			 	SELECT * 
				FROM `employee`
				WHERE 	USER_ID='".$EMPLOYEE_ID."'
				LIMIT 1
			 ";
		$result=$DB->query($sql);
		if($DB->num_rows($result)==1)
		{
			$fetch=$DB->fetch_assoc($result);

			if(empty($fetch['EMAIL'])){
				return false;	
			}
			$newPassword = $EMPLOYEE_ID.$fetch['FIRST_NAME'];
			$md5NewPassword = md5($newPassword);
			$updatePass = "UPDATE user SET PASSWORD= '".$md5NewPassword."' WHERE ID ='".$EMPLOYEE_ID."'";
			if($DB->query($updatePass)){

				$mail             = new PHPMailer();
				$body="
					Welcome '".$fetch['FIRST_NAME']."'.<br><br />
					To log in and get started with Online DOORDATA, click on the following link. You will need your user name 	
					and password, which was assigned by your system administrator. All of this information is below for your 
					reference.<br />
					\r\n<br />
					Online DOORDATA: http://www.onlinedoordata.com
					User Name: '".$fetch['FIRST_NAME']."'
					Password: '".$newPassword."'
					\r\n<br />
					If you have any trouble signing in, please contact your system administrator.
					\r\n<br />
					Thank you for your business!
					\r\n<br /><br />
					With best regards,
					\r\n<br />
					Hal Kelton, AHC/CDC, CDT, Certified Fire Door Inspector <br />
					President<br />
					DOORDATA Solutions, Inc.<br />
					877-521-DATA<br />
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
				$mail->AddAddress($fetch['EMAIL']);
//				$mail->AddAddress("purshottam.sandbox@hotmail.com");
				$mail->Send();





				
				/*$to = $fetch['EMAIL'];
				$subject = "Your Online DOORDATA account";
				$body = "Welcome '".$fetch['FIRST_NAME']."' '".$fetch['FIRST_NAME']."'.<br>
						To log in and get started with Online DOORDATA, click on the following link. You will need your user name 	
						and password, which was assigned by your system administrator. All of this information is below for your 
						reference.
						\r\n
						Online DOORDATA: http://www.onlinedoordata.com
						User Name: '".$fetch['FIRST_NAME']."'
						Password: '".$newPassword."'
						\r\n
						If you have any trouble signing in, please contact your system administrator.
						\r\n
						Thank you for your business!
						\r\n
						With best regards,
						\r\n
						Hal Kelton, AHC/CDC, CDT, Certified Fire Door Inspector 
						President
						DOORDATA Solutions, Inc.
						877-521-DATA
						";
				$header = "From: 	Online DOORDATA <hal.kelton@doordatasolutions.com>";*/

				//echo $body ;
				///////////mail($to,$subject,$body,$header);
				return true;	
			}
			else{
				return false;
			}
		}
		else
		{
			return false;	
		}
		
	}
	
	
}
?>