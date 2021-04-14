<?php

/*require_once('../config.php');
require_once('../database.php');*/

class M_employee{
	public static function get_company_id($USER_ID)
	{
		global $DB;
		 $sql="
			 	SELECT COMPANY_ID
				FROM  `employee` 
				WHERE USER_ID ='".$USER_ID."'
				LIMIT 1
			 ";
		$result=$DB->query($sql);
		$records;
		if($DB->num_rows($result)>0)
		{
			while($fetch=$DB->fetch_assoc($result))
			{
				$records=$fetch['COMPANY_ID'];
			}
		}
		return $records;
	}
	public static function get_company_details($COMPANY_ID)
	{
		global $DB;
		 $sql="
			 	SELECT NAME
				FROM  `company` 
				WHERE ID ='".$COMPANY_ID."'
				LIMIT 1
			 ";
		$result=$DB->query($sql);
		$records;
		if($DB->num_rows($result)>0)
		{
			while($fetch=$DB->fetch_assoc($result))
			{
				$records=$fetch['NAME'];
			}
		}
		return $records;
	}
	public static function get_user_info($USER_ID)
	{
		global $DB;
		 $sql="
			 	SELECT * 
				FROM  `employee` 
				WHERE USER_ID ='".$USER_ID."'
			 ";
		$result=$DB->query($sql);
		if($DB->num_rows($result)==1)
		{
			$record=$DB->fetch_assoc($result);
			return $record;		
		}
		else
		{
			return false;
		}
	}
	
	public static function get_my_info($USER_ID){
		global $DB;
		 $sql="
			 	SELECT * 
				FROM  `employee` AS e 
				JOIN `user` AS u
				ON e.USER_ID=u.ID
				WHERE e.USER_ID ='".$USER_ID."'
			 ";
		$result=$DB->query($sql);
		if($DB->num_rows($result)==1){
			$record=$DB->fetch_assoc($result);
			return $record;		
		}else{
			return false;
		}
	}
	
	
	public static function update_user_info($USER_ID , $PASSWORD , $FIRST_NAME , $LAST_NAME , $EMAIL , $PHONE)
	{
		global $DB;
		$sqlUpdateEmployee="
			 	UPDATE `employee` SET 	
				FIRST_NAME = '".$FIRST_NAME."' , LAST_NAME = '".$LAST_NAME."' , EMAIL = '".$EMAIL."' , PHONE = '".$PHONE."'
				WHERE USER_ID ='".$USER_ID."'
			 ";
		if($DB->query($sqlUpdateEmployee)){
			if($PASSWORD!=''){
				$pwd =	md5($PASSWORD);
				$sqlUpdateUser="
			 	UPDATE `user` SET 	
				PASSWORD  = '".$pwd."' WHERE ID ='".$USER_ID."'
			 ";
				if($DB->query($sqlUpdateUser)){
					return true;
				}	
				else{
					return 10;
				}
			}
			return true;
		}
		else{
			return 11;
		}
		
	}
	public static function is_this_employee_exists($ID)
	{
		global $DB;
		$sql="
			 	SELECT *
				FROM employee
				WHERE ID='".$ID."'
				LIMIT 1
			 ";
		$result=$DB->query($sql);
		if($DB->num_rows($result)==1)
		{
			return true;	
		}
		else
		{
			return false;	
		}
	}
}
?>