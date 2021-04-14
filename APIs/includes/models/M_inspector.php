<?php
/*require_once('../config.php');
require_once('../database.php');
*/
class M_inspector
{
	public static function get_inspector_of_a_company($COMPANY_ID)
	{
		global $DB;
		$sql="
			 	SELECT emp.ID ,  emp.FIRST_NAME , emp.LAST_NAME 
				FROM  `employee` as emp JOIN `user_role`
				as ur ON ur.USER_ID=emp.USER_ID	AND ur.ROLE_ID = 3 WHERE emp.COMPANY_ID ='".$COMPANY_ID."'
			 ";
		$result=$DB->query($sql);
		$record=array();
		if($DB->num_rows($result)>0)
		{
			while($fetch=$DB->fetch_assoc($result))
			{
				$record[]=$fetch;
				
			}
			return $record;
		}
		else{
			return false;
		}
	}	
	public static function get_inspector_name($INSPECTOR_ID)
	{
		global $DB;
		$sql="
			 	SELECT    LAST_NAME 
				FROM  `employee`  WHERE ID ='".$INSPECTOR_ID."'
			 ";
		$result=$DB->query($sql);
		///$record=array();
		if($DB->num_rows($result)>0)
		{
			$fetch=$DB->fetch_assoc($result);
			
				$record=$fetch;
				
			
			return $record;
		}
		else{
			return false;
		}
	}	
}
?>