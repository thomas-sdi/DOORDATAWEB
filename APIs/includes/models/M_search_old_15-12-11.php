<?php
/*require_once('../config.php');
require_once('../database.php');
*/
class M_search
{
	public static function get_search_result($searchParameter)
	{
		global $DB;
		
	
		$record = array();
		$searchSql = "SELECT  i.Id , i.INSPECTION_DATE , i.INSPECTION_COMPLETE_DATE , i.REINSPECT_DATE , i.BUILDING_ID , i.COMPANY_ID 	 , i.SIGNATURE_INSPECTOR , i.SIGNATURE_BUILDING , i.STATUS , i.SUMMARY  , i.PDF  , i.INSPECTOR_ID , i.TEMPLATE_ID
	  FROM `inspection` as i
		 ";
		$searchWhere = " ";
		$arrayCount = count($searchParameter);
		switch($arrayCount){
			case 1 :
				
				if (isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !=''){
				
						$searchWhere .="  JOIN `building` as b ON i.BUILDING_ID  = b.ID WHERE b.CUSTOMER_ID  = '".$searchParameter['BUILDING_OWNER_ID']."' ";
				
				}
				if(isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!=''){
				
						$searchWhere .= " WHERE i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."'";
				
				} 
				if(isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='' ){
				
				
						$searchWhere .= " JOIN `building` as b ON i.BUILDING_ID = b.ID WHERE ADDRESS_1 = '".$searchParameter['ADDRESS']."'";
				
				}
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
					$searchWhere .= 	" JOIN company as c ON i.COMPANY_ID = c.INSPECTION_COMPANY AND c.ID = '".$searchParameter['BUILDING_OWNER_ID']."'  JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID";
				
				
				}
				// BOI X ADD
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='' )){
					$searchWhere .= 	" JOIN company as c ON i.COMPANY_ID = c.INSPECTION_COMPANY AND c.ID = '".$searchParameter['BUILDING_OWNER_ID']."'  JOIN  building as b ON i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."'";
				}
				// BOI X INS
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='')  && (isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='')){
					$searchWhere .= " JOIN company as c ON i.COMPANY_ID = c.INSPECTION_COMPANY AND c.ID = '".$searchParameter['BUILDING_OWNER_ID']."'  AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'";
				}
				// BOI X DB
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='')){
					$searchWhere .= 	" JOIN company as c ON i.COMPANY_ID = c.INSPECTION_COMPANY AND c.ID = '".$searchParameter['BUILDING_OWNER_ID']."'  JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."'";
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
					$searchWhere .= 	" JOIN company as c ON i.COMPANY_ID = c.INSPECTION_COMPANY AND c.ID = '".$searchParameter['BUILDING_OWNER_ID']."'  JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' ";
				}
				// BOI X BI X INS
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!='') && (isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='')){
				
					$searchWhere .= " JOIN company as c ON i.COMPANY_ID = c.INSPECTION_COMPANY AND c.ID = '".$searchParameter['BUILDING_OWNER_ID']."'  JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."' ";
					
				}
				// BOI X BI X DB
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!='') && (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='')){
				
					$searchWhere .= " JOIN company as c ON i.COMPANY_ID = c.INSPECTION_COMPANY AND c.ID = '".$searchParameter['BUILDING_OWNER_ID']."'  JOIN  building as b ON i.BUILDING_ID = b.ID AND i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."'";
				}
				// BOI X ADD X INS
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='' ) && (isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='') ){
				
					$searchWhere .= " JOIN company as c ON i.COMPANY_ID = c.INSPECTION_COMPANY AND c.ID = '".$searchParameter['BUILDING_OWNER_ID']."'  JOIN  building as b ON i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'  ";
					
				}
				// BOI X ADD X DB
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='' ) && (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='')){ 
				
					$searchWhere .= " JOIN company as c ON i.COMPANY_ID = c.INSPECTION_COMPANY AND c.ID = '".$searchParameter['BUILDING_OWNER_ID']."'  JOIN  building as b ON i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."'";
					
				}
				// BOI X INS X DB
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='') && (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='')){
				
				$searchWhere .= " JOIN company as c ON i.COMPANY_ID = c.INSPECTION_COMPANY AND c.ID = '".$searchParameter['BUILDING_OWNER_ID']."'  AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'  JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."'";
				
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
				if ( (isset($searchParameter['BUILDING_OWNER_ID'] ) && $searchParameter['BUILDING_OWNER_ID'] !='') && 
					 (isset($searchParameter['BUILDING_ID'])&&	$searchParameter['BUILDING_ID']!='') && 
					  (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='') &&
					   (isset($searchParameter['INSPECTOR'] ) && $searchParameter['INSPECTOR'] !='' )){
						$searchWhere .=	" JOIN company as c ON i.COMPANY_ID = c.INSPECTION_COMPANY AND c.ID = '".$searchParameter['BUILDING_OWNER_ID']."'  JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'  ";
				}
				if ( (isset($searchParameter['BUILDING_OWNER_ID'] ) && $searchParameter['BUILDING_OWNER_ID'] !='') && 
					 (isset($searchParameter['BUILDING_ID'])&&	$searchParameter['BUILDING_ID']!='') && 
					  (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='') &&
					   (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='' )){
						$searchWhere .=	" JOIN company as c ON i.COMPANY_ID = c.INSPECTION_COMPANY AND c.ID = '".$searchParameter['BUILDING_OWNER_ID']."'  JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."'";
				}
				if ( (isset($searchParameter['BUILDING_OWNER_ID'] ) && $searchParameter['BUILDING_OWNER_ID'] !='') && 
					 (isset($searchParameter['BUILDING_ID'])&&	$searchParameter['BUILDING_ID']!='') && 
					  (isset($searchParameter['DOOR_BARCODE']) && $searchParameter['DOOR_BARCODE'] !='') &&
					   (isset($searchParameter['INSPECTOR'] ) && $searchParameter['INSPECTOR'] !='' )){
						$searchWhere .=	" JOIN company as c ON i.COMPANY_ID = c.INSPECTION_COMPANY AND c.ID = '".$searchParameter['BUILDING_OWNER_ID']."'  JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID  AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'  JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."'";
				}
				if ( (isset($searchParameter['BUILDING_OWNER_ID'] ) && $searchParameter['BUILDING_OWNER_ID'] !='') && 
					 (isset($searchParameter['DOOR_BARCODE'])&&	$searchParameter['DOOR_BARCODE']!='') && 
					  (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='') &&
					   (isset($searchParameter['INSPECTOR'] ) && $searchParameter['INSPECTOR'] !='' )){
							$searchWhere .= " JOIN company as c ON i.COMPANY_ID = c.INSPECTION_COMPANY AND c.ID = '".$searchParameter['BUILDING_OWNER_ID']."'  JOIN  building as b ON b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'  JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."'";
				}
				if ( (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !='') && 
					 (isset($searchParameter['BUILDING_ID'])&&	$searchParameter['BUILDING_ID']!='') && 
					  (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='') &&
					   (isset($searchParameter['INSPECTOR'] ) && $searchParameter['INSPECTOR'] !='' )){
						
						$searchWhere .= " JOIN building as b  ON  i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."' JOIN door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE  = '".$searchParameter['DOOR_BARCODE']."' ";
						
				}
				
				
				
					
			break;
			case 5:
				if((isset($searchParameter['BUILDING_OWNER_ID']) && $searchParameter['BUILDING_OWNER_ID'] !='') && (isset($searchParameter['BUILDING_ID']) && $searchParameter['BUILDING_ID']!='') && (isset($searchParameter['ADDRESS']) && $searchParameter['ADDRESS'] !='' ) && (isset($searchParameter['INSPECTOR']) && $searchParameter['INSPECTOR'] !='') && (isset($searchParameter['DOOR_BARCODE'] ) && $searchParameter['DOOR_BARCODE'] !=''))
				{
						
						$searchWhere .= 	" JOIN company as c ON i.COMPANY_ID = c.INSPECTION_COMPANY AND c.ID = '".$searchParameter['BUILDING_OWNER_ID']."'  JOIN  building as b ON i.BUILDING_ID = '".$searchParameter['BUILDING_ID']."' AND i.BUILDING_ID = b.ID AND b.ADDRESS_1 = '".$searchParameter['ADDRESS']."' AND i.INSPECTOR_ID = '".$searchParameter['INSPECTOR']."'  JOIN  door as d ON i.ID = d.INSPECTION_ID AND d.DOOR_BARCODE = '".$searchParameter['DOOR_BARCODE']."'";
					
				}
			break;
		}
		
		
		
	
		echo $finalSelectSearch  = $searchSql ." ".$searchWhere." ";////die;
		
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
}
?>