<?
require_once APPLICATION_PATH . '/models/DBTable/Inspection.php';
require_once APPLICATION_PATH . '/models/Building.php';
require_once APPLICATION_PATH . '/models/Abstract.php';
require_once APPLICATION_PATH . '/models/Dictionary.php';
require_once APPLICATION_PATH . '/models/Employee.php';
require_once APPLICATION_PATH . '/models/Company.php';
require_once APPLICATION_PATH . '/models/Inspect.php';
require_once APPLICATION_PATH . '/models/Audio.php';
require_once APPLICATION_PATH . '/models/Door.php';
require_once APPLICATION_PATH . '/models/DoorCode.php';
require_once APPLICATION_PATH . '/models/DoorNote.php';
require_once APPLICATION_PATH . '/models/DoorType.php';
require_once APPLICATION_PATH . '/models/Floorplan.php';
require_once APPLICATION_PATH . '/models/Framedetail.php';
require_once APPLICATION_PATH . '/models/Hardware.php';
require_once APPLICATION_PATH . '/models/Ink.php';
require_once APPLICATION_PATH . '/models/Picture.php';
require_once APPLICATION_PATH . '/models/Inspectionother.php';

class Model_Inspection extends Model_Abstract{
	
	const PENDING   	= 1080;	
	const SUBMITTING 	= 1079;
	const SUBMITTED   	= 1078;
	const COMPLETED  	= 1077;
	const INCOMPLETED	= 1383; 

	const DEFAULT_TEMPLATE = 1367;
	
	const THEME_BLUE   = 0;
	const THEME_CHROME = 1;
	const THEME_RED    = 2;
	const THEME_GREEN  = 3;
	const THEME_BROWN  = 4;
	
	var $buildingOwnerMap;
	var $buildingMap;
	var $doorsMap;


	protected function _init(){
		$this->_table = new DBTable_Inspection();
		$this->addReferenceModel('BUILDING_ID', Model_Building::retrieve());
		$this->addReferenceModel('STATUS', Model_Dictionary::retrieve());
		$this->addReferenceModel('COMPANY_ID', Model_Company::retrieve());
		$this->addReferenceModel('INSPECTOR_ID', Model_Employee::retrieve());
		$this->addReferenceModel('TEMPLATE_ID', Model_Dictionary::retrieve());

		parent::_init();
	}

	public static function retrieve($class=null) {
		return parent::retrieve(__CLASS__);
	}

	public function save($data, $ignored = null){
       	// save the inspection data. It will give us current inspection id

		if(!array_key_exists('COMPANY_ID', $data && App::inspectionCompanyId() !='')){
			$data['COMPANY_ID'] = App::inspectionCompanyId();
		}
		$inspectionId = parent::save($data, $ignored);

		// update the information about the inspector that made the last updates
		$this->updateInspectionHistory($inspectionId);
		
		return $inspectionId;
	}

	/**
	 * Updates the inspection with the information about the last inspector that made the changes
	 */
	public function updateInspectionHistory($inspectionId) {
		// not updated if this is a new inspection
		if (!$inspectionId)
			return;
		
		// not updated if the operation is done by a non-authenticated user
		$session = new Zend_Session_Namespace('default');
		$currentUser = $session->userRecord;
		if (!$currentUser) {
			Zend_Registry::get('logger')->warn('An inspection was updated by a guest, inspection id: ' . $inspectionId);
			return;
		}

    	// check if there were updates done at the current date already
		Model_Inspect::retrieve()->save(array(
			'INSPECTION_ID' => $inspectionId,
			'INSPECTOR_ID'  => $session->employeeId,
			'ASSIGNED_DATE' => date("Y-m-d") 
		));
	}
	
	public function getInspectionBuildingId($inspectionId) {
		if (!$inspectionId) {
			throw new Exception('Trying to call getInspectionBuilding method for a non-existing inspection');
		}
		
		$inspection = $this->fetchEntry($inspectionId, array('BUILDING_ID'));
		
		return $inspection['BUILDING_ID'];
	}
	
	/**
	 * Creating a map of inspectors mapping XML ID's to database ID's
	 * @param $inspectorObjects XML object of inspectors
	 * @return map in a form of {XML_ID => {INSPECTOR_DB_RECORD}}
	 */
	function importInspectorMap($inspectorObjects) {
		$inspectorMap = array();
		foreach($inspectorObjects as $inspector) {
			// the inspector must already be pre-created in the database
			$existingInspector = Model_Employee::retrieve()->fetchEntry(null, array(
				'ID', 'COMPANY_ID', new Data_Column('USER_ID', $inspector->login_name . '', Model_Employee::retrieve(), 'LOGIN')
			));
			if (!$existingInspector) {
				throw new Exception('Could not find any inspector with login ' . $inspector->login_name);
			}
			
			// check that the company is filled for this inspector  
			if (!$existingInspector['COMPANY_ID']) {
				throw new Exception('Cannot import inspector ' . $inspector->login_name . ' as he does not have any assigned company');
			}
			
			// update the map
			$inspectorMap[$inspector->id] = $inspector;
		}
		
		return $inspectorMap;
	}
	
	function assertImportDoorExists($xmlDoorId) {
		$doorId = array_value(trim($xmlDoorId) . '', $this->doorsMap);
		if (!$doorId) {
			throw new Exception('XML contains an invalid door reference: ' . $xmlDoorId);
		}
		return $doorId;
	}
	
	/**
	 * Loading building owners (and creating, if missing)
	 * @param $ownerObjects XML object of building owners
	 * @param $inspectionCompanyId The ID of the inspection company that owns the buildings
	 * @return array(XML_OWNER_ID => DB_OWNER_ID)
	 */	
	function importBuildingOwners($ownerObjects, $inspectionCompanyId) {
		Zend_Registry::get('logger')->info('Loading building owners...');
		
		$this->buildingOwnerMap = array();
		foreach ($ownerObjects as $owner) {
			// check if such building owner already exists
			$existingOwner = Model_Company::retrieve()->fetchEntry(null, array(
				'ID', 'INSPECTION_COMPANY',
				new Data_Column('NAME', $owner->name),
				new Data_Column('TYPE', Model_Company::BUILDING_OWNER),
				new Data_Column('INSPECTION_COMPANY', $inspectionCompanyId)
			));
			
			// if doesn't exist, create a new one
			$ownerId = $existingOwner ? $existingOwner['ID'] : null;
			if (!$existingOwner) {
				Zend_Registry::get('logger')->info('Building owner ' . $owner->name . ' not found, creating new...');
				$ownerId = Model_Company::retrieve()->save(array(
					'NAME'        			=> $owner->name,
					'TYPE'					=> Model_Company::BUILDING_OWNER,
					'INSPECTION_COMPANY' 	=> $inspectionCompanyId
				));
			}
			
			$this->buildingOwnerMap[$owner->id] = $ownerId;
		}
		Zend_Registry::get('logger')->info('Building owners loaded');
	}

	/**
	 * Loading buildings (and creating, if missing)
	 * @param $buildingObjects XML object of buildings
	 * @return array(XML_BUILDING_ID => DB_BUILDING_ID)
	 */	
	function importBuildings($buildingObjects, $inspectionCompanyId) {
		Zend_Registry::get('logger')->info('Loading buildings...');
		
		$this->buildingMap = array();
		foreach($buildingObjects as $building) {
			// check if the building has a building owner
			$buildingOwnerId = $this->buildingOwnerMap[$building->customer_id];
			if (!$buildingOwnerId) {
				throw new Exception('Cannot import building ' . $building->name . ' as its owner ' . $building->customer_id . ' cannot be recognized');
			}
			
			// check if such building already exists
			$existingBuilding = Model_Building::retrieve()->fetchEntry(false, array(
				'ID', new Data_Column('NAME', $building->name), new Data_Column('CUSTOMER_ID', $buildingOwnerId)
			));
			$buildingId = $existingBuilding ? $existingBuilding['ID'] : null;
			if (!$existingBuilding) {
				Zend_Registry::get('logger')->info('Building ' . $building->name . ' not found for owner ' . $buildingOwnerId . ', creating a new one');
				$buildingId = Model_Building::retrieve()->save(array(
					'NAME'        	=> $building->name,
					'ADDRESS_1'   	=> $building->address1,
					'ADDRESS_2'   	=> $building->address2,
					'CITY'        	=> $building->city,
					'STATE'       	=> Model_Dictionary::getIdByItem($building->state, 'State'),
					'COUNTRY'     	=> Model_Dictionary::getIdByItem($building->country, 'Country'), 
					'ZIP'         	=> $building->zipcode . '',
					'SUMMARY'     	=> $building->summary . '',
					'CUSTOMER_ID' 	=> $buildingOwnerId
				));
			}
			$this->buildingMap[$building->id] = $buildingId;
		}
		
		Zend_Registry::get('logger')->info('Buildings loaded');
	}

	/**
	 * Imports doors (and overrides existing, if necessary)
	 */
	function importDoors($doorObjects, $inspectionData, $overwriteExisting = true) {
		Zend_Registry::get('logger')->info('Loading doors...');
		
		// preload existing doors
		$inspectionDoorsByNumber = array();
		$existingDoors = Model_Door::retrieve()->fetchEntries(array(
			'NUMBER', 'ID', new Data_Column('INSPECTION_ID', $inspectionData['ID'])
		), 0, true);
		foreach($existingDoors as $door) {
			$inspectionDoorsByNumber[$door['NUMBER'] . ''] = $door['ID'];
		}
		
		$inspectionHasDoors = count($inspectionDoorsByNumber) > 0;
		
		$this->doorsMap = array(); // this array contains pairs xml_door_id => db_door_id
		foreach($doorObjects as $door) {
			Zend_Registry::get('logger')->info('Loading door number ' . $door->number);
			
       		// check if a door with the same number already exists
			$existingDoorId = null;
			if ($inspectionHasDoors) {
				$existingDoorId = array_value($door->number . '', $inspectionDoorsByNumber);
				
				// skip this door, if "overwrite duplicates" set to "false"
				if ($existingDoorId && !$overwriteExisting) {
					$this->doorsMap[$door->number . ''] = $existingDoorId;
					continue;
				}
			}
			
			// check if it was created within this session
			$previousDoorId = array_value($door->number . '', $this->doorsMap);
			if ($previousDoorId) {
				if ($overwriteExisting)
					$existingDoorId = $previousDoorId;
				else
					continue; 
			}
			
			// get dictionary items
			$doorStyle = Model_Dictionary::getIdByItem($door->door_style, 'Door Style');
			$doorMaterial = Model_Dictionary::getIdByItem($door->door_material, 'Door Material');
			$doorElevation = Model_Dictionary::getIdByItem($door->door_elevation, 'Door Elevation');
			$frameMaterial = Model_Dictionary::getIdByItem($door->frame_material, 'Frame Material');
			$frameElevation = Model_Dictionary::getIdByItem($door->frame_elevation, 'Frame Elevation');
			$fireRating = Model_Dictionary::getIdByItem($door->fire_rating, 'Fire-Rating 1');
			$listingAgency = Model_Dictionary::getIdByItem($door->listingagency, 'Door Listing Agency');
			
			$newDoorId = Model_Door::retrieve()->saveClean(Helper::changeEmptyStrings(array(
				'ID'					=> $existingDoorId,
				'BUILDING_ID'			=> $inspectionData['BUILDING_ID'],
				'INSPECTION_ID'			=> $inspectionData['ID'],
				'INSPECTOR_ID'			=> $inspectionData['INSPECTOR_ID'],
				'NUMBER'   				=> $door->number,
				'DOOR_BARCODE'			=> $door->doorBarcode,
				'TYPE_OTHER'      		=> $door->door_type_other,
				'STYLE'    				=> $doorStyle,
				'MATERIAL' 				=> $doorMaterial,
				'MATERIAL_OTHER'		=> $doorMaterial ? null : $door->door_material,
				'ELEVATION'    			=> $doorElevation,
				'ELEVATION_OTHER'		=> $doorElevation ? null : $door->door_elevation,
				'FRAME_MATERIAL'		=> $frameMaterial,
				'FRAME_MATERIAL_OTHER'	=> $frameMaterial ? null : $door->frame_material,
				'FRAME_ELEVATION'		=> $frameElevation,
				'FRAME_ELEVATION_OTHER'	=> $frameElevation ? null : $door->frame_elevation,
				'FIRE_RATING_1'			=> $fireRating,
				'TEMP_RISE'				=> Model_Dictionary::getIdByItem($door->temprise, 'Door Temperature Rise'),
				'LOCATION'     			=> $door->location,
				'REMARKS'     			=> $door->remarks,
				'COMPLIANT'    			=> Model_Dictionary::getIdByItem($door->compliant, 'Logical'),
				'MANUFACTURER'			=> $door->maker,
				'MODEL'  				=> $door->model,
				'FRAME_MANUFACTURER'	=> $door->framemaker,
				'BARCODE'				=> $door->barcode,
				'RFID'     				=> $door->RFID,
				'LISTING_AGENCY' 		=> $listingAgency,
				'LISTING_AGENCY_OTHER'	=> $door->listingagency,
				'GAUGE'					=> $door->gauge,
				'HANDING'   			=> Model_Dictionary::getIdByItem($door->handing, 'Handing')
			)));
			Zend_Registry::get('logger')->info('Saved door: ' . $newDoorId);
			
			// emptying existing door data
			if ($existingDoorId) {
				$key = array(new Data_Column('DOOR_ID', $existingDoorId));
				Model_Audio::retrieve()->deleteEntries($key);
				Model_DoorCode::retrieve()->deleteEntries($key);
				Model_DoorNote::retrieve()->deleteEntries($key);
				Model_DoorType::retrieve()->deleteEntries($key);	   		  	
				Model_Floorplan::retrieve()->deleteEntries($key);
				Model_Hardware::retrieve()->deleteEntries($key);
				Model_Ink::retrieve()->deleteEntries($key);
				Model_Picture::retrieve()->deleteEntries($key);
			}
			
			// saving door types
			Zend_Registry::get('logger')->info('Loading door types...');
			$types = explode(',', $door->door_type.'');
			foreach ($types as $type) {
				if (strlen(trim($type)) > 0) {
					$doorTypeId = Model_Dictionary::getIdByItem(trim($type), 'Door Type');
					if (!$doorTypeId) {
						Zend_Registry::get('logger')->info('Door type ' . $type . ' not found');
						continue;
					}
					Model_DoorType::retrieve()->save((Helper::changeEmptyStrings(array(
						'DOOR_ID'  => $newDoorId,
						'TYPE_ID'  => $doorTypeId
					))));
				}
			}
			Zend_Registry::get('logger')->info('Door types loaded');
			
			$this->doorsMap[$door->number . ''] = $newDoorId;
		}

		Zend_Registry::get('logger')->info('Doors loading complete');
	}

	function importDoorPictures($pictureObjects){
		Zend_Registry::get('logger')->info('Loading door pictures...');
		
		$pictureId = 1; // either picture 1 or picture 2
		foreach($pictureObjects as $picture) {
    		// ensure door reference is consistent
			$doorId = assertImportDoorExists($picture->door_id);
			
			// construct the picture name
			$pictureName = 'pict_door_' . $picture->door_id . '_' . $pictureId++ . '.jpg';
			Helper::saveBase64File($picture->image, '/content/pictures/', $pictureName);
			Model_Picture::retrieve()->save(Helper::changeEmptyStrings(array(
				'DOOR_ID'  		=> $doorId,
				'PICTURE_FILE'  => $pictureName,	
				'CONTROL_NAME'	=> $picture->control_name,	
				'ROTATION'		=> $picture->rotation,
				'INK_STROKES'	=> $picture->inkstrokes
			)));
		}
		
		Zend_Registry::get('logger')->info('Door pictures loading complete');
	}

	function importDoorAudio($audioObjects) {
		Zend_Registry::get('logger')->info('Loading door audio...');
		
		/* audio record */
		foreach($audioObjects as $audio) {
			// ensure door reference is consistent
			$doorId = $this->assertImportDoorExists($audio->door_id);
			
			// construct the audio file name
			$audioName = 'audio_door_' . $audio->door_id . '_' . date('YmdHis') . '.wav';
			Helper::saveBase64File($audio->image, '/content/audio/', $audioName);
			Model_Audio::retrieve()->save(Helper::changeEmptyStrings(array(
				'DOOR_ID'  		=> $doorId,
				'AUDIO_FILE'	=>  $audioName,
				'CONTROL_NAME'	=> $audio->control_name,
				'INK_STROKES'	=> $audio->inkstrokes
			)));
		}

		Zend_Registry::get('logger')->info('Door audio loading complete');
	}

	function importDoorDetail($doorDetailObjects) {
		Zend_Registry::get('logger')->info('Importing door detail...');
		
		foreach ($doorDetailObjects as $doordetail) {
			// ensure door reference is consistent
			$doorId = $this->assertImportDoorExists($doordetail->door_id);
			
			Model_Door::retrieve()->saveClean(Helper::changeEmptyStrings(array(
				'ID'						=> $doorId,
				'INK_STROKES'   			=> $doordetail->ink_strokes,
				'HINGE_HEIGHT' 				=> Model_Dictionary::getIdByItem($doordetail->hinge_height, 'Hinge Height'),
				'HINGE_THICKNESS'			=> Model_Dictionary::getIdByItem($doordetail->hinge_thickness, 'Hinge Thickness'),
				'HINGE_HEIGHT1'   			=> $doordetail->hinge_height1,
				'HINGE_HEIGHT2'   			=> $doordetail->hinge_height2,
				'HINGE_HEIGHT3'   			=> $doordetail->hinge_height3,
				'HINGE_HEIGHT4'   			=> $doordetail->hinge_height4,
				'HINGE_FRACTION1'   		=> Model_Dictionary::getIdByItem($doordetail->hinge_fraction1, 'Hinge Fraction1'),
				'HINGE_FRACTION2'   		=> Model_Dictionary::getIdByItem($doordetail->hinge_fraction2, 'Hinge Fraction2'),
				'HINGE_FRACTION3'   		=> Model_Dictionary::getIdByItem($doordetail->hinge_fraction3, 'Hinge Fraction3'),
				'HINGE_FRACTION4'   		=> Model_Dictionary::getIdByItem($doordetail->hinge_fraction4, 'Hinge Fraction4'),
				'HINGE_BACKSET'   			=> Model_Dictionary::getIdByItem($doordetail->hinge_backset, 'Hinge Backset'),
				'HINGE_MANUFACTURER'   		=> $doordetail->hinge_manufacturer,
				'HINGE_MANUFACTURER_NO'   	=> $doordetail->hinge_manufacturer_no,
				'TOP_TO_CENTERLINE'   		=> $doordetail->top_to_centerline,
				'TOP_TO_CENTERLINE_FRACTION'=> Model_Dictionary::getIdByItem($doordetail->top_to_centerline_fraction, 'Top To Centerline Fraction'),
				'LOCK_BACKSET'   			=> Model_Dictionary::getIdByItem($doordetail->lock_bracket, 'Lock Backset'),
				'FRAME_BOTTOM_TO_CENTER'   	=> $doordetail->frame_bottom_to_center,
				'STRIKE_HEIGHT'   			=> Model_Dictionary::getIdByItem($doordetail->strike_height, 'Strike Height'),
				'PREFIT_DOOR_SIZE_X'   		=> $doordetail->prefit_door_size_x,
				'PREFIT_FRACTION_X'   		=> Model_Dictionary::getIdByItem($doordetail->prefit_fraction_x, 'Prefit Fraction X'),
				'PREFIT_DOOR_SIZE_Y'   		=> $doordetail->prefit_door_size_y,
				'PREFIT_FRACTION_Y'   		=> Model_Dictionary::getIdByItem($doordetail->prefit_fraction_y, 'Prefit Fraction Y'),
				'FRAME_OPENING_SIZE_X'   	=> $doordetail->frame_opening_size_x,
				'FRAME_OPENING_FRACTION_X'  => Model_Dictionary::getIdByItem($doordetail->frame_opening_fraction_x, 'Frame Opening Fraction X'),
				'FRAME_OPENING_SIZE_Y'   	=> $doordetail->frame_opening_size_y,
				'FRAME_OPENING_FRACTION_Y'  => Model_Dictionary::getIdByItem($doordetail->frame_opening_fraction_y, 'Frame Opening Fraction Y'),
				'LITE_CUTOUT_SIZE_X'   		=> $doordetail->lite_cutout_size_x,
				'LITE_CUTOUT_FRACTION_X'   	=> Model_Dictionary::getIdByItem($doordetail->lite_cutout_fraction_x, 'Lite Cutout Fraction X'),
				'LITE_CUTOUT_SIZE_Y'   		=> $doordetail->lite_cutout_size_y,
				'LITE_CUTOUT_FRACTION_Y'   	=> Model_Dictionary::getIdByItem($doordetail->lite_cutout_fraction_y, 'Lite Cutout Fraction Y'),
				'LOCKSTILE_SIZE'   			=> $doordetail->lockstile_size,
				'LOCKSTILE_FRACTION'   		=> Model_Dictionary::getIdByItem($doordetail->lockstile_fraction, 'Lockstile Fraction'),
				'TOPRAIL_SIZE'   			=> $doordetail->toprail_size,
				'TOPRAIL_FRACTION'   		=> Model_Dictionary::getIdByItem($doordetail->toprail_fraction, 'Top Rail Fraction')
			)));
		}

		Zend_Registry::get('logger')->info('Door details loading complete');
	}

	function importDoorFrames($doorFrameObjects) {
		Zend_Registry::get('logger')->info('Loading door frames...');
		
		foreach($doorFrameObjects as $framedetail) {
			// ensure door reference is consistent
			$doorId = $this->assertImportDoorExists($framedetail->door_id);
			
			Model_Door::retrieve()->saveClean(Helper::changeEmptyStrings(array(
				'ID'			=> $doorId,
				'FRAME_INK_STROKES'	=> $framedetail->ink_strokes,
				'A'				=> $framedetail->a,
				'A_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->afraction, 'Frame Fraction'),
				'B'				=> $framedetail->b,
				'B_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->bfraction, 'Frame Fraction'),
				'C'				=> $framedetail->c,
				'C_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->cfraction, 'Frame Fraction'),
				'D'				=> $framedetail->d,
				'D_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->dfraction, 'Frame Fraction'),
				'E'				=> $framedetail->e,
				'E_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->efraction, 'Frame Fraction'),
				'F'				=> $framedetail->f,
				'F_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->ffraction, 'Frame Fraction'),
				'G'				=> $framedetail->g,
				'G_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->gfraction, 'Frame Fraction'),
				'H'				=> $framedetail->h,
				'H_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->hfraction, 'Frame Fraction'),
				'I'				=> $framedetail->i,
				'I_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->ifraction, 'Frame Fraction'),
				'J'				=> $framedetail->j,
				'J_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->jfraction, 'Frame Fraction'),
				'K'				=> $framedetail->k,
				'K_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->kfraction, 'Frame Fraction'),
				'L'				=> $framedetail->l,
				'L_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->lfraction, 'Frame Fraction'),
				'M'				=> $framedetail->m,
				'M_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->mfraction, 'Frame Fraction'),
				'N'				=> $framedetail->n,
				'N_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->nfraction, 'Frame Fraction'),
				'O'				=> $framedetail->o,
				'O_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->ofraction, 'Frame Fraction'),
				'P'				=> $framedetail->p,
				'P_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->pfraction, 'Frame Fraction'),
				'Q'				=> $framedetail->q,
				'Q_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->qfraction, 'Frame Fraction'),
				'R'				=> $framedetail->r,
				'R_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->rfraction, 'Frame Fraction'),
				'S'				=> $framedetail->s,
				'S_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->sfraction, 'Frame Fraction'),
				'T'				=> $framedetail->t,
				'T_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->tfraction, 'Frame Fraction'),
				'U'				=> $framedetail->u,
				'U_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->ufraction, 'Frame Fraction'),
				'V'				=> $framedetail->v,
				'V_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->vfraction, 'Frame Fraction')			
			)));
		}

		Zend_Registry::get('logger')->info('Door frame loading complete');
	}

	function importDoorFloorplan($doorFloorplanObjects) {
		Zend_Registry::get('logger')->info('Loading door floorplan...');
		
		foreach($doorFloorplanObjects as $floorplan) {
			// ensure door reference is consistent
			$doorId = $this->assertImportDoorExists($floorplan->door_id);
			
			Model_Floorplan::retrieve()->save(Helper::changeEmptyStrings(array(
				'DOOR_ID'		=> $doorId,
				'INK_STROKES'	=> $floorplan->ink_strokes
			)));
		}
		
		Zend_Registry::get('logger')->info('Door floorplan loading complete');
	}
	
	function importDoorHardware($doorHardwareObjects) {
		Zend_Registry::get('logger')->info('Loading door hardware...');
		
		/* hardwareset record */
		
		if (!is_array($doorHardwareObjects)){
			Zend_Registry::get('logger')->info('The door does not have hardware objects.');
			return;
		}
		
		foreach($doorHardwareObjects as $hardware) {
			// might be multiple door_ids for the same hardware set
			$doorNumbers = explode(',', $hardware['door_id']);
			Zend_Registry::get('logger')->info('Loading hardware for door IDs: ' . $hardware['door_id']);
			
			foreach($doorNumbers as $doorNumber) {
				// ensure door reference is consistent
				$doorId = $this->assertImportDoorExists($doorNumber);
				
				// update the door hardware set
				Model_Door::retrieve()->saveClean(array(
					'ID' => $doorId,
					'HARDWARE_SET' => $hardware['id']
				));
				
				// upload the hardware items
				foreach($hardware->item as $item) {
					Model_Hardware::retrieve()->save(Helper::changeEmptyStrings(array(
						'DOOR_ID'			=> $doorId,
						'QTY'  				=> substr($item->qty, 0, 10),
						'ITEM'  			=> substr($item->ItemName, 0, 42),
						'PRODUCT'  			=> substr($item->product, 0, 55),
						'MFG'  				=> substr($item->mfg, 0, 10),
						'FINISH'  			=> substr($item->finish, 0,  10)
					)));
				}
			}
		}

		Zend_Registry::get('logger')->info('Door hardware loading complete');
	}
	
	function importDoorInk($doorInkObjects) {
		Zend_Registry::get('logger')->info('Loading door ink...');
		
		foreach($doorInkObjects as $ink) {
    		// ensure door reference is consistent
			$doorId = $this->assertImportDoorExists($ink->door_id);
			
			Model_Ink::retrieve()->save(Helper::changeEmptyStrings(array(
				'DOOR_ID'  		=> $doorId,
				'INK_STROKE'	=> $ink->inkstroke,
				'FORM_NUM'		=> $ink->formnum,
				'CONTROL_NAME'	=> $ink->control_name
			)));
		}
		
		Zend_Registry::get('logger')->info('Door ink loaded');
	}
	
	function importDoorNotes($doorNoteObjects) {
		Zend_Registry::get('logger')->info('Loading door notes...');
		
		/* door_note record */
		foreach($doorNoteObjects as $note) {
			// ensure door reference is consistent
			$doorId = $this->assertImportDoorExists($note->door_id);
			
			Model_DoorNote::retrieve()->save(Helper::changeEmptyStrings(array(
			//	'ID'	  		=> $note->id,
				'DOOR_ID'  		=> $doors[$note->door_id.''],
				'NOTE'  		=> $note->note,
				'CONTROL_NAME'  => $note->control_name
			)));
		}
		
		Zend_Registry::get('logger')->info('Door notes loading complete');
	}
	
	function importDoorCodes($allCodeObjects, $doorCodeObjects) {
		Zend_Registry::get('logger')->info('Loading door codes...');
		
		// get list of all codes from xml
		$codes = array();
		foreach ($allCodeObjects as $code) {
			$codes[$code->id] = $code;
		}
		
		$incompliantsaved = array();
		foreach($doorCodeObjects as $door_to_code) {
			// ensure door reference is consistent
			$doorId = $this->assertImportDoorExists($door_to_code->door_id);
			
			// since there is at least one non-compliancy code, save this door as "non-compliant"
			if (!array_key_exists($doorId, $incompliantsaved)) {
				Model_Door::retrieve()->saveClean(array(
					'ID' => $doorId,
					'COMPLIANT' => Model_Dictionary::getIdByItem('No', 'Logical')));
				$incompliantsaved[$doorId] = true;
			}
			
			Model_DoorCode::retrieve()->save(Helper::changeEmptyStrings(array(
				'DOOR_ID'  		=> $doorId,
				'CODE_ID'		=> $door_to_code->code_id,
				'ACTIVE'  		=> Helper::getBool($door_to_code->active),
				'CONTROL_NAME'	=> $door_to_code->control_name
			)));
			
			// verify if it's an "Other" value
			$doorCode = $door_to_code->code_id;
			if (Model_Dictionary::retrieve()->getDescriptionById($doorCode) == 'Other') {
				$other = Model_Inspection_Other::retrieve()->fetchEntry(null, array(
					'ID',
					new Data_Column('INSPECTION_ID', $inspection->id . ''),
					new Data_Column('OTHER_ID', $doorCode)
				));
				$existingOtherId = $other ? $other['ID'] : null;
				Model_Inspection_Other::retrieve()->save(array(
					'ID' => $existingOtherId,
					'OTHER_ID'      => $doorCode, 
					'OTHER_VALUE'   => $codes[$doorCode]->code_label,
					'INSPECTION_ID' => $inspection->id
				));
			}
		}

		Zend_Registry::get('logger')->info('Door code loading complete');
	}

    /**
     * Imports inspection door data from a standard Doordata XML format
     * @param $xmlString
	 * @param $inspectionId The inspection that the import is performed against
     */
    public function import($xmlString, $inspectionId, $overwriteExisting = true) {
    	Zend_Registry::get('logger')->info(
    		'Initiating import for inspection ' . $inspectionId 
    		. ', overwrite existing door mode: ' . ($overwriteExisting ? 'ON' : 'OFF')
    	);

    	// transform the content into an XML object
    	libxml_use_internal_errors(true); // suppress all XML errors
    	$xml = simplexml_load_string($xmlString);
    	if (!$xml) {
    		throw new Exception('Cannot perform the import: the provided content is not a valid XML document');
    		return;
    	}
    	if($xml->DoorData) {
    		$xml = $xml->DoorData;
    	} 

		// get the inspection information
    	$inspection = $this->fetchEntry($inspectionId);

		// check if the XML contains more than one inspection
    	if(count($xml->inspections) > 1) {
    		throw new Exception('Loading more than one inspection is not currently supported');
    	}

		// importing door header information
    	$this->importDoors($xml->Doors->door, $inspection, $overwriteExisting);
    	$this->importDoorHardware($xml->hardware_sets->hardware_set);
    	$this->importDoorPictures($xml->pictures);
    	$this->importDoorDetail($xml->doordetail);
    	$this->importDoorFrames($xml->framedetail);
    	$this->importDoorFloorplan($xml->floorplan);
    	$this->importDoorInk($xml->ink);
    	$this->importDoorNotes($xml->notes);
    	$this->importDoorCodes($xml->codes, $xml->door_to_code, $inspectionId);

    	Zend_Registry::get('logger')->info('Import for inspection ' . $inspectionId . ' is successfully completed');
    }

	/**
	 * Creates an identical inspection based on the ID provided
	 */
	public function saveAsNew($id) {
		// retrieve the existing inspection record
		$existingInspection = $this->fetchEntry($id);
		
		// create a new record basing on the existing one
		$newInspectionId = $this->save(array(
			'INSPECTION_DATE' => date("Y-m-d"),
			'BUILDING_ID'     => $existingInspection['BUILDING_ID'],
			'COMPANY_ID'	  => $existingInspection['COMPANY_ID'],
			'STATUS'		  => self::PENDING,
			'TEMPLATE_ID'	  => $existingInspection['TEMPLATE_ID']
		));
		
		return $newInspectionId;
	}

	public function getThemeById($themeId) {
		switch ($themeId) {
			case self::THEME_CHROME: return 'themeChrome'; break;
			case self::THEME_RED:    return 'themeRed';    break;
			case self::THEME_GREEN:  return 'themeGreen';  break;
			case self::THEME_BROWN:  return 'themeBrown';  break;
			case self::THEME_BLUE:   
			default: 		         return 'themeBlue';   break; 
		}
	}

	/**
	 * This function deletes all the "inspection other" entries for a given inspection id
	 */
	public function cleanInspectionOther($inspectionId){
		
		if (!$inspectionId) return;	

		// Model_Inspection_Other::retrieve()->deleteEntries(array(new Data_Column('INSPECTION_ID' => $inspecitonId));

		$db = Zend_Registry::getInstance()->dbAdapter;
		$sql = "DELETE FROM doordt.inspection_other WHERE INSPECTION_ID = ".$inspectionId;
		$db->query($sql);
	}

	/**
	 * The function sets the inspection other values for the given inspection id
	 */
	public function setInspectionOther($inspectionId, $others){
		if (!$inspectionId) {
			App::log('no inspection id, can not delete others');
			return;
		}
		
		App::log($others);
		
		foreach($others as $other){
			
			App::log(var_export($other, true));
			
			if (!array_key_exists('OTHER_ID', $other) || !array_key_exists('OTHER_VALUE', $other)){
				return;
			}
			Model_Inspection_Other::retrieve()->save(array(
				'INSPECTION_ID'	=> $inspectionId,
				'OTHER_ID'	=> $other['OTHER_ID'],
				'OTHER_VALUE' => $other['OTHER_VALUE']
			));
		}
	}
}