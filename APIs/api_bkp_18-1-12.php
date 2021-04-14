<?php
error_reporting(-1);
/**************************************************************************************************************
	 *  *  *  *  *  *  *  *  *  *  *   I D E A V A T E   S O L U T I O N S   *  *  *  *  *  *  *  *   *   *
	***********************************************************************************************************
	 * Filename				 :	api.php
	 * Description 		  	 :  contain all the apis for BACONS MOUTH project
	 * External Files called :  NA
	 * Global Variables	  	 :  NA
	 * 
	 * Modification Log
	 * Date:20 SEPT    	 	Author:IDEVATE SOLUTIONS              		Description
	 * -------------------------------------------------------------------------------------------------------
	 * 
***************************************************************************************************************/
//INCLUDING THE CONFIGURATION FILE
require_once 'includes/config.php';
//INCLUDEING THE DATABASE FILE
require_once 'includes/database.php';

//GETTING THE JSON FROM DEVICES AND SET INTO A LOCAL VARIABLE
$dataJson='';
if($_POST) 
	$dataJson = $_POST['requestJSON'];
if(trim($dataJson) == '') {
	$dataJson = file_get_contents("php://input");
}

//print_r($dataJson);
if(trim($dataJson)){
		
	//CONVERT THE JSON INTO AN ARRAY
	$arrayPostData = json_decode($dataJson,true); 
	
	//CHECK FOR CORRECT JSON FORMAT STRING
	if(trim(!$arrayPostData)){
		$DB->json_response_basic(ERROR_CODE,'Json string you sent is not valid!');
	}
		
	//SET THE METHOD IDENTIFIER INTO A LOCAL VARIABLE
	$methodIdentifier = $arrayPostData['methodIdentifier'];
	
	//ROUTE THE API CALL ACCORDING TO THE METHOD IDENTIFIER
	switch ($methodIdentifier) {

		/***********************************************************************************
			NAME		 :getLogin
			FUNCTIONALITY:WILL GOING TO LOGIN THE USER
			ATTRIBUTES	 :LOGIN,PASSWORD
			RETURN		 :100,200 WITH PROPER MESSAGE
		************************************************************************************/
		case 'getLogin':
			if(!isset($arrayPostData['LOGIN']) || empty($arrayPostData['LOGIN']))
			{
				$DB->json_response_basic(ERROR_CODE,"Login Name can not be empty");
			}
			if(!isset($arrayPostData['PASSWORD']) || empty($arrayPostData['PASSWORD']))
			{
				$DB->json_response_basic(ERROR_CODE,"Password can not be empty");
			}
			$LOGIN   =$DB->escape_value($arrayPostData['LOGIN']);
			$PASSWORD=$DB->escape_value($arrayPostData['PASSWORD']);
			
			//NOW LOAD THE USER MODEL
			require_once('includes/models/M_user.php');
			//VERIFY THE LOGIN
			$result=Model_user::VerifyLogIn($LOGIN,md5($PASSWORD));
			if($result!==false)
			{
				$data=array();
				$data['ID']=$result;
				//NOW LOAD THE USER_ROLE MODEL
				require_once('includes/models/M_user_role.php');
				//GET THE ROLE ID
				$role_id=M_user_role::get_role($data['ID']);
				
				if($role_id!==false){
					$data['ROLE_ID']=$role_id['ROLE_ID'];	
					$data['ROLE_NAME']=$role_id['ROLE_NAME'];	
				}
				else{
					$DB->json_response_basic(ERROR_CODE,
												"Unable to get the role, please try again later");
				}
				//NOW LOAD THE USER_ROLE MODEL
				require_once('includes/models/M_employee.php');
				
				$dataEmp['EMPLOYEE_DETAIL'] = M_employee::get_user_info($data['ID']);
				
				$data['EMPLOYEE_ID'] = $dataEmp['EMPLOYEE_DETAIL']['ID'];
				$data['FIRST_NAME'] = $dataEmp['EMPLOYEE_DETAIL']['FIRST_NAME'];
				$data['LAST_NAME'] = $dataEmp['EMPLOYEE_DETAIL']['LAST_NAME'];
					 		
				$data['COMPANY_ID']=M_employee::get_company_id($data['ID']);
				$data['COMPANY_NAME']=M_employee::get_company_details($data['COMPANY_ID']);
				$data['success']=SUCCESS_CODE;
				$data['message']="You have been successfully loged in";
				$DB->json_response_adv($data);
			}
			else
			{
				$DB->json_response_basic(ERROR_CODE,"Invalid Credentials");
			}
			break;	
		//------------login ends-----------------//
		
		//------------building owner listing starts-----//
		case 'BuildingOwnerListing':
			if(!isset($arrayPostData['INSPECTION_COMPANY']) || empty($arrayPostData['INSPECTION_COMPANY']))
			{
				$DB->json_response_basic(ERROR_CODE,"INSPECTION_COMPANY	can not be empty");
			}
			
			$CUSTOMER_ID=$arrayPostData['INSPECTION_COMPANY'];
			//LOAD THE M_company MODEL
			require_once('includes/models/M_company.php');
			//PAGINATION CONFIGURATION
			$ID=M_company::get_builiding_owner_listing($CUSTOMER_ID);
			
			$DB->json_response_adv(
									array(
											'success'=> SUCCESS_CODE, 
											'message'=> $ID,
										 )
									);
			break;
		//------------building owner listing ends  -----//
			
		
		/***********************************************************************************
			NAME		 :getMyAccount
			FUNCTIONALITY:WILL GET MY ACCOUNT DETAIL
			ATTRIBUTES	 :USER ID AND COMPANY ID
			RETURN		 :100,200 WITH PROPER MESSAGE
		************************************************************************************/
		case 'getMyAccount':
			if(!isset($arrayPostData['USER_ID']) || empty($arrayPostData['USER_ID']))
			{
				$DB->json_response_basic(ERROR_CODE,"USER ID can not be empty");
			}
			//STORE THE USER ID INTO A LOCAL VARIABLE
			$USER_ID=$arrayPostData['USER_ID'];
			//NOW LOAD THE EMPLOYEED MODEL TO GET THE USER INFO
			require_once('includes/models/M_employee.php');
			$RESPONSE_1=M_employee::get_user_info($USER_ID);
			if($RESPONSE_1!==false)
			{
				$DB->json_response_basic(SUCCESS_CODE,$RESPONSE_1);
			}
			else
			{
				$DB->json_response_basic(ERROR_CODE,"This user id does not exists");
			}
			break;
		
		/***********************************************************************************
			NAME		 :updateMyAccount
			FUNCTIONALITY:WILL UPDATE MY ACCOUNT DETAIL
			ATTRIBUTES	 :USER ID AND COMPANY ID
			RETURN		 :100,200 WITH PROPER MESSAGE
		************************************************************************************/
		case 'updateMyAccount':
			if(!isset($arrayPostData['USER_ID']) || empty($arrayPostData['USER_ID']))
			{
				$DB->json_response_basic(ERROR_CODE,"USER ID can not be empty");
			}
			else{//STORE THE USER ID INTO A LOCAL VARIABLE
				$USER_ID=$arrayPostData['USER_ID'];
			}
			//STORE THE USER ID INTO A LOCAL VARIABLE
				$PASSWORD=$arrayPostData['PASSWORD'];
			
			$FIRST_NAME = $arrayPostData['FIRST_NAME'];
			$LAST_NAME = $arrayPostData['LAST_NAME'];
			$EMAIL = $arrayPostData['EMAIL'];
			$PHONE = $arrayPostData['PHONE'];

			//NOW LOAD THE EMPLOYEED MODEL TO GET THE USER INFO
			require_once('includes/models/M_employee.php');
			$RESPONSE_1=M_employee::update_user_info($USER_ID , $PASSWORD , $FIRST_NAME , $LAST_NAME , $EMAIL , $PHONE);
			if($RESPONSE_1==true)
			{
				$DB->json_response_basic(SUCCESS_CODE,"Account Updated successfully");
			}
			elseif($RESPONSE_1==10)
			{
				$DB->json_response_basic(ERROR_CODE,"Password Updation Failed");
			}
			elseif($RESPONSE_1==11)
			{
				$DB->json_response_basic(ERROR_CODE,"Updation Failed");
			}
			break;
		/***********************************************************************************
			NAME		 :buildingListing
			FUNCTIONALITY:WILL GET THE BUILDING LISTING OF A PARTICULAR COMPANY
			ATTRIBUTES	 :
			RETURN		 :100,200 WITH PROPER MESSAGE
		************************************************************************************/
		case "buildingListing":
			if(!isset($arrayPostData['INSPECTION_COMPANY']) || empty($arrayPostData['INSPECTION_COMPANY']))
			{
				$DB->json_response_basic(ERROR_CODE,"INSPECTION_COMPANY can not be empty");
			}
			$INSPECTION_COMPANY=$arrayPostData['INSPECTION_COMPANY'];
			//LOAD THE M_company MODEL
			require_once('includes/models/M_company.php');
			//BUILDING ONWERS IDS
			$RESPONSE_1=M_company::get_builiding_owner_listing($INSPECTION_COMPANY,0,30);
			//BUILDING LISTINGS
			$RESPONSE_2=array();
			if(count($RESPONSE_1)>0)
			{
				//NOW GET THE BUILDING OWNER IDS
				$CUSTOMER_ID=array();
				foreach($RESPONSE_1 as $r)
				{
					$CUSTOMER_ID[]=$r['ID'];	
				}
				
				//NOW GET THE BUILDGING LISTINGS
				//NOW LOAD THE BUILIDINGS MODEL
				require_once("includes/models/M_buildings.php");	
				$RESPONSE_2=M_buildings::get_buildings_listing(join(',',array_values($CUSTOMER_ID)));
			}
			$DB->json_response_basic(SUCCESS_CODE,$RESPONSE_2);
			break;
		/***********************************************************************************
			NAME		 :editBuildingOwner
			FUNCTIONALITY:
			ATTRIBUTES	 :
			RETURN		 :100,200 WITH PROPER MESSAGE
		************************************************************************************/
		case "editBuildingOwner":
			//THIS IS THE ID OF THE BUILDING OWNER AND WILL FOUND IN THE COMPANY TABLE
			if(!isset($arrayPostData['ID']) || empty($arrayPostData['ID']))
			{
				$DB->json_response_basic(ERROR_CODE,"ID can not be empty");
			}
			if(!isset($arrayPostData['NAME']) || empty($arrayPostData['NAME']))
			{
				$DB->json_response_basic(ERROR_CODE,"NAME can not be empty");
			}
			$ID		  		=$arrayPostData['ID'];
			$NAME	  		=$DB->escape_value($arrayPostData['NAME']);
			$ADDRESS_1		=$DB->escape_value($arrayPostData['ADDRESS_1']);
			//////////////////$ADDRESS_2		=$DB->escape_value($arrayPostData['ADDRESS_2']);
			$CITY	  		=$DB->escape_value($arrayPostData['CITY']);
			$STATE	  		=$DB->escape_value($arrayPostData['STATE']);
			$PRIMARY_CONTACT=NULL;
			//CHECK IF PRIMARY_CONTACT IS NOT EMPTY
			if(!empty($arrayPostData['PRIMARY_CONTACT']))
			{
				$PRIMARY_CONTACT=$DB->escape_value($arrayPostData['PRIMARY_CONTACT']);
				//LOAD THE EMPLOYEE MODEL
				require_once('includes/models/M_employee.php');

				//NOW CHECK IS THIS EMPLOYEE EXITS OR NOT
				$RESPONSE_3=M_employee::is_this_employee_exists($PRIMARY_CONTACT);
				if(!$RESPONSE_3)
				{
					$DB->json_response_basic(ERROR_CODE,"Please provide valid PRIMARY_CONTACT");
				}
			}
			//LOAD THE M_dictionary MODEL
			require_once('includes/models/M_dictionary.php');
			//NOW CHECK THE STATE THAT YOU ARE PROVIDING IS EXITS OR NOT
			$RESPONSE_2=M_dictionary::is_this_id_exists($STATE);
			if(!$RESPONSE_2)
			{
				$DB->json_response_basic(ERROR_CODE,"Please provide valid State");
			}
			//LOAD THE M_company MODEL
			require_once('includes/models/M_company.php');
			//NOW UPDATE THE OWNER
			$RESPONSE_1=M_company::update_owner($ID,$NAME,$ADDRESS_1,$CITY,$STATE,$PRIMARY_CONTACT);
			//IF UPDATE IS SUCC
			if($RESPONSE_1)
			{
				$DB->json_response_basic(SUCCESS_CODE,"Building Owner has been updated");
			}
			//IF UPDDATE IS UNSUCC
			else
			{
				$DB->json_response_basic(ERROR_CODE,"We are not able to update the owner, please try again later");
			}
			break;
		/***********************************************************************************
			NAME		 :addBuildingOwner
			FUNCTIONALITY:
			ATTRIBUTES	 :
			RETURN		 :100,200 WITH PROPER MESSAGE
		************************************************************************************/
		case "addBuildingOwner";
			if(!isset($arrayPostData['INSPECTION_COMPANY']) || empty($arrayPostData['INSPECTION_COMPANY']))
			{
				$DB->json_response_basic(ERROR_CODE,"INSPECTION_COMPANY can not be empty");
			}
			if(!isset($arrayPostData['NAME']) || empty($arrayPostData['NAME']))
			{
				$DB->json_response_basic(ERROR_CODE,"NAME can not be empty");
			}
			//THIS IS THE COMANY TABLE
			$company_table=array(
									'NAME',
									'ADDRESS_1',
									'ADDRESS_2',
									'CITY',
									'STATE',
									'TYPE',
									'INSPECTION_COMPANY',
									'PRIMARY_CONTACT'
								);
			//SANITIZED DATA
			$sanitized_data=array();
			foreach($company_table as $val)
			{
				//ADD TYPE TO BUILDING OWNER
				if($val=='TYPE')
				{
					$sanitized_data[$val]='1001';
					continue;
				}
				if(!empty($arrayPostData[$val]))
				{
					$sanitized_data[$val]="'".$DB->escape_value($arrayPostData[$val])."'";
				}
				
			}
			if(!empty($arrayPostData['PRIMARY_CONTACT']))
			{
				$PRIMARY_CONTACT=$DB->escape_value($arrayPostData['PRIMARY_CONTACT']);
				//LOAD THE EMPLOYEE MODEL
				require_once('includes/models/M_employee.php');

				//NOW CHECK IS THIS EMPLOYEE EXITS OR NOT
				$RESPONSE_3=M_employee::is_this_employee_exists($PRIMARY_CONTACT);
				if(!$RESPONSE_3)
				{
					$DB->json_response_basic(ERROR_CODE,"Please provide valid PRIMARY_CONTACT");
				}
			}
			//NOW CHECK FOR VALID STATE
			if(!empty($arrayPostData['STATE']))
			{
				//LOAD THE M_dictionary MODEL
				require_once('includes/models/M_dictionary.php');
				//NOW CHECK THE STATE THAT YOU ARE PROVIDING IS EXITS OR NOT
				$RESPONSE_2=M_dictionary::is_this_id_exists($arrayPostData['STATE']);
				if(!$RESPONSE_2)
				{
					$DB->json_response_basic(ERROR_CODE,"Please provide valid State");
				}
			}
			//LOAD THE M_company MODEL
			require_once('includes/models/M_company.php');
			//NOW INSERT THE OWNER
			$RESPONSE_3=M_company::insert_owner($sanitized_data);
			if($RESPONSE_3!==false)
			{
				$DB->json_response_basic(SUCCESS_CODE,$RESPONSE_3);
			}
			else
			{
				$DB->json_response_basic(ERROR_CODE,"We are not able to add a new owner,please try again later");
			}
			break;

		/***********************************************************************************
			NAME		 :addBuilding
			FUNCTIONALITY:
			ATTRIBUTES	 :
			RETURN		 :100,200 WITH PROPER MESSAGE
		************************************************************************************/
		case "addBuilding":
			//NAME OF THE BUILDING
			if(!isset($arrayPostData['NAME']) || empty($arrayPostData['NAME']))
			{
				$DB->json_response_basic(ERROR_CODE,"NAME can not be empty");
			}
			//CUSTOMER_ID IS THE BUILDING OWNER ID
			if(!isset($arrayPostData['CUSTOMER_ID']) || empty($arrayPostData['CUSTOMER_ID']))
			{
				$DB->json_response_basic(ERROR_CODE,"CUSTOMER_ID can not be empty");
			}
			//THIS IS THE BUILDING TABLE
			$building_table=array(
									'NAME',
									'ADDRESS_1',
									'CITY',
									'STATE',
									'ZIP',
									///////////'COUNTRY',
									'SUMMARY',
									'CUSTOMER_ID',
									'PRIMARY_CONTACT'
								);
			//SANITIZED DATA
			$sanitized_data=array();
			foreach($building_table as $val)
			{
				//CHECK IS THE STATE EXISTS 
				if(!empty($arrayPostData[$val]))
				{
					switch($val){
						case"STATE":						
							//LOAD THE M_dictionary MODEL
							require_once('includes/models/M_dictionary.php');
							$RESPONSE_1=M_dictionary::is_this_state_exists($arrayPostData['STATE']);
							if(!$RESPONSE_1)
							{
								$DB->json_response_basic(ERROR_CODE,"Please provide valid STATE");
							}
							//STORE THE STATE
							$sanitized_data[$val]="'".$DB->escape_value($arrayPostData[$val])."'";
							break;
						case"COUNTRY":
							//LOAD THE M_dictionary MODEL
							require_once('includes/models/M_dictionary.php');
							$RESPONSE_2=M_dictionary::is_this_country_exists($arrayPostData['COUNTRY']);
							if(!$RESPONSE_2)
							{
								$DB->json_response_basic(ERROR_CODE,"Please provide valid COUNTRY");
							}
							//STORE THE COUNTRY
							$sanitized_data[$val]="'".$DB->escape_value($arrayPostData[$val])."'";
							break;
						case"CUSTOMER_ID":
							//LOAD THE M_company MODEL
							require_once('includes/models/M_company.php');
							$RESPONSE_3=M_company::is_this_building_owner_exists($arrayPostData['CUSTOMER_ID']);
							if(!$RESPONSE_3)
							{
								$DB->json_response_basic(ERROR_CODE,"This CUSTOMER_ID does not exists");
							}
							//STORE THE COUNTRY
							$sanitized_data[$val]="'".$DB->escape_value($arrayPostData[$val])."'";
							break;
						default:
							$sanitized_data[$val]="'".$DB->escape_value($arrayPostData[$val])."'";
							break;
					}
				}
			}
			
			//LOAD THE BUILDING MODEL
			require_once('includes/models/M_buildings.php');
			//NOW INSERT NEW BUILDING
			$RESPONSE_4=M_buildings::insert_new_building($sanitized_data);
			if($RESPONSE_4!==false)
			{
				$DB->json_response_basic(SUCCESS_CODE,$RESPONSE_4);
			}
			else
			{
				$DB->json_response_basic(ERROR_CODE,"We are not able to insert new building, Please try agian later");
			}
			break;
			
		/***********************************************************************************
			NAME		 : editBuilding
			FUNCTIONALITY: editing building details 
			ATTRIBUTES	 :
			RETURN		 : 100,200 WITH PROPER MESSAGE
		************************************************************************************/
		case "editBuilding":
			//NAME OF THE BUILDING
			
			//CUSTOMER_ID IS THE BUILDING OWNER ID
			if(!isset($arrayPostData['BUILDING_ID']) || empty($arrayPostData['BUILDING_ID']))
			{
				$DB->json_response_basic(ERROR_CODE,"BUILDING_ID can not be empty");
			}
			//THIS IS THE BUILDING TABLE
			$building_table=array(
									'BUILDING_ID',
									'NAME',
									'ADDRESS_1',
									'CITY',
									'STATE',
									'ZIP',
									'SUMMARY',
									'CUSTOMER_ID',
									'PRIMARY_CONTACT'
								);
			//SANITIZED DATA
			$sanitized_data=array();
			foreach($building_table as $val)
			{
				//CHECK IS THE STATE EXISTS 
				if(!empty($arrayPostData[$val]))
				{
					switch($val){
						case "STATE":						
							//LOAD THE M_dictionary MODEL
							require_once('includes/models/M_dictionary.php');
							$RESPONSE_1=M_dictionary::is_this_state_exists($arrayPostData['STATE']);
							if(!$RESPONSE_1)
							{
								$DB->json_response_basic(ERROR_CODE,"Please provide valid STATE");
							}
							//STORE THE STATE
							$sanitized_data[$val]="'".$DB->escape_value($arrayPostData[$val])."'";
						
						case "CUSTOMER_ID":
							//LOAD THE M_company MODEL
							require_once('includes/models/M_company.php');
							$RESPONSE_3=M_company::is_this_building_owner_exists($arrayPostData['CUSTOMER_ID']);
							if(!$RESPONSE_3)
							{
								$DB->json_response_basic(ERROR_CODE,"This CUSTOMER_ID does not exists");
							}
							//STORE THE COUNTRY
							$sanitized_data[$val]="'".$DB->escape_value($arrayPostData[$val])."'";
							break;
							
						default:
							$sanitized_data[$val]="'".$DB->escape_value($arrayPostData[$val])."'";
							break;
					}
				}
			}
			
			//LOAD THE BUILDING MODEL
			require_once('includes/models/M_buildings.php');
			//NOW INSERT NEW BUILDING
			$RESPONSE_4=M_buildings::update_building($sanitized_data);
			if($RESPONSE_4!==false)
			{
				$DB->json_response_basic(SUCCESS_CODE,'Building edited successfully');
			}
			else
			{
				$DB->json_response_basic(ERROR_CODE,"We are not able to update building data, Please try agian later");
			}
			break;
		/***********************************************************************************
			NAME		 :getInspectionList
			FUNCTIONALITY:
			ATTRIBUTES	 :
			RETURN		 :100,200 WITH PROPER MESSAGE
		************************************************************************************/
			case "getInspectionList":
				//INSEPECTION COMPANY ID
				if(!isset($arrayPostData['COMPANY_ID']) || empty($arrayPostData['COMPANY_ID']))
				{
					$DB->json_response_basic(ERROR_CODE,"COMPANY_ID can not be empty");
				}
				$COMPANY_ID=$arrayPostData['COMPANY_ID'];
				//LOAD THE INSEPECTION MODEL
				require_once('includes/models/M_inspection.php');
				$RESPONSE_1=M_inspection::get_inspection_of_a_company($COMPANY_ID);
				//////print_r($RESPONSE_1);die;
				if(count($RESPONSE_1>0))
				{
					//LOAD THE M_dictionary
					require_once('includes/models/M_dictionary.php');
					//LOAD THE M_dictionary
					require_once('includes/models/M_buildings.php');
					//LOAD THE M_company
					require_once('includes/models/M_company.php');
						//LOAD THE M_employee
					require_once('includes/models/M_employee.php');
						//LOAD THE M_inspector
					require_once('includes/models/M_inspector.php');
					
					foreach($RESPONSE_1 as $k=>$v)
					{
						$RESPONSE_1[$k]['STATUS'] = M_dictionary::get_inspection_status($v['STATUS']);
						$RESPONSE_1[$k]['STATUS_ID'] = $v['STATUS'];
						$RESPONSE_buildingDetail['buildingDetail'] = M_buildings::get_building_name($v['BUILDING_ID']);
						$RESPONSE_1[$k]['BUILDING_NAME'] = $RESPONSE_buildingDetail['buildingDetail']['NAME'];
						$RESPONSE_BuildingOwnerDetail['BuildingOwnerDetail'] = M_company::get_building_owner($RESPONSE_buildingDetail['buildingDetail']['CUSTOMER_ID']);
						$RESPONSE_1[$k]['BUILDING_OWNER_ID'] = $RESPONSE_BuildingOwnerDetail['BuildingOwnerDetail']['ID'];
						$RESPONSE_1[$k]['BUILDING_OWNER_NAME'] = $RESPONSE_BuildingOwnerDetail['BuildingOwnerDetail']['NAME'];
						$RESPONSE_1[$k]['COMPANY_NAME']  = M_employee::get_company_details($COMPANY_ID);
						$RESPONSE_inspectorDetail['inspectorDetail'] = M_inspector::get_inspector_name($v['INSPECTOR_ID']);
						$RESPONSE_1[$k]['INSPECTOR_NAME'] = $RESPONSE_inspectorDetail['inspectorDetail']['LAST_NAME'];
						$RESPONSE_1[$k]['URL'] = IMAGE_DIR_ROOT; 
					}	
				}
			/*
				echo "<pre>";
				print_r($RESPONSE_1);die;*/
				$DB->json_response_basic(SUCCESS_CODE,$RESPONSE_1);
				break;
		/***********************************************************************************
			NAME		 :getInspectorList
			FUNCTIONALITY:
			ATTRIBUTES	 :
			RETURN		 :100,200 WITH PROPER MESSAGE
		************************************************************************************/
			case "getInspectorList":
				//INSEPECTION COMPANY ID
				if(!isset($arrayPostData['INSPECTION_COMPANY']) || empty($arrayPostData['INSPECTION_COMPANY']))
				{
					$DB->json_response_basic(ERROR_CODE,"INSPECTION_COMPANY can not be empty");
				}
				$COMPANY_ID=$arrayPostData['INSPECTION_COMPANY'];
				//LOAD THE INSEPECTION MODEL
				require_once('includes/models/M_inspector.php');
				$RESPONSE_1=M_inspector::get_inspector_of_a_company($COMPANY_ID);
				
				if($RESPONSE_1!==false)
					{
						$DB->json_response_basic(SUCCESS_CODE,$RESPONSE_1);
					}
					else
					{
						$DB->json_response_basic(ERROR_CODE,"Soory No Inspector is Found");
					}
			
				
			break;
		/***********************************************************************************
			NAME		 :
			FUNCTIONALITY:
			ATTRIBUTES	 :
			RETURN		 :100,200 WITH PROPER MESSAGE
		************************************************************************************/
		case 'getState':
			//LOAD THE DICTIONARY MODEL
			require_once('includes/models/M_dictionary.php');
			//GET THE STATES
			$RESPONSE_1=M_dictionary::get('State');
			if($RESPONSE_1!==false)
			{
				$DB->json_response_basic(SUCCESS_CODE,$RESPONSE_1);
			}
			else
			{
				$DB->json_response_basic(ERROR_CODE,"Error occured while getting the states,Please try again later");
			}
			break;
		/***********************************************************************************
			NAME		 :
			FUNCTIONALITY:
			ATTRIBUTES	 :
			RETURN		 :100,200 WITH PROPER MESSAGE
		************************************************************************************/
		case 'getCountry':
			//LOAD THE DICTIONARY MODEL
			require_once('includes/models/M_dictionary.php');
			//GET THE countries
			$RESPONSE_1=M_dictionary::get('Country');
			if($RESPONSE_1!==false)
			{
				$DB->json_response_basic(SUCCESS_CODE,$RESPONSE_1);
			}
			else
			{
				$DB->json_response_basic(ERROR_CODE,"Error occured while getting the countries,Please try again later");
			}
			break;
		
		/***********************************************************************************
			NAME		 :getMyAccount
			FUNCTIONALITY:WILL GET MY ACCOUNT DETAIL
			ATTRIBUTES	 :USER ID AND COMPANY ID
			RETURN		 :100,200 WITH PROPER MESSAGE
		************************************************************************************/
		case 'getMainContact':
			if(!isset($arrayPostData['BUILDING_OWNER']) || empty($arrayPostData['BUILDING_OWNER']))
			{
				$DB->json_response_basic(ERROR_CODE,"BUILDING OWNER can not be empty");
			}
			//STORE THE USER ID INTO A LOCAL VARIABLE
			$BUILDING_OWNER_ID=$arrayPostData['BUILDING_OWNER'];
			//NOW LOAD THE EMPLOYEED MODEL TO GET THE USER INFO
			require_once('includes/models/M_buildings.php');
			
					$RESPONSE_2=M_buildings::get_main_contact($BUILDING_OWNER_ID);
					/*echo "<pre>";
					print_r($RESPONSE_2);*/
					if($RESPONSE_2!==false){
						$DB->json_response_basic(SUCCESS_CODE,$RESPONSE_2);
					}
					else{
						$DB->json_response_basic(ERROR_CODE,"This BUILDING OWNER does not exists");
					}
			
			
			break;
		/***********************************************************************************
			NAME		 :search
			FUNCTIONALITY:WILL GET MY DETAIL ACCORDING TO SEARCH CRITERIA
			ATTRIBUTES	 :BUILDING OWNER ID OR BUILDING ID OR ADDRESS OR INSPECTOR OR DOOR BARCODE
			RETURN		 :100,200 WITH PROPER MESSAGE
		************************************************************************************/
		case 'getSearchResult':
		
			$searchParameter = array();
			if(isset($arrayPostData['COMPANY_ID']) || !empty($arrayPostData['COMPANY_ID']))
			{
				//STORE THE USER ID INTO A LOCAL VARIABLE
				$COMPANY_ID=$arrayPostData['COMPANY_ID'];
				///////////$searchParameter['COMPANY_ID'] = $COMPANY_ID;
			}
			else{
				$DB->json_response_basic(ERROR_CODE,"COMPANY ID CAN NOT BE BLANK");
			}
			if(isset($arrayPostData['BUILDING_OWNER']) || !empty($arrayPostData['BUILDING_OWNER']))
			{
				//STORE THE USER ID INTO A LOCAL VARIABLE
				$BUILDING_OWNER_ID=$arrayPostData['BUILDING_OWNER'];
				$searchParameter['BUILDING_OWNER_ID'] = $BUILDING_OWNER_ID;
			}
			else
			{
				$searchParameter['BUILDING_OWNER_ID'] = "";
			}
			if(isset($arrayPostData['BUILDING']) || !empty($arrayPostData['BUILDING']))
			{
				//STORE THE USER ID INTO A LOCAL VARIABLE
				$BUILDING_ID=$arrayPostData['BUILDING'];
				$searchParameter['BUILDING_ID'] = $BUILDING_ID;
			}
			else
			{
				$searchParameter['BUILDING_ID'] = "";
			}
			if(isset($arrayPostData['CITY']) || !empty($arrayPostData['CITY']))
			{
				//STORE THE USER ID INTO A LOCAL VARIABLE
				$CITY=$arrayPostData['CITY'];
					$searchParameter['CITY'] = $CITY;
			}
			else
			{
				$searchParameter['CITY'] = "";
			}
			if(isset($arrayPostData['STATE_ID']) || !empty($arrayPostData['STATE_ID']))
			{
				//STORE THE USER ID INTO A LOCAL VARIABLE
				$STATE=$arrayPostData['STATE_ID'];
					$searchParameter['STATE_ID'] = $STATE;
			}
			else
			{
				$searchParameter['STATE_ID'] ="";
			}
			if(isset($arrayPostData['ADDRESS']) || !empty($arrayPostData['ADDRESS']))
			{
				//STORE THE USER ID INTO A LOCAL VARIABLE
				$ADDRESS=$arrayPostData['ADDRESS'];
					$searchParameter['ADDRESS'] = $ADDRESS;
			}
			else
			{
				$searchParameter['ADDRESS'] ="";
			}
			if(isset($arrayPostData['INSPECTOR']) || !empty($arrayPostData['INSPECTOR']))
			{
				//STORE THE USER ID INTO A LOCAL VARIABLE
				$INSPECTOR_ID=$arrayPostData['INSPECTOR'];
					$searchParameter['INSPECTOR'] = $INSPECTOR_ID;
			}
			else
			{
				$searchParameter['INSPECTOR'] ="";
			}
			if(isset($arrayPostData['DOOR_BARCODE']) || !empty($arrayPostData['DOOR_BARCODE']))
			{
				//STORE THE USER ID INTO A LOCAL VARIABLE
				$DOOR_BARCODE=$arrayPostData['DOOR_BARCODE'];
					$searchParameter['DOOR_BARCODE'] = $DOOR_BARCODE;
			}
			else
			{
				$searchParameter['DOOR_BARCODE'] = "";
			}
			if(count($searchParameter)==0){
				$DB->json_response_basic(ERROR_CODE,"Also Please Provide Atleast One Parameter To Search With Company Id");
			}
			else{
				
				//NOW LOAD THE SEARCH MODEL TO GET THE USER INFO
				require_once('includes/models/M_search_building.php');
				
				$RESPONSE_1=M_search_building::get_search_result_inspection($searchParameter,$COMPANY_ID);
				if(count($RESPONSE_1>0) && $RESPONSE_1!=false)
					{
					//LOAD THE M_dictionary
					require_once('includes/models/M_dictionary.php');
					//LOAD THE M_dictionary
					require_once('includes/models/M_buildings.php');
					//LOAD THE M_company
					require_once('includes/models/M_company.php');
					//LOAD THE M_employee
					require_once('includes/models/M_employee.php');
					//LOAD THE M_inspector
					require_once('includes/models/M_inspector.php');
					
					
					foreach($RESPONSE_1 as $k=>$v)
					{
						$RESPONSE_inspectorDetail['inspectorDetail'] = M_inspector::get_inspector_name($v['INSPECTOR_ID']);
						$RESPONSE_1[$k]['INSPECTOR_NAME'] = $RESPONSE_inspectorDetail['inspectorDetail']['LAST_NAME'];
						$RESPONSE_1[$k]['STATUS'] = M_dictionary::get_inspection_status($v['STATUS']);
						$RESPONSE_1[$k]['STATUS_ID'] = $v['STATUS'];
						$RESPONSE_buildingDetail['buildingDetail'] = M_buildings::get_building_name($v['BUILDING_ID']);
						
						$RESPONSE_STATE = M_dictionary::getState($RESPONSE_buildingDetail['buildingDetail']['STATE']);
					
						$RESPONSE_1[$k]['STATE_NAME'] = $RESPONSE_STATE['ITEM'];
						$RESPONSE_1[$k]['BUILDING_NAME'] = $RESPONSE_buildingDetail['buildingDetail']['NAME'];
						$RESPONSE_1[$k]['BUILDING_ADDRESS'] = $RESPONSE_buildingDetail['buildingDetail']['ADDRESS_1'];
						$RESPONSE_1[$k]['BUILDING_CITY'] =  $RESPONSE_buildingDetail['buildingDetail']['CITY'];
						$RESPONSE_1[$k]['BUILDING_STATE'] =  $RESPONSE_buildingDetail['buildingDetail']['STATE'];
						$RESPONSE_1[$k]['BUILDING_ZIP'] =  $RESPONSE_buildingDetail['buildingDetail']['ZIP'];
						$RESPONSE_BuildingOwnerDetail['BuildingOwnerDetail'] = M_company::get_building_owner($RESPONSE_buildingDetail['buildingDetail']['CUSTOMER_ID']);
						$RESPONSE_1[$k]['BUILDING_OWNER_ID'] = $RESPONSE_BuildingOwnerDetail['BuildingOwnerDetail']['ID'];
						$RESPONSE_1[$k]['BUILDING_OWNER_NAME'] = $RESPONSE_BuildingOwnerDetail['BuildingOwnerDetail']['NAME'];
						$RESPONSE_1[$k]['COMPANY_NAME']  = M_employee::get_company_details($v['COMPANY_ID']);
						$add1 = explode(' ',$RESPONSE_1[$k]['BUILDING_ADDRESS']);
						$add = implode('+',$add1);
						$address = $add."+".$RESPONSE_1[$k]['BUILDING_CITY']."+".$RESPONSE_1[$k]['STATE_NAME']."+".$RESPONSE_1[$k]['BUILDING_ZIP'];
						///$geocode=file_get_contents('http://maps.google.com/maps/api/geocode/json?address='.$address.'&sensor=false');
						$ch = curl_init();
  						////////$timeout = 5;
						$url = 'http://maps.google.com/maps/api/geocode/json?address='.$address.'&sensor=false';
  						curl_setopt($ch,CURLOPT_URL,$url);
 						curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
 						////////// curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
  						$data = curl_exec($ch);

						if(!curl_errno($ch)){ 
							$geocode =  $data;
						 }else{
						   echo 'Curl error: ' . curl_error($ch); 
						 }
						curl_close($ch);
						$output= json_decode($geocode);
						//////////////////////echo "<pre>";print_r($output->results);
						if(!empty($output->results)){
						$latitude = $output->results[0]->geometry->location->lat;
						$RESPONSE_1[$k]['LATITUDE'] = $latitude;
						$longitude = $output->results[0]->geometry->location->lng;
						$RESPONSE_1[$k]['LONGITUDE'] = $longitude;
						  $RESPONSE_1[$k]['URL'] = IMAGE_DIR_ROOT; 
						}
						else{
								$RESPONSE_1[$k]['LATITUDE'] = 0;
						
							$RESPONSE_1[$k]['LONGITUDE'] = 0;
						}
					}	
				}
			
			/*//NOW LOAD THE SEARCH MODEL TO GET THE USER INFO
			require_once('includes/models/M_search.php');
					
					$RESPONSE_1=M_search::get_search_result_inspection($searchParameter , $COMPANY_ID);
					
					if(count($RESPONSE_1>0) && $RESPONSE_1!=false)
					{
					//LOAD THE M_dictionary
					require_once('includes/models/M_dictionary.php');
					//LOAD THE M_dictionary
					require_once('includes/models/M_buildings.php');
					//LOAD THE M_company
					require_once('includes/models/M_company.php');
					//LOAD THE M_employee
					require_once('includes/models/M_employee.php');
					//LOAD THE M_inspector
					require_once('includes/models/M_inspector.php');
					
					
					foreach($RESPONSE_1 as $k=>$v)
					{
						$RESPONSE_inspectorDetail['inspectorDetail'] = M_inspector::get_inspector_name($v['INSPECTOR_ID']);
						$RESPONSE_1[$k]['INSPECTOR_NAME'] = $RESPONSE_inspectorDetail['inspectorDetail']['LAST_NAME'];
						$RESPONSE_1[$k]['STATUS'] = M_dictionary::get_inspection_status($v['STATUS']);
						$RESPONSE_1[$k]['STATUS_ID'] = $v['STATUS'];
						$RESPONSE_buildingDetail['buildingDetail'] = M_buildings::get_building_name($v['BUILDING_ID']);
						
						$RESPONSE_STATE = M_dictionary::getState($RESPONSE_buildingDetail['buildingDetail']['STATE']);
					
						$RESPONSE_1[$k]['STATE_NAME'] = $RESPONSE_STATE['ITEM'];
						$RESPONSE_1[$k]['BUILDING_NAME'] = $RESPONSE_buildingDetail['buildingDetail']['NAME'];
						$RESPONSE_1[$k]['BUILDING_ADDRESS'] = $RESPONSE_buildingDetail['buildingDetail']['ADDRESS_1'];
						$RESPONSE_1[$k]['BUILDING_CITY'] =  $RESPONSE_buildingDetail['buildingDetail']['CITY'];
						$RESPONSE_1[$k]['BUILDING_STATE'] =  $RESPONSE_buildingDetail['buildingDetail']['STATE'];
						$RESPONSE_1[$k]['BUILDING_ZIP'] =  $RESPONSE_buildingDetail['buildingDetail']['ZIP'];
						$RESPONSE_BuildingOwnerDetail['BuildingOwnerDetail'] = M_company::get_building_owner($RESPONSE_buildingDetail['buildingDetail']['CUSTOMER_ID']);
						$RESPONSE_1[$k]['BUILDING_OWNER_ID'] = $RESPONSE_BuildingOwnerDetail['BuildingOwnerDetail']['ID'];
						$RESPONSE_1[$k]['BUILDING_OWNER_NAME'] = $RESPONSE_BuildingOwnerDetail['BuildingOwnerDetail']['NAME'];
						$RESPONSE_1[$k]['COMPANY_NAME']  = M_employee::get_company_details($v['COMPANY_ID']);
						$add1 = explode(' ',$RESPONSE_1[$k]['BUILDING_ADDRESS']);
						$add = implode('+',$add1);
						$address = $add."+".$RESPONSE_1[$k]['BUILDING_CITY']."+".$RESPONSE_1[$k]['STATE_NAME']."+".$RESPONSE_1[$k]['BUILDING_ZIP'];
						///$geocode=file_get_contents('http://maps.google.com/maps/api/geocode/json?address='.$address.'&sensor=false');
						$ch = curl_init();
  						////////$timeout = 5;
						$url = 'http://maps.google.com/maps/api/geocode/json?address='.$address.'&sensor=false';
  						curl_setopt($ch,CURLOPT_URL,$url);
 						curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
 						////////// curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
  						$data = curl_exec($ch);

						if(!curl_errno($ch)){ 
							$geocode =  $data;
						 }else{
						   echo 'Curl error: ' . curl_error($ch); 
						 }
						curl_close($ch);
						$output= json_decode($geocode);
						//////////////////////echo "<pre>";print_r($output->results);
						if(!empty($output->results)){
						$latitude = $output->results[0]->geometry->location->lat;
						$RESPONSE_1[$k]['LATITUDE'] = $latitude;
						$longitude = $output->results[0]->geometry->location->lng;
						$RESPONSE_1[$k]['LONGITUDE'] = $longitude;
						  $RESPONSE_1[$k]['URL'] = IMAGE_DIR_ROOT; 
						}
						else{
								$RESPONSE_1[$k]['LATITUDE'] = 0;
						
							$RESPONSE_1[$k]['LONGITUDE'] = 0;
						}
					}	
				}*/
				
				
					
					if($RESPONSE_1!=false){
						$DB->json_response_basic(SUCCESS_CODE,$RESPONSE_1);
					}
					else{
						$DB->json_response_basic(ERROR_CODE,"Nothing is found related to given parameters");
					}
					
					
					
			}
		/***********************************************************************************
			NAME		 :search for building owner
			FUNCTIONALITY:WILL GET MY DETAIL ACCORDING TO SEARCH CRITERIA
			ATTRIBUTES	 :BUILDING OWNER ID OR BUILDING ID OR ADDRESS OR INSPECTOR OR DOOR BARCODE
			RETURN		 :100,200 WITH PROPER MESSAGE
		************************************************************************************/
		case 'getSearchResultForBuildingOwner':

			$searchParameter = array();
			if(isset($arrayPostData['COMPANY_ID']) || !empty($arrayPostData['COMPANY_ID']))
			{
				//STORE THE USER ID INTO A LOCAL VARIABLE
				$COMPANY_ID=$arrayPostData['COMPANY_ID'];
				////$searchParameter['COMPANY_ID'] = $COMPANY_ID;
			}
			else{
				$DB->json_response_basic(ERROR_CODE,"COMPANY ID CAN NOT BE BLANK");
			}
			if(isset($arrayPostData['BUILDING_OWNER']) || !empty($arrayPostData['BUILDING_OWNER']))
			{
				//STORE THE USER ID INTO A LOCAL VARIABLE
				$BUILDING_OWNER_ID=$arrayPostData['BUILDING_OWNER'];
				$searchParameter['BUILDING_OWNER_ID'] = $BUILDING_OWNER_ID;
			}
			else
			{
				$searchParameter['BUILDING_OWNER_ID'] = "";
			}
			if(isset($arrayPostData['BUILDING']) || !empty($arrayPostData['BUILDING']))
			{
				//STORE THE USER ID INTO A LOCAL VARIABLE
				$BUILDING_ID=$arrayPostData['BUILDING'];
				$searchParameter['BUILDING_ID'] = $BUILDING_ID;
			}
			else
			{
				$searchParameter['BUILDING_ID'] = "";
			}
			if(isset($arrayPostData['CITY']) || !empty($arrayPostData['CITY']))
			{
				//STORE THE USER ID INTO A LOCAL VARIABLE
				$CITY=$arrayPostData['CITY'];
					$searchParameter['CITY'] = $CITY;
			}
			else
			{
				$searchParameter['CITY'] = "";
			}
			if(isset($arrayPostData['STATE_ID']) || !empty($arrayPostData['STATE_ID']))
			{
				//STORE THE USER ID INTO A LOCAL VARIABLE
				$STATE=$arrayPostData['STATE_ID'];
					$searchParameter['STATE_ID'] = $STATE;
			}
			else
			{
				$searchParameter['STATE_ID'] = "";
			}
			if(isset($arrayPostData['ADDRESS']) || !empty($arrayPostData['ADDRESS']))
			{
				//STORE THE USER ID INTO A LOCAL VARIABLE
				$ADDRESS=$arrayPostData['ADDRESS'];
					$searchParameter['ADDRESS'] = $ADDRESS;
			}
			else
			{
				$searchParameter['ADDRESS'] = "";
			}
			if(isset($arrayPostData['INSPECTOR']) || !empty($arrayPostData['INSPECTOR']))
			{
				//STORE THE USER ID INTO A LOCAL VARIABLE
				$INSPECTOR_ID=$arrayPostData['INSPECTOR'];
					$searchParameter['INSPECTOR'] = $INSPECTOR_ID;
			}
			else
			{
				$searchParameter['INSPECTOR'] ="";
			}
			if(isset($arrayPostData['DOOR_BARCODE']) || !empty($arrayPostData['DOOR_BARCODE']))
			{
				//STORE THE USER ID INTO A LOCAL VARIABLE
				$DOOR_BARCODE=$arrayPostData['DOOR_BARCODE'];
					$searchParameter['DOOR_BARCODE'] = $DOOR_BARCODE;
			}
			else
			{
				$searchParameter['DOOR_BARCODE'] ="";
			}
			if(count($searchParameter)==0){
				$DB->json_response_basic(ERROR_CODE,"Also Please Provide Atleast One Parameter To Search With Company Id");
			}
			else{
				
					//NOW LOAD THE SEARCH BUILDING MODEL TO GET THE BUILDING OWNER INFO
					require_once('includes/models/M_search_building.php');
			
					$RESPONSE_1=M_search_building::get_search_result_building_owner($searchParameter,$COMPANY_ID); ////die;
					
					/*$DB->json_response_adv(
									array(
											'success'=> SUCCESS_CODE, 
											'message'=> $RESPONSE_1,
										 )
									);*/
					if($RESPONSE_1 == false){
						$DB->json_response_basic(ERROR_CODE,'No Record Found');
					}
					else{
						$DB->json_response_basic(SUCCESS_CODE,$RESPONSE_1);
					}
					
						
			/*//NOW LOAD THE SEARCH MODEL TO GET THE USER INFO
			require_once('includes/models/M_search.php');
			
					$RESPONSE_1=M_search::get_search_result_building_owner($searchParameter , $COMPANY_ID); ////die;
					
					if(count($RESPONSE_1>0) && $RESPONSE_1!=false)
					{
					//LOAD THE M_dictionary
					require_once('includes/models/M_dictionary.php');
					//LOAD THE M_dictionary
					require_once('includes/models/M_buildings.php');
					//LOAD THE M_company
					require_once('includes/models/M_company.php');
					//LOAD THE M_employee
					require_once('includes/models/M_employee.php');
					//LOAD THE M_inspector
					require_once('includes/models/M_inspector.php');
					
					
					foreach($RESPONSE_1 as $k=>$v)
					{
						////$RESPONSE_inspectorDetail['inspectorDetail'] = M_inspector::get_inspector_name($v['INSPECTOR_ID']);
						////$RESPONSE_1[$k]['INSPECTOR_NAME'] = $RESPONSE_inspectorDetail['inspectorDetail']['LAST_NAME'];
						///$RESPONSE_1[$k]['STATUS'] = M_dictionary::get_inspection_status($v['STATUS']);
						///$RESPONSE_1[$k]['STATUS_ID'] = $v['STATUS'];
					//////	$RESPONSE_buildingDetail['buildingDetail'] = M_buildings::get_building_name($v['BUILDING_ID']);
						
						$RESPONSE_STATE = M_dictionary::getState($v['STATE']);
					
						$RESPONSE_1[$k]['STATE_NAME'] = $RESPONSE_STATE['ITEM'];
						////////$RESPONSE_1[$k]['BUILDING_NAME'] = $RESPONSE_buildingDetail['buildingDetail']['NAME'];
						////////$RESPONSE_1[$k]['BUILDING_ADDRESS'] = $RESPONSE_buildingDetail['buildingDetail']['ADDRESS_1'];
						/////////$RESPONSE_1[$k]['BUILDING_CITY'] =  $RESPONSE_buildingDetail['buildingDetail']['CITY'];
						/////////$RESPONSE_1[$k]['BUILDING_STATE'] =  $RESPONSE_buildingDetail['buildingDetail']['STATE'];
						/////////$RESPONSE_1[$k]['BUILDING_ZIP'] =  $RESPONSE_buildingDetail['buildingDetail']['ZIP'];
						////////$RESPONSE_BuildingOwnerDetail['BuildingOwnerDetail'] = M_company::get_building_owner($RESPONSE_buildingDetail['buildingDetail']['CUSTOMER_ID']);
						////////$RESPONSE_1[$k]['BUILDING_OWNER_ID'] = $RESPONSE_BuildingOwnerDetail['BuildingOwnerDetail']['ID'];
						//////////$RESPONSE_1[$k]['BUILDING_OWNER_NAME'] = $RESPONSE_BuildingOwnerDetail['BuildingOwnerDetail']['NAME'];
						$RESPONSE_1[$k]['INSPECTION_COMPANY_NAME']  = M_employee::get_company_details($v['INSPECTION_COMPANY']);
						///////$add1 = explode(' ',$RESPONSE_1[$k]['BUILDING_ADDRESS']);
					///////////	$add = implode('+',$add1);
						///////////////$address = $add."+".$RESPONSE_1[$k]['BUILDING_CITY']."+".$RESPONSE_1[$k]['STATE_NAME']."+".$RESPONSE_1[$k]['BUILDING_ZIP'];
						///$geocode=file_get_contents('http://maps.google.com/maps/api/geocode/json?address='.$address.'&sensor=false');
						/*$ch = curl_init();
  						////////$timeout = 5;
						$url = 'http://maps.google.com/maps/api/geocode/json?address='.$address.'&sensor=false';
  						curl_setopt($ch,CURLOPT_URL,$url);
 						curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
 						////////// curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
  						$data = curl_exec($ch);

						if(!curl_errno($ch)){ 
							$geocode =  $data;
						 }else{
						   echo 'Curl error: ' . curl_error($ch); 
						 }
						curl_close($ch);
						$output= json_decode($geocode);
						//////////////////////echo "<pre>";print_r($output->results);
						if(!empty($output->results)){
						$latitude = $output->results[0]->geometry->location->lat;
						$RESPONSE_1[$k]['LATITUDE'] = $latitude;
						$longitude = $output->results[0]->geometry->location->lng;
						$RESPONSE_1[$k]['LONGITUDE'] = $longitude;
						  $RESPONSE_1[$k]['URL'] = IMAGE_DIR_ROOT; 
						}
						else{
								$RESPONSE_1[$k]['LATITUDE'] = 0;
						
							$RESPONSE_1[$k]['LONGITUDE'] = 0;
						}
					}	
				}
				
				
					/*echo "<pre>";
					print_r($RESPONSE_1);
					echo "</pre>";
					if($RESPONSE_1!=false){
						$DB->json_response_basic(SUCCESS_CODE,$RESPONSE_1);
					}
					else{
						$DB->json_response_basic(ERROR_CODE,"Nothing is found related to given parameters");
					}
					
					
					*/
			}
		break;
		/***********************************************************************************
			NAME		 :search for building 
			FUNCTIONALITY:WILL GET MY DETAIL ACCORDING TO SEARCH CRITERIA
			ATTRIBUTES	 :BUILDING OWNER ID OR BUILDING ID OR ADDRESS OR INSPECTOR OR DOOR BARCODE
			RETURN		 :100,200 WITH PROPER MESSAGE
		************************************************************************************/
		case 'getSearchResultForBuilding':
		
			$searchParameter = array();
			if(isset($arrayPostData['COMPANY_ID']) || !empty($arrayPostData['COMPANY_ID']))
			{
				//STORE THE USER ID INTO A LOCAL VARIABLE
				$COMPANY_ID=$arrayPostData['COMPANY_ID'];
				$searchParameter['COMPANY_ID'] = $COMPANY_ID;
			}
			else{
				$DB->json_response_basic(ERROR_CODE,"COMPANY ID CAN NOT BE BLANK");
			}
			if(isset($arrayPostData['BUILDING_OWNER']) || !empty($arrayPostData['BUILDING_OWNER']))
			{
				//STORE THE USER ID INTO A LOCAL VARIABLE
				$BUILDING_OWNER_ID=$arrayPostData['BUILDING_OWNER'];
				$searchParameter['BUILDING_OWNER_ID'] = $BUILDING_OWNER_ID;
			}
			else
			{
				$searchParameter['BUILDING_OWNER_ID'] ="";
			}
			if(isset($arrayPostData['BUILDING']) || !empty($arrayPostData['BUILDING']))
			{
				//STORE THE USER ID INTO A LOCAL VARIABLE
				$BUILDING_ID=$arrayPostData['BUILDING'];
				$searchParameter['BUILDING_ID'] = $BUILDING_ID;
			}
			else
			{
				$searchParameter['BUILDING_ID'] = "";
			}
			if(isset($arrayPostData['STATE_ID']) || !empty($arrayPostData['STATE_ID']))
			{
					//STORE THE USER ID INTO A LOCAL VARIABLE
					$STATE_ID=$arrayPostData['STATE_ID'];
					$searchParameter['STATE_ID'] = $STATE_ID;
			}
			else
			{
				$searchParameter['STATE_ID'] = "";
			}
			if(isset($arrayPostData['CITY']) || !empty($arrayPostData['CITY']))
			{
					//STORE THE USER ID INTO A LOCAL VARIABLE
					$CITY=$arrayPostData['CITY'];
					$searchParameter['CITY'] = $CITY;
			}
			else
			{
				$searchParameter['CITY'] = "";
			}
			if(isset($arrayPostData['ADDRESS']) || !empty($arrayPostData['ADDRESS']))
			{
				//STORE THE USER ID INTO A LOCAL VARIABLE
				$ADDRESS=$arrayPostData['ADDRESS'];
				$searchParameter['ADDRESS'] = $ADDRESS;
			}
			else
			{
				$searchParameter['ADDRESS'] = "";
			}
			if(isset($arrayPostData['INSPECTOR']) || !empty($arrayPostData['INSPECTOR']))
			{
				//STORE THE USER ID INTO A LOCAL VARIABLE
				$INSPECTOR_ID=$arrayPostData['INSPECTOR'];
				$searchParameter['INSPECTOR'] = $INSPECTOR_ID;
			}
			else
			{
				$searchParameter['INSPECTOR'] = "";
			}
			if(isset($arrayPostData['DOOR_BARCODE']) || !empty($arrayPostData['DOOR_BARCODE']))
			{
				//STORE THE USER ID INTO A LOCAL VARIABLE
				$DOOR_BARCODE=$arrayPostData['DOOR_BARCODE'];
				$searchParameter['DOOR_BARCODE'] = $DOOR_BARCODE;
			}
			else
			{
				$searchParameter['DOOR_BARCODE'] = "";
			}
			if(count($searchParameter)==0){
				$DB->json_response_basic(ERROR_CODE,"Also Please Provide Atleast One Parameter To Search With Company Id");
			}
			else{
			//NOW LOAD THE SEARCH MODEL TO GET THE USER INFO
			require_once('includes/models/M_search_building.php');
			
			$RESPONSE_1=M_search_building::get_search_result_building($searchParameter);
			if($RESPONSE_1 == false){
				$DB->json_response_basic(ERROR_CODE,$RESPONSE_1);
			}
			else{
				$DB->json_response_basic(SUCCESS_CODE,$RESPONSE_1);
			}
					
					/*if(count($RESPONSE_1>0) && $RESPONSE_1!=false)
					{
					//LOAD THE M_dictionary
					require_once('includes/models/M_dictionary.php');
					//LOAD THE M_dictionary
					require_once('includes/models/M_buildings.php');
					//LOAD THE M_company
					require_once('includes/models/M_company.php');
					//LOAD THE M_employee
					require_once('includes/models/M_employee.php');
					//LOAD THE M_inspector
					require_once('includes/models/M_inspector.php');
					
					
					foreach($RESPONSE_1 as $k=>$v)
					{
						$RESPONSE_inspectorDetail['inspectorDetail'] = M_inspector::get_inspector_name($v['INSPECTOR_ID']);
						$RESPONSE_1[$k]['INSPECTOR_NAME'] = $RESPONSE_inspectorDetail['inspectorDetail']['LAST_NAME'];
						$RESPONSE_1[$k]['STATUS'] = M_dictionary::get_inspection_status($v['STATUS']);
						$RESPONSE_1[$k]['STATUS_ID'] = $v['STATUS'];
						$RESPONSE_buildingDetail['buildingDetail'] = M_buildings::get_building_name($v['BUILDING_ID']);
						
						$RESPONSE_STATE = M_dictionary::getState($RESPONSE_buildingDetail['buildingDetail']['STATE']);
					
						$RESPONSE_1[$k]['STATE_NAME'] = $RESPONSE_STATE['ITEM'];
						$RESPONSE_1[$k]['BUILDING_NAME'] = $RESPONSE_buildingDetail['buildingDetail']['NAME'];
						$RESPONSE_1[$k]['BUILDING_ADDRESS'] = $RESPONSE_buildingDetail['buildingDetail']['ADDRESS_1'];
						$RESPONSE_1[$k]['BUILDING_CITY'] =  $RESPONSE_buildingDetail['buildingDetail']['CITY'];
						$RESPONSE_1[$k]['BUILDING_STATE'] =  $RESPONSE_buildingDetail['buildingDetail']['STATE'];
						$RESPONSE_1[$k]['BUILDING_ZIP'] =  $RESPONSE_buildingDetail['buildingDetail']['ZIP'];
						$RESPONSE_BuildingOwnerDetail['BuildingOwnerDetail'] = M_company::get_building_owner($RESPONSE_buildingDetail['buildingDetail']['CUSTOMER_ID']);
						$RESPONSE_1[$k]['BUILDING_OWNER_ID'] = $RESPONSE_BuildingOwnerDetail['BuildingOwnerDetail']['ID'];
						$RESPONSE_1[$k]['BUILDING_OWNER_NAME'] = $RESPONSE_BuildingOwnerDetail['BuildingOwnerDetail']['NAME'];
						$RESPONSE_1[$k]['COMPANY_NAME']  = M_employee::get_company_details($v['COMPANY_ID']);
						$add1 = explode(' ',$RESPONSE_1[$k]['BUILDING_ADDRESS']);
						$add = implode('+',$add1);
						$address = $add."+".$RESPONSE_1[$k]['BUILDING_CITY']."+".$RESPONSE_1[$k]['STATE_NAME']."+".$RESPONSE_1[$k]['BUILDING_ZIP'];
					}	
				}
				
				if($RESPONSE_1!=false){
					$DB->json_response_basic(SUCCESS_CODE,$RESPONSE_1);
				}
				else{
					$DB->json_response_basic(ERROR_CODE,"Nothing is found related to given parameters");
				}*/				
			}
		break;

		/***********************************************************************************
			NAME		 : getDoorData
			FUNCTIONALITY: fetches all data related to door of perticular inspection
			ATTRIBUTES	 :
			RETURN		 :100,200 WITH PROPER MESSAGE
		************************************************************************************/
			case "getDoorData":
				//INSEPECTION COMPANY ID
				if(!isset($arrayPostData['INSPECTION_ID']) || empty($arrayPostData['INSPECTION_ID']))
				{
					$DB->json_response_basic(ERROR_CODE,"INSPECTION_ID can not be empty");
				}
				$INSPECTION_ID=$arrayPostData['INSPECTION_ID'];
				//LOAD THE INSEPECTION MODEL
				require_once('includes/models/M_door.php');
				$RESPONSE_1=M_door::get_door_data($INSPECTION_ID);
				/*echo "<pre>";
				print_r($RESPONSE_1);*/
				if($RESPONSE_1!==false)
					{
						$DB->json_response_basic(SUCCESS_CODE,$RESPONSE_1);
					}
					else
					{
						$DB->json_response_basic(ERROR_CODE,"Sorry No Door Data is Found");
					}
			
				
		    break;
		/***********************************************************************************
			NAME		 : getDictionaryData
			FUNCTIONALITY: fetches all dictionary related data
			ATTRIBUTES	 :
			RETURN		 :100,200 WITH PROPER MESSAGE
		************************************************************************************/
			case "getDictionaryData":				
				require_once('includes/models/M_dictionary.php');
				$RESPONSE_1=M_dictionary::get_all_dictionary_data();
				
				if($RESPONSE_1!==false)
					{
						$DB->json_response_basic(SUCCESS_CODE,$RESPONSE_1);
					}
					else
					{
						$DB->json_response_basic(ERROR_CODE,"Soory No Dictionary Data is Found");
					}
			
				
				break;
						
				break;
		/***********************************************************************************
			NAME		 : forgetPassword
			FUNCTIONALITY: fetches all dictionary related data
			ATTRIBUTES	 :
			RETURN		 :100,200 WITH PROPER MESSAGE
		************************************************************************************/
			case "forgetPassword":
			
				//INSEPECTION COMPANY ID
				if(!isset($arrayPostData['LOGIN_NAME']) || empty($arrayPostData['LOGIN_NAME']))
				{
					$DB->json_response_basic(ERROR_CODE,"LOGIN_NAME can not be empty");
				}
				$LOGIN_NAME=$arrayPostData['LOGIN_NAME'];
				require_once('includes/models/M_forgetPassword.php');
				
				$RESPONSE_1=M_forgetPassword::is_login_name_exist($LOGIN_NAME);
				if($RESPONSE_1!=false){
					$RESPONSE_2=M_forgetPassword::setPassword($RESPONSE_1['ID']);
					if($RESPONSE_2!=false){
						$DB->json_response_basic(SUCCESS_CODE,"set password");
					}
					else{
						$DB->json_response_basic(ERROR_CODE,"Sorry, please try again");
					}
				}
				else{
						$DB->json_response_basic(ERROR_CODE,"Sorry Login Name not exist");
				}
		break;
		/***********************************************************************************
			NAME		 :uploadInspection
			FUNCTIONALITY:
			ATTRIBUTES	 :
			RETURN		 :100,200 WITH PROPER MESSAGE
		************************************************************************************/
		case "uploadInspection";
			$arrayPostData1 = $arrayPostData['INSPECTION'];
			//echo "<pre>";
			
			//print_r($arrayPostData1);
			
			if(!isset($arrayPostData1['COMPANY_ID']) || empty($arrayPostData1['COMPANY_ID']))
			{
				$DB->json_response_basic(ERROR_CODE,"COMPANY_ID can not be empty");
			}
			else
			{
				$COMPANY_ID = $arrayPostData1['COMPANY_ID'];
			}
			
			if(!isset($arrayPostData1['BUILDING_ID']) || empty($arrayPostData1['BUILDING_ID']))
			{
				$DB->json_response_basic(ERROR_CODE,"BUILDING_ID can not be empty");
			}
			else{
				$BUILDING_ID = $arrayPostData1['BUILDING_ID'];
			}
			
			//LOAD THE M_uploadInspectionDoor MODEL
			require_once('includes/models/M_uploadInspectionDoor.php');
			
			//NOW INSERT NEW/UPDATE Inspection with door
			$RESPONSE_4=M_uploadInspectionDoor::uploadInspectionWithDoor($COMPANY_ID,$BUILDING_ID,$arrayPostData1);
			
			if($RESPONSE_4!==false)
			{
				$DB->json_response_basic(SUCCESS_CODE,$RESPONSE_4);
			}
			else
			{
				$DB->json_response_basic(ERROR_CODE,"We are not able to update inspedtion/door, Please try agian later");
			}
				
		break;
		/***********************************************************************************
			NAME		 : Download inpection with door information
			FUNCTIONALITY: fetches all dictionary related data
			ATTRIBUTES	 :
			RETURN		 :100,200 WITH PROPER MESSAGE
		************************************************************************************/
			case "downloadInspection":
			
				//INSEPECTION COMPANY ID
				if(!isset($arrayPostData['COMPANY_ID']) || empty($arrayPostData['COMPANY_ID']))
				{
					$DB->json_response_basic(ERROR_CODE,"COMPANY_ID can not be empty");
				}
				else
				{
					$COMPANY_ID=$arrayPostData['COMPANY_ID'];
				}
				if(!isset($arrayPostData['INSPECTION_ID']) || empty($arrayPostData['INSPECTION_ID']))
				{
					$DB->json_response_basic(ERROR_CODE,"INSPECTION_ID can not be empty");
				}
				else
				{
					$INSPECTION_ID=$arrayPostData['INSPECTION_ID'];
				}
				
				//LOAD THE INSEPECTION MODEL
				require_once('includes/models/M_download_inspection.php');
				$RESPONSE_1=M_download_inspection::download_inspection_detail_by_inspection_id($COMPANY_ID,$INSPECTION_ID);
				//print_r($RESPONSE_1);die;
				
				if($RESPONSE_1!==false)
					{
						$DB->json_response_basic(SUCCESS_CODE,$RESPONSE_1);
					}
					else
					{
						$DB->json_response_basic(ERROR_CODE,"Sorry No Inpection Data is Found");
					}
					
		/***********************************************************************************
			NAME		 : Sync Inspection with door.
			FUNCTIONALITY: fetches all dictionary related data
			ATTRIBUTES	 :
			RETURN		 :100,200 WITH PROPER MESSAGE
		************************************************************************************/
			case "syncInspection":
			
				$arrayPostDataInspection = $arrayPostData['INSPECTION'];
				$DB->query("BEGIN");
					
				foreach($arrayPostDataInspection as $arrayPostData1)
				{						
					if(!isset($arrayPostData1['COMPANY_ID']) || empty($arrayPostData1['COMPANY_ID']))
					{
						$DB->json_response_basic(ERROR_CODE,"COMPANY_ID can not be empty");
					}
					else
					{
						$COMPANY_ID = $arrayPostData1['COMPANY_ID'];
					}
					
					if(!isset($arrayPostData1['BUILDING_ID']) || empty($arrayPostData1['BUILDING_ID']))
					{
						$DB->json_response_basic(ERROR_CODE,"BUILDING_ID can not be empty");
					}
					else{
						$BUILDING_ID = $arrayPostData1['BUILDING_ID'];
					}
					
					//LOAD THE M_uploadInspectionDoor MODEL
					require_once('includes/models/M_uploadInspectionDoor.php');
					
					//NOW INSERT NEW/UPDATE Inspection with door
					$RESPONSE_4=M_uploadInspectionDoor::uploadInspectionWithDoor($COMPANY_ID,$BUILDING_ID,$arrayPostData1);
					
					if($RESPONSE_4==false)
					{
						mysql_query("ROLLBACK");
					}
				}
				
				if($RESPONSE_4!==false)
				{
					mysql_query("COMMIT");
					
					require_once('includes/models/M_sync_inspection.php');
					$RESPONSE_1=M_sync_inspection::sync_inspection_detail_by_company_id($COMPANY_ID);
					
					if($RESPONSE_1!==false)
					{
						$DB->json_response_basic(SUCCESS_CODE,$RESPONSE_1);
					}
					else
					{
						$DB->json_response_basic(ERROR_CODE,"Sorry No inpection data is Found");
					}
					
				}
				else
				{
					$DB->json_response_basic(ERROR_CODE,"We are not able to update inspections, Please try again later");
				}
				break;
				
		/***********************************************************************************
			NAME		 :
			FUNCTIONALITY:
			ATTRIBUTES	 :
			RETURN		 :100,200 WITH PROPER MESSAGE
		************************************************************************************/
		default :
			$DB->json_response_basic(ERROR_CODE,"The method you specified is not valid!");
			break;
	}

} 
else {
		$DB->json_response_basic(ERROR_CODE,"Unable to get the data!");
}
