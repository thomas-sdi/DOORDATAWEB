<?php
/*require_once('../config.php');
require_once('../database.php');
*/
class M_search_building
{
	//SEARCHING FOR Inpections
	public static function get_search_result_inspection($searchParameter,$COMPANY_ID)
	{
		global $DB;

		$record = array();
		
		$searchWhere="";
		$searchTables="";
		$searchSelect="";
		
		if($searchParameter['BUILDING_OWNER_ID'] !='' or $searchParameter['STATE_ID'] !='' or $searchParameter['CITY'] !='' or $searchParameter['BUILDING_ID'] !='')
		{
			$searchSelect.="";
			$searchTables.=",building";
			$searchWhere .=" AND building.ID = inspection.BUILDING_ID";
		}
		
		
		if (isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='')
		{
			$BUILDING_OWNER_ID = $searchParameter['BUILDING_OWNER_ID'];
			$searchSelect.="";
			$searchTables.="";
			$searchWhere .=" AND building.CUSTOMER_ID ='".$BUILDING_OWNER_ID."'";	
		}
		if (isset($searchParameter['STATE_ID']) && $searchParameter['STATE_ID'] !='')
		{

				$STATE_ID = $searchParameter['STATE_ID'];
				$searchSelect.="";
				$searchTables.="";
				$searchWhere.=" AND building.STATE ='".$STATE_ID."'";

		}
		if (isset($searchParameter['CITY']) && $searchParameter['CITY'] !='')
		{
			$CITY = $searchParameter['CITY'];
			$searchSelect.="";
			$searchTables.="";
			$searchWhere .=" AND building.CITY ='".$CITY."'";	
		}
		
		if (isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID'] !='')
		{
			$BUILDING = $searchParameter['BUILDING_ID'];
			$searchSelect.="";
			$searchTables.="";
			$searchWhere .=" AND building.ID ='".$BUILDING."'";	
		}
		
		
		
		if (isset($searchParameter['DOOR_BARCODE']) && $searchParameter['DOOR_BARCODE'] !='')
		{
			$DOOR_BARCODE = $searchParameter['DOOR_BARCODE'];
			$searchSelect.="";
			$searchTables.=",door";
			$searchWhere .=" AND inspection.ID =door.INSPECTION_ID 
							AND door.DOOR_BARCODE ='".$DOOR_BARCODE."'";	
		}
		
		
		
		if (isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='')
		{
			$INSPECTOR = $searchParameter['INSPECTOR'];
			
			$searchSelect.="";
			$searchTables.="";
			$searchWhere.=" AND inspection.INSPECTOR_ID  ='".$INSPECTOR."'";	

		}
		
		
		if (isset($searchParameter['COMPANY_ID']) && $searchParameter['COMPANY_ID'] !='')
		{
			$COMPANY_ID = $searchParameter['COMPANY_ID'];
		}
		
		
		$searchSql = "SELECT inspection.* $searchSelect
											FROM 
									 				inspection $searchTables
											WHERE 	inspection.ID!='' 
												AND COMPANY_ID = '".$COMPANY_ID."'
													$searchWhere
													GROUP BY inspection.ID";
											
		$result=$DB->query($searchSql );
		
		if($DB->num_rows($result)==0){
			return false;
		}
		else{
			while($fetch=$DB->fetch_assoc($result)){
				$record[] = $fetch;
			
			}
			return $record;
		}
	}
	
	//SEARCHING FOR building
	public static function get_search_result_building($searchParameter)
	{
		global $DB;

		$record = array();
		
		$searchWhere="";
		$searchTables="";
		$searchSelect="";
		
		if (isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='')
		{
			$BUILDING_OWNER_ID = $searchParameter['BUILDING_OWNER_ID'];
			$searchSelect.="";
			$searchTables.="";
			$searchWhere .="AND building.CUSTOMER_ID ='".$BUILDING_OWNER_ID."'";	
		}
		if (isset($searchParameter['STATE_ID']) && $searchParameter['STATE_ID'] !='')
		{
			$STATE_ID = $searchParameter['STATE_ID'];
			$searchSelect.="";
			$searchTables.="";
			$searchWhere.="AND building.STATE ='".$STATE_ID."'";	
		}
		if (isset($searchParameter['CITY']) && $searchParameter['CITY'] !='')
		{
			$CITY = $searchParameter['CITY'];
			$searchSelect.="";
			$searchTables.="";
			$searchWhere .="AND building.CITY ='".$CITY."'";	
		}
		if (isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID'] !='')
		{
			$BUILDING = $searchParameter['BUILDING_ID'];
			$searchSelect.="";
			$searchTables.="";
			$searchWhere .=" AND building.ID ='".$BUILDING."'";	
		}
		
		if (isset($searchParameter['DOOR_BARCODE']) && $searchParameter['DOOR_BARCODE'] !='')
		{
			$DOOR_BARCODE = $searchParameter['DOOR_BARCODE'];
			$searchSelect.="";
			$searchTables.=",door";
			$searchWhere .="AND building.ID =door.BUILDING_ID 
							AND door.DOOR_BARCODE ='".$DOOR_BARCODE."'";	
		}
		
		
		
		if (isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='')
		{
			$INSPECTOR = $searchParameter['INSPECTOR'];
			
			if (isset($searchParameter['DOOR_BARCODE']) && $searchParameter['DOOR_BARCODE'] !='')
			{
				$searchSelect.="";
				$searchTables.=",inspection";
				$searchWhere.="	AND inspection.ID =door.INSPECTION_ID
								AND building.ID =inspection.BUILDING_ID 
								AND inspection.INSPECTOR_ID  ='".$INSPECTOR."'";
			}
			else
			{	
				$searchSelect.="";
				$searchTables.=",inspection";
				$searchWhere.="AND building.ID =inspection.BUILDING_ID 
									AND inspection.INSPECTOR_ID  ='".$INSPECTOR."'";
			}	

		}
		
		
		if (isset($searchParameter['COMPANY_ID']) && $searchParameter['COMPANY_ID'] !='')
		{
			$COMPANY_ID = $searchParameter['COMPANY_ID'];
		}
		
		
		$searchSql = "
						SELECT distinct building.*,
							company.NAME as BUILDINGOWNER,
							dictionary.ITEM as STATE_NAME,
							d.ITEM as COUNTRY_NAME,employee.PHONE as PHONE 
							$searchSelect
						FROM building  
						LEFT JOIN employee ON building.PRIMARY_CONTACT = employee.ID 
						LEFT JOIN dictionary ON building.STATE = dictionary.ID 
						LEFT JOIN dictionary as d ON building.COUNTRY = d.ID,company $searchTables
						WHERE 	building.ID!='' 
						AND CUSTOMER_ID = company.ID
						AND INSPECTION_COMPANY = '".$COMPANY_ID."'
						$searchWhere";
											
		$result=$DB->query($searchSql );
		
		if($DB->num_rows($result)==0){
			return false;
		}
		else{
			while($fetch=$DB->fetch_assoc($result)){
				$record[] = $fetch;
			
			}
			return $record;
		}
	}
	
	//SEARCHING FOR building owner
	public static function get_search_result_building_owner($searchParameter,$COMPANY_ID)
	{
		global $DB;

		//echo "<pre>";
		//print_r($searchParameter);
		//echo "</pre>";
		
		$record = array();
		
		$searchWhere="";
		$searchTables="";
		$searchSelect="";
		
		if (isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='')
		{
			$BUILDING_OWNER_ID = $searchParameter['BUILDING_OWNER_ID'];
			$searchSelect.="";
			$searchTables.="";
			$searchWhere .="AND company.ID ='".$BUILDING_OWNER_ID."'";	
		}
		if (isset($searchParameter['STATE_ID']) && $searchParameter['STATE_ID'] !='')
		{
			$STATE_ID = $searchParameter['STATE_ID'];
			$searchSelect.="";
			$searchTables.="";
			$searchWhere.="AND company.STATE ='".$STATE_ID."'";	
		}
		if (isset($searchParameter['CITY']) && $searchParameter['CITY'] !='')
		{
			$CITY = $searchParameter['CITY'];
			$searchSelect.="";
			$searchTables.="";
			$searchWhere .="AND company.CITY ='".$CITY."'";	
		}
		if (isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID'] !='')
		{
			$BUILDING = $searchParameter['BUILDING_ID'];
			$searchSelect.="";
			$searchTables.=",building ";
			$searchWhere .="AND company.ID = building.CUSTOMER_ID 
							AND building.ID ='".$BUILDING."'";	
		}
		
		
		
		if (isset($searchParameter['DOOR_BARCODE']) && $searchParameter['DOOR_BARCODE'] !='')
		{
			$DOOR_BARCODE = $searchParameter['DOOR_BARCODE'];
			
			if ($searchParameter['BUILDING_ID'] =='')
			{
	
				$searchSelect.="";
				$searchTables.=",building";
				$searchWhere .="
								AND company.ID =building.CUSTOMER_ID";	
			}

			$searchSelect.="";
			$searchTables.=",door";
			$searchWhere .="
							AND building.ID =door.BUILDING_ID 
							AND door.DOOR_BARCODE ='".$DOOR_BARCODE."'";
		}		
		
		if (isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='')
		{
			
			$INSPECTOR = $searchParameter['INSPECTOR'];
			
			if ($searchParameter['BUILDING_ID'] =='' and $searchParameter['DOOR_BARCODE'] =='')
			{
			
				$searchSelect.="";
				$searchTables.=",building";
				$searchWhere .="
								AND company.ID =building.CUSTOMER_ID";	
			}
			else if($searchParameter['DOOR_BARCODE'] !='')
			{
				$searchSelect.="";
				$searchTables.="";
				$searchWhere .="
								AND door.INSPECTION_ID =inspection.ID";
			}
			
			
			$searchSelect.="";
			$searchTables.=",inspection";
			$searchWhere.="	AND building.ID =inspection.BUILDING_ID 
							AND inspection.INSPECTOR_ID  ='".$INSPECTOR."'";

		}
		
		
		if (isset($searchParameter['COMPANY_ID']) && $searchParameter['COMPANY_ID'] !='')
		{
			$COMPANY_ID = $searchParameter['COMPANY_ID'];
		}
		
		
		$searchSql = "SELECT company.*,dictionary.ITEM as STATE_NAME,employee.PHONE as PHONE,employee.FIRST_NAME as PC_NAME $searchSelect
															
															FROM 
																	company LEFT JOIN dictionary ON company.STATE = dictionary.ID LEFT JOIN employee ON company.PRIMARY_CONTACT = employee.ID $searchTables
															WHERE 	company.ID!='' 
																AND INSPECTION_COMPANY = '".$COMPANY_ID."'
																	$searchWhere
																	GROUP BY company.ID";
											
		$result=$DB->query($searchSql );
		
		if($DB->num_rows($result)==0){
			return false;
		}
		else{
			while($fetch=$DB->fetch_assoc($result)){
				$record[] = $fetch;
			
			}
			return $record;
		}
	}	
			
}
?>