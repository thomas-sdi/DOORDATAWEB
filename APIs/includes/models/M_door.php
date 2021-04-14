<?php
/*require_once('../config.php');
require_once('../database.php');
*/
class M_door
{
	public static function get_door_data($INSPECTION_ID)
	{
		global $DB;
		$sqlDoor="
			 	SELECT d.* 
				FROM  `door` as d WHERE INSPECTION_ID ='".$INSPECTION_ID."'
				
			 ";
		$resultDoor = $DB->query($sqlDoor);
		$record1 = array();
		///$record = array();
		$FinalRecord = array();
		
 		if($DB->num_rows($resultDoor)>0)
		{
			while($fetchDoor=$DB->fetch_assoc($resultDoor))
			{
				$record = array();
				
				/// STORES DATA FROM door TABLE
				$record=$fetchDoor;
				
				/// STORES DATA FROM door_code TABLE
				$sqlDoor_Code = "
								SELECT dc.* 
								FROM `door_code` as dc WHERE DOOR_ID = '".$fetchDoor['ID']."'
								";
				$resultDoor_Code = $DB->query($sqlDoor_Code);
				$record1['door_code']=array();
				if($DB->num_rows($resultDoor_Code)>0){
					while($fetchDoor_Code=$DB->fetch_assoc($resultDoor_Code)){
					
						$record1['door_code'][]=$fetchDoor_Code;
					}
				}
				$record['door_code'] = $record1['door_code'];
				/// STORES DATA FROM door_type TABLE
				$sqlDoor_Type = "
								SELECT dt.* 
								FROM `door_type` as dt WHERE DOOR_ID = '".$fetchDoor['ID']."'
								";
				$resultDoor_Type = $DB->query($sqlDoor_Type);
				$record1['door_type'] = array();
				if($DB->num_rows($resultDoor_Type)>0){
					while($fetchDoor_Type=$DB->fetch_assoc($resultDoor_Type)){
							
						$record1['door_type'][]=$fetchDoor_Type;
					}
				}
				$record['door_type'] = $record1['door_type'];
				/// STORES DATA FROM door_note TABLE
				$sqlDoor_Note = "
								SELECT dn.* 
								FROM `door_note` as dn WHERE DOOR_ID = '".$fetchDoor['ID']."'
								";
				$resultDoor_Note = $DB->query($sqlDoor_Note);
			$record1['door_note'] = array();
				if($DB->num_rows($resultDoor_Note)>0){
					while($fetchDoor_Note=$DB->fetch_assoc($resultDoor_Note)){
							
						$record1['door_note'][]=$fetchDoor_Note;
					}
				}
				$record['door_note']=$record1['door_note'];
				/// STORES DATA FROM hardware TABLE
				$sqlHardware = "
								SELECT h.* 
								FROM `hardware` as h WHERE DOOR_ID = '".$fetchDoor['ID']."'
								";
				$resultHardware = $DB->query($sqlHardware);
				$record1['hardware'] = array();
				if($DB->num_rows($resultHardware)>0){
					while($fetchHardware=$DB->fetch_assoc($resultHardware)){
							
						$record1['hardware'][]=$fetchHardware;
					}
				}
				$record['hardware'] = $record1['hardware'];
				/// STORES DATA FROM picture TABLE
				$sqlPicture = "
								SELECT p.* 
								FROM `picture` as p WHERE DOOR_ID = '".$fetchDoor['ID']."'
								";
				$resultPicture = $DB->query($sqlPicture);
			$record1['picture'] = array();
			
				if($DB->num_rows($resultPicture)>0){
					while($fetchPicture = $DB->fetch_assoc($resultPicture)){
							
						$record1['picture'][]=$fetchPicture;
						
					}
				}
				$record['picture'] = $record1['picture'];
				/// STORES DATA FROM floorplan TABLE
				/*$sqlFloorPlan = "
								SELECT fp.* 
								FROM `floorplan` as fp WHERE DOOR_ID = '".$fetchDoor['ID']."'
								";
				$resultFloorPlan = $DB->query($sqlFloorPlan);
			
				if($DB->num_rows($resultFloorPlan)>0){
					while($fetchFloorPlan = $DB->fetch_assoc($resultFloorPlan)){
							
						$record['door']['floor_plan'][]=$fetchFloorPlan;
					}
				}*/
				/// STORES DATA FROM framedetail TABLE
				$sqlFrameDetail = "
								SELECT fd.* 
								FROM `framedetail` as fd WHERE DOOR_ID = '".$fetchDoor['ID']."'
								";
				$resultFrameDetail = $DB->query($sqlFrameDetail);
			$record1['frame_detail'] = array();
				if($DB->num_rows($resultFrameDetail)>0){
					while($fetchFrameDetail = $DB->fetch_assoc($resultFrameDetail)){
							
						$record1['frame_detail'][]=$fetchFrameDetail;
					}
				}
				$record['frame_detail'] = $record1['frame_detail'];
				/// STORES DATA FROM ink TABLE
				$sqlInk = "
								SELECT i.* 
								FROM `ink` as i WHERE DOOR_ID = '".$fetchDoor['ID']."'
								";
				$resultInk = $DB->query($sqlInk);
				$record1['ink'] = array();
				if($DB->num_rows($resultInk)>0){
					while($fetchInk = $DB->fetch_assoc($resultInk)){
							
						$record1['ink'][]=$fetchInk;
					}
				}
				$record['ink']  =  $record1['ink'];
				$record['picture_URL'] = IMAGE_DIR_ROOT;
				/// RECOMANDATIONS DATA FROM RECOMENTATION TABLE
				$sqlRecomendation= "
				SELECT dt.RECOMENDATION
				FROM `recomendation` as dt WHERE DOOR_ID = '".$fetchDoor['ID']."'
				";
				$resultRecomend = $DB->query($sqlRecomendation);
				$strRecomand='';
				if($DB->num_rows($resultRecomend)>0){
					while($fetchRecomend=$DB->fetch_assoc($resultRecomend)){						
						$strRecomand=$fetchRecomend['RECOMENDATION'];
					}
				}
				$record['RECOMANDATION'] = $strRecomand;

				$FinalRecord[] =  $record;
			}
		}
		
		$FinalRecord1['Door']=$FinalRecord;
		
		$sqlOtherInpection = "SELECT io.* 
										FROM `inspection_other` as io WHERE INSPECTION_ID = '".$INSPECTION_ID."'";
		$resultOtherInpection = $DB->query($sqlOtherInpection);
		$recordOtherInpection = array();
		if($DB->num_rows($resultOtherInpection)>0){
			
			while($fetchOtherInpection = $DB->fetch_assoc($resultOtherInpection)){				
				$recordOtherInpection[]=$fetchOtherInpection;
			}
		}
		
		$FinalRecord1['OtherInpection']=$recordOtherInpection;
		
		/*echo "<pre>";
		print_r($FinalRecord1);*/
		return $FinalRecord1;
	}	
	
	public static function insert_new_door($data)
	{
		global $DB;
		$sql="
			 	INSERT INTO door
				(
					".join(',',array_keys($data))."
				)
				VALUES
				(
					".join(',',array_values($data))."
				)
			 ";	
		$result=$DB->query($sql);
		if($result)
		{
			return $DB->insert_id();
		}
		else
		{
			return false;	
		}
		
	}	
}
?>