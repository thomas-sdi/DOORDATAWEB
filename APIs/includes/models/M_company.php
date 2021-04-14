<?php

/*require_once('../config.php');
require_once('../database.php');
*/
class M_company
{
	public static function get_builiding_owner_listing($INSPECTION_COMPANY,$offset='',$limit='')
	{
		global $DB;
		/*$sql="
				SELECT *
				FROM  `company` 
				WHERE INSPECTION_COMPANY ='".$INSPECTION_COMPANY."'
				AND TYPE='1001'
			    LIMIT $offset , $limit

		     ";*/
			 
		 $sql="
				SELECT *
				FROM  `company` 
				WHERE INSPECTION_COMPANY ='".$INSPECTION_COMPANY."'
				AND TYPE='1001' 
				ORDER BY NAME
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
	
	public static function get_no_of_builiding_owner($INSPECTION_COMPANY)
	{
		global $DB;
		$sql="
				SELECT ID
				FROM  `company` 
				WHERE INSPECTION_COMPANY ='".$INSPECTION_COMPANY."'
				AND TYPE='1001'
		     ";
		$result=$DB->query($sql);
		return $DB->num_rows($result);
	}

	
	public static function update_owner($ID,$NAME,$ADDRESS_1,$CITY,$STATE,$PRIMARY_CONTACT)
	{
		global $DB;
		$sql="
			 	UPDATE company
				SET NAME			='".$NAME."',
					ADDRESS_1		='".$ADDRESS_1."',
					
					CITY	 		='".$CITY."',
					STATE	 		='".$STATE."'
					";
		if($PRIMARY_CONTACT!==NULL)
		{
			 $sql.=",PRIMARY_CONTACT='".$PRIMARY_CONTACT."'";
			
		}
			$sql.="WHERE ID='".$ID."'";	
		$result=$DB->query($sql);
		if($result)
		{
			return true;	
		}
		else
		{
			return false;
		}
	}
	
	public static function insert_owner($sanitized_data)
	{
		global $DB;
		$sql="
			 	INSERT INTO company
				(
					".join(',',array_keys($sanitized_data))."
				)
				VALUES
				(
					".join(',',array_values($sanitized_data))."
				)
				";
		$result=$DB->query($sql);
		if($result)
		{
			return $DB->insert_id();
		}
		else
		{
			return false;
		}
	}
	
	public static function is_this_building_owner_exists($ID)
	{
		global $DB;		
		$sql="
				SELECT * 
				FROM  `company` 
				WHERE ID =  '".$ID."'
				AND TYPE =1001
			 ";
		$result=$DB->query($sql);
		if($DB->num_rows($result)>0)
		{
			return true;	
		}
		else
		{
			return false;	
		}
	}
	public static function get_building_owner($ID)
	{
		global $DB;		
		$sql="
				SELECT * 
				FROM  `company` 
				WHERE ID =  '".$ID."'
				AND TYPE =1001
			 ";
		$result=$DB->query($sql);
		if($DB->num_rows($result)>0)
		{
			$fetch=$DB->fetch_assoc($result);
			$records = $fetch;	return $records;	
		}
		else
		{
			return false;	
		}
	}

}
?>
