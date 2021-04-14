<?php
/*require_once('../config.php');
require_once('../database.php');
*/
class M_sync_inspection
{
	public static function sync_inspection_detail_by_company_id($COMPANY_ID)
	{
		global $DB;
		
		$INSPECTION['INSPECTION'] = array();
		
		$sql="
			 	SELECT * 
						FROM  `inspection` 
						WHERE COMPANY_ID ='".$COMPANY_ID."'";
		$result=$DB->query($sql);
		$record=array();
		if($DB->num_rows($result)>0)
		{
			$i=0;
			while($fetch=$DB->fetch_assoc($result))
			{
				$INSPECTION_ID = $fetch['ID'];
				//$record['']=$fetch;
				$INSPECTION['INSPECTION'][$i]=$fetch;
			
				$sql_door="SELECT * 
									FROM  `door` 
									WHERE INSPECTION_ID ='".$fetch['ID']."'";
				$result_door=$DB->query($sql_door);
				$record_door=array();
				if($DB->num_rows($result_door)>0)
				{
					$INSPECTION['INSPECTION'][$i]['DOORINFORMATION']= array();
					$i=0;
					while($fetch_door=$DB->fetch_assoc($result_door))
					{
						$INSPECTION['INSPECTION'][$i]['DOORINFORMATION'][$i] = $fetch_door;
						
						// for door type
						
						$sql_door_type = "SELECT *
													FROM door_type 
											WHERE	DOOR_ID='".$fetch_door['ID']."'";
						
						$result_door_type=$DB->query($sql_door_type);
						
						if($DB->num_rows($result_door_type)>0)
						{
							$INSPECTION['INSPECTION'][$i]['DOORINFORMATION'][$i]['DOOR_TYPE'] = array();
							while($fetch_door_type=$DB->fetch_assoc($result_door_type))
							{
								$INSPECTION['INSPECTION'][$i]['DOORINFORMATION'][$i]['DOOR_TYPE'] [] = $fetch_door_type['TYPE_ID'];
							}		
						}
						
						// for HARDWARE_CHECK_LIST
						
						$sql_hardware = "SELECT *
													FROM hardware 
											WHERE	DOOR_ID='".$fetch_door['ID']."'";
						
						$result_hardware=$DB->query($sql_hardware);
						
						if($DB->num_rows($result_hardware)>0)
						{
							$INSPECTION['INSPECTION'][$i]['DOORINFORMATION'][$i]['HARDWARE_CHECK_LIST'] = array();
							
							while($fetch_hardware=$DB->fetch_assoc($result_hardware))
							{
								$INSPECTION['INSPECTION'][$i]['DOORINFORMATION'][$i]['HARDWARE_CHECK_LIST'] [] = $fetch_hardware;
							}		
						}
						
						
						// for INSECPTION_CHECK_LIST
						
						$sql_inpection_list = "SELECT *
													FROM door_code 
											WHERE	DOOR_ID='".$fetch_door['ID']."'";
						
						$result_inpection_list=$DB->query($sql_inpection_list);
						
						if($DB->num_rows($result_inpection_list)>0)
						{
							$INSPECTION['INSPECTION'][$i]['DOORINFORMATION'][$i]['INSECPTION_CHECK_LIST'] = array();
							
							while($fetch_inpection_list=$DB->fetch_assoc($result_inpection_list))
							{
								$INSPECTION['INSPECTION'][$i]['DOORINFORMATION'][$i]['INSECPTION_CHECK_LIST'] [] = $fetch_inpection_list;
							}		
						}
						
						// for INSPECTION_OTHER
						
						$sql_inpection_other = "SELECT *
													FROM inspection_other 
											WHERE	INSPECTION_ID='".$INSPECTION_ID."'";
						
						$result_inpection_other=$DB->query($sql_inpection_other);
						
						if($DB->num_rows($result_inpection_other)>0)
						{
							$INSPECTION['INSPECTION'][$i]['DOORINFORMATION'][$i]['INSPECTION_OTHER'] = array();
							
							while($fetch_inpection_other=$DB->fetch_assoc($result_inpection_other))
							{
								$INSPECTION['INSPECTION'][$i]['DOORINFORMATION'][$i]['INSPECTION_OTHER'] [] = $fetch_inpection_other;
							}		
						}
						
						// for door picture
						
						$sql_picture = "SELECT *
													FROM picture 
											WHERE	DOOR_ID='".$fetch_door['ID']."'";
						
						$result_picture=$DB->query($sql_picture);
						
						if($DB->num_rows($result_picture)>0)
						{
							$INSPECTION['INSPECTION'][$i]['DOORINFORMATION'][$i]['PICTURES'] = array();
							
							while($fetch_picture=$DB->fetch_assoc($result_picture))
							{
								$INSPECTION['INSPECTION'][$i]['DOORINFORMATION'][$i]['PICTURES'] [] = $fetch_picture;
							}		
						}
						
						
						$i++;	
					}
				}
			}
			
		}
		//echo "<pre>";
		//print_r($INSPECTION);
		return $INSPECTION;
	}	
}
?>