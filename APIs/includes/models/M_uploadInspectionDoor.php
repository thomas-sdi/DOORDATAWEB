<?php
/*require_once('../config.php');
require_once('../database.php');
*/
class M_uploadInspectionDoor
{
	public static $Data_Return=array();
	
	private static $door_numbers = array();
	
	public static function arrayValue($array, $key){
		if(array_key_exists($key, $array)) return str_replace('”', "'", str_replace('“', "'", $array[$key]));
		else return null;
	}
	
	public static function arrayValueArray($array, $key){
		$value = self::arrayValue($array, $key);
		if (is_null($value)) 
			return array(); 
		else 
			return $value;
	}
	
	public static function arrayValueZero($array, $key){	
		$value = self::arrayValue($array, $key);
		return (strlen($value) > 0) ? (($value !== '0' && $value !== 0) ? $value : '') : '';
		
	}
	
	/**
	 * This function is needed in order to format the door number to make sure
	 * - it is no longer than 20 symbols
	 * - there are no duplicated door numbers within the same inspection
	 */
	public static function formatDoorNumber($NUMBER){
		$NUMBER = ltrim(rtrim($NUMBER));
		
		//door number should only be 30 symbols (database allows 35)
		if (strlen($NUMBER) > 30){
			$NUMBER = substr($NUMBER, 0, 30);
		}
		
		//now check if the door number is unique within the inspection
		if (array_key_exists($NUMBER, self::$door_numbers)){
			//this means the door with such number was already recorded. let's add a sequential number to the door number
			self::$door_numbers[$NUMBER] += 1;
			$NUMBER = $NUMBER . '_' . self::$door_numbers[$NUMBER];
		}
		else {
			self::$door_numbers[$NUMBER] = 1;
		}
		
		return $NUMBER;
	}
	
	
	
	public static function uploadInspectionWithDoor($COMPANY_ID,$BUILDING_ID,$arrayPostData1,$SIGNATURE_INSPECTOR_IMG = null,$SIGNATURE_BUILDING_IMG = null)
	{
		
		global $DB;
		
		self::$door_numbers = null;
		self::$door_numbers = array();
		
		
			//add the building id in the response
		self::$Data_Return['methodIdentifier']="uploadInspection";

		
			// INPECTION DETAIL PARAMETER

		$INSPECTION_ID = self::arrayValue($arrayPostData1, 'INSPECTION_ID');
		$INSPECTION_DATE = self::arrayValue($arrayPostData1, 'INSPECTION_DATE');
		$INSPECTION_COMPLETE_DATE = self::arrayValue($arrayPostData1, 'INSPECTION_COMPLETE_DATE');
		$REINSPECT_DATE = self::arrayValue($arrayPostData1, 'REINSPECT_DATE');
		$STATUS = self::arrayValue($arrayPostData1, 'STATUS');
		$COMPANY_ID = self::arrayValue($arrayPostData1, 'COMPANY_ID');
		$BUILDING_ID = self::arrayValue($arrayPostData1, 'BUILDING_ID');
		$INSPECTOR_ID  = self::arrayValue($arrayPostData1, 'INSPECTOR_ID');
		$TEMPLATE_ID = self::arrayValue($arrayPostData1, 'TEMPLATE_ID');
		
		$SIGNATURE_INSPECTOR_DATE = self::arrayValue($arrayPostData1, 'SIGNATURE_INSPECTOR_DATE');
		$SIGNATURE_BUILDING_DATE = self::arrayValue($arrayPostData1, 'SIGNATURE_BUILDING_DATE');


			//$SUMMARY = mysql_real_escape_string(self::arrayValue($arrayPostData1, 'SUMMARY'));
		$SUMMARY = $DB->mysqli_escape_value(self::arrayValue($arrayPostData1, 'SUMMARY'));

			// PARATMETER NOT SEND BY JSON

		$SIGNATURE_INSPECTOR ="";
		$SIGNATURE_STROKES_INSPECTOR ="";
		$SIGNATURE_BUILDING ="";
		$SIGNATURE_STROKES_BUILDING ="";
		$PDF = "";				


		$INSPECTION_OPERATION_TYPE = $arrayPostData1['INSPECTION_OPERATION_TYPE'];

			//support for the "COMPLETE" operation
		if ($INSPECTION_OPERATION_TYPE == 'NEW' || $INSPECTION_OPERATION_TYPE == 'UPLOAD'){
			error_log('The mobile app is trying to finish inspection with INSPECTION_OPERATION_TYPE ="' . $INSPECTION_OPERATION_TYPE . '"  and INSPECTION_ID="' . $INSPECTION_ID . '"');
		}

		if ($INSPECTION_OPERATION_TYPE == 'SAVE'){
			if ($INSPECTION_ID === null){
				$INSPECTION_OPERATION_TYPE = 'NEW';
			}
			else {
				$INSPECTION_OPERATION_TYPE = 'UPDATE';
			}
				$STATUS = 1078; //inspection type assigned
				
				error_log('The mobile app is trying to save  inspection with INSPECTION_OPERATION_TYPE ="' . $INSPECTION_OPERATION_TYPE . '"  and INSPECTION_ID="' . $INSPECTION_ID . '"');
			}
			
			//TODO: this part below needs to be removed after an update for the mobile app is published in apple store
			if (!is_null($INSPECTION_ID) && $INSPECTION_ID >= 1000000){
				//this means that this inspection id is actually from a pdfexport database and should be disregarded when the inspection is uploaded to the doordata database
				$INSPECTION_ID = null;
				$INSPECTION_OPERATION_TYPE = 'NEW';
			}
			
			
			if($INSPECTION_OPERATION_TYPE=="NEW") //this is a new inspection and it needs to be added to the database as insert
			{
				
				if (!is_null($INSPECTION_ID)){
					//NEW inspection should come with empty inspection id. Have to generate an error
					self::$Data_Return = 'Trying to save NEW inspection, but the INSPECTION_ID is already provided: ' . $INSPECTION_ID . ', INSPECTION_ID should be empty in this case.';
					error_log('Error during inspection insert: ' . self::$Data_Return, 0);
					return false;
				}
				
				// adding new inpection 
				$sql_inpection = "INSERT INTO `inspection` set
				REINSPECT_DATE = IF(LENGTH('".$REINSPECT_DATE."')=0,NULL,'".$REINSPECT_DATE."'),
				INSPECTION_DATE = IF(LENGTH('".$INSPECTION_DATE."')=0,NULL,'".$INSPECTION_DATE."'),
				INSPECTION_COMPLETE_DATE = IF(LENGTH('".$INSPECTION_COMPLETE_DATE."')=0,NULL,'".$INSPECTION_COMPLETE_DATE."'),
				BUILDING_ID = IF(LENGTH('".$BUILDING_ID."')=0,NULL,'".$BUILDING_ID."'),
				COMPANY_ID = IF(LENGTH('".$COMPANY_ID."')=0,NULL,'".$COMPANY_ID."'),
				SIGNATURE_INSPECTOR = IF(LENGTH('".$SIGNATURE_INSPECTOR."')=0,NULL,'".$SIGNATURE_INSPECTOR."'),
				SIGNATURE_STROKES_INSPECTOR = IF(LENGTH('".$SIGNATURE_STROKES_INSPECTOR."')=0,NULL,'".$SIGNATURE_STROKES_INSPECTOR."'),
				SIGNATURE_BUILDING = IF(LENGTH('".$SIGNATURE_BUILDING."')=0,NULL,'".$SIGNATURE_BUILDING."'),
				SIGNATURE_STROKES_BUILDING = IF(LENGTH('".$SIGNATURE_STROKES_BUILDING."')=0,NULL,'".$SIGNATURE_STROKES_BUILDING."'),
				STATUS = IF(LENGTH('".$STATUS."')=0,NULL,'".$STATUS."'),
				SUMMARY = IF(LENGTH('".$SUMMARY."')=0,NULL,'".$SUMMARY."'),
				PDF = IF(LENGTH('".$PDF."')=0,NULL,'".$PDF."'),
				INSPECTOR_ID = IF(LENGTH('".$INSPECTOR_ID."')=0,NULL,'".$INSPECTOR_ID."'),
				TEMPLATE_ID = IF(LENGTH('".$TEMPLATE_ID."')=0,NULL,'".$TEMPLATE_ID."'),
				SIGNATURE_INSPECTOR_DATE = IF(LENGTH('".$SIGNATURE_INSPECTOR_DATE."')=0,NULL,'".$SIGNATURE_INSPECTOR_DATE."'),
				SIGNATURE_BUILDING_DATE = IF(LENGTH('".$SIGNATURE_BUILDING_DATE."')=0,NULL,'".$SIGNATURE_BUILDING_DATE."')";			


				$result_inpection=$DB->query($sql_inpection);
				
				if(!$result_inpection)
				{
					self::$Data_Return = $DB->get_error_message();
					error_log('Error during inspection insert: ' . self::$Data_Return, 0);
					return false;
				}
				
				
				self::$Data_Return['INSPECTION_ID']=$INSPECTION_ID = $DB->insert_id();
				error_log('Newly generated inspection id:' . $INSPECTION_ID);
				
				//upload the SIGNATURE_INSPECTOR
				if(!is_null($SIGNATURE_INSPECTOR_IMG))
				{
					//error_log('Inspector signature file is sent for NEW inspection ' . $INSPECTION_ID);
					
					$SIGNATURE_INSPECTOR_NAME="sign_inspector_".$INSPECTION_ID.".png";
					if(move_uploaded_file($SIGNATURE_INSPECTOR_IMG['tmp_name'],IMAGE_UPLOAD_PATH.$SIGNATURE_INSPECTOR_NAME))
					{
						//echo 'file is moved 1';
						$sql_update_SIGNATURE_INSPECTOR="update `inspection` set SIGNATURE_INSPECTOR='".$SIGNATURE_INSPECTOR_NAME."'
						where ID=".$INSPECTION_ID;
						$signatureSQLResult = $DB->query($sql_update_SIGNATURE_INSPECTOR);
						if(!$signatureSQLResult)
						{
							self::$Data_Return = $DB->get_error_message();
							error_log('Error during inspector signature sql update: ' . self::$Data_Return, 0);
							return false;
						}
					}
					else
					{
						//echo 'error1';
						self::$Data_Return = 'Was not able to upload inspector signature';
						return false;
					}
				}
				else {
					//error_log('Inspector signature file is NOT sent for NEW inspection ' . $INSPECTION_ID);
				}
				//upload the SIGNATURE_BUILDING
				if(!is_null($SIGNATURE_BUILDING_IMG))
				{
					$SIGNATURE_BUILDING_NAME="sign_building_".$INSPECTION_ID.".png";
					if(move_uploaded_file($SIGNATURE_BUILDING_IMG['tmp_name'],IMAGE_UPLOAD_PATH.$SIGNATURE_BUILDING_NAME))
					{
						//echo 'file is moved 2';
						$sql_update_SIGNATURE_BUILDING="update `inspection` set SIGNATURE_BUILDING='".$SIGNATURE_BUILDING_NAME."'
						where ID=".$INSPECTION_ID;
						$signatureSQLResult = $DB->query($sql_update_SIGNATURE_BUILDING);
						
						if(!$signatureSQLResult)
						{
							self::$Data_Return = $DB->get_error_message();
							error_log('Error during building signature sql update: ' . self::$Data_Return, 0);
							return false;
						}

					}
					else
					{
						//echo 'error2';
						self::$Data_Return = 'Was not able to upload Building Manager signature';
						return false;
					}
				}
			} //end of new inspection that needs to be added to the database as insert
			else
			{
				// updating inpection according to the inspection id 
				if ($INSPECTION_ID === null){
					self::$Data_Return = 'Inspection ID was not provided for the update operation, while INSPECTION_OPERATION_TYPE as sent as ' . $INSPECTION_OPERATION_TYPE;
					error_log('Error during inspection update: ' . self::$Data_Return, 0);
					return false;
				}

				self::$Data_Return['INSPECTION_ID']=$INSPECTION_ID = $arrayPostData1['INSPECTION_ID'];
				error_log('existing inspection id: ' . $INSPECTION_ID);
				
				$sql_inpection_update = "update `inspection` set
				REINSPECT_DATE = IF(LENGTH('".$REINSPECT_DATE."')=0,NULL,'".$REINSPECT_DATE."'),
				INSPECTION_DATE = IF(LENGTH('".$INSPECTION_DATE."')=0,NULL,'".$INSPECTION_DATE."'),
				INSPECTION_COMPLETE_DATE = IF(LENGTH('".$INSPECTION_COMPLETE_DATE."')=0,NULL,'".$INSPECTION_COMPLETE_DATE."'),
				BUILDING_ID = IF(LENGTH('".$BUILDING_ID."')=0,NULL,'".$BUILDING_ID."'),
				COMPANY_ID = IF(LENGTH('".$COMPANY_ID."')=0,NULL,'".$COMPANY_ID."'),
				SIGNATURE_INSPECTOR = IF(LENGTH('".$SIGNATURE_INSPECTOR."')=0,NULL,'".$SIGNATURE_INSPECTOR."'),
				SIGNATURE_STROKES_INSPECTOR = IF(LENGTH('".$SIGNATURE_STROKES_INSPECTOR."')=0,NULL,'".$SIGNATURE_STROKES_INSPECTOR."'),
				SIGNATURE_BUILDING = IF(LENGTH('".$SIGNATURE_BUILDING."')=0,NULL,'".$SIGNATURE_BUILDING."'),
				SIGNATURE_STROKES_BUILDING = IF(LENGTH('".$SIGNATURE_STROKES_BUILDING."')=0,NULL,'".$SIGNATURE_STROKES_BUILDING."'),
				STATUS = IF(LENGTH('".$STATUS."')=0,NULL,'".$STATUS."'),
				SUMMARY = IF(LENGTH('".$SUMMARY."')=0,NULL,'".$SUMMARY."'),
				PDF = IF(LENGTH('".$PDF."')=0,NULL,'".$PDF."'),
				INSPECTOR_ID = IF(LENGTH('".$INSPECTOR_ID."')=0,NULL,'".$INSPECTOR_ID."'),
				TEMPLATE_ID = IF(LENGTH('".$TEMPLATE_ID."')=0,NULL,'".$TEMPLATE_ID."'),
				SIGNATURE_INSPECTOR_DATE = IF(LENGTH('".$SIGNATURE_INSPECTOR_DATE."')=0,NULL,'".$SIGNATURE_INSPECTOR_DATE."'),
				SIGNATURE_BUILDING_DATE = IF(LENGTH('".$SIGNATURE_BUILDING_DATE."')=0,NULL,'".$SIGNATURE_BUILDING_DATE."')
				WHERE	ID =".$INSPECTION_ID;			
				
				$result_inpection=$DB->query($sql_inpection_update);
				
				if(!$result_inpection)
				{
					self::$Data_Return = $DB->get_error_message();
					error_log($sql_inpection_update);
					error_log('Error during inspection update: ' . self::$Data_Return, 0);
					return false;
				}

				
				//upload the SIGNATURE_INSPECTOR
				if(!is_null($SIGNATURE_INSPECTOR_IMG))
				{
					//error_log('Inspector signature file is sent for EXISTING inspection ' . $INSPECTION_ID);
					//echo var_export($SIGNATURE_INSPECTOR_IMG, true);
					
					//remove the existing signature
					if(file_exists(IMAGE_UPLOAD_PATH."sign_inspector_".$INSPECTION_ID.".png")) {
					    chmod(IMAGE_UPLOAD_PATH."sign_inspector_".$INSPECTION_ID.".png", 0755); //Change the file permissions if allowed
					    unlink(IMAGE_UPLOAD_PATH."sign_inspector_".$INSPECTION_ID.".png" ); //remove the file
					}

					if(move_uploaded_file($SIGNATURE_INSPECTOR_IMG['tmp_name'],IMAGE_UPLOAD_PATH."sign_inspector_".$INSPECTION_ID.".png"))
					{
						//echo 'file is moved 3';
						$SIGNATURE_INSPECTOR_NAME="sign_inspector_".$INSPECTION_ID.".png";
						
						$sql_update_SIGNATURE_INSPECTOR="update `inspection` set SIGNATURE_INSPECTOR='".$SIGNATURE_INSPECTOR_NAME."'
						where ID=".$INSPECTION_ID;

						$result_file = $DB->query($sql_update_SIGNATURE_INSPECTOR);
						
						if(!$result_file)
						{
							self::$Data_Return = $DB->get_error_message();
							error_log('Error during inspector signature update: ' . self::$Data_Return, 0);
							return false;
						}
					}
					else
					{
						//echo 'error3';
						self::$Data_Return = 'Was not able to upload inspector signature';
						return false;	
					}
				}
				else {
					//error_log('Inspector signature file is NOT sent for EXISTING inspection ' . $INSPECTION_ID);
				}
				
				//upload the SIGNATURE_BUILDING
				if(!is_null($SIGNATURE_BUILDING_IMG))
				{
					//error_log('Building owner signature provided');
					
					//remove the existing signature
					if(file_exists(IMAGE_UPLOAD_PATH."sign_building_".$INSPECTION_ID.".png")) {
					    chmod(IMAGE_UPLOAD_PATH."sign_building_".$INSPECTION_ID.".png", 0755); //Change the file permissions if allowed
					    unlink(IMAGE_UPLOAD_PATH."sign_building_".$INSPECTION_ID.".png"); //remove the file
					}
					
					if(move_uploaded_file($SIGNATURE_BUILDING_IMG['tmp_name'],IMAGE_UPLOAD_PATH."sign_building_".$INSPECTION_ID.".png"))
					{
						//echo 'file is moved 4';
						$SIGNATURE_BUILDING_NAME="sign_building_".$INSPECTION_ID.".png";
						$sql_update_SIGNATURE_BUILDING="update `inspection` set SIGNATURE_BUILDING='".$SIGNATURE_BUILDING_NAME."'
						where ID=".$INSPECTION_ID;
						$result_file = $DB->query($sql_update_SIGNATURE_BUILDING);
						
						if(!$result_file)
						{
							self::$Data_Return = $DB->get_error_message();
							error_log('Error during building signature update: ' . self::$Data_Return, 0);
							return false;
						}

					}
					else
					{
						//echo 'error4';
						self::$Data_Return = 'Was not able to upload building manager signature';
						return false;	
					}
				}

			} //end of inspection information update
			
			//if door info is provided
			if(count($arrayPostData1['DOORINFORMATION'])>0)
			{
				//the door subsequent number
				$door_i = 0;
				
				//echo 'door is provided';			
				foreach ($arrayPostData1['DOORINFORMATION'] as $DOORINFORMATION)
				{

					//Check for new door or update door
					

					$DOOR_OPERATION_TYPE = $DOORINFORMATION['DOOR_OPERATION_TYPE'];
					$DOORID =$DOORINFORMATION['DOORID'];



					//door id sent by device
					

					//remove doors and is codes
					if($DOORINFORMATION['DOOR_DELETED'] == 1){

						$sql_delete_door_type = "DELETE from door_type 	
						WHERE DOOR_ID='".$DOORID."'";
						$DB->query($sql_delete_door_type);

						$sql_delete_hardware = "DELETE from hardware 	
						WHERE DOOR_ID='".$DOORID."'";
						$DB->query($sql_delete_hardware);

						$sql_delete_door_code = "DELETE from door_code 	
						WHERE DOOR_ID='".$DOORID."'";
						$DB->query($sql_delete_door_code);
						$sql_delete_recomanded = "DELETE from recomendation WHERE DOOR_ID='".$DOORID."'";
						$DB->query($sql_delete_recomanded);
						$sql_delete_door = "DELETE from door WHERE ID='".$DOORID."'";
						$DB->query($sql_delete_door);
						continue;
					}

					//if the door id is provided, then it is an update; if the door id is not provided, it is a new door
					//if (strlen($DOORID) > 0) $DOOR_OPERATION_TYPE = 'UPDATE';
					
					$NUMBER = $DB->mysqli_escape_value($DOORINFORMATION['DOORFIREDETAIL']['NUMBER']);
					$NUMBER = self::formatDoorNumber($NUMBER);
					


					if($DOOR_OPERATION_TYPE == "NEW"){
						$sql_dr_recomanded = "select ID from door WHERE NUMBER='".$NUMBER."' and INSPECTION_ID=".$INSPECTION_ID;
						$drResponse = $DB->query($sql_dr_recomanded);

						if(mysqli_num_rows($drResponse) > 0 ) $DOOR_OPERATION_TYPE = "UPDATE";
					}



					//TODO: remove the logging below, debug purposes only
					//error_log('Saving door number ' . $NUMBER . ', operation type: ' . $DOOR_OPERATION_TYPE . ', DOOR ID: ' . $DOORID);
					$now = date ('Y-m-d H:i:s', time());
					if(isset($DOORINFORMATION['DOORFIREDETAIL']['UPDATED_AT']))
						$now = $DOORINFORMATION['DOORFIREDETAIL']['UPDATED_AT'];


					if($DOOR_OPERATION_TYPE=="NEW")
					{			

						$test_variable=$DOORINFORMATION['DOORFIREDETAIL'];
						$MODEL	 				=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'MODEL')); // (strlen($test_variable['MODEL'])>0) 				? (($test_variable['MODEL']!=='0')					?$test_variable['MODEL']:'') : '';	
						$LISTING_AGENCY 		=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'LISTING_AGENCY')); //(strlen($test_variable['LISTING_AGENCY'])>0) 		? (($test_variable['LISTING_AGENCY']!=='0')			?$test_variable['LISTING_AGENCY']:'') : '';	
						$LISTING_AGENCY_OTHER	=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'LISTING_AGENCY_OTHER'));//(strlen($test_variable['LISTING_AGENCY_OTHER'])>0) ? (($test_variable['LISTING_AGENCY_OTHER']!=='0')	?$test_variable['LISTING_AGENCY_OTHER']:'') : '';	
						$GAUGE 					=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'GAUGE'));//(strlen($test_variable['GAUGE'])>0) 				? (($test_variable['GAUGE']!=='0')					?$test_variable['GAUGE']:'') : '';	
						$HANDING 				=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'HANDING'));//(strlen($test_variable['HANDING'])>0) 				? (($test_variable['HANDING']!=='0')				?$test_variable['HANDING']:'') : '';	
						$DOOR_BARCODE 			=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'DOOR_BARCODE'));//(strlen($test_variable['DOOR_BARCODE'])>0) 		? (($test_variable['DOOR_BARCODE']!=='0')			?$test_variable['DOOR_BARCODE']:'') : '';	
						$COMPLIANT 				=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'COMPLIANT'));//(strlen($test_variable['COMPLIANT'])>0) 			? (($test_variable['COMPLIANT']!=='0')				?$test_variable['COMPLIANT']:'') : '';	
						$TYPE_OTHER 			=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'TYPE_OTHER'));//(strlen($test_variable['TYPE_OTHER'])>0)			? (($test_variable['TYPE_OTHER']!=='0')				?$test_variable['TYPE_OTHER']:'') : '';
						$STYLE 					=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'STYLE'));//(strlen($test_variable['STYLE'])>0)				? (($test_variable['STYLE']!=='0')					?$test_variable['STYLE']:'') : '';
						$MATERIAL 				=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'MATERIAL'));//(strlen($test_variable['MATERIAL'])>0)				? (($test_variable['MATERIAL']!=='0')				?$test_variable['MATERIAL']:'') : '';
						$MATERIAL_OTHER 		=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'MATERIAL_OTHER'));//(strlen($test_variable['MATERIAL_OTHER'])>0)		? (($test_variable['MATERIAL_OTHER']!=='0')			?$test_variable['MATERIAL_OTHER']:'') : '';
						$ELEVATION 				=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'ELEVATION'));//(strlen($test_variable['ELEVATION'])>0)			? (($test_variable['ELEVATION']!=='0')				?$test_variable['ELEVATION']:'') : '';
						$ELEVATION_OTHER 		=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'ELEVATION_OTHER'));//(strlen($test_variable['ELEVATION_OTHER'])>0)		? (($test_variable['ELEVATION_OTHER']!=='0')		?$test_variable['ELEVATION_OTHER']:'') : '';
						$FRAME_MATERIAL 		=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'FRAME_MATERIAL'));//(strlen($test_variable['FRAME_MATERIAL'])>0)		? (($test_variable['FRAME_MATERIAL']!=='0')			?$test_variable['FRAME_MATERIAL']:'') : '';
						$FRAME_MATERIAL_OTHER 	=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'FRAME_MATERIAL_OTHER'));//(strlen($test_variable['FRAME_MATERIAL_OTHER'])>0) ? (($test_variable['FRAME_MATERIAL_OTHER']!=='0')	?$test_variable['FRAME_MATERIAL_OTHER']:'') : '';
						$FRAME_ELEVATION 		=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'FRAME_ELEVATION'));//(strlen($test_variable['FRAME_ELEVATION'])>0)		? (($test_variable['FRAME_ELEVATION']!=='0')		?$test_variable['FRAME_ELEVATION']:'') : '';
						$FRAME_ELEVATION_OTHER 	=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'FRAME_ELEVATION_OTHER'));//(strlen($test_variable['FRAME_ELEVATION_OTHER'])>0)? (($test_variable['FRAME_ELEVATION_OTHER']!=='0')	?$test_variable['FRAME_ELEVATION_OTHER']:'') : '';
						$FIRE_RATING_1 			=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'FIRE_RATING_1'));//(strlen($test_variable['FIRE_RATING_1'])>0)		? (($test_variable['FIRE_RATING_1']!=='0')			?$test_variable['FIRE_RATING_1']:'') : '';
						$FIRE_RATING_2 			=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'FIRE_RATING_2'));//(strlen($test_variable['FIRE_RATING_2'])>0)		? (($test_variable['FIRE_RATING_2']!=='0')			?$test_variable['FIRE_RATING_2']:'') : '';
						$FIRE_RATING_3 			=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'FIRE_RATING_3'));//(strlen($test_variable['FIRE_RATING_3'])>0)		? (($test_variable['FIRE_RATING_3']!=='0')			?$test_variable['FIRE_RATING_3']:'') : '';
						$FIRE_RATING_4 			=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'FIRE_RATING_4'));//(strlen($test_variable['FIRE_RATING_4'])>0)		? (($test_variable['FIRE_RATING_4']!=='0')			?$test_variable['FIRE_RATING_4']:'') : '';
						$FIRE_RATING_5 			=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'FIRE_RATING_5'));//(strlen($test_variable['FIRE_RATING_5'])>0)		? (($test_variable['FIRE_RATING_5']!=='0')			?$test_variable['FIRE_RATING_5']:'') : '';
						$TEMP_RISE 				=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'TEMP_RISE'));//(strlen($test_variable['TEMP_RISE'])>0)			? (($test_variable['TEMP_RISE']!=='0')				?$test_variable['TEMP_RISE']:'') : '';
						$MANUFACTURER		 	=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'MANUFACTURER'));//(strlen($test_variable['MANUFACTURER'])>0)			? (($test_variable['MANUFACTURER']!=='0')			?$test_variable['MANUFACTURER']:'') : '';
						$BARCODE 				=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'BARCODE'));//(strlen($test_variable['BARCODE'])>0)				? (($test_variable['BARCODE']!=='0')				?$test_variable['DOOR_BARCODE']:'') : '';
						$REMARKS 				=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'REMARKS'));//(strlen($test_variable['REMARKS'])>0)				? (($test_variable['REMARKS']!=='0')				?$test_variable['REMARKS']:'') : '';
						$LOCATION				=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'LOCATION'));//(strlen($test_variable['LOCATION'])>0)				? (($test_variable['LOCATION']!=='0')				?$test_variable['LOCATION']:'') : '';
						$HARDWARE_GROUP 		=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'HARDWARE_GROUP'));//(strlen($test_variable['HARDWARE_GROUP'])>0)		? (($test_variable['HARDWARE_GROUP']!=='0')			?$test_variable['HARDWARE_GROUP']:'') : '';
						$HARDWARE_SET 			=$DB->mysqli_escape_value(self::arrayValueZero($test_variable, 'HARDWARE_SET'));//(strlen($test_variable['HARDWARE_SET'])>0)			? (($test_variable['HARDWARE_SET']!=='0')			?$test_variable['HARDWARE_SET']:'') : '';
						
						//Converting array to sql string for door detail 
						$SQL_DOOR_DETAILS="";
						
						foreach($DOORINFORMATION['DOOR_DETAILS'] as $DOOR_DETAILS_KEY =>$DOOR_DETAILS_VALUE)
						{
							$SQL_DOOR_DETAILS .= $DOOR_DETAILS_KEY." = IF(LENGTH('".$DB->mysqli_escape_value($DOOR_DETAILS_VALUE)."')=0,NULL,'".$DB->mysqli_escape_value($DOOR_DETAILS_VALUE)."'),";
						}

						//Converting array to sql string for Frame detail 
						
						$SQL_FRAME_CHECK_LIST="";
						
						foreach(self::arrayValueArray($DOORINFORMATION,'FRAME_CHECK_LIST') as $FRAME_CHECK_LIST_Key =>$FRAME_CHECK_LIST_VALUE)
						{
							$SQL_FRAME_CHECK_LIST .= $FRAME_CHECK_LIST_Key." = IF(LENGTH('".$DB->mysqli_escape_value($FRAME_CHECK_LIST_VALUE)."')=0,NULL,'".$DB->mysqli_escape_value($FRAME_CHECK_LIST_VALUE)."'),";
						}	
						
						
						//TODO: reomve the logging
						//error_log('door type: ' . var_export($DOORINFORMATION['DOORFIREDETAIL']['DOOR_TYPE'], true));
						// insertion of new door
						$sql_door = "INSERT INTO door set
						MODEL	 = IF(LENGTH('".$MODEL	."')=0,NULL,'".$MODEL	."'),
						LISTING_AGENCY = IF(LENGTH('".$LISTING_AGENCY."')=0,NULL,'".$LISTING_AGENCY."'),
						LISTING_AGENCY_OTHER = IF(LENGTH('".$LISTING_AGENCY_OTHER."')=0,NULL,'".$LISTING_AGENCY_OTHER."'),
						GAUGE = IF(LENGTH('".$GAUGE."')=0,NULL,'".$GAUGE."'),
						HANDING = IF(LENGTH('".$HANDING."')=0,NULL,'".$HANDING."'),
						BUILDING_ID = IF(LENGTH('".$BUILDING_ID."')=0,NULL,'".$BUILDING_ID."'),
						COMPLIANT = IF(LENGTH('".$COMPLIANT."')=0,NULL,'".$COMPLIANT."'),
						INSPECTION_ID = IF(LENGTH('".$INSPECTION_ID."')=0,NULL,'".$INSPECTION_ID."'),
						INSPECTOR_ID = IF(LENGTH('".$INSPECTOR_ID."')=0,NULL,'".$INSPECTOR_ID."'),
						NUMBER = '".$NUMBER."',
						DOOR_BARCODE = IF(LENGTH('".$DOOR_BARCODE."')=0,NULL,'".$DOOR_BARCODE."'),
						TYPE_OTHER = IF(LENGTH('".$TYPE_OTHER."')=0,NULL,'".$TYPE_OTHER."'),
						STYLE = IF(LENGTH('".$STYLE."')=0,NULL,'".$STYLE."'),
						MATERIAL = IF(LENGTH('".$MATERIAL."')=0,NULL,'".$MATERIAL."'),
						MATERIAL_OTHER = IF(LENGTH('".$MATERIAL_OTHER."')=0,NULL,'".$MATERIAL_OTHER."'),
						ELEVATION = IF(LENGTH('".$ELEVATION."')=0,NULL,'".$ELEVATION."'),
						ELEVATION_OTHER = IF(LENGTH('".$ELEVATION_OTHER."')=0,NULL,'".$ELEVATION_OTHER."'),
						FRAME_MATERIAL = IF(LENGTH('".$FRAME_MATERIAL."')=0,NULL,'".$FRAME_MATERIAL."'),
						FRAME_MATERIAL_OTHER = IF(LENGTH('".$FRAME_MATERIAL_OTHER."')=0,NULL,'".$FRAME_MATERIAL_OTHER."'),
						FRAME_ELEVATION = IF(LENGTH('".$FRAME_ELEVATION."')=0,NULL,'".$FRAME_ELEVATION."'),
						FRAME_ELEVATION_OTHER = IF(LENGTH('".$FRAME_ELEVATION_OTHER."')=0,NULL,'".$FRAME_ELEVATION_OTHER."'),
						LOCATION = IF(LENGTH('".$LOCATION."')=0,NULL,'".$LOCATION."'),
						FIRE_RATING_1 = IF(LENGTH('".$FIRE_RATING_1."')=0,NULL,'".$FIRE_RATING_1."'),
						FIRE_RATING_2 = IF(LENGTH('".$FIRE_RATING_2."')=0,NULL,'".$FIRE_RATING_2."'),
						FIRE_RATING_3 = IF(LENGTH('".$FIRE_RATING_3."')=0,NULL,'".$FIRE_RATING_3."'),
						FIRE_RATING_4 = IF(LENGTH('".$FIRE_RATING_4."')=0,NULL,'".$FIRE_RATING_4."'),
						FIRE_RATING_5 = IF(LENGTH('".$FIRE_RATING_5."')=0,NULL,'".$FIRE_RATING_5."'),
						TEMP_RISE = IF(LENGTH('".$TEMP_RISE."')=0,NULL,'".$TEMP_RISE."'),
						MANUFACTURER = IF(LENGTH('".$MANUFACTURER."')=0,NULL,'".$MANUFACTURER."'),
						BARCODE = IF(LENGTH('".$BARCODE."')=0,NULL,'".$BARCODE."'),
						REMARKS = IF(LENGTH('".$REMARKS."')=0,NULL,'".$REMARKS."'),"
						.$SQL_DOOR_DETAILS
						.$SQL_FRAME_CHECK_LIST.	
						"HARDWARE_GROUP = IF(LENGTH('".$HARDWARE_GROUP."')=0,NULL,'".$HARDWARE_GROUP."'),
						HARDWARE_SET = IF(LENGTH('".$HARDWARE_SET."')=0,NULL,'".$HARDWARE_SET."'),
						UPDATED_AT = '".$now."'";		

						

						//echo $sql_door;
						//error_log('Door SQL: ' . $sql_door, 0);

						$result_door=$DB->query($sql_door);
						if(!$result_door)
						{
							self::$Data_Return = $DB->get_error_message();
							error_log('Error during door insert for inspection id: ' . $INSPECTION_ID . ', door number: ' . $NUMBER .', error message:' . self::$Data_Return, 0);
							error_log($sql_door);
							error_log($DB->get_error_message());
							return false;
						}
						
						//add the door id in the response
						self::$Data_Return['DOOR_ID'][$DOORID]=$DOOR_ID = $DB->insert_id();
						
						//add the building id in the response
						self::$Data_Return['methodIdentifier']="uploadInspection";

						if(isset($DOORINFORMATION['DOORFIREDETAIL']['RECOMANDATION'])){

							$sql_delete_recomanded = "DELETE from recomendation WHERE DOOR_ID='".$DOOR_ID."'";						
							$result_delete_recomanded=$DB->query($sql_delete_recomanded);

							$sql_recomanded = "INSERT INTO recomendation set DOOR_ID='".$DOOR_ID."', RECOMENDATION = '".$DOORINFORMATION['DOORFIREDETAIL']['RECOMANDATION']."'";
							$result_recomanded=$DB->query($sql_recomanded);	

						}


						
						//add the building owner id in the response
						
						//TODO: reomve the logging
						//error_log('door type: ' . var_export($DOORINFORMATION['DOORFIREDETAIL']['DOOR_TYPE'], true));
						foreach($DOORINFORMATION['DOORFIREDETAIL']['DOOR_TYPE']['TYPE_ID'] as $DOOR_TYPE_KEY =>$DOOR_TYPE_VALUE)
						{
							// insertion of door type from the door fire panel

							$sql_door_type = "INSERT INTO door_type set
							DOOR_ID='".$DOOR_ID."',
							TYPE_ID = '".$DOOR_TYPE_VALUE."'";

							
							$result_inpection_list=$DB->query($sql_door_type);	
							if (!$result_inpection_list){
								self::$Data_Return = $DB->get_error_message();
								error_log($sql_door_type);
								error_log('Error during door type insert: ' . self::$Data_Return, 0);
								return false;
							}											

						}
						
						
						foreach($DOORINFORMATION['HARDWARE_CHECK_LIST'] as $HARDWARE_CHECK_LIST_KEY =>$HARDWARE_CHECK_LIST_VALUE)
						{
							// insertion of hardware detail 
							
							$sql_hardware = "INSERT INTO hardware set
							DOOR_ID='".$DOOR_ID."',
							ITEM_ID = '". $DB->mysqli_escape_value(self::arrayValue($HARDWARE_CHECK_LIST_VALUE, 'ITEM_ID'))."',
							VERIFY = '". $DB->mysqli_escape_value(self::arrayValue($HARDWARE_CHECK_LIST_VALUE, 'VERIFY'))."',
							QTY = '". $DB->mysqli_escape_value(self::arrayValue($HARDWARE_CHECK_LIST_VALUE, 'QTY')) ."',
							ITEM = '". $DB->mysqli_escape_value(self::arrayValue($HARDWARE_CHECK_LIST_VALUE, 'ITEM')) ."',
							PRODUCT = '". $DB->mysqli_escape_value(self::arrayValue($HARDWARE_CHECK_LIST_VALUE, 'PRODUCT')) ."',
							MFG = '". $DB->mysqli_escape_value(self::arrayValue($HARDWARE_CHECK_LIST_VALUE, 'MFG')) ."',
							FINISH = '". $DB->mysqli_escape_value(self::arrayValue($HARDWARE_CHECK_LIST_VALUE, 'FINISH')) ."'";

							$result_hardware=$DB->query($sql_hardware);		
							
							if (!$result_hardware){
								self::$Data_Return = $DB->get_error_message();
								error_log($sql_hardware);
								error_log('Error during door hardware insert: ' . self::$Data_Return, 0);
								return false;
							}														
						}
						
						
						
						
						$error_codes = array();
						foreach($DOORINFORMATION['INSECPTION_CHECK_LIST'] as $INSECPTION_CHECK_LIST_KEY =>$INSECPTION_CHECK_LIST_VALUE)
						{
							// inserstion of the inpection check list
							if (in_array($INSECPTION_CHECK_LIST_VALUE, $error_codes)) continue;

							$sql_inpection_list = "INSERT INTO door_code set
							DOOR_ID='".$DOOR_ID."',
							CODE_ID = '" . self::arrayValue($INSECPTION_CHECK_LIST_VALUE, 'CODE_ID')."',
							ACTIVE = '". self::arrayValue($INSECPTION_CHECK_LIST_VALUE, 'ACTIVE')."',
							CONTROL_NAME = '" . self::arrayValue($INSECPTION_CHECK_LIST_VALUE,'CONTROL_NAME')."'";

							$result_inpection_list=$DB->query($sql_inpection_list);	
							
							if (!$result_inpection_list){
								self::$Data_Return = $DB->get_error_message();
								error_log('Error during door code insert for new door: ' . self::$Data_Return, 0);
								return false;
							}		
							array_push($error_codes, $INSECPTION_CHECK_LIST_VALUE);									

						}
						$error_codes = null;
						
						
						// inserstion of the inpection check list other field detail. 
						//we are only doing it for the first door, since all the information is the same for all the doors
						if ($door_i == 0){
							$sql_delete_door_type = "DELETE from inspection_other WHERE INSPECTION_ID='".$INSPECTION_ID."'";

							$result_delete_door_type=$DB->query($sql_delete_door_type);
							if (!$result_delete_door_type){
								self::$Data_Return = $DB->get_error_message();
								error_log('Error during inspection other cleanup: ' . self::$Data_Return, 0);
								return false;
							}	

							foreach($DOORINFORMATION['INSPECTION_OTHER'] as $INSPECTION_OTHER_KEY =>$INSPECTION_OTHER_VALUE)
							{

								$sql_other_inpection = "INSERT INTO inspection_other set
								INSPECTION_ID ='".$INSPECTION_ID."',
								OTHER_ID = '" . $DB->mysqli_escape_value(self::arrayValue($INSPECTION_OTHER_VALUE, 'OTHER_ID'))."',
								OTHER_VALUE = '" . $DB->mysqli_escape_value(self::arrayValue($INSPECTION_OTHER_VALUE, 'OTHER_VALUE'))."'";

								$result_inpection_list=$DB->query($sql_other_inpection);
								
								if (!$result_inpection_list){
									self::$Data_Return = $DB->get_error_message();
									error_log('Error during inspection other insert: ' . self::$Data_Return, 0);
									return false;
								}													

							}						
						}

						
						
						//error_log("Door pictures submitted: " . var_export($DOORINFORMATION['PICTURES'], true) . "\n", 3, '/var/www/mydoordata.com/logs/error.log');
						
						$DB->query("delete from picture where DOOR_ID=".$DOOR_ID);	

						foreach($DOORINFORMATION['PICTURES'] as $PICTURES_KEY =>$PICTURES_VALUE)
						{

							// $Picture_File_Location = IMAGE_DIR_ROOT.$PICTURES_VALUE['PICTURE_FILE'];
							//error_log("Picture file location: " . $Picture_File_Location . "\n", 3, '/var/www/mydoordata.com/logs/error.log');
							// chmod(IMAGE_UPLOAD_PATH.$Picture_File_Location, 0777);

							// if (move_uploaded_file($_FILES['Profile_Image']['tmp_name'],$Picture_File_Location ))
							// {

							$notes = (isset($PICTURES_VALUE['NOTE']))? $PICTURES_VALUE['NOTE']:"";

							$sql_picture = "INSERT INTO picture set
							DOOR_ID = ".$DOOR_ID.",
							PICTURE_FILE = '".$PICTURES_VALUE['PICTURE_FILE']."',
							CONTROL_NAME = '".$PICTURES_VALUE['CONTROL_NAME']."',
							NOTE = '".$notes."'";

							$result_picture=$DB->query($sql_picture);		

							// 	//TODO: we also need to copy the pictures to the PDF Server

							// }
							// else {
							// 	error_log("Error uploading file.", 0);
							// }

						}
					}
					else
					{
						
						$DOOR_ID = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION, 'DOORID'));
						$MODEL	 = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'], 'MODEL'));
						$LISTING_AGENCY = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'], 'LISTING_AGENCY'));
						$LISTING_AGENCY_OTHER = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'], 'LISTING_AGENCY_OTHER'));
						$GAUGE = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'], 'GAUGE'));
						$HANDING = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'], 'HANDING'));
						
						$COMPLIANT = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'], 'COMPLIANT'));
						$DOOR_BARCODE = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'], 'DOOR_BARCODE'));
						$TYPE_OTHER = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'], 'TYPE_OTHER'));
						$STYLE = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'], 'STYLE'));
						$MATERIAL = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'], 'MATERIAL'));
						$MATERIAL_OTHER = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'], 'MATERIAL_OTHER'));
						$ELEVATION = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'], 'ELEVATION'));
						$ELEVATION_OTHER = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'], 'ELEVATION_OTHER'));
						$FRAME_MATERIAL = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'], 'FRAME_MATERIAL'));
						$FRAME_MATERIAL_OTHER = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'], 'FRAME_MATERIAL_OTHER'));
						$FRAME_ELEVATION = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'], 'FRAME_ELEVATION'));
						$FRAME_ELEVATION_OTHER = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'], 'FRAME_ELEVATION_OTHER'));
						$FIRE_RATING_1 = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'], 'FIRE_RATING_1'));
						$FIRE_RATING_2 = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'], 'FIRE_RATING_2'));
						$FIRE_RATING_3 = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'], 'FIRE_RATING_3'));
						$FIRE_RATING_4 = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'], 'FIRE_RATING_4'));
						$FIRE_RATING_5 = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'], 'FIRE_RATING_5'));
						$TEMP_RISE = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'], 'TEMP_RISE'));
						$MANUFACTURER = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'], 'MANUFACTURER'));
						$BARCODE = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'], 'BARCODE'));
						$REMARKS = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'], 'REMARKS'));
						$LOCATION  = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'],'LOCATION'));
						$HARDWARE_GROUP = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'], 'HARDWARE_GROUP'));
						$HARDWARE_SET = $DB->mysqli_escape_value(self::arrayValue($DOORINFORMATION['DOORFIREDETAIL'], 'HARDWARE_SET'));

						$INSPECTOR_ID ="";
						
						//Converting array to sql string for door detail 
						$SQL_DOOR_DETAILS="";
						
						foreach($DOORINFORMATION['DOOR_DETAILS'] as $DOOR_DETAILS_KEY =>$DOOR_DETAILS_VALUE)
						{
							$SQL_DOOR_DETAILS .= $DOOR_DETAILS_KEY." = IF(LENGTH('".$DB->mysqli_escape_value($DOOR_DETAILS_VALUE)."')=0,NULL,'".$DB->mysqli_escape_value($DOOR_DETAILS_VALUE)."'),";
						}

						//Converting array to sql string for Frame detail 
						
						$SQL_FRAME_CHECK_LIST="";
						
						if(array_key_exists('FRAME_CHECK_LIST', $DOORINFORMATION))
							foreach($DOORINFORMATION['FRAME_CHECK_LIST'] as $FRAME_CHECK_LIST_Key =>$FRAME_CHECK_LIST_VALUE)
							{
								$SQL_FRAME_CHECK_LIST .= $FRAME_CHECK_LIST_Key." = IF(LENGTH('".$DB->mysqli_escape_value($FRAME_CHECK_LIST_VALUE)."')=0,NULL,'".$DB->mysqli_escape_value($FRAME_CHECK_LIST_VALUE)."'),";
							}



							$sql_door = "UPDATE door set
							MODEL	 = IF(LENGTH('".$MODEL	."')=0,NULL,'".$MODEL."'),
							LISTING_AGENCY = IF(LENGTH('".$LISTING_AGENCY."')=0,NULL,'".$LISTING_AGENCY."'),
							LISTING_AGENCY_OTHER = IF(LENGTH('".$LISTING_AGENCY_OTHER."')=0,NULL,'".$LISTING_AGENCY_OTHER."'),
							GAUGE = IF(LENGTH('".$GAUGE."')=0,NULL,'".$GAUGE."'),
							HANDING = IF(LENGTH('".$HANDING."')=0,NULL,'".$HANDING."'),
							BUILDING_ID = IF(LENGTH('".$BUILDING_ID."')=0,NULL,'".$BUILDING_ID."'),
							INSPECTION_ID = IF(LENGTH('".$INSPECTION_ID."')=0,NULL,'".$INSPECTION_ID."'),
							COMPLIANT = IF(LENGTH('".$COMPLIANT."')=0,NULL,'".$COMPLIANT."'),
							INSPECTOR_ID = IF(LENGTH('".$INSPECTOR_ID."')=0,NULL,'".$INSPECTOR_ID."'),
							NUMBER = '".$NUMBER."',
							DOOR_BARCODE = IF(LENGTH('".$DOOR_BARCODE."')=0,NULL,'".$DOOR_BARCODE."'),
							TYPE_OTHER = IF(LENGTH('".$TYPE_OTHER."')=0,NULL,'".$TYPE_OTHER."'),
							STYLE = IF(LENGTH('".$STYLE."')=0,NULL,'".$STYLE."'),
							MATERIAL = IF(LENGTH('".$MATERIAL."')=0,NULL,'".$MATERIAL."'),
							MATERIAL_OTHER = IF(LENGTH('".$MATERIAL_OTHER."')=0,NULL,'".$MATERIAL_OTHER."'),
							ELEVATION = IF(LENGTH('".$ELEVATION."')=0,NULL,'".$ELEVATION."'),
							ELEVATION_OTHER = IF(LENGTH('".$ELEVATION_OTHER."')=0,NULL,'".$ELEVATION_OTHER."'),
							FRAME_MATERIAL = IF(LENGTH('".$FRAME_MATERIAL."')=0,NULL,'".$FRAME_MATERIAL."'),
							FRAME_MATERIAL_OTHER = IF(LENGTH('".$FRAME_MATERIAL_OTHER."')=0,NULL,'".$FRAME_MATERIAL_OTHER."'),
							FRAME_ELEVATION = IF(LENGTH('".$FRAME_ELEVATION."')=0,NULL,'".$FRAME_ELEVATION."'),
							FRAME_ELEVATION_OTHER = IF(LENGTH('".$FRAME_ELEVATION_OTHER."')=0,NULL,'".$FRAME_ELEVATION_OTHER."'),
							LOCATION = IF(LENGTH('".$LOCATION."')=0,NULL,'".$LOCATION."'),
							FIRE_RATING_1 = IF(LENGTH('".$FIRE_RATING_1."')=0,NULL,'".$FIRE_RATING_1."'),
							FIRE_RATING_2 = IF(LENGTH('".$FIRE_RATING_2."')=0,NULL,'".$FIRE_RATING_2."'),
							FIRE_RATING_3 = IF(LENGTH('".$FIRE_RATING_3."')=0,NULL,'".$FIRE_RATING_3."'),
							FIRE_RATING_4 = IF(LENGTH('".$FIRE_RATING_4."')=0,NULL,'".$FIRE_RATING_4."'),
							FIRE_RATING_5 = IF(LENGTH('".$FIRE_RATING_5."')=0,NULL,'".$FIRE_RATING_5."'),
							TEMP_RISE = IF(LENGTH('".$TEMP_RISE."')=0,NULL,'".$TEMP_RISE."'),
							MANUFACTURER = IF(LENGTH('".$MANUFACTURER."')=0,NULL,'".$MANUFACTURER."'),
							BARCODE = IF(LENGTH('".$BARCODE."')=0,NULL,'".$BARCODE."'),
							REMARKS = IF(LENGTH('".$REMARKS."')=0,NULL,'".$REMARKS."'),"
							.$SQL_DOOR_DETAILS
							.$SQL_FRAME_CHECK_LIST.	
							"HARDWARE_GROUP = IF(LENGTH('".$HARDWARE_GROUP."')=0,NULL,'".$HARDWARE_GROUP."'),
							HARDWARE_SET = IF(LENGTH('".$HARDWARE_SET."')=0,NULL,'".$HARDWARE_SET."'),
							UPDATED_AT = '".$now."' WHERE ID = '".$DOOR_ID."'";			
							
							$result_door=$DB->query($sql_door);

							if (!$result_door){
								self::$Data_Return = $DB->get_error_message();
								error_log('Error during door update: ' . self::$Data_Return, 0);
								return false;
							}	

						//add the door id in the response
							self::$Data_Return['DOOR_ID'][$DOORID]=$DOOR_ID ;
						//self::$Data_Return['DOOR_ID'][$DOORID]=$DOOR_ID = $DB->insert_id();

						/// Update recomanded list

							if(isset($DOORINFORMATION['DOORFIREDETAIL']['RECOMANDATION'])){

								$sql_delete_recomanded = "DELETE from recomendation WHERE DOOR_ID='".$DOOR_ID."'";						
								$result_delete_recomanded=$DB->query($sql_delete_recomanded);

								$sql_recomanded = "INSERT INTO recomendation set DOOR_ID='".$DOOR_ID."', RECOMENDATION = '".$DOORINFORMATION['DOORFIREDETAIL']['RECOMANDATION']."'";
								$result_recomanded=$DB->query($sql_recomanded);	

							}

						//add the building owner id in the response

						//$DOOR_ID = $DB->insert_id();
						//$DOOR_ID = '';

							$sql_delete_door_type = "DELETE from door_type 	
							WHERE DOOR_ID='".$DOOR_ID."'";

							$result_delete_door_type=$DB->query($sql_delete_door_type);

							if (!$result_delete_door_type){
								self::$Data_Return = $DB->get_error_message();
								error_log('Error during door type delete: ' . self::$Data_Return, 0);
								return false;
							}

							foreach($DOORINFORMATION['DOORFIREDETAIL']['DOOR_TYPE']['TYPE_ID'] as $DOOR_TYPE_KEY =>$DOOR_TYPE_VALUE)
							{

								$sql_door_type = "INSERT INTO door_type set
								DOOR_ID='".$DOOR_ID."',
								TYPE_ID = '".$DOOR_TYPE_VALUE."'";


								$result_inpection_list=$DB->query($sql_door_type);	

								if (!$result_inpection_list){
									self::$Data_Return = $DB->get_error_message();
									error_log($sql_door_type);
									error_log('Error during door type insert: ' . self::$Data_Return, 0);
									return false;
								}											

							}

							$sql_delete_door_type = "DELETE from hardware 	
							WHERE DOOR_ID='".$DOOR_ID."'";

							$result_delete_door_type=$DB->query($sql_delete_door_type);

							if (!$result_delete_door_type){
								self::$Data_Return = $DB->get_error_message();
								error_log('Error during door hardware delete: ' . self::$Data_Return, 0);
								return false;
							}	


							foreach($DOORINFORMATION['HARDWARE_CHECK_LIST'] as $HARDWARE_CHECK_LIST_KEY =>$HARDWARE_CHECK_LIST_VALUE)
							{
								//ITEM_ID = '".  $DB->mysqli_escape_value(self::arrayValue($HARDWARE_CHECK_LIST_VALUE, 'ITEM_ID'))."',

								$sql_hardware = "INSERT INTO hardware set
								DOOR_ID='".$DOOR_ID."',
								VERIFY = '". $DB->mysqli_escape_value(self::arrayValue($HARDWARE_CHECK_LIST_VALUE, 'VERIFY')) ."',
								QTY = '". $DB->mysqli_escape_value(self::arrayValue($HARDWARE_CHECK_LIST_VALUE, 'QTY'))."',
								ITEM = '".  $DB->mysqli_escape_value(self::arrayValue($HARDWARE_CHECK_LIST_VALUE, 'ITEM'))."',
								PRODUCT = '". $DB->mysqli_escape_value(self::arrayValue($HARDWARE_CHECK_LIST_VALUE, 'PRODUCT'))."',
								MFG = '". $DB->mysqli_escape_value(self::arrayValue($HARDWARE_CHECK_LIST_VALUE, 'MFG')) ."',
								FINISH = '".  $DB->mysqli_escape_value(self::arrayValue($HARDWARE_CHECK_LIST_VALUE, 'FINISH')) ."'";

								$result_hardware=$DB->query($sql_hardware);		

								if (!$result_hardware){
									self::$Data_Return = $DB->get_error_message();
									error_log($sql_hardware);
									error_log('Error during door hardware insert: ' . self::$Data_Return, 0);
									return false;
								}														
							}

							$sql_delete_door_type = "DELETE from door_code 	
							WHERE DOOR_ID='".$DOOR_ID."'";

							$result_delete_door_type=$DB->query($sql_delete_door_type);

							if (!$result_delete_door_type){
								self::$Data_Return = $DB->get_error_message();
								error_log('Error during door code delete: ' . self::$Data_Return, 0);
								return false;
							}

							$error_codes = array();
							foreach($DOORINFORMATION['INSECPTION_CHECK_LIST'] as $INSECPTION_CHECK_LIST_KEY =>$INSECPTION_CHECK_LIST_VALUE)
							{

								if(in_array($INSECPTION_CHECK_LIST_VALUE, $error_codes)) continue;
								$sql_inpection_list = "INSERT INTO door_code set
								DOOR_ID='".$DOOR_ID."',
								CODE_ID = '". self::arrayValue($INSECPTION_CHECK_LIST_VALUE, 'CODE_ID')."',
								ACTIVE = '". self::arrayValue($INSECPTION_CHECK_LIST_VALUE, 'ACTIVE') ."',
								CONTROL_NAME = '".  self::arrayValue($INSECPTION_CHECK_LIST_VALUE, 'CONTROL_NAME') ."'";

								$result_inpection_list=$DB->query($sql_inpection_list);	

								if (!$result_inpection_list){
									self::$Data_Return = $DB->get_error_message();
									error_log('Error during door code insert for existing door: ' . self::$Data_Return, 0);
									return false;
								}	
								array_push($error_codes, $INSECPTION_CHECK_LIST_VALUE);										

							}
							$error_codes = null;


							if ($door_i == 0){
								$sql_delete_door_type = "DELETE from inspection_other WHERE INSPECTION_ID='".$INSPECTION_ID."'";

								$result_delete_door_type=$DB->query($sql_delete_door_type);

								if (!$result_delete_door_type){
									self::$Data_Return = $DB->get_error_message();
									error_log('Error during inspection other delete: ' . self::$Data_Return, 0);
									return false;
								}							
							}


							foreach($DOORINFORMATION['INSPECTION_OTHER'] as $INSPECTION_OTHER_KEY =>$INSPECTION_OTHER_VALUE)
							{

								$sql_other_inpection = "INSERT INTO inspection_other set
								INSPECTION_ID ='".$INSPECTION_ID."',
								OTHER_ID = '".  $DB->mysqli_escape_value(self::arrayValue($INSPECTION_OTHER_VALUE, 'OTHER_ID')) ."',
								OTHER_VALUE = '".  $DB->mysqli_escape_value(self::arrayValue($INSPECTION_OTHER_VALUE, 'OTHER_VALUE')) ."'";

								$result_inpection_list=$DB->query($sql_other_inpection);	

								if (!$result_inpection_list){
									self::$Data_Return = $DB->get_error_message();
									error_log('Error during inspection other insert: ' . self::$Data_Return, 0);
									return false;
								}												

							}

							$DB->query("delete from picture where DOOR_ID=".$DOOR_ID);

							foreach($DOORINFORMATION['PICTURES'] as $PICTURES_KEY =>$PICTURES_VALUE)
							{

								// $Picture_File_Location = IMAGE_DIR_ROOT.$PICTURES_VALUE['PICTURE_FILE'];
								// chmod(IMAGE_UPLOAD_PATH.$Picture_File_Location, 0777);
								// if (move_uploaded_file($_FILES['Profile_Image']['tmp_name'],$Picture_File_Location ))
								// {
								//$notes = (isset($PICTURES_VALUE['NOTE']))? $PICTURES_VALUE['NOTE']:"";

								$sql_picture = "INSERT INTO picture set
								DOOR_ID = '".$DOOR_ID."',
								PICTURE_FILE = '". self::arrayValue($PICTURES_VALUE, 'PICTURE_FILE') ."',
								CONTROL_NAME = '".  self::arrayValue($PICTURES_VALUE, 'CONTROL_NAME') ."',
								NOTE = '".  self::arrayValue($PICTURES_VALUE, 'NOTE')."'";
								$result_picture=$DB->query($sql_picture);								  
								// }
								// else {
								// 	error_log('Was not able to move uploaded file.');
								// }
							}	

						}

						$door_i = $door_i + 1;

				}// end foreach of multiple door detail		
			}
			else
			{
				//add the building id in the response
				self::$Data_Return['methodIdentifier']="uploadInspection";
				//reteurn true for result_door
				$result_door=true;	
			}
			
			if($result_inpection and $result_door)
			{
				///echo "fsdfsgf";
				return true;
			}
			else
			{
					////echo "else";
				self::$Data_Return=array();
				self::$Data_Return['methodIdentifier']="uploadInspection";
				self::$Data_Return['error_message'] = "There was an error during inspection upload";
				return false;
			}
		}	
	}
	?>
