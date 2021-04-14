<?php 
class M_user_role
{
	public static function get_role($USER_ID)
	{
		global $DB;
		$sql="
			SELECT ROLE_ID
			FROM  `user_role` 
			WHERE USER_ID ='".$USER_ID."'
			LIMIT 1
			 ";
			 $data = array();
		$result=$DB->query($sql);
		if($result)
		{
			$record=$DB->fetch_assoc($result);
		$sqlRole="
			SELECT NAME
			FROM  `role` 
			WHERE ID ='".$record['ROLE_ID']."'
			LIMIT 1
			 ";
			$resultRole=$DB->query($sqlRole);
			$recordRole=$DB->fetch_assoc($resultRole);
			 $data['ROLE_ID'] = $record['ROLE_ID'];
			 $data['ROLE_NAME'] = $recordRole['NAME'];
			 return $data;
		}
		return false;
	}	
}
?>