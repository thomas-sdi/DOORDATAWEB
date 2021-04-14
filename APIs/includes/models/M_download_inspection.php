<?php
/*require_once('../config.php');
require_once('../database.php');
*/
class M_download_inspection
{
	public static function download_inspection_detail_by_inspection_id($COMPANY_ID,$INSPECTION_ID)
	{
		global $DB;
		
		$INSPECTION['INSPECTION'] = array();
		
		$sql="
			 	SELECT  a.*,
						b.NAME 				as COMPANY_NAME, 
						c.ITEM 				as INSPECTION_STATUS,
						d.FIRST_NAME 		as INSPECTOR_FIRST_NAME,
						d.LAST_NAME 		as INSPECTOR_LAST_NAME,
						e.NAME 				as BUILDING_NAME,
						e.ADDRESS_1 		as BUILDING_ADDRESS_1,
						e.CITY 				as BUILDING_CITY,
						e.STATE 			as BUILDING_STATE_ID,
						e.ZIP 				as BUILDING_ZIP,
						e.PRIMARY_CONTACT 	as BUILDING_PRIMARY_CONTACT_ID,
						e.CUSTOMER_ID 		as BUILDING_CUSTOMER_ID,
						g.ITEM 				as BUILDING_STATE,
						f.ID 				as BUILDING_OWNER_ID,					
						f.NAME 				as BUILDING_OWNER_NAME,					
						f.ADDRESS_1 		as BUILDING_OWNER_ADDRESS,
						f.CITY 				as BUILDING_OWNER_CITY,
						f.STATE 			as BUILDING_OWNER_STATE_ID,
						f.PRIMARY_CONTACT	as BUILDING_OWNER_MAINCONTACT_ID,
						h.FIRST_NAME 		as BUILDING_OWNER_MAINCONTACT_FIRST_NAME,
						h.LAST_NAME 		as BUILDING_OWNER_MAINCONTACT_LAST_NAME,
						h.PHONE 			as BUILDING_OWNER_MAINCONTACT_PHONE
						
						
						FROM  `inspection` as a
						left join company 	as b on a.COMPANY_ID=b.ID
						left join dictionary as c on a.STATUS=c.ID
						left join employee	as d on a.INSPECTOR_ID=d.ID	
						left join building	as e on a.BUILDING_ID=e.ID	
						left join company	as f on e.CUSTOMER_ID=f.ID	
						left join dictionary as g on g.ID=e.STATE
						left join employee   as h on b.PRIMARY_CONTACT=h.ID
												
						WHERE a.COMPANY_ID ='".$COMPANY_ID."'
						AND a.ID='".$INSPECTION_ID."'";
		$result=$DB->query($sql);
		$record=array();
		if($DB->num_rows($result)>0)
		{
			while($fetch=$DB->fetch_assoc($result))
			{
				//$record['']=$fetch;
				$INSPECTION['INSPECTION']=$fetch;
			/*	$INSPECTION['INSPECTION']['INSPECTION_ID'] = $fetch['ID'];
				$INSPECTION['INSPECTION']['INSPECTION_DATE'] = $fetch['INSPECTION_DATE'];
				$INSPECTION['INSPECTION']['INSPECTION_COMPLETE_DATE'] = $fetch['INSPECTION_COMPLETE_DATE'];
				$INSPECTION['INSPECTION']['REINSPECT_DATE'] = $fetch['REINSPECT_DATE'];
				$INSPECTION['INSPECTION']['STATUS'] = $fetch['STATUS'];
				$INSPECTION['INSPECTION']['COMPANY_ID'] = $fetch['COMPANY_ID'];
				$INSPECTION['INSPECTION']['BUILDING_ID'] = $fetch['BUILDING_ID'];
				$INSPECTION['INSPECTION']['SUMMARY'] = $fetch['SUMMARY'];
				$INSPECTION['INSPECTION']['INSPECTOR_ID'] = $fetch['INSPECTOR_ID'];
				$INSPECTION['INSPECTION']['TEMPLATE_ID'] = $fetch['TEMPLATE_ID'];*/
			
				$sql_door="SELECT * 
									FROM  `door` 
									WHERE INSPECTION_ID ='".$fetch['ID']."'";
				$result_door=$DB->query($sql_door);
				$record_door=array();
				if($DB->num_rows($result_door)>0)
				{
					$INSPECTION['INSPECTION']['DOORINFORMATION']= array();
					$i=0;
					while($fetch_door=$DB->fetch_assoc($result_door))
					{
						$INSPECTION['INSPECTION']['DOORINFORMATION'][$i] = $fetch_door;
						
						// for door type
						
						$sql_door_type = "SELECT *
													FROM door_type 
											WHERE	DOOR_ID='".$fetch_door['ID']."'";
						
						$result_door_type=$DB->query($sql_door_type);
						
						if($DB->num_rows($result_door_type)>0)
						{
							$INSPECTION['INSPECTION']['DOORINFORMATION'][$i]['DOOR_TYPE'] = array();
							while($fetch_door_type=$DB->fetch_assoc($result_door_type))
							{
								$INSPECTION['INSPECTION']['DOORINFORMATION'][$i]['DOOR_TYPE'] [] = $fetch_door_type['TYPE_ID'];
							}		
						}
						
						// for HARDWARE_CHECK_LIST
						
						$sql_hardware = "SELECT *
													FROM hardware 
											WHERE	DOOR_ID='".$fetch_door['ID']."'";
						
						$result_hardware=$DB->query($sql_hardware);
						
						if($DB->num_rows($result_hardware)>0)
						{
							$INSPECTION['INSPECTION']['DOORINFORMATION'][$i]['HARDWARE_CHECK_LIST'] = array();
							
							while($fetch_hardware=$DB->fetch_assoc($result_hardware))
							{
								$INSPECTION['INSPECTION']['DOORINFORMATION'][$i]['HARDWARE_CHECK_LIST'] [] = $fetch_hardware;
							}		
						}
						
						
						// for INSECPTION_CHECK_LIST
						
						$sql_inpection_list = "SELECT *
													FROM door_code 
											WHERE	DOOR_ID='".$fetch_door['ID']."'";
						
						$result_inpection_list=$DB->query($sql_inpection_list);
						
						if($DB->num_rows($result_inpection_list)>0)
						{
							$INSPECTION['INSPECTION']['DOORINFORMATION'][$i]['INSECPTION_CHECK_LIST'] = array();
							
							while($fetch_inpection_list=$DB->fetch_assoc($result_inpection_list))
							{
								$INSPECTION['INSPECTION']['DOORINFORMATION'][$i]['INSECPTION_CHECK_LIST'] [] = $fetch_inpection_list;
							}		
						}
						
						
						// for INSPECTION_OTHER - we only do it for the first door; we are not going to do it for other doors
						if ($i == 0){
							$sql_inpection_other = "SELECT *
														FROM inspection_other 
												WHERE	INSPECTION_ID='".$INSPECTION_ID."'";
							
							$result_inpection_other=$DB->query($sql_inpection_other);
							
							if($DB->num_rows($result_inpection_other)>0)
							{
								$INSPECTION['INSPECTION']['DOORINFORMATION'][$i]['INSPECTION_OTHER'] = array();
								
								while($fetch_inpection_other=$DB->fetch_assoc($result_inpection_other))
								{
									$INSPECTION['INSPECTION']['DOORINFORMATION'][$i]['INSPECTION_OTHER'] [] = $fetch_inpection_other;
								}		
							}							
						}

						
						// for door picture
						
						$sql_picture = "SELECT *
													FROM picture 
											WHERE	DOOR_ID='".$fetch_door['ID']."'";
						
						$result_picture=$DB->query($sql_picture);
						
						if($DB->num_rows($result_picture)>0)
						{
							$INSPECTION['INSPECTION']['DOORINFORMATION'][$i]['PICTURES'] = array();
							
							while($fetch_picture=$DB->fetch_assoc($result_picture))
							{
								$INSPECTION['INSPECTION']['DOORINFORMATION'][$i]['PICTURES'] [] = $fetch_picture;
							}		
						}
						
						
						$i++;	
					}
				}
			}
			
		}
/*		echo "<pre>";
		print_r($INSPECTION);*/

			
		return $INSPECTION;
	}	
}
?>