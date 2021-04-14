<?
require_once APPLICATION_PATH . '/models/DBTable/Door.php';
require_once APPLICATION_PATH . '/models/Abstract.php';
require_once APPLICATION_PATH . '/models/Inspect.php';
require_once APPLICATION_PATH . '/models/Dictionary.php';
require_once APPLICATION_PATH . '/models/Inspection.php';
require_once APPLICATION_PATH . '/models/Building.php';
require_once APPLICATION_PATH . '/models/Hardware.php';
require_once APPLICATION_PATH . '/models/Inspectionother.php';
require_once APPLICATION_PATH . '/models/DoorCode.php';
require_once APPLICATION_PATH . '/models/DoorType.php';
require_once APPLICATION_PATH . '/models/DoorNote.php';
require_once APPLICATION_PATH . '/models/Floorplan.php';
require_once APPLICATION_PATH . '/models/Employee.php';
require_once APPLICATION_PATH . '/models/Audio.php';
require_once APPLICATION_PATH . '/models/Picture.php';
require_once APPLICATION_PATH . '/models/Ink.php';

class Model_Door extends Model_Abstract{
	
	const DEFAULT_DOOR_MATERIAL = 1015;
	const DEFAULT_DOOR_ELEVATION = 1019;
	const DEFAULT_FRAME_MATERIAL = 1028;
	const DEFAULT_FRAME_ELEVATION = 1030;
	const DEFAULT_DOOR_TYPE = 1006;
	const DEFAULT_DOOR_STYLE = 1003;

	protected function _init(){
		$this->_table = new DBTable_Door();
        //add reference models for look-up columns
		$this->addReferenceModel('INSPECTION_ID', Model_Inspection::retrieve());
		$this->addReferenceModel('MATERIAL', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('COMPLIANT', Model_Dictionary::retrieve());
		$this->addReferenceModel('ELEVATION', Model_Dictionary::retrieve());
		$this->addReferenceModel('FRAME_MATERIAL', Model_Dictionary::retrieve());
		$this->addReferenceModel('FRAME_ELEVATION', Model_Dictionary::retrieve());
		$this->addReferenceModel('FIRE_RATING_1', Model_Dictionary::retrieve());
		$this->addReferenceModel('FIRE_RATING_2', Model_Dictionary::retrieve());
		$this->addReferenceModel('FIRE_RATING_3', Model_Dictionary::retrieve());   
		$this->addReferenceModel('FIRE_RATING_4', Model_Dictionary::retrieve());
		$this->addReferenceModel('STYLE', Model_Dictionary::retrieve());  
		$this->addReferenceModel('BUILDING_ID', Model_Building::retrieve()); 
		$this->addReferenceModel('TEMP_RISE', Model_Dictionary::retrieve());
		$this->addReferenceModel('LISTING_AGENCY', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('STYLE', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('HANDING', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('HINGE_HEIGHT', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('HINGE_THICKNESS', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('HINGE_FRACTION1', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('HINGE_FRACTION2', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('HINGE_FRACTION3', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('HINGE_FRACTION4', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('LISTING_AGENCY', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('HINGE_BACKSET', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('TOP_TO_CENTERLINE_FRACTION', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('LOCK_BACKSET', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('STRIKE_HEIGHT', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('PREFIT_FRACTION_X', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('PREFIT_FRACTION_Y', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('FRAME_OPENING_FRACTION_X', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('FRAME_OPENING_FRACTION_Y', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('LITE_CUTOUT_FRACTION_X', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('LITE_CUTOUT_FRACTION_Y', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('LOCKSTILE_FRACTION', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('TOPRAIL_FRACTION', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('A_FRACTION', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('B_FRACTION', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('C_FRACTION', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('D_FRACTION', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('E_FRACTION', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('F_FRACTION', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('G_FRACTION', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('H_FRACTION', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('I_FRACTION', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('J_FRACTION', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('K_FRACTION', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('L_FRACTION', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('M_FRACTION', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('N_FRACTION', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('O_FRACTION', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('P_FRACTION', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('Q_FRACTION', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('R_FRACTION', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('S_FRACTION', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('T_FRACTION', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('U_FRACTION', Model_Dictionary::retrieve()); 
		$this->addReferenceModel('V_FRACTION', Model_Dictionary::retrieve()); 

		$this->addValidationRule(new Validation_Rule_Required('NUMBER'));
		$this->addValidationRule(new Validation_Rule_Unique(array('NUMBER', 'INSPECTION_ID'), Validation_Rule::ERROR, 'Door number must be unique for each inspection'));
		
		$this->_name = 'LOCATION';

		parent::_init();
	}

	public static function retrieve($class=null) {
		return parent::retrieve(__CLASS__);
	}
	
	public function isDoorEditable($doorId){
		if (!$doorId) return;
		
		$door = self::retrieve()->fetchEntry($doorId, array('INSPECTION_ID'));
		$inspection = Model_Inspection::retrieve()->fetchEntry($door['INSPECTION_ID'], array('STATUS', 'INSPECTOR_ID'));

		/*
		$user = App::user();
		if (!$user) return false;
		if (!$user->id) return false;
		$employeeId = Model_Employee::getEmployeeIdbyUserId($user->id);
		App::log('$employeeId: ' . $employeeId);
		 * */
		
		//TODO: this has to be based on user roles!
		switch($inspection['STATUS']){
			case Model_Inspection::PENDING:
			case Model_Inspection::INCOMPLETED:
			return true;
			break;
			case Model_Inspection::SUBMITTING:
			return false;
			break;
			case Model_Inspection::SUBMITTED:
				//this means the inspection is being edited on the mobile device
			return false;
			break;
			case Model_Inspection::COMPLETED:
			return false;
			break;
			default:
			return false;
			break;
		}
		
	}

	// make sure to delete related door types, door codes and door notes
	public function delete($id, $ignored = NULL) {

		Model_DoorCode::retrieve()->deleteEntries(array(new Data_Column('DOOR_ID', $id)));
		Model_DoorType::retrieve()->deleteEntries(array(new Data_Column('DOOR_ID', $id)));
		Model_DoorNote::retrieve()->deleteEntries(array(new Data_Column('DOOR_ID', $id)));
		Model_Hardware::retrieve()->deleteEntries(array(new Data_Column('DOOR_ID', $id)));
		Model_Floorplan::retrieve()->deleteEntries(array(new Data_Column('DOOR_ID', $id)));
		Model_Audio::retrieve()->deleteEntries(array(new Data_Column('DOOR_ID', $id)));
		Model_Picture::retrieve()->deleteEntries(array(new Data_Column('DOOR_ID', $id)));
		Model_Ink::retrieve()->deleteEntries(array(new Data_Column('DOOR_ID', $id)));

		return parent::delete($id);
	}

	public function saveClean($data) {
		return parent::save($data);
	}
	
	public function save($data, $ignored=null, $clean=true){
		$isNewDoor = !array_key_exists('ID', $data);
		
    	// delete all doors with number 000
		if ($isNewDoor) {
			self::retrieve()->deleteEntries(array(new Data_Column('NUMBER', '000')));
		}

		$inspectionId = array_value('INSPECTION_ID', $data);
		
		// if the building information is missing, take it from inspection
		if ($inspectionId) {
			$data['BUILDING_ID'] = Model_Inspection::retrieve()->getInspectionBuildingId($inspectionId);
		}
		
		// cleanup the door record by extracting artificial informational attributes
		$codes = array();
		$codes_other = array();
		$types = array();
		$pictures = array();
		$audio = array();
		foreach ($data as $field => $value) {	
			//door_code and door_code_other
			if (strpos($field, 'CODE_') === 0) {
		    	if (strpos($field, '_OTHER') == false){ // what means this is not "Other" additional field
		    	$code_id = substr($field, 5);
		    	$codes[$code_id] = $value;
		    	unset($data[$field]);
		    }
		    	else { //this is "Other" additional field, we'll put it in a separate array
		    	$code_id = str_replace('_OTHER', '', str_replace('CODE_', '', $field));
		    	$codes_other[$code_id] = $value;
		    	unset($data[$field]);
		    }
		}


			//door_type
		if (strpos($field, 'doorType_') === 0) {
			$type_id = substr($field, 9);
			$types[$type_id] = $value;
			unset($data[$field]);
		}

			//pictures
		if (strpos($field, 'picture_') === 0) {
			$pictures[$field] = $value;
			unset($data[$field]);
		}

			//audio
		if (strpos($field, 'audio_') === 0) {
			$audio[$field] = $value;
			unset($data[$field]);
		}
	}
	$door_id = parent::save($data, $ignored);

		// first we always erase old codes
	Model_DoorCode::retrieve()->deleteEntries(array(new Data_Column('DOOR_ID', $door_id)));
	foreach ($codes as $code_id => $value) {
		Model_DoorCode::retrieve()->save(array('DOOR_ID' => $door_id, 'CODE_ID' => $code_id));
	}

	Model_Inspection_Other::retrieve()->deleteEntries(array(new Data_Column('INSPECTION_ID', $inspectionId)));
	foreach ($codes_other as $code_id => $value){
		Model_Inspection_Other::retrieve()->save(
			array('OTHER_ID'      => $code_id, 
				'OTHER_VALUE'   => $value,
				'INSPECTION_ID' => $inspectionId)
		);
	}

	$codesDB = Model_DoorCode::retrieve()->fetchEntries(array(new Data_Column('DOOR_ID', $door_id)));
		if (count($codesDB) > 0) //if has non-compliant codes
		parent::save(array('ID' => $door_id, 'COMPLIANT' => Model_Dictionary::getIdByItem('No', 'Logical')));	
		else {
			//if non-compliant field not set to 'Yes', set it to null
			$door_comp = Model_Door::retrieve()->fetchEntry($door_id);
			if ($door_comp['COMPLIANT'] == 136 && !array_key_exists('COMPLIANT', $data)) {
				parent::save(array('ID' => $door_id, 'COMPLIANT' => null));	 
			}
		}

		//in order to avoid duplication of type entries, remove all existing door types
		//if (count($types) > 0) {
		Model_DoorType::retrieve()->deleteEntries(array(new Data_Column('DOOR_ID', $door_id)));
		//}
		$types = array_reverse($types);
		foreach ($types as $type_id => $value) {
			if ($value == '1') {
				Model_DoorType::retrieve()->save(array('DOOR_ID' => $door_id, 'TYPE_ID' => $type_id));
			}
		}
		
		foreach ($pictures as $picture_id) {
			Model_Picture::retrieve()->save(array('ID' => $picture_id, 'DOOR_ID' => $door_id));
		}
		
		
		foreach ($audio as $audio_id) {
			Model_Audio::retrieve()->save(array('ID' => $audio_id, 'DOOR_ID' => $door_id));
		}
		
		// trigger the update event for the master inspection (e.g. to record the update on the inspection itself)
		Model_Inspection::retrieve()->updateInspectionHistory($inspectionId);

		return  $door_id;
	}

	static public function getCodes($doorId) {
		$codes = array();
		if ($doorId > 0) { 
			foreach (Model_DoorCode::retrieve()->fetchEntries(array(
				new Data_Column('DOOR_ID', $doorId), 'CODE_ID'), null, true) as $code)
			{
				$codes[$code['CODE_ID']] = true;
			}
		}
		return $codes;
	}

	/*
	 * The function deletes all the codes for a specific door 
	 */
	public function cleanCodes($doorId){
		if (!$doorId) return;
		Model_DoorCode::retrieve()->deleteEntries(array(new Data_Column('DOOR_ID', $doorId)));
	}
	
	/**
	 * The function sets the door codes for a specific door
	 */
	public function setCodes($doorId, $codes){
		if (!$doorId) return;
		
		foreach ($codes as $code){
			if (!$code) continue;
			Model_DoorCode::retrieve()->save(array(
				'DOOR_ID' => $doorId,
				'CODE_ID' => $code
			));
		}
		
	}
	
	/**
	 * The function deletes all the door types for a specific door
	 */
	public function cleanDoorTypes($doorId){
		if ($doorId) return;
		Model_DoorType::retrieve()->deleteEntries(array(new Data_Column('DOOR_ID', $doorId)));
	}
	
	/**
	 * The function sets the door types for a specific door
	 */
	public function setDoorTypes($doorId, $types){
		if (!$doorId) return;
		$types = array_reverse($types);
		
		foreach($types as $type){
			if (!$type) continue;
			Model_DoorType::retrieve()->save(array(
				'DOOR_ID'	=> $doorId,
				'TYPE_ID'	=> $type
			));
		}
	}
	
	static public function getTypes($doorId) {
		$types = array();
		if ($doorId > 0) { // existing door
			foreach (Model_DoorType::retrieve()->fetchEntries(array(
				new Data_Column('DOOR_ID', $doorId),'TYPE_ID'), null, true) as $type)
			{
				$types[$type['TYPE_ID']] = true;
			}
		}
		else { // new door, need default values
			$types[self::DEFAULT_DOOR_TYPE] = true;
		}
		
		return $types;
	}
	
	public function getPictures($doorId) {
		$pictures = array(null, null);
		if ($doorId > 0) {
			foreach (Model_Picture::retrieve()->fetchEntries(array(
				'ID', 'DOOR_ID', 'PICTURE_FILE', 'NOTE', 'CONTROL_NAME', new Data_Column('DOOR_ID', $doorId)),
				null, true, 'ID') as $picture)
			{
				if ($picture['CONTROL_NAME'][6] == '1') $pictures[0] = $picture; 
				if ($picture['CONTROL_NAME'][6] == '2') $pictures[1] = $picture; 
				if ($picture['CONTROL_NAME'][6] == '3') $pictures[2] = $picture; 
				if ($picture['CONTROL_NAME'][6] == '4') $pictures[3] = $picture;
				//$pictures[] = $picture;
			}
		}
		return $pictures;
	}
	
	public function getAudio($doorId) {
		return $doorId > 0 ? Model_Audio::retrieve()->fetchEntry(null, array(
			'ID', 'AUDIO_FILE', 'NOTE', new Data_Column('DOOR_ID', $doorId)), null, true) : null;
	}
	
	static public function getOthers($doorId, $inspectionIdSuper) {
		$other = array();
		$inspectionId = null;
		//if inspectionId was already passed, let's use this value
		if ($inspectionIdSuper != null) $inspectionId = $inspectionIdSuper;
		//if it wasn't let's take it from the database
		if ($doorId > 0) { 
			$inspectionId = Model_Door::retrieve()->fetchEntry($doorId, array('INSPECTION_ID'));
			$inspectionId = $inspectionId['INSPECTION_ID'];
		}
		
		if ($inspectionId != null) { 
			foreach (Model_Inspection_Other::retrieve()->fetchEntries(array(
				new Data_Column('INSPECTION_ID', $inspectionId),
				'OTHER_ID', 'OTHER_VALUE'), null, true) as $value)
			{
				$other[$value['OTHER_ID']] = $value['OTHER_VALUE'];		
			}
		}
		return $other;		
	}
	
	public function savePicture($doorId, $picture, $pictureId, $pictureNote, $pictureOrder) {
		$result = array(); $targetFileName = null;
		$controlName = 'Camera' . $pictureOrder . '_1';
		$record = array('DOOR_ID' => $doorId, 'NOTE' => $pictureNote, 'CONTROL_NAME' => $controlName);
		
		
		if (strlen($picture['name']) > 0) {
			$parts = explode('.', $picture['name']);
			$ext = array_pop($parts);
			$targetFileName = 'pict_door_' . $doorId . '_' . date('YmdHis') . '.' . $ext;
			move_uploaded_file($picture['tmp_name'], ROOT_PATH . '/content/pictures/' . $targetFileName);
			$record['PICTURE_FILE'] = $targetFileName;
			
			//if id is set, update picture
			$oldPictureFile = null;
			if (strlen($pictureId) > 0) {
				$oldPicture = Model_Picture::retrieve()->fetchEntry($pictureId);
				unlink(ROOT_PATH . '/content/pictures/' .  $oldPicture['PICTURE_FILE']);
				$oldPictureFile = $oldPicture['PICTURE_FILE'];
				$record['ID'] = $pictureId;
			}
			$pictureId = Model_Picture::retrieve()->save($record);
			
			//now, we need to upload the picture to FTP server
			/*Model_Picture::retrieve()->updatePictureOnPDFServer(
				ROOT_PATH . '/content/pictures/' . $targetFileName,
				$oldPictureFile //this file will be deleted, if it exists on the PDF Server
			);*/
			
		} else if (strlen($pictureId) > 0) {
			$record['ID'] = $pictureId;
			$pictureId = Model_Picture::retrieve()->save($record);
		}
		
		$result[$pictureId] = '/content/pictures?id=' . $targetFileName;
		return $result;
	}
	
	public function saveAudio($doorId, $audio, $audioId, $audioNote) {
		$result = array(); $targetFileName = null;
		$record = array('DOOR_ID' => $doorId, 'NOTE' => $audioNote);
		if ($audio['name'] != '') {
			$parts = explode('.', $audio['name']);
			$ext = array_pop($parts);
			$targetFileName = 'audio_door_' . $doorId . '_' . date('YmdHis') . '.' . $ext;
			move_uploaded_file($audio['tmp_name'], ROOT_PATH . '/content/audio/' . $targetFileName);
			$record['AUDIO_FILE'] = $targetFileName;
		}
		if ($audioId && $audioId != '') {
			$oldAudio = Model_Audio::retrieve()->fetchEntry($audioId);
			unlink(ROOT_PATH . '/content/audio/' . $oldAudio['AUDIO_FILE']);
			$record['ID'] = $audioId;
		}
		
		$audioId = Model_Audio::retrieve()->save($record);
		$result[$audioId] = '/content/audio?id=' . $targetFileName;
		return $result;		
	}
	
	/**
	 * Copies an existing door to a new inspection
	 */
	public function copyToInspection($existingDoorId, $newInspectionId, $clearChecklist=false) {
		// get the door record & copy it entirely as a new door at a new inspection
		$door = $this->fetchEntry($existingDoorId);
		unset($door['ID']);
		$door['INSPECTION_ID'] = $newInspectionId;
		
		// if the checklist is getting cleared, clear the "inspected" flag as well
		if ($clearChecklist) {
			$door['COMPLIANT'] = null;
		}
		$newDoorId = $this->saveClean($door);
		
		// copy the door types
		$this->_copyDoorTypes($existingDoorId, $newDoorId);
		
		// copy the door notes
		$this->_copyDoorNotes($existingDoorId, $newDoorId);
		
		// copy the door floor plan
		$this->_copyFloorPlan($existingDoorId, $newDoorId);
		
		// copy the door ink
		$this->_copyInk($existingDoorId, $newDoorId);
		
		// copy the door hardware
		$this->_copyHardware($existingDoorId, $newDoorId);
		
		// copy the door pictures
		$this->_copyPictures($existingDoorId, $newDoorId);
		
		// copy the door audio
		$this->_copyAudio($existingDoorId, $newDoorId);
		
		// copy the checklit
		if (!$clearChecklist) {
			$this->_copyChecklist($existingDoorId, $newDoorId);
		}
	}
	
	/**
	 * Copies all the door types from one door to another
	 */
	protected function _copyDoorTypes($fromDoorId, $toDoorId) {
		$types = Model_DoorType::retrieve()->fetchEntries(array(new Data_Column('DOOR_ID', $fromDoorId), 'TYPE_ID'));
		foreach ($types as $type) {
			Model_DoorType::retrieve()->save(array(
				'DOOR_ID' => $toDoorId,
				'TYPE_ID' => $type['TYPE_ID']));
		}
	}
	
	/**
	 * Copies all the door notes over from one door to another
	 */
	protected function _copyDoorNotes($fromDoorId, $toDoorId) {
		$notes = Model_DoorNote::retrieve()->fetchEntries(array(new Data_Column('DOOR_ID', $fromDoorId), 'NOTE', 'CONTROL_NAME'));
		foreach ($notes as $note) {
			Model_DoorNote::retrieve()->save(array(
				'DOOR_ID'      => $toDoorId,
				'NOTE' 	       => $note['NOTE'],
				'CONTROL_NAME' => $note['CONTROL_NAME']
			));
		}
	}
	
	/**
	 * Copies all the door floor plans over from one door to another
	 */
	protected function _copyFloorPlan($fromDoorId, $toDoorId) {
		$plans = Model_Floorplan::retrieve()->fetchEntries(array(new Data_Column('DOOR_ID', $fromDoorId), 'INK_STROKES'));
		foreach ($plans as $plan) {
			Model_Floorplan::retrieve()->save(array(
				'DOOR_ID'      => $toDoorId,
				'INK_STROKES'  => $note['INK_STROKES']
			));
		}
	}
	
	/**
	 * Copies all the door ink over from one door to another
	 */
	protected function _copyInk($fromDoorId, $toDoorId) {
		$inks = Model_Ink::retrieve()->fetchEntries(array(new Data_Column('DOOR_ID', $fromDoorId), 'INK_STROKE', 'FORM_NUM', 'CONTROL_NAME'));
		foreach ($inks as $ink) {
			Model_Ink::retrieve()->save(array(
				'DOOR_ID'      => $toDoorId,
				'INK_STROKE'   => $ink['INK_STROKE'],
				'FORM_NUM'     => $ink['FORM_NUM'],
				'CONTROL_NAME' => $ink['CONTROL_NAME']
			));
		}
	}

	/**
	 * Copies all the door hardware over from one door to another
	 */
	protected function _copyHardware($fromDoorId, $toDoorId) {
		$hardwares = Model_Hardware::retrieve()->fetchEntries(array(new Data_Column('DOOR_ID', $fromDoorId), 'ITEM_ID', 'VERIFY', 'QTY', 'ITEM', 'PRODUCT', 'MFG', 'FINISH'));
		foreach ($hardwares as $hardware) {
			Model_Hardware::retrieve()->save(array(
				'DOOR_ID'   => $toDoorId,
				'ITEM_ID'   => $hardware['ITEM_ID'],
				'VERIFY'    => $hardware['VERIFY'],
				'QTY'	 	=> $hardware['QTY'],
				'ITEM' 		=> $hardware['ITEM'],
				'PRODUCT' 	=> $hardware['PRODUCT'],
				'MFG' 		=> $hardware['MFG'],
				'FINISH'    => $hardware['FINISH']
			));
		}
	}
	
	/**
	 * Copies all the door pictures over from one door to another
	 */
	protected function _copyPictures($fromDoorId, $toDoorId) {
		$pictures = Model_Picture::retrieve()->fetchEntries(array(new Data_Column('DOOR_ID', $fromDoorId), 'PICTURE_FILE', 'CONTROL_NAME', 'ROTATION', 'INK_STROKES', 'NOTE'));
		$pictureId = 1;
		
		//connect to the FTP server, if needed
		/*$config = Zend_Registry::getInstance()->configuration;
		if ($config->pdfserver->ftp->enabled == "yes"){
			$ftp = new \FtpClient\FtpClient();
			$ftp->connect($config->pdfserver->ftp->host, true);
			$ftp->login($config->pdfserver->ftp->username, $config->pdfserver->ftp->password);
			$ftp->pasv(true);
		}*/
		
		foreach ($pictures as $picture) {
			// source file name and full path
			$fileName = $picture['PICTURE_FILE'];
			$source = ROOT_PATH . '/content/pictures/' . $fileName;
			
			// get the picture extension
			$ext = strtolower(array_pop(explode('.', $fileName)));

			// generate a new file name and full path
			$newFileName = 'pict_door_' . $toDoorId . '_' . date('YmdHis') . '_' . $pictureId++ . '.' . $ext;
			$dest = ROOT_PATH . '/content/pictures/' . $newFileName;
			
			// copy the files
			copy ($source, $dest);
			
			// copy the files to FTP as well
			//$ftp->putFromPath(ROOT_PATH . '/content/pictures/' . $newFileName);

    		// update the record in the database
			Model_Picture::retrieve()->save(array(
				'DOOR_ID'      => $toDoorId,
				'PICTURE_FILE' => $newFileName,
				'CONTROL_NAME' => $picture['CONTROL_NAME'],
				'ROTATION'     => $picture['ROTATION'],
				'INK_STROKES'  => $picture['INK_STROKES'],
				'NOTE'		   => $picture['NOTE']
			));
		}
		
		/*if ($config->pdfserver->ftp->enabled == "yes"){
			$ftp->close();
		}*/
	}

	/**
	 * Copies all the door audio records over from one door to another
	 */
	protected function _copyAudio($fromDoorId, $toDoorId) {
		$audios = Model_Audio::retrieve()->fetchEntries(array(new Data_Column('DOOR_ID', $fromDoorId), 'AUDIO_FILE', 'CONTROL_NAME', 'INK_STROKES', 'NOTE'));
		foreach ($audios as $audio) {
			// source file name and full path
			$fileName = $audio['AUDIO_FILE'];
			$source = ROOT_PATH . '/content/audio/' . $fileName;
			
			// get the picture extension
			$ext = strtolower(array_pop(explode('.', $fileName)));

			// generate a new file name and full path
			$newFileName = 'audio_door_' . $toDoorId . '_' . date('YmdHis') . '.' . $ext;
			$dest = ROOT_PATH . '/content/audio/' . $newFileName;
			
			// copy the files
			copy ($source, $dest);

    		// update the record in the database
			Model_Audio::retrieve()->save(array(
				'DOOR_ID'      => $toDoorId,
				'AUDIO_FILE'   => $newFileName,
				'CONTROL_NAME' => $audio['CONTROL_NAME'],
				'INK_STROKES'  => $audio['INK_STROKES'],
				'NOTE'		   => $audio['NOTE']
			));
		}
	}
	
	/**
	 * Copies all the door types from one door to another
	 */
	protected function _copyChecklist($fromDoorId, $toDoorId) {
		$codes = Model_DoorCode::retrieve()->fetchEntries(array(new Data_Column('DOOR_ID', $fromDoorId), 'CODE_ID', 'ACTIVE', 'CONTROL_NAME'));
		foreach ($codes as $code) {
			Model_DoorCode::retrieve()->save(array(
				'DOOR_ID'       => $toDoorId,
				'CODE_ID'       => $code['CODE_ID'], 
				'ACTIVE'        => $code['ACTIVE'],
				'CONTROL_NAME'  => $code['CONTROL_NAME']
			));
		}
	}
}



