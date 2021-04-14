<?php
/*require_once('../config.php');
require_once('../database.php');
*/
class M_search
{
	//SEARCHING FOR INSPECTIONS
	public static function get_search_result_inspection($searchParameter , $COMPANY_ID)
	{
		global $DB;
		
	
		$record = array();
		//echo 
		$searchSql = "SELECT  i.Id , i.INSPECTION_DATE , i.INSPECTION_COMPLETE_DATE , i.REINSPECT_DATE , i.BUILDING_ID , i.COMPANY_ID 	 , i.SIGNATURE_INSPECTOR , i.SIGNATURE_BUILDING , i.STATUS , i.SUMMARY  , i.PDF  , i.INSPECTOR_ID , i.TEMPLATE_ID
	  FROM `inspection` as i
		 ";
		$searchWhere = " ";
		$arrayCount = count($searchParameter);
		switch($arrayCount){
			case 1 :
				// BOI 
				if (isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !=''){
				
						$searchWhere .="  JOIN `building` as b ON i.BUILDING_ID  = b.ID WHERE b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."' ";
				
				}
				// BI 
				if(isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!=''){
				
						$searchWhere .= " WHERE i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."'";
				
				} 
				// ADD
				if(isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='' ){
				
				
						$searchWhere .= " JOIN `building` as b ON i.BUILDING_ID = b.ID WHERE ADDRESS_1 = '".$searchParameter['ADDRESS']."'";
				
				}
				// INS
				if(isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='' ){
				
									$searchWhere .= " WHERE i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'";
				
				}
				if(isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='' ){
				
								$searchWhere .= " JOIN `door` as d ON i.ID = d.INSPECTION_ID WHERE d.DOOR_BARCODE  = '".$searchParameter['DOOR_BARCODE']."'";
				
				}
				
			break;
			case 2:
				// BOI X BI 
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!='')){
					////$searchWhere .= 	" JOIN company as c ON i.COMPANY_ID = c.INSPECTION_COMPANY AND c.ID = '".$searchParameter['BUILDING_OWNER_ID']."'  JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID";
					$searchWhere .= 	"  JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID AND  b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."'";
				
				}
				// BOI X ADD
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='' )){
					////$searchWhere .= 	" JOIN company as c ON i.COMPANY_ID = c.INSPECTION_COMPANY AND c.ID = '".$searchParameter['BUILDING_OWNER_ID']."'  JOIN  building as b ON i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."'";
					$searchWhere .= 	" JOIN  building as b ON i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' AND b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."'";
				}
				// BOI X INS
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='')  && (isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='')){
					////////$searchWhere .= " JOIN company as c ON i.COMPANY_ID = c.INSPECTION_COMPANY AND c.ID = '".$searchParameter['BUILDING_OWNER_ID']."'  AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'";
					$searchWhere .= " JOIN `building` as b ON i.BUILDING_ID  = b.ID WHERE b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."'  AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'";
				}
				// BOI X DB
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='')){
					$searchWhere .= 	" `building` as b ON i.BUILDING_ID  = b.ID WHERE b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."'  JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."'";
				}
				// BI X ADD
				if((isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!='') && (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='' )){
					$searchWhere .= 	" JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."'";
				}
				// BI X INS
				if((isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!='') && (isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='')){
				
						$searchWhere .= " JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."' ";
				}
				// BI X DB
				if((isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!='') && (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='')){
				
						$searchWhere .= " JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."'";
				}
				//  ADD X INS
				if((isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='' ) && (isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='')){
						$searchWhere .= 	" JOIN  building as b ON i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'";
				}
				//  ADD X DB
				if((isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='' ) && (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='')){
						$searchWhere .= " JOIN  building as b ON  i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."'";
				}
				// INS X DB
				if((isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='') && (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='')){
						$searchWhere .= 	"   JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."' AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'";
				
				}
			break;
			case 3:
				// BOI X BI X ADD
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!='') && (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='' )){
					$searchWhere .= 	"   JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' AND b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."'";
				}
				// BOI X BI X INS
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!='') && (isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='')){
				
					$searchWhere .= " JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID AND b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."' AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."' ";
					
				}
				// BOI X BI X DB
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!='') && (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='')){
				
					$searchWhere .= " JOIN  building as b ON i.BUILDING_ID = b.ID AND i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."' JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."'";
				}
				// BOI X ADD X INS
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='' ) && (isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='') ){
				
					$searchWhere .= " JOIN `building` as b ON i.BUILDING_ID  = b.ID WHERE b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."'  JOIN  building as b ON i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'  ";
					
				}
				// BOI X ADD X DB
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='' ) && (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='')){ 
				
					$searchWhere .= "  JOIN  building as b ON i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' AND b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."' JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."'";
					
				}
				// BOI X INS X DB
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='') && (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='')){
				
				$searchWhere .= " JOIN `building` as b ON i.BUILDING_ID  = b.ID WHERE b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."'  AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'  JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."'";
				
				}
				// BI X ADD X INS
				if((isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!='') && (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='') && (isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='') ){
				
					$searchWhere .= " JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'  ";
				
				}
				// BI X ADD X DB
				if((isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!='') && (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='' ) && (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='')){				
					$searchWhere .= " JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."'";
				}
				// ADD X INS X DB
				if((isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='' ) && (isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='') && (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='')){
					
					$searchWhere .= 	" JOIN  building as b ON  i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'  JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."' ";
					
				}
				
			break;
			case 4:
				// BOI X BI X AD X INS
				if ( (isset($searchParameter['BUILDING_OWNER_ID'] ) && $searchParameter['BUILDING_OWNER_ID'] !='') && 
					 (isset($searchParameter['BUILDING_ID'])&&	$searchParameter['BUILDING_ID']!='') && 
					  (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='') &&
					   (isset($searchParameter['INSPECTOR'] ) && $searchParameter['INSPECTOR'] !='' )){
						$searchWhere .=	" JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' AND b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."' AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'  ";
				}
				// BOI X BI X AD X DB
				if ( (isset($searchParameter['BUILDING_OWNER_ID'] ) && $searchParameter['BUILDING_OWNER_ID'] !='') && 
					 (isset($searchParameter['BUILDING_ID'])&&	$searchParameter['BUILDING_ID']!='') && 
					  (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='') &&
					   (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='' )){
						$searchWhere .=	" JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' AND b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."' JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."'";
				}
				// BOI X BI X DB X INS
				if ( (isset($searchParameter['BUILDING_OWNER_ID'] ) && $searchParameter['BUILDING_OWNER_ID'] !='') && 
					 (isset($searchParameter['BUILDING_ID'])&&	$searchParameter['BUILDING_ID']!='') && 
					  (isset($searchParameter['DOOR_BARCODE']) && $searchParameter['DOOR_BARCODE'] !='') &&
					   (isset($searchParameter['INSPECTOR'] ) && $searchParameter['INSPECTOR'] !='' )){
						$searchWhere .=	" JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID AND b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."' AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'  JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."'";
				}
				// BOI X DB X AD X INS
				if ( (isset($searchParameter['BUILDING_OWNER_ID'] ) && $searchParameter['BUILDING_OWNER_ID'] !='') && 
					 (isset($searchParameter['DOOR_BARCODE'])&&	$searchParameter['DOOR_BARCODE']!='') && 
					  (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='') &&
					   (isset($searchParameter['INSPECTOR'] ) && $searchParameter['INSPECTOR'] !='' )){
							$searchWhere .= " JOIN  building as b ON b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' AND b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."' AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'  JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."'";
				}
				// DB X BI X AD X INS
				if ( (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='') && 
					 (isset($searchParameter['BUILDING_ID'])&&	$searchParameter['BUILDING_ID']!='') && 
					  (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='') &&
					   (isset($searchParameter['INSPECTOR'] ) && $searchParameter['INSPECTOR'] !='' )){
						
						$searchWhere .= " JOIN building as b  ON  i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."' JOIN door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE  = '".$searchParameter['DOOR_BARCODE']."' ";
						
				}
				
				
				
					
			break;
			case 5:
				// BOI X DB X BI X AD X INS
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!='') && (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='' ) && (isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='') && (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !=''))
				{
						
						$searchWhere .= 	" JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' AND b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."' AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'  JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."'";
					
				}
			break;
		}
		
		
		
	
		$finalSelectSearch  = $searchSql ." ".$searchWhere." AND i.COMPANY_ID = '".$COMPANY_ID."' ";////die;
		
		$result=$DB->query($finalSelectSearch );
		
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
	
	// SEARCHING FOR BUILDINGS OWNER
	public static function get_search_result_building_owner($searchParameter , $COMPANY_ID)
	{
		global $DB;
		
		echo "<pre>";
		print_r($searchParameter);
		echo "</pre>";
		$record = array();
		$searchSql = "
					  SELECT  c.ID , c.NAME , c.ADDRESS_1 , c.CITY , c.STATE , c.PRIMARY_CONTACT , c.INSPECTION_COMPANY 
					  FROM `company` as c
					 ";
		$searchWhere = " ";
		$arrayCount = count($searchParameter);
		switch($arrayCount){
			case 1 :
				// BOI 
				if (isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !=''){
				
					$searchWhere .="  WHERE c.ID  = '".$searchParameter['BUILDING_OWNER_ID']."' ";
				
				}
				// BI 
				if(isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!=''){
				
					$searchWhere .= " JOIN `building` as b ON b.CUSTOMER_ID = c.ID AND  b.ID = '".$searchParameter['BUILDING_ID']."'";
				
				} 
				// CITY
				if(isset($searchParameter['CITY']) && $searchParameter['CITY'] !='' ){
				
				
					$searchWhere .= " WHERE c.CITY = '".$searchParameter['CITY']."'";
				
				}
				// STATE
				if(isset($searchParameter['STATE']) && $searchParameter['STATE'] !='' ){
				
				
					$searchWhere .= " WHERE c.STATE = '".$searchParameter['STATE']."'";
				
				}
				// INS
				if(isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='' ){
				
					$searchWhere .= " JOIN inspection as i ON  i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."' AND i.COMPANY_ID = '".$COMPANY_ID."' JOIN `building` as b ON b.ID = i.BUILDING_ID AND c.ID = b.CUSTOMER_ID ";
				
				}
				// DB
				if(isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='' ){
				
					$searchWhere .= " JOIN `door` as d ON d.DOOR_BARCODE  = '".$searchParameter['DOOR_BARCODE']."' JOIN `building`  as b ON b.ID = d.BUILDING_ID AND c.ID = b.CUSTOMER_ID ";
				
				}
				
			break;
			case 2:
				// BOI X BI 
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!='')){
					
					$searchWhere .= 	"  JOIN  building as b ON b.ID = '".$searchParameter['BUILDING_ID']."' AND b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."' AND c.ID = '".$searchParameter['BUILDING_OWNER_ID']."'";
				
				}
				// BOI X CT
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['CITY']) && $searchParameter['CITY'] !='' )){
					
					$searchWhere .= " WHERE c.CITY = '".$searchParameter['CITY']."' AND c.ID  = '".$searchParameter['BUILDING_OWNER_ID']."'";
				}
				// BOI X ST
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['STATE']) && $searchParameter['STATE'] !='' )){
					
					$searchWhere .= " WHERE c.STATE = '".$searchParameter['STATE']."' AND c.ID  = '".$searchParameter['BUILDING_OWNER_ID']."'";
				}
				// BOI X INS
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='')  && (isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='')){
					
					$searchWhere .= " JOIN `building` as b ON b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."' JOIN `inspection` as i ON i.BUILDING_ID  = b.ID AND c.ID = b.CUSTOMER_ID   AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'";
				}
				// BOI X DB
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='')){
					$searchWhere .= " JOIN `door` as d ON d.DOOR_BARCODE  = '".$searchParameter['DOOR_BARCODE']."' JOIN `building`  as b ON b.ID = d.BUILDING_ID AND c.ID = b.CUSTOMER_ID AND c.ID = '".$searchParameter['BUILDING_OWNER_ID']."'";
				}
				// BI X CITY
				if((isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!='') && (isset($searchParameter['CITY']) && $searchParameter['CITY'] !='' )){
					$searchWhere .= 	" JOIN `building` as b ON b.CUSTOMER_ID = c.ID AND  b.ID = '".$searchParameter['BUILDING_ID']."' AND c.CITY = '".$searchParameter['CITY']."'";
				}
				// BI X STATE
				if((isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!='') && (isset($searchParameter['STATE']) && $searchParameter['STATE'] !='' )){
					$searchWhere .= " JOIN `building` as b ON b.CUSTOMER_ID = c.ID AND  b.ID = '".$searchParameter['BUILDING_ID']."' AND c.STATE = '".$searchParameter['STATE']."'";
				}
				// BI X INS
				if((isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!='') && (isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='')){
				
						$searchWhere .= " JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."' ";
				}
				// BI X DB
				if((isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!='') && (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='')){
				
						$searchWhere .= " JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."'";
				}
				//  CT X ST
				if((isset($searchParameter['CITY']) && $searchParameter['CITY'] !='' ) && (isset($searchParameter['STATE']) && $searchParameter['STATE'] !='')){
						$searchWhere .= " WHERE c.STATE = '".$searchParameter['STATE']."' AND c.CITY = '".$searchParameter['CITY']."'";
				}
				//  CT X INS
				if((isset($searchParameter['CITY']) && $searchParameter['CITY'] !='' ) && (isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='')){
						$searchWhere .= 	" JOIN inspection as i ON  i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."' AND i.COMPANY_ID = '".$COMPANY_ID."' JOIN `building` as b ON b.ID = i.BUILDING_ID AND c.ID = b.CUSTOMER_ID AND c.CITY = '".$searchParameter['CITY']."'";
				}
				//  CT X DB
				if((isset($searchParameter['CITY']) && $searchParameter['CITY'] !='' ) && (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='')){
						$searchWhere .= " JOIN `door` as d ON d.DOOR_BARCODE  = '".$searchParameter['DOOR_BARCODE']."' JOIN `building`  as b ON b.ID = d.BUILDING_ID AND c.ID = b.CUSTOMER_ID AND c.CITY = '".$searchParameter['CITY']."'";
				}
				// INS X DB
				if((isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='') && (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='')){
						$searchWhere .= 	"   JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."' AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'";
				
				}
			break;
			case 3:
				// BOI X BI X ADD
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!='') && (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='' )){
					$searchWhere .= 	"   JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' AND b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."'";
				}
				// BOI X BI X INS
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!='') && (isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='')){
				
					$searchWhere .= " JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID AND b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."' AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."' ";
					
				}
				// BOI X BI X DB
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!='') && (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='')){
				
					$searchWhere .= " JOIN  building as b ON i.BUILDING_ID = b.ID AND i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."' JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."'";
				}
				// BOI X ADD X INS
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='' ) && (isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='') ){
				
					$searchWhere .= " JOIN `building` as b ON i.BUILDING_ID  = b.ID WHERE b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."'  JOIN  building as b ON i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'  ";
					
				}
				// BOI X ADD X DB
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='' ) && (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='')){ 
				
					$searchWhere .= "  JOIN  building as b ON i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' AND b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."' JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."'";
					
				}
				// BOI X INS X DB
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='') && (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='')){
				
				$searchWhere .= " JOIN `building` as b ON i.BUILDING_ID  = b.ID WHERE b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."'  AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'  JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."'";
				
				}
				// BI X ADD X INS
				if((isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!='') && (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='') && (isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='') ){
				
					$searchWhere .= " JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'  ";
				
				}
				// BI X ADD X DB
				if((isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!='') && (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='' ) && (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='')){				
					$searchWhere .= " JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."'";
				}
				// ADD X INS X DB
				if((isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='' ) && (isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='') && (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='')){
					
					$searchWhere .= 	" JOIN  building as b ON  i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'  JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."'";
					
				}
				
			break;
			case 4:
				// BOI X BI X AD X INS
				if ( (isset($searchParameter['BUILDING_OWNER_ID'] ) && $searchParameter['BUILDING_OWNER_ID'] !='') && 
					 (isset($searchParameter['BUILDING_ID'])&&	$searchParameter['BUILDING_ID']!='') && 
					  (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='') &&
					   (isset($searchParameter['INSPECTOR'] ) && $searchParameter['INSPECTOR'] !='' )){
						$searchWhere .=	" JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' AND b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."' AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'  ";
				}
				// BOI X BI X AD X DB
				if ( (isset($searchParameter['BUILDING_OWNER_ID'] ) && $searchParameter['BUILDING_OWNER_ID'] !='') && 
					 (isset($searchParameter['BUILDING_ID'])&&	$searchParameter['BUILDING_ID']!='') && 
					  (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='') &&
					   (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='' )){
						$searchWhere .=	" JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' AND b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."' JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."'";
				}
				// BOI X BI X DB X INS
				if ( (isset($searchParameter['BUILDING_OWNER_ID'] ) && $searchParameter['BUILDING_OWNER_ID'] !='') && 
					 (isset($searchParameter['BUILDING_ID'])&&	$searchParameter['BUILDING_ID']!='') && 
					  (isset($searchParameter['DOOR_BARCODE']) && $searchParameter['DOOR_BARCODE'] !='') &&
					   (isset($searchParameter['INSPECTOR'] ) && $searchParameter['INSPECTOR'] !='' )){
						$searchWhere .=	" JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID AND b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."' AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'  JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."'";
				}
				// BOI X DB X AD X INS
				if ( (isset($searchParameter['BUILDING_OWNER_ID'] ) && $searchParameter['BUILDING_OWNER_ID'] !='') && 
					 (isset($searchParameter['DOOR_BARCODE'])&&	$searchParameter['DOOR_BARCODE']!='') && 
					  (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='') &&
					   (isset($searchParameter['INSPECTOR'] ) && $searchParameter['INSPECTOR'] !='' )){
							$searchWhere .= " JOIN  building as b ON b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' AND b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."' AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'  JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."'";
				}
				// DB X BI X AD X INS
				if ( (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='') && 
					 (isset($searchParameter['BUILDING_ID'])&&	$searchParameter['BUILDING_ID']!='') && 
					  (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='') &&
					   (isset($searchParameter['INSPECTOR'] ) && $searchParameter['INSPECTOR'] !='' )){
						
						$searchWhere .= " JOIN building as b  ON  i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."' JOIN door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE  = '".$searchParameter['DOOR_BARCODE']."' ";
						
				}
				
				
				
					
			break;
			case 5:
				// BOI X BI X CT X ST X DB
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='')
				  	&& (isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!='')
				  	&& (isset($searchParameter['CITY']) && $searchParameter['CITY'] !='' ) 
				  	&& (isset($searchParameter['STATE']) && $searchParameter['STATE'] !='' ) 
				  	&& (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !=''))
				{
						
						$searchWhere .= " JOIN `building` as b ON b.ID = '".$searchParameter['BUILDING_ID']."' AND b.CUSTOMER_ID = c.ID JOIN `inspection` as i ON  i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.COMPANY_ID = '".$COMPANY_ID."' AND b.ID = i.BUILDING_ID	 AND c.CITY = '".$searchParameter['CITY']."' AND c.STATE = '".$searchParameter['STATE']."' AND c.ID = '".$searchParameter['BUILDING_OWNER_ID']."' JOIN `door` as d ON DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."' AND d.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.ID = d.INSPECTION_ID";
					
				}
				// BOI X BI X CT X ST X INS
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') 
					&& (isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!='') 
					&& (isset($searchParameter['CITY']) && $searchParameter['CITY'] !='' ) 
					&& (isset($searchParameter['STATE']) && $searchParameter['STATE'] !='' ) 
					&& (isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='') 
					&& (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !=''))
				{
						$searchWhere .= " JOIN `building` as b ON b.ID = '".$searchParameter['BUILDING_ID']."' AND b.CUSTOMER_ID = c.ID JOIN `inspection` as i ON i.INSPECTOR_ID = '". $searchParameter['INSPECTOR']."' AND i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.COMPANY_ID = '".$COMPANY_ID."' AND b.ID = i.BUILDING_ID	 AND c.CITY = '".$searchParameter['CITY']."' AND c.STATE = '".$searchParameter['STATE']."' AND c.ID = '".$searchParameter['BUILDING_OWNER_ID']."'";
					
				}
			break;
			case 6:
				// BOI X DB X BI X CT X ST X INS
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!='') && (isset($searchParameter['CITY']) && $searchParameter['CITY'] !='' ) && (isset($searchParameter['STATE']) && $searchParameter['STATE'] !='' ) && (isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='') && (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !=''))
				{
						$searchWhere .= " JOIN `building` as b ON b.ID = '".$searchParameter['BUILDING_ID']."' AND b.CUSTOMER_ID = c.ID JOIN `inspection` as i ON i.INSPECTOR_ID = '". $searchParameter['INSPECTOR']."' AND i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.COMPANY_ID = '".$COMPANY_ID."' AND b.ID = i.BUILDING_ID	 AND c.CITY = '".$searchParameter['CITY']."' AND c.STATE = '".$searchParameter['STATE']."' AND c.ID = '".$searchParameter['BUILDING_OWNER_ID']."' JOIN `door` as d ON DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."' AND d.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.ID = d.INSPECTION_ID";
					
				}
			break;
		}
		
	
		echo 
		$finalSelectSearch  = $searchSql ." ".$searchWhere." AND c.INSPECTION_COMPANY  = '".$COMPANY_ID."' ";////die;
		
		$result=$DB->query($finalSelectSearch );
		
		if($DB->num_rows($result)==0){
			return false;
		}
		else{
			while($fetch=$DB->fetch_assoc($result)){
				/*echo "<pre>";
				print_r($fetch);*/
				$fetch['MAIN_CONTACT'] = '';
				$fetch['PHONE'] = '';
				if($fetch['PRIMARY_CONTACT']!=''){
					$selectEmpDetails = "SELECT * FROM `employee` WHERE ID = '".$fetch['PRIMARY_CONTACT']."'";
					$resultEmpDetails=$DB->query($selectEmpDetails );
					if($DB->num_rows($result)==1){
						$fetchEmpDetails=$DB->fetch_assoc($resultEmpDetails);
						$fetch['MAIN_CONTACT'] = $fetchEmpDetails['FIRST_NAME'];
						$fetch['PHONE'] = $fetchEmpDetails['PHONE'];
					}
					
				
				}
				
				$record[] = $fetch;
			
			}
			return $record;
		}
	
		
	}		
}
?>