<?php
/*require_once('../config.php');
require_once('../database.php');
*/
class M_inspection
{
	public static function get_inspection_of_a_company($COMPANY_ID)
	{
		global $DB;
		$sql="
			 	SELECT * 
				FROM  `inspection` 
				WHERE COMPANY_ID ='".$COMPANY_ID."'
			 ";
		$result=$DB->query($sql);
		$record=array();
		if($DB->num_rows($result)>0)
		{
			while($fetch=$DB->fetch_assoc($result))
			{
				$record[]=$fetch;
			}
		}
		return $record;
	}	
	public static function get_inspection_of_a_company_not_assigned_to_me($COMPANY_ID,$INSPECTOR_ID,$LAST_INSPECTION_ASSIGNED)
	{
		global $DB;
	 	$sql="
			SELECT 
				ID, 	
				INSPECTION_DATE,	
				INSPECTION_COMPLETE_DATE,	
				REINSPECT_DATE,	
				BUILDING_ID,	
				COMPANY_ID,	
				SIGNATURE_INSPECTOR,	
				SIGNATURE_BUILDING,	
				STATUS,	
				SUMMARY,	
				PDF,	
				INSPECTOR_ID,	
				TEMPLATE_ID,
				SIGNATURE_INSPECTOR_DATE,
				SIGNATURE_BUILDING_DATE  
			FROM  `inspection` 
			WHERE  
				COMPANY_ID =  '".$COMPANY_ID."'
			AND
				`ID` 
			IN 
			(
				SELECT DISTINCT  `ID` 
				FROM inspection
				WHERE ID NOT 
				IN 
				(
					SELECT ID
						FROM `inspection` 
					WHERE 
						INSPECTOR_ID ='".$INSPECTOR_ID."'
					AND 
						STATUS ='1078'
				)
				UNION 
				SELECT ID
					FROM  `inspection` 
				WHERE 	
					INSPECTOR_ID =  '".$INSPECTOR_ID."'
				AND 
					STATUS =  '1078'
				AND 
					ID >  '".$LAST_INSPECTION_ASSIGNED."'
			)
			
			ORDER BY  `inspection`.`ID` DESC 
		";
		$result=$DB->query($sql);
		$record=array();
		if($DB->num_rows($result)>0)
		{
			while($fetch=$DB->fetch_assoc($result))
			{
				$record[]=$fetch;
			}
		}
		return $record;
	}	
	
	
}
?>