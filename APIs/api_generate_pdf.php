<?php
error_reporting(0);
set_time_limit(120);

define('IMAGE_UPLOAD_PATH','generate_pdf_images/');
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

$con1=mysql_connect("localhost","shailendra","doordata");
mysql_select_db("pdfexport",$con1) or die("selection failed");			

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
		json_response_basic(200,'Json string you sent is not valid!');
	}
		
	//SET THE METHOD IDENTIFIER INTO A LOCAL VARIABLE
	$methodIdentifier = $arrayPostData['methodIdentifier'];
	
	//error_log($methodIdentifier);
	
	//ROUTE THE API CALL ACCORDING TO THE METHOD IDENTIFIER
	switch ($methodIdentifier) {
		/***********************************************************************************
			NAME		 :uploadInspection
			FUNCTIONALITY:
			ATTRIBUTES	 :
			RETURN		 :100,200 WITH PROPER MESSAGE
		************************************************************************************/
		case "upload_insection_to_generate_pdf";
			//need to sent to java server
			$arrayPages=$arrayPostData['PAGES'];
			
			$arrayPostData1 = $arrayPostData['INSPECTION'];
			
			if(!isset($arrayPostData1['COMPANY_ID']) || empty($arrayPostData1['COMPANY_ID'])){
				json_response_basic(200,"COMPANY_ID can not be empty");
			}else{
				$COMPANY_ID = $arrayPostData1['COMPANY_ID'];
			}
			
			if(!isset($arrayPostData1['BUILDING_ID']) || empty($arrayPostData1['BUILDING_ID'])){
				json_response_basic(200,"BUILDING_ID can not be empty");
			}else{
				$BUILDING_ID = $arrayPostData1['BUILDING_ID'];
			}
			
			//echo "step1";
			
			//LOAD THE M_uploadInspectionDoor MODEL
			require_once('includes/models/M_generate_pdf.php');
			
			//echo "step2";
			
			//NOW INSERT NEW/UPDATE Inspection with door
			if(isset($_FILES['SIGNATURE_INSPECTOR']))
				$SIGNATURE_INSPECTOR=$_FILES['SIGNATURE_INSPECTOR'];
			else
				$SIGNATURE_INSPECTOR='';
			if(isset($_FILES['SIGNATURE_BUILDING']))
				$SIGNATURE_BUILDING=$_FILES['SIGNATURE_BUILDING'];
			else
				$SIGNATURE_BUILDING='';
			
			//echo "step3";

			$RESPONSE_4=M_Generate_PDF::uploadInspectionWithDoor($COMPANY_ID,$BUILDING_ID,$arrayPostData1,$SIGNATURE_INSPECTOR,$SIGNATURE_BUILDING);
			$ReturnData=M_Generate_PDF::$Data_Return;
			
			//echo "step4";
			
			
			if($RESPONSE_4!==false){
				//close previous connection
				mysql_close($con1);
				//get buililding info from 
				$con2=mysql_connect("localhost","root","cachalot08");
				mysql_select_db("doordata",$con2) or die("selection failed 2");			
				$strSqlGetBuildingInfo="
					SELECT * 
					FROM building
					WHERE ID= '".$BUILDING_ID."'
					LIMIT 1
				";
				$resultSetGetBuildingInfo=mysql_query($strSqlGetBuildingInfo);
				$recordsBuildingInfo=array();
				if(mysql_num_rows($resultSetGetBuildingInfo)>0){
					$recordsBuildingInfo=mysql_fetch_assoc($resultSetGetBuildingInfo);
				}
				//get inspector info
				$strSqlGetInspectorInfo="
					SELECT * 
					FROM employee
					WHERE ID= '".$arrayPostData1['INSPECTOR_ID']."'
					LIMIT 1
				";
				$resultSetGetInspectorInfo=mysql_query($strSqlGetInspectorInfo);
				$recordsInspectorInfo=array();
				if(mysql_num_rows($resultSetGetInspectorInfo)>0){
					$recordsInspectorInfo=mysql_fetch_assoc($resultSetGetInspectorInfo);
				}
				
				//get comany info
				$strSqlGetCompanyInfo="
					SELECT * 
					FROM company
					WHERE  	ID= '".$COMPANY_ID."'
					LIMIT 1
				";
				$resultSetGetCompanyInfo=mysql_query($strSqlGetCompanyInfo);
				$recordsCompanyInfo=array();
				if(mysql_num_rows($resultSetGetCompanyInfo)>0){
					$recordsCompanyInfo=mysql_fetch_assoc($resultSetGetCompanyInfo);
				}
				
				if(!empty($recordsCompanyInfo['PRIMARY_CONTACT'])){
					//get employee info
					$strSqlGetEmployeeInfo="
						SELECT * 
						FROM employee
						WHERE ID= '".$recordsCompanyInfo['PRIMARY_CONTACT']."'
						LIMIT 1
					";
					$resultSetGetEmployeeInfo=mysql_query($strSqlGetEmployeeInfo);
					$recordsEmployeeInfo=array();
					if(mysql_num_rows($resultSetGetEmployeeInfo)>0){
						$recordsEmployeeInfo=mysql_fetch_assoc($resultSetGetEmployeeInfo);
					}
				}
				
				//close previous connection
				mysql_close($con2);
				//connect new connection
				$con1=mysql_connect("localhost","shailendra","doordata");
				mysql_select_db("pdfexport",$con1) or die("selection failed 1");			
				
				//insert building info
				$strSqlCheckBuildingIdExists="
					select ID
					from building
					where ID='".$recordsBuildingInfo['ID']."' 
				";
				$resultCheckBuildingIdExists=mysql_query($strSqlCheckBuildingIdExists);
				if(mysql_num_rows($resultCheckBuildingIdExists)>0){
					//to update
					mysql_query("
						UPDATE`building`							
							SET
								`NAME`='".$recordsBuildingInfo['NAME']."' , 
								`ADDRESS_1`='".$recordsBuildingInfo['ADDRESS_1']."' , 
								`ADDRESS_2`='".$recordsBuildingInfo['ADDRESS_2']."' , 
								`CITY`='".$recordsBuildingInfo['CITY']."' , 
								`STATE`='".$recordsBuildingInfo['STATE']."' , 
								`COUNTRY`='".$recordsBuildingInfo['COUNTRY']."' , 
								`ZIP`='".$recordsBuildingInfo['ZIP']."' , 
								`SUMMARY`='".$recordsBuildingInfo['SUMMARY']."' , 
								`CUSTOMER_ID`='".$recordsBuildingInfo['CUSTOMER_ID']."' , 
								`PRIMARY_CONTACT`='".$recordsBuildingInfo['PRIMARY_CONTACT']."'  
							WHERE `ID`='".$recordsBuildingInfo['ID']."'
						"
					);
				}else{
					mysql_query("
						INSERT INTO `building` (
							`ID` ,
							`NAME` ,
							`ADDRESS_1` ,
							`ADDRESS_2` ,
							`CITY` ,
							`STATE` ,
							`COUNTRY` ,
							`ZIP` ,
							`SUMMARY` ,
							`CUSTOMER_ID` ,
							`PRIMARY_CONTACT`
						)
						VALUES (
							'".$recordsBuildingInfo['ID']."' , 
							'".$recordsBuildingInfo['NAME']."' , 
							'".$recordsBuildingInfo['ADDRESS_1']."' , 
							'".$recordsBuildingInfo['ADDRESS_2']."' , 
							'".$recordsBuildingInfo['CITY']."' , 
							'".$recordsBuildingInfo['STATE']."' , 
							'".$recordsBuildingInfo['COUNTRY']."' , 
							'".$recordsBuildingInfo['ZIP']."' , 
							'".$recordsBuildingInfo['SUMMARY']."' , 
							'".$recordsBuildingInfo['CUSTOMER_ID']."' , 
							'".$recordsBuildingInfo['PRIMARY_CONTACT']."'  
						)"
					);
				}
				
				
				$strSqlCheckInsptectorIdExists="
					select ID
 					from employee
					where ID='".$recordsInspectorInfo['ID']."' 
				";
				$resultCheckInspectorIdExists=mysql_query($strSqlCheckInsptectorIdExists);
				if(mysql_num_rows($resultCheckInspectorIdExists)>0){
					//to update
					//insert inspector info
					mysql_query("
						UPDATE `employee` 
						SET
							`FIRST_NAME`='".$recordsInspectorInfo['FIRST_NAME']."' , 
							`LAST_NAME`='".$recordsInspectorInfo['LAST_NAME']."' , 
							`LAST_LOGIN`='".$recordsInspectorInfo['LAST_LOGIN']."' , 
							`LICENSE_NUMBER`='".$recordsInspectorInfo['LICENSE_NUMBER']."' , 
							`EXPIRATION_DATE`='".$recordsInspectorInfo['EXPIRATION_DATE']."' , 
							`USER_ID`='".$recordsInspectorInfo['USER_ID']."' , 
							`COMPANY_ID`='".$recordsInspectorInfo['COMPANY_ID']."' , 
							`EMAIL`='".$recordsInspectorInfo['EMAIL']."' , 
							`PHONE`='".$recordsInspectorInfo['PHONE']."'  
						WHERE 
							`ID`='".$recordsInspectorInfo['ID']."'
					");
				}else{
					//insert inspector info
					mysql_query("
						INSERT INTO `employee` (
							`ID` ,
							`FIRST_NAME` ,
							`LAST_NAME` ,
							`LAST_LOGIN` ,
							`LICENSE_NUMBER` ,
							`EXPIRATION_DATE` ,
							`USER_ID` ,
							`COMPANY_ID` ,
							`EMAIL` ,
							`PHONE`
						) VALUES (
							'".$recordsInspectorInfo['ID']."' , 
							'".$recordsInspectorInfo['FIRST_NAME']."' , 
							'".$recordsInspectorInfo['LAST_NAME']."' , 
							'".$recordsInspectorInfo['LAST_LOGIN']."' , 
							'".$recordsInspectorInfo['LICENSE_NUMBER']."' , 
							'".$recordsInspectorInfo['EXPIRATION_DATE']."' , 
							'".$recordsInspectorInfo['USER_ID']."' , 
							'".$recordsInspectorInfo['COMPANY_ID']."' , 
							'".$recordsInspectorInfo['EMAIL']."' , 
							'".$recordsInspectorInfo['PHONE']."'  
						)"
					);
				}
				

				if(!empty($recordsCompanyInfo['PRIMARY_CONTACT'])){
					//update employee data from primary contact id
					$strSqlCheckInsptectorIdExists_PC="
						select ID
						from employee
						where ID='".$recordsCompanyInfo['PRIMARY_CONTACT']."' 
					";
					$resultCheckInspectorIdExists_PC=mysql_query($strSqlCheckInsptectorIdExists_PC);
					if(mysql_num_rows($resultCheckInspectorIdExists_PC)>0){
						//to update
						//insert inspector info
						mysql_query("
							UPDATE `employee` 
							SET
								`FIRST_NAME`='".$recordsEmployeeInfo['FIRST_NAME']."' , 
								`LAST_NAME`='".$recordsEmployeeInfo['LAST_NAME']."' , 
								`LAST_LOGIN`='".$recordsEmployeeInfo['LAST_LOGIN']."' , 
								`LICENSE_NUMBER`='".$recordsEmployeeInfo['LICENSE_NUMBER']."' , 
								`EXPIRATION_DATE`='".$recordsEmployeeInfo['EXPIRATION_DATE']."' , 
								`USER_ID`='".$recordsEmployeeInfo['USER_ID']."' , 
								`COMPANY_ID`='".$recordsEmployeeInfo['COMPANY_ID']."' , 
								`EMAIL`='".$recordsEmployeeInfo['EMAIL']."' , 
								`PHONE`='".$recordsEmployeeInfo['PHONE']."'  
							WHERE 
								`ID`='".$recordsEmployeeInfo['ID']."'
						");
					}else{
						//insert inspector info
						mysql_query("
							INSERT INTO `employee` (
								`ID` ,
								`FIRST_NAME` ,
								`LAST_NAME` ,
								`LAST_LOGIN` ,
								`LICENSE_NUMBER` ,
								`EXPIRATION_DATE` ,
								`USER_ID` ,
								`COMPANY_ID` ,
								`EMAIL` ,
								`PHONE`
							) VALUES (
								'".$recordsEmployeeInfo['ID']."' , 
								'".$recordsEmployeeInfo['FIRST_NAME']."' , 
								'".$recordsEmployeeInfo['LAST_NAME']."' , 
								'".$recordsEmployeeInfo['LAST_LOGIN']."' , 
								'".$recordsEmployeeInfo['LICENSE_NUMBER']."' , 
								'".$recordsEmployeeInfo['EXPIRATION_DATE']."' , 
								'".$recordsEmployeeInfo['USER_ID']."' , 
								'".$recordsEmployeeInfo['COMPANY_ID']."' , 
								'".$recordsEmployeeInfo['EMAIL']."' , 
								'".$recordsEmployeeInfo['PHONE']."'  
							)"
						);
					}
				}


				$strSqlCheckCompanyIdExists="
					select ID
 					from company
					where ID='".$recordsInspectorInfo['COMPANY_ID']."' 
				";
				$resultCheckCompanyIdExists=mysql_query($strSqlCheckCompanyIdExists);
				if(mysql_num_rows($resultCheckCompanyIdExists)>0){
					//to update
					//insert company info
					mysql_query(
						"
						UPDATE `company` 
						SET 
							`NAME`='".$recordsCompanyInfo['NAME']."' , 
							`ADDRESS_1`='".$recordsCompanyInfo['ADDRESS_1']."' , 
							`ADDRESS_2`='".$recordsCompanyInfo['ADDRESS_2']."' , 
							`CITY`='".$recordsCompanyInfo['CITY']."' , 
							`STATE`='".$recordsCompanyInfo['STATE']."' , 
							`ZIP`='".$recordsCompanyInfo['ZIP']."' , 
							`TYPE`='".$recordsCompanyInfo['TYPE']."' , 
							`INSPECTION_COMPANY`='".$recordsCompanyInfo['INSPECTION_COMPANY']."' , 
							`PRIMARY_CONTACT`='".$recordsCompanyInfo['PRIMARY_CONTACT']."' 
						WHERE 
							`ID`='".$recordsCompanyInfo['ID']."'			
						"
					);
				}else{
					//insert company info
					mysql_query(
						"
							INSERT INTO `company` (
								`ID` ,
								`NAME` ,
								`ADDRESS_1` ,
								`ADDRESS_2` ,
								`CITY` ,
								`STATE` ,
								`ZIP` ,
								`TYPE` ,
								`INSPECTION_COMPANY` ,
								`PRIMARY_CONTACT`
							)
							VALUES (
								'".$recordsCompanyInfo['ID']."' , 
								'".$recordsCompanyInfo['NAME']."' , 
								'".$recordsCompanyInfo['ADDRESS_1']."' , 
								'".$recordsCompanyInfo['ADDRESS_2']."' , 
								'".$recordsCompanyInfo['CITY']."' , 
								'".$recordsCompanyInfo['STATE']."' , 
								'".$recordsCompanyInfo['ZIP']."' , 
								'".$recordsCompanyInfo['TYPE']."' , 
								'".$recordsCompanyInfo['INSPECTION_COMPANY']."' , 
								'".$recordsCompanyInfo['PRIMARY_CONTACT']."' 
							)					
						"
					);
				}
				json_response_basic(100,$ReturnData);
			}else{
				json_response_basic(200,$ReturnData);
			}
				
		break;

		/***********************************************************************************
			NAME		 : upload door images.
			FUNCTIONALITY: fetches all dictionary related data
			ATTRIBUTES	 :
			RETURN		 :100,200 WITH PROPER MESSAGE
		************************************************************************************/
			case "upload_door_images_to_generate_pdf":
				//error_log(var_export($arrayPostData,true));	
				
				if(empty($arrayPostData['DOOR_ID'])){
					json_response_basic(200, 'Please provide door id');
				}
				else{
					$DOOR_ID = $arrayPostData['DOOR_ID'];
				}
				
				if(!isset($_FILES['PICTURE_FILE_1']) && !isset($_FILES['PICTURE_FILE_2'])){
					json_response_basic(200,"No files found for upload");
				}
				
				//delete previous images
				mysql_query("delete from picture where DOOR_ID='".$DOOR_ID."'");
				
			    if(isset($_FILES['PICTURE_FILE_1']) && $_FILES['PICTURE_FILE_1']['error']==0 && $_FILES['PICTURE_FILE_1']['size']>0){
					$PICTURE_FILE_1="pict_door_".$DOOR_ID."_1.png";
					if(move_uploaded_file($_FILES['PICTURE_FILE_1']['tmp_name'],IMAGE_UPLOAD_PATH.$PICTURE_FILE_1)){
						chmod(IMAGE_UPLOAD_PATH.$PICTURE_FILE_1, 777); 
						$sql_update_PICTURE_FILE_1=
							"INSERT INTO picture(DOOR_ID,PICTURE_FILE,CONTROL_NAME) VALUES('".$DOOR_ID."', '".$PICTURE_FILE_1."', 'Camera1_1')";
						$picInsertResult = mysql_query($sql_update_PICTURE_FILE_1);
						if(!$picInsertResult)
						{
							$me = mysql_error($sql_update_PICTURE_FILE_1);
							error_log($sql_update_PICTURE_FILE_1);
							error_log('Error during insert 1 into the picture table during pdf print: ' . $me, 0);
							json_response_basic(200,"Unable to record information on PICTURE_FILE_1");
						}
					}else{
						json_response_basic(200,"Unable to upload PICTURE_FILE_1");
					}
				}

			    if(isset($_FILES['PICTURE_FILE_2']) && $_FILES['PICTURE_FILE_2']['error']==0 && $_FILES['PICTURE_FILE_2']['size']>0){
					$PICTURE_FILE_2="pict_door_".$DOOR_ID."_2.png";
					if(move_uploaded_file($_FILES['PICTURE_FILE_2']['tmp_name'],IMAGE_UPLOAD_PATH.$PICTURE_FILE_2)){
						chmod(IMAGE_UPLOAD_PATH.$PICTURE_FILE_2, 777); 
						$sql_update_PICTURE_FILE_2=
							"INSERT INTO picture(DOOR_ID,PICTURE_FILE,CONTROL_NAME) VALUES('".$DOOR_ID."', '".$PICTURE_FILE_2."', 'Camera2_1')";
						
						$picInsertResult = mysql_query($sql_update_PICTURE_FILE_2);
						if(!$picInsertResult)
						{
							$me = mysql_error();
							error_log($sql_update_PICTURE_FILE_2);
							error_log('Error during insert 2 into the picture table during pdf print: ' . $me, 0);
							json_response_basic(200,"Unable to record information on PICTURE_FILE_2");
						}
					}else{
						json_response_basic(200,"Unable to upload PICTURE_FILE_2");
					}
				}
				json_response_basic(100,$DOOR_ID);
			break;

			case "delete_data":
				$INSPECTION_ID=$arrayPostData['INSPECTION_ID'];
				$str_sql_get_door_ids="
					SELECT id
					FROM door
					WHERE INSPECTION_ID='".$INSPECTION_ID."'
				";
				$result_sql_get_door_ids=mysql_query($str_sql_get_door_ids);
				$door_ids=0;
				if(mysql_num_rows($result_sql_get_door_ids)>0){
					$records_get_door_ids=array();
					while($fetch_door_ids=mysql_fetch_assoc($result_sql_get_door_ids)){
						$records_get_door_ids[]="'".$fetch_door_ids['id']."'";
					}	
					echo $door_ids=join(',',array_values($records_get_door_ids));	
				}
				
				if(!empty($door_ids)){
					mysql_query("DELETE FROM door_code WHERE DOOR_ID IN (".$door_ids.")");
					mysql_query("DELETE FROM door_type WHERE DOOR_ID IN (".$door_ids.")");
					mysql_query("DELETE FROM hardware WHERE DOOR_ID IN (".$door_ids.")");
					$strSqlGetPictures="
						SELECT *
						FROM `picture` 
						WHERE
							DOOR_ID IN ( ".$door_ids." )
					";
					$resultPictures=mysql_query($strSqlGetPictures);
					$recordsPictures=array();
					while($fetchPicutres=mysql_fetch_assoc($resultPictures)){
						//echo 'generate_pdf_images/'.$fetchPicutres['PICTURE_FILE'];
						unlink('generate_pdf_images/'.$fetchPicutres['PICTURE_FILE']);
					}
					mysql_query("DELETE FROM door WHERE ID IN (".$door_ids.")") or die(mysql_error());
					mysql_query("DELETE FROM picture WHERE DOOR_ID IN (".$door_ids.")");
					mysql_query("DELETE FROM inspection_other WHERE INSPECTION_ID='".$INSPECTION_ID."' ");
					$strSqlGetSignatures="
						SELECT *
						FROM `inspection`
						WHERE ID='".$INSPECTION_ID."'
					";
					$resultGetSignatures=mysql_query($strSqlGetSignatures);
					$recordsGetSignatures=array();
					while($fetchGetSignatures=mysql_fetch_assoc($resultGetSignatures)){
						//echo 'generate_pdf_images/'.$fetchGetSignatures['SIGNATURE_INSPECTOR'];
						//echo 'generate_pdf_images/'.$fetchGetSignatures['SIGNATURE_BUILDING'];
						unlink('generate_pdf_images/'.$fetchGetSignatures['SIGNATURE_INSPECTOR']);
						unlink('generate_pdf_images/'.$fetchGetSignatures['SIGNATURE_BUILDING']);
					}
					mysql_query("DELETE FROM inspection WHERE ID='".$INSPECTION_ID."' ");
				}
				json_response_basic(100,"delete successfully");
			break;
				
		/***********************************************************************************
			NAME		 :
			FUNCTIONALITY:
			ATTRIBUTES	 :
			RETURN		 :100,200 WITH PROPER MESSAGE
		************************************************************************************/
		default :
			json_response_basic(200,"The method you specified is not valid!");
			break;
	}		

} 
else {
	json_response_basic(200,"Unable to get the data!");
}

function json_response_basic($success,$error){
	$arrayResponseError =array(
								'success'=> $success, 
								'message'=> $error
							);
	echo $jsonResponseError = json_encode($arrayResponseError);
	exit();
}