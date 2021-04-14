<?php
class M_Generate_PDF{
	public static $Data_Return=array();
	
	private static $door_numbers = array();
	
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
	
	public static function uploadInspectionWithDoor($COMPANY_ID,$BUILDING_ID,$arrayPostData1,$SIGNATURE_INSPECTOR_IMG,$SIGNATURE_BUILDING_IMG)	{
		//add the building id in the response
		self::$Data_Return['methodIdentifier']="uploadInspection";
		// INPECTION DETAIL PARAMETER
		$INSPECTION_DATE = $arrayPostData1['INSPECTION_DATE'];
		$INSPECTION_COMPLETE_DATE = $arrayPostData1['INSPECTION_COMPLETE_DATE'];
		$REINSPECT_DATE = $arrayPostData1['REINSPECT_DATE'];
		$STATUS = $arrayPostData1['STATUS'];
		$COMPANY_ID = $arrayPostData1['COMPANY_ID'];
		$BUILDING_ID = $arrayPostData1['BUILDING_ID'];
		$INSPECTOR_ID  = $arrayPostData1['INSPECTOR_ID'];
		$TEMPLATE_ID = $arrayPostData1['TEMPLATE_ID'];
		$SUMMARY = mysql_real_escape_string($arrayPostData1['SUMMARY']);
		// PARATMETER NOT SEND BY JSON
		$SIGNATURE_INSPECTOR ="";
		$SIGNATURE_STROKES_INSPECTOR ="";
		$SIGNATURE_BUILDING ="";
		$SIGNATURE_STROKES_BUILDING ="";
		$PDF = "";				
		$INSPECTION_OPERATION_TYPE = $arrayPostData1['INSPECTION_OPERATION_TYPE'];
		
		//echo "upload1";
		
		error_log('M_generate_pdf::uploadInspectionWithDoor');
		
		if($INSPECTION_OPERATION_TYPE=="NEW"){
			// adding new inpection 
			$sql_inpection = "
				INSERT INTO inspection set
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
				TEMPLATE_ID = IF(LENGTH('".$TEMPLATE_ID."')=0,NULL,'".$TEMPLATE_ID."')
			";			
			$result_inpection=mysql_query($sql_inpection);
			if (!$result_inpection){
				$me = mysql_error();
				self::$Data_Return['error_message'] = $me;
				error_log('Error during inspection insert in printing: ' . $me);
				return false;
			}
			self::$Data_Return['INSPECTION_ID']=$INSPECTION_ID = mysql_insert_id();
			
			
			//echo "upload2";
			
			//upload the SIGNATURE_INSPECTOR
			if(!empty($SIGNATURE_INSPECTOR_IMG)){
				$SIGNATURE_INSPECTOR_NAME="sign_inspector_".$INSPECTION_ID.".png";
				if(move_uploaded_file($SIGNATURE_INSPECTOR_IMG['tmp_name'],IMAGE_UPLOAD_PATH.$SIGNATURE_INSPECTOR_NAME)){
						$sql_update_SIGNATURE_INSPECTOR="update inspection set SIGNATURE_INSPECTOR='".$SIGNATURE_INSPECTOR_NAME."'
														where ID='".$INSPECTION_ID."'";
					$queryResult = mysql_query($sql_update_SIGNATURE_INSPECTOR);
					if (!$queryResult){
						$me = mysql_error();
						self::$Data_Return['error_message'] = $me;
						error_log('Error during adding inspector signature to new inspection in printing: ' . $me);
						return false;
					}
				}else{
					error_log('Error during inspector signature upload for new inspection in printing');
					self::$Data_Return['error_message'] = 'Error during inspector signature upload for new inspection in printing';
					return false;
				}
			}

			//echo "upload3";


			//upload the SIGNATURE_BUILDING
			if(!empty($SIGNATURE_BUILDING_IMG))
			{
				$SIGNATURE_BUILDING_NAME="sign_building_".$INSPECTION_ID.".png";
				if(move_uploaded_file($SIGNATURE_BUILDING_IMG['tmp_name'],IMAGE_UPLOAD_PATH.$SIGNATURE_BUILDING_NAME))
				{
					$sql_update_SIGNATURE_BUILDING="update inspection set SIGNATURE_BUILDING='".$SIGNATURE_BUILDING_NAME."'
													where ID='".$INSPECTION_ID."'";
					$queryResult = mysql_query($sql_update_SIGNATURE_BUILDING);
					if (!$queryResult){
						$me = mysql_error();
						self::$Data_Return['error_message'] = $me;
						error_log('Error during adding building manager signature to new inspection in printing: ' . $me);
						return false;
					}
				
				}
				else
				{
					error_log('Error during building manager signature upload to new inspection in printing');
					self::$Data_Return['error_message'] = 'Error during building manager signature upload to new inspection in printing';
					return false;
				}
			}
			
			//echo "upload4";
		}
		
				
		//upload the SIGNATURE_INSPECTOR
		if(!empty($SIGNATURE_INSPECTOR)){
			if(!move_uploaded_file($SIGNATURE_INSPECTOR['tmp_name'],IMAGE_UPLOAD_PATH."sign_inspector_".$INSPECTION_ID.".png"))
			{
				$SIGNATURE_INSPECTOR_NAME="sign_inspector_".$INSPECTION_ID.".png";
				$sql_update_SIGNATURE_INSPECTOR="update inspection set SIGNATURE_INSPECTOR='".$SIGNATURE_INSPECTOR_NAME."'
												where ID='".$INSPECTION_ID."'";
				$queryResult = mysql_query($sql_update_SIGNATURE_INSPECTOR);
				if (!$queryResult){
					$me = mysql_error();
					self::$Data_Return['error_message'] = $me;
					error_log('Error during adding inspector signature to existing inspection in printing: ' . $me);
					return false;
				}
			}
			else
			{
				error_log('Error during inspector signature upload to existing inspection in printing');
				self::$Data_Return['error_message'] = 'Error during inspector signature upload to existing inspection in printing';
				return false;	
			}
		}
		//upload the SIGNATURE_BUILDING
		if(!empty($SIGNATURE_BUILDING))	{
			if(!move_uploaded_file($SIGNATURE_BUILDING['tmp_name'],IMAGE_UPLOAD_PATH."sign_building_".$INSPECTION_ID.".png"))
			{
				$SIGNATURE_BUILDING_NAME="sign_building_".$INSPECTION_ID.".png";
				$sql_update_SIGNATURE_BUILDING="update inspection set SIGNATURE_BUILDING='".$SIGNATURE_BUILDING_NAME."'
												where ID='".$INSPECTION_ID."'";
				$queryResult = mysql_query($sql_update_SIGNATURE_BUILDING);
				if (!$queryResult){
					$me = mysql_error();
					self::$Data_Return['error_message'] = $me;
					error_log('Error during adding building manager signature to existing inspection in printing: ' . $me);
					return false;
				}
			
			}
			else
			{
				error_log('Error during building manager signature upload to existing inspection in printing');
				self::$Data_Return['error_message'] = 'Error during building manager signature upload to existing inspection in printing';
				return false;	
			}
		}
		
		//echo "upload5";
		
		//if door info is provided
		if(count($arrayPostData1['DOORINFORMATION'])>0){
			foreach ($arrayPostData1['DOORINFORMATION'] as $DOORINFORMATION){
				//Check for new door or update door
				$DOOR_OPERATION_TYPE = $DOORINFORMATION['DOOR_OPERATION_TYPE'];
				//door id sent by device
				$DOORID 				=$DOORINFORMATION['DOORID'];	
				if($DOOR_OPERATION_TYPE=="NEW"){			
					$NUMBER = mysql_real_escape_string($DOORINFORMATION['DOORFIREDETAIL']['NUMBER']);
					$NUMBER = self::formatDoorNumber($NUMBER);
					$test_variable=$DOORINFORMATION['DOORFIREDETAIL'];
					$MODEL	 				=mysql_real_escape_string((strlen($test_variable['MODEL'])>0) 				? (($test_variable['MODEL']!=='0')				?$test_variable['MODEL']:'') : '');	
					$LISTING_AGENCY 		=mysql_real_escape_string((strlen($test_variable['LISTING_AGENCY'])>0) 		? (($test_variable['LISTING_AGENCY']!=='0')			?$test_variable['LISTING_AGENCY']:'') : '');	
					$LISTING_AGENCY_OTHER	=mysql_real_escape_string((strlen($test_variable['LISTING_AGENCY_OTHER'])>0) ? (($test_variable['LISTING_AGENCY_OTHER']!=='0')	?$test_variable['LISTING_AGENCY_OTHER']:'') : '');	
					$GAUGE 					=mysql_real_escape_string((strlen($test_variable['GAUGE'])>0) 				? (($test_variable['GAUGE']!=='0')					?$test_variable['GAUGE']:'') : '');	
					$HANDING 				=mysql_real_escape_string((strlen($test_variable['HANDING'])>0) 				? (($test_variable['HANDING']!=='0')				?$test_variable['HANDING']:'') : '');	
					$DOOR_BARCODE 			=mysql_real_escape_string((strlen($test_variable['DOOR_BARCODE'])>0) 		? (($test_variable['DOOR_BARCODE']!=='0')			?$test_variable['DOOR_BARCODE']:'') : '');	
					$COMPLIANT 				=mysql_real_escape_string((strlen($test_variable['COMPLIANT'])>0) 			? (($test_variable['COMPLIANT']!=='0')				?$test_variable['COMPLIANT']:'') : '');	
					$TYPE_OTHER 			=mysql_real_escape_string((strlen($test_variable['TYPE_OTHER'])>0)			? (($test_variable['TYPE_OTHER']!=='0')				?$test_variable['TYPE_OTHER']:'') : '');
					$STYLE 					=mysql_real_escape_string((strlen($test_variable['STYLE'])>0)				? (($test_variable['STYLE']!=='0')					?$test_variable['STYLE']:'') : '');
					$MATERIAL 				=mysql_real_escape_string((strlen($test_variable['MATERIAL'])>0)				? (($test_variable['MATERIAL']!=='0')				?$test_variable['MATERIAL']:'') : '');
					$MATERIAL_OTHER 		=mysql_real_escape_string((strlen($test_variable['MATERIAL_OTHER'])>0)		? (($test_variable['MATERIAL_OTHER']!=='0')			?$test_variable['MATERIAL_OTHER']:'') : '');
					$ELEVATION 				=mysql_real_escape_string((strlen($test_variable['ELEVATION'])>0)			? (($test_variable['ELEVATION']!=='0')				?$test_variable['ELEVATION']:'') : '');
					$ELEVATION_OTHER 		=mysql_real_escape_string((strlen($test_variable['ELEVATION_OTHER'])>0)		? (($test_variable['ELEVATION_OTHER']!=='0')		?$test_variable['ELEVATION_OTHER']:'') : '');
					$FRAME_MATERIAL 		=mysql_real_escape_string((strlen($test_variable['FRAME_MATERIAL'])>0)		? (($test_variable['FRAME_MATERIAL']!=='0')			?$test_variable['FRAME_MATERIAL']:'') : '');
					$FRAME_MATERIAL_OTHER 	=mysql_real_escape_string((strlen($test_variable['FRAME_MATERIAL_OTHER'])>0) ? (($test_variable['FRAME_MATERIAL_OTHER']!=='0')	?$test_variable['FRAME_MATERIAL_OTHER']:'') : '');
					$FRAME_ELEVATION 		=mysql_real_escape_string((strlen($test_variable['FRAME_ELEVATION'])>0)		? (($test_variable['FRAME_ELEVATION']!=='0')		?$test_variable['FRAME_ELEVATION']:'') : '');
					$FRAME_ELEVATION_OTHER 	=mysql_real_escape_string((strlen($test_variable['FRAME_ELEVATION_OTHER'])>0)? (($test_variable['FRAME_ELEVATION_OTHER']!=='0')	?$test_variable['FRAME_ELEVATION_OTHER']:'') : '');
					$FIRE_RATING_1 			=mysql_real_escape_string((strlen($test_variable['FIRE_RATING_1'])>0)		? (($test_variable['FIRE_RATING_1']!=='0')			?$test_variable['FIRE_RATING_1']:'') : '');
					$FIRE_RATING_2 			=mysql_real_escape_string((strlen($test_variable['FIRE_RATING_2'])>0)		? (($test_variable['FIRE_RATING_2']!=='0')			?$test_variable['FIRE_RATING_2']:'') : '');
					$FIRE_RATING_3 			=mysql_real_escape_string((strlen($test_variable['FIRE_RATING_3'])>0)		? (($test_variable['FIRE_RATING_3']!=='0')			?$test_variable['FIRE_RATING_3']:'') : '');
					$FIRE_RATING_4 			=mysql_real_escape_string((strlen($test_variable['FIRE_RATING_4'])>0)		? (($test_variable['FIRE_RATING_4']!=='0')			?$test_variable['FIRE_RATING_4']:'') : '');
					$TEMP_RISE 				=mysql_real_escape_string((strlen($test_variable['TEMP_RISE'])>0)			? (($test_variable['TEMP_RISE']!=='0')				?$test_variable['TEMP_RISE']:'') : '');
					$MANUFACTURER		 	=mysql_real_escape_string((strlen($test_variable['MANUFACTURER'])>0)			? (($test_variable['MANUFACTURER']!=='0')			?$test_variable['MANUFACTURER']:'') : '');
					$BARCODE 				=mysql_real_escape_string((strlen($test_variable['BARCODE'])>0)				? (($test_variable['BARCODE']!=='0')				?$test_variable['DOOR_BARCODE']:'') : '');
					$REMARKS 				=mysql_real_escape_string((strlen($test_variable['REMARKS'])>0)				? (($test_variable['REMARKS']!=='0')				?mysql_real_escape_string($test_variable['REMARKS']):'') : '');
					$LOCATION				=mysql_real_escape_string((strlen($test_variable['LOCATION'])>0)				? (($test_variable['LOCATION']!=='0')				?mysql_real_escape_string($test_variable['LOCATION']):'') : '');
					$HARDWARE_GROUP 		=mysql_real_escape_string((strlen($test_variable['HARDWARE_GROUP'])>0)		? (($test_variable['HARDWARE_GROUP']!=='0')			?$test_variable['HARDWARE_GROUP']:'') : '');
					$HARDWARE_SET 			=mysql_real_escape_string((strlen($test_variable['HARDWARE_SET'])>0)			? (($test_variable['HARDWARE_SET']!=='0')			?$test_variable['HARDWARE_SET']:'') : '');
					//Converting array to sql string for door detail 
					$SQL_DOOR_DETAILS="";
					foreach($DOORINFORMATION['DOOR_DETAILS'] as $DOOR_DETAILS_KEY =>$DOOR_DETAILS_VALUE){
						$SQL_DOOR_DETAILS .= $DOOR_DETAILS_KEY." = IF(LENGTH('".mysql_real_escape_string($DOOR_DETAILS_VALUE)."')=0,NULL,'".mysql_real_escape_string($DOOR_DETAILS_VALUE)."'),";
					}
					//Converting array to sql string for Frame detail 
					$SQL_FRAME_CHECK_LIST="";
					foreach($DOORINFORMATION['FRAME_CHECK_LIST'] as $FRAME_CHECK_LIST_Key =>$FRAME_CHECK_LIST_VALUE){
						$SQL_FRAME_CHECK_LIST .= $FRAME_CHECK_LIST_Key." = IF(LENGTH('".mysql_real_escape_string($FRAME_CHECK_LIST_VALUE)."')=0,NULL,'".mysql_real_escape_string($FRAME_CHECK_LIST_VALUE)."'),";
					}	
					// insertion of new door
					$sql_door = "
						INSERT INTO door set
						MODEL	 = IF(LENGTH('".$MODEL."')=0,NULL,'".$MODEL	."'),
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
						TEMP_RISE = IF(LENGTH('".$TEMP_RISE."')=0,NULL,'".$TEMP_RISE."'),
						MANUFACTURER = IF(LENGTH('".$MANUFACTURER."')=0,NULL,'".$MANUFACTURER."'),
						BARCODE = IF(LENGTH('".$BARCODE."')=0,NULL,'".$BARCODE."'),
						REMARKS = IF(LENGTH('".$REMARKS."')=0,NULL,'".$REMARKS."'),"
						.$SQL_DOOR_DETAILS
						.$SQL_FRAME_CHECK_LIST.	
						"HARDWARE_GROUP = IF(LENGTH('".$HARDWARE_GROUP."')=0,NULL,'".$HARDWARE_GROUP."'),
						HARDWARE_SET = IF(LENGTH('".$HARDWARE_SET."')=0,NULL,'".$HARDWARE_SET."')
					";			
				$result_door=mysql_query($sql_door);
				if(!$result_door){
					$me = mysql_error();
					self::$Data_Return['error_message'] = $me;
					error_log($sql_door);
					error_log('Error during door insert in printing: ' . $me);
					return false;
				}
				//add the door id in the response
				self::$Data_Return['DOOR_ID'][$DOORID]=$DOOR_ID = mysql_insert_id();
				//add the building id in the response
				self::$Data_Return['methodIdentifier']="uploadInspection";
				//add the building owner id in the response
				foreach($DOORINFORMATION['DOORFIREDETAIL']['DOOR_TYPE']['TYPE_ID'] as $DOOR_TYPE_KEY =>$DOOR_TYPE_VALUE){
					// insertion of door type from the door fire panel
					$sql_door_type = "
						INSERT INTO door_type set
						DOOR_ID='".$DOOR_ID."',
						TYPE_ID = '".$DOOR_TYPE_VALUE."'
					";
					$result_inpection_list=mysql_query($sql_door_type);
					if (!$result_inpection_list){
						$me = mysql_error();
						self::$Data_Return['error_message'] = $me;
						error_log($sql_door_type);
						error_log('Error during insert to the door type in printing: ' . $me);
						return false;
					}										
				}
				foreach($DOORINFORMATION['HARDWARE_CHECK_LIST'] as $HARDWARE_CHECK_LIST_KEY =>$HARDWARE_CHECK_LIST_VALUE){
					// insertion of hardware detail 
					$sql_hardware = "
						INSERT INTO hardware set
						DOOR_ID='".$DOOR_ID."',
						ITEM_ID = '".mysql_real_escape_string($HARDWARE_CHECK_LIST_VALUE['ITEM_ID'])."',
						VERIFY = '".mysql_real_escape_string($HARDWARE_CHECK_LIST_VALUE['VERIFY'])."',
						QTY = '".mysql_real_escape_string($HARDWARE_CHECK_LIST_VALUE['QTY'])."',
						ITEM = '".mysql_real_escape_string($HARDWARE_CHECK_LIST_VALUE['ITEM'])."',
						PRODUCT = '".mysql_real_escape_string($HARDWARE_CHECK_LIST_VALUE['PRODUCT'])."',
						MFG = '".mysql_real_escape_string($HARDWARE_CHECK_LIST_VALUE['MFG'])."',
						FINISH = '".mysql_real_escape_string($HARDWARE_CHECK_LIST_VALUE['FINISH'])."'
					";
					$result_hardware=mysql_query($sql_hardware);
					if(!$result_hardware){
						$me = mysql_error();
						self::$Data_Return['error_message'] = $me;
						error_log($sql_hardware);
						error_log('Error during insert to hardware in printing: ' . $me);
						return false;
					}																
				}
				foreach($DOORINFORMATION['INSECPTION_CHECK_LIST'] as $INSECPTION_CHECK_LIST_KEY =>$INSECPTION_CHECK_LIST_VALUE){
					// inserstion of the inpection check list
					$sql_inpection_list = "
						INSERT INTO door_code set
						DOOR_ID='".mysql_real_escape_string($DOOR_ID)."',
						CODE_ID = '".mysql_real_escape_string($INSECPTION_CHECK_LIST_VALUE['CODE_ID'])."',
						ACTIVE = '".mysql_real_escape_string($INSECPTION_CHECK_LIST_VALUE['ACTIVE'])."',
						CONTROL_NAME = '".mysql_real_escape_string($INSECPTION_CHECK_LIST_VALUE['CONTROL_NAME'])."'
					";
					$result_inpection_list=mysql_query($sql_inpection_list);
					if (!$result_inpection_list){
						$me = mysql_error();
						self::$Data_Return['error_message'] = $me;
						error_log($sql_inpection_list);
						error_log('Error during insert to door code in printing: ' . $me);
						return false;
					}											
				}
				// inserstion of the inpection check list other field detail.
				$sql_delete_door_type = "
					DELETE from inspection_other 	
					WHERE INSPECTION_ID='".$INSPECTION_ID."'
				";
				foreach($DOORINFORMATION['INSPECTION_OTHER'] as $INSPECTION_OTHER_KEY =>$INSPECTION_OTHER_VALUE){
					$sql_other_inpection = "
						INSERT INTO inspection_other set
						INSPECTION_ID ='".$INSPECTION_ID."',
						OTHER_ID = '".mysql_real_escape_string($INSPECTION_OTHER_VALUE['OTHER_ID'])."',
						OTHER_VALUE = '".mysql_real_escape_string($INSPECTION_OTHER_VALUE['OTHER_VALUE'])."'
					";
					$result_inpection_list=mysql_query($sql_other_inpection);
					if(!$result_inpection_list){
						$me = mysql_error();
						self::$Data_Return['error_message'] = $me;
						error_log($sql_other_inpection);
						error_log('Error during insert to inspection_other in printing: ' . $me);
						return false;
					}											
				}
				foreach($DOORINFORMATION['PICTURES'] as $PICTURES_KEY =>$PICTURES_VALUE){
					$Picture_File_Location = IMAGE_DIR_ROOT.$PICTURES_VALUE['PICTURE_FILE'];
					if (move_uploaded_file($_FILES['Profile_Image']['tmp_name'],$Picture_File_Location )){
						$sql_picture = "
							INSERT INTO picture set
							DOOR_ID = '".$DOOR_ID."',
							PICTURE_FILE = '".$PICTURES_VALUE['PICTURE_FILE']."',
							CONTROL_NAME = '".$PICTURES_VALUE['CONTROL_NAME']."',
							NOTE = '".$PICTURES_VALUE['NOTE']."'
						";
						$queryResult = mysql_query($sql_picture);
						if (!$queryResult){
							$me = mysql_error();
							self::$Data_Return['error_message'] = $me;
							error_log($sql_picture);
							error_log('Error during insert to picture in printing: ' . $me);
							return false;
						}
					}
					else{
						error_log('Was not able to properly upload the door picture in printing for DOOR_ID:' . $DOOR_ID);
						self::$Data_Return['error_message'] = 'Was not able to properly upload the door picture in printing for DOOR_ID:' . $DOOR_ID;
						return false;
					}
				}
			}
			}// end foreach of multiple door detail		
		}else{
			//add the building id in the response
			self::$Data_Return['methodIdentifier']="uploadInspection";
			//reteurn true for result_door
			$result_door=true;	
		}
			
		if($result_inpection and $result_door){
			return true;
		}else{
			self::$Data_Return['methodIdentifier']="uploadInspection";
			return false;
		}
	}	
}
?>
