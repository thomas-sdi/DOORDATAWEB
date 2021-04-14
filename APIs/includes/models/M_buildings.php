<?php
/*require_once("../config.php");
require_once("../database.php");
*/
class M_buildings
{
	public static function get_buildings_listing($CUSTOMER_ID)
	{
		global $DB;
		$sql="
			 	SELECT * 
				FROM building
				WHERE CUSTOMER_ID IN (".$CUSTOMER_ID.") 
				ORDER BY NAME
			 ";
		$result=$DB->query($sql);
		$records=array();
		if($DB->num_rows($result)>0)
		{
			while($fetch=$DB->fetch_assoc($result))
			{
				$sqlOwner="
					SELECT NAME  
					FROM company
					WHERE ID ='".$fetch['CUSTOMER_ID']."' 
				 ";
				 $fetch['OWNER_NAME']="";
				$resultOwner=$DB->query($sqlOwner);
				
				if($DB->num_rows($resultOwner)>0)
				{
					$fetchOwner=$DB->fetch_assoc($resultOwner);
					$fetch['OWNER_NAME'] = $fetchOwner['NAME'];
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
	
	public static function insert_new_building($data)
	{
		global $DB;
		$sql="
			 	INSERT INTO building
				(
					".join(',',array_keys($data))."
				)
				VALUES
				(
					".join(',',array_values($data))."
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
	public static function update_building($data)
	{
		global $DB;
		//////print_r($data);
		$updateString = "";//
		foreach($data as $key => $val){
			if($key == 'BUILDING_ID'){
				continue;
			}
			else{
				$updateString .= $key ." = ".$val." , "; 
			}
		}
		
		$setString = 	substr($updateString, 0, strlen($updateString)-2);//	eregi_replace(',$', '', $updateString) ;//
		
		 $sql="
			 	UPDATE building SET ".$setString ." 
				WHERE ID = ".$data['BUILDING_ID']."
				
			 ";	
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
	public static function get_building_name($ID)
	{
		
		global $DB;
		$sql="
			 	SELECT * 
				FROM  `building` 
				WHERE  `ID` ='".$ID."'
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
	public static function get_main_contact($COMPANY_ID)
	{
		global $DB;
		$sql="
			 	SELECT * 
				FROM  `employee` 
				WHERE 	COMPANY_ID ='".$COMPANY_ID."'
			 ";
		$result=$DB->query($sql);
		$record = array();
		if($DB->num_rows($result)!=0)
		{
			while($fetch=$DB->fetch_assoc($result))	{
				$record[] = $fetch;
			}
			return $record;		
		}
		else
		{
			return false;
		}
	}
	
	public static function generate_building_address($building_id)
	{
		global $DB;
		 $sql="
			SELECT a.ADDRESS_1, a.CITY, b.ITEM AS state, a.ZIP, c.ITEM AS country
			FROM  `building` AS a
			LEFT JOIN dictionary AS b ON a.STATE = b.ID
			AND b.CATEGORY =  'State'
			LEFT JOIN dictionary AS c ON a.COUNTRY = c.ID
			AND c.CATEGORY =  'Country'
			WHERE a.ID =  '$building_id'			 
			";
		
		$result=$DB->query($sql);
		$record = array();
		if($DB->num_rows($result)!=0)
		{
			while($fetch=$DB->fetch_assoc($result))	{
				$record[] = $fetch;
			}
			
			$address=implode(" , " , $record[0]);
			return $address;
		
		}
		else
		{
			return false;
		}
	exit();
	}
}
?>