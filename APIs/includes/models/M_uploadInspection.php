<?php

/*require_once('../config.php');
require_once('../database.php');
*/
class M_company
{
	public static function uploadInspection($INSPECTION)
	{
		global $DB;
		
		 $sql="
				SELECT *
				FROM  `company` 
				WHERE INSPECTION_COMPANY ='".$INSPECTION_COMPANY."'
				AND TYPE='1001' 

		     ";
		$result=$DB->query($sql);
		$records=array();
		if($DB->num_rows($result)>0)
		{
			while($fetch=$DB->fetch_assoc($result))
			{
				// GET PHONE NO
				// THIS PHONE NO IS TAKEN FROM COMPANY TABLE :: PRIMARY_CONTACT WHICH IS TAKEN FROM EMPLOYEE TABLE 
				$fetch['PHONE'] = "";
				$selectPhone = "
								SELECT *
								FROM `employee`
								WHERE `ID` ='".$fetch['PRIMARY_CONTACT']."'

					";
					$resultPhone=$DB->query($selectPhone);					
					if($DB->num_rows($resultPhone)>0)
					{
						$fetchPhone=$DB->fetch_assoc($resultPhone);
						$fetch['PHONE'] = $fetchPhone['PHONE'];
					}	
				 $fetch['STATE_NAME'] = "";
					$selectStateName = "
								SELECT *
								FROM `dictionary`
								WHERE `ID` = '".$fetch['STATE']."'
						";
					$resultStateName=$DB->query($selectStateName);					
					if($DB->num_rows($resultStateName)>0)
					{
						$fetchStateName=$DB->fetch_assoc($resultStateName);
						$fetch['STATE_NAME']  = $fetchStateName['ITEM'];
					}	
				 $fetch['PC_NAME'] = "";
					$selectPCName = "
								SELECT *
								FROM `employee`
								WHERE `ID` = '".$fetch['PRIMARY_CONTACT']."'
						";
					$resultPCName=$DB->query($selectPCName);					
					if($DB->num_rows($resultPCName)>0)
					{
						$fetchPCName=$DB->fetch_assoc($resultPCName);
						$fetch['PC_NAME']  = $fetchPCName['FIRST_NAME'];
					}					
				$records[]=$fetch;	
			}
		}
		return $records;
	}
	
	
	
	
	
	
	
}
?>
