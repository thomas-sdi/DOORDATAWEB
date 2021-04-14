<?
include_once APPLICATION_PATH . '/controllers/RESTService.php';
include_once APPLICATION_PATH . '/controllers/Helper.php';
include_once APPLICATION_PATH . '/models/Building.php';
include_once APPLICATION_PATH . '/models/Door.php';
include_once APPLICATION_PATH . '/models/Picture.php';
include_once APPLICATION_PATH . '/models/Audio.php';
include_once APPLICATION_PATH . '/models/Inspection.php';
include_once APPLICATION_PATH . '/models/Employee.php';
include_once APPLICATION_PATH . '/models/Dictionary.php';
include_once APPLICATION_PATH . '/models/DoorNote.php';
include_once APPLICATION_PATH . '/models/Company.php';
include_once APPLICATION_PATH . '/models/Inspect.php';
include_once APPLICATION_PATH . '/models/Hardware.php';
include_once APPLICATION_PATH . '/models/Floorplan.php';
include_once APPLICATION_PATH . '/models/Ink.php';
include_once APPLICATION_PATH . '/models/Framedetail.php';
include_once APPLICATION_PATH . '/models/DoorCode.php';
include_once APPLICATION_PATH . '/models/DoorType.php';
include_once APPLICATION_PATH . '/models/Integration.php';

class InspectionserviceController extends Controller_RESTService  {
	
	
	protected function get() {
    	
    }
    
    protected function post() {
    	// get content of POST request as it was sent by a client
    	$raw_post = file_get_contents("php://input");
        
    	// transform the content into an XML object
    	libxml_use_internal_errors(true); // suppress all XML errors
    	$xml = simplexml_load_string($raw_post);
    	
    	$db = Zend_Registry::getInstance()->dbAdapter;
    	
    	//start transaction
    	$db->beginTransaction();
    	Zend_Registry::get('logger')->info('Step0');
    	//return HTTP 500 Server Error if error occured
    	$this->_helper->layout->setLayout("http500servererror");
    	
    	try { 
    		
    		//if id=1, then it is a new inspection
			$isNewInspection = false;
    		$inspection = $xml->inspections[0];
			if ($inspection) {
				if ($inspection->id == '1') {
					//Zend_Registry::getInstance()->logger->info('This is a new inspection');
    				$isNewInspection = true;
				}
			}
    		
    	    /*
    	     * 
    	     *  inspector record 
    	     *  
    	     *  
    	     *  */
			$inspectorId = '';
			$companyId   = '';
			Zend_Registry::get('logger')->info('Loading inspectors...');
       	 	foreach($xml->inspectors as $inspector) {
	   			$inspector_record=array(
    		    //	'ID'  				=> $inspector->id . '',
    				'FIRST_NAME'  		=> $inspector->firstname . '',
    				'LAST_NAME'  		=> $inspector->lastname . '',
    				'LICENSE_NUMBER'  	=> $inspector->license_number . '',
    		    	'EXPIRATION_DATE'	=> Helper::getDate($inspector->expiration_date) . ''
	   			);
    			$inspector_record = Helper::changeEmptyStrings($inspector_record);
       	 		
    			//find the inspector by his login name
   				$old_inspector = Model_Employee::retrieve()->fetchEntry(
   					null, array('ID', 'COMPANY_ID', new Data_Column('USER_ID', $inspector->login_name . '', Model_Employee::retrieve(), 'LOGIN')));
    			if ($old_inspector) { //we have found the inspector with such login name in the system
    				$inspectorId = $old_inspector['ID'];
    				//Zend_Registry::getInstance()->logger->info('found inspector with id = "' . $inspectorId . '"');
    				//retrieve his company
    				$companyId = $old_inspector['COMPANY_ID'];
    				if (!$companyId)
    					throw new Exception('Inspector does not have assigned company');
    			} else {	
    				throw new Exception('Could not find the inspector with login name = ' . $inspector->login_name);
    			}
    			$inspector_record['ID'] = $inspectorId;
    			//$inspectorId = Model_Employee::retrieve()->save($inspector_record);
    			
    			//Zend_Registry::getInstance()->logger->info('InspectorId = ' . $inspectorId);
    			
       	 		/*
       			 * 
       		 	* check if this inspection was already assigned to a different inspector
       		 	* 
       		 	*/
    	    	//inspection_id == 1 means that it is a new inspection
        		if (trim($inspection->id) != '1') {
        			$old_inspection = Model_Inspection::retrieve()->fetchEntry($inspection->id);
        			if ($old_inspection){
        				if ( strlen($old_inspection['INSPECTOR_ID'])>0 && 
        					 (trim($old_inspection['INSPECTOR_ID']) != trim($inspectorId)) ){
        						throw new Exception('Inspection '. $inspection->id . ' is already assigned to another inspector');
        				}
        			}
        		}
   			
       		}
       		Zend_Registry::get('logger')->info('Inspectors loaded. Loading customers...');
        	//before saving an inspection we need to know customer (building owner), building and company (inspection company) ids
       		
    	    /* 
    	     * 
    	     * customer record 
    	     * 
    	     * */
        	$customerId = '';
			if (count($xml->customers) > 0) {
        	  foreach($xml->customers as $customer) {
    			$customer_record=array(
    		    	'ID'        			=> $customer->id . '',
    				'NAME'        			=> $customer->name . '',
    		    	'TYPE'					=> Model_Company::BUILDING_OWNER . '',
    				'INSPECTION_COMPANY' 	=> $companyId
    			);
    			$customer_record = Helper::changeEmptyStrings($customer_record);
    		 	
        		//if inspection is new, we must check if this customer exist or create new
    			if ($isNewInspection) {
    				$old_customer = Model_Company::retrieve()->fetchEntry(false, array('ID', new Data_Column('NAME', $customer->name),
    														  	  new Data_Column('TYPE', Model_Company::BUILDING_OWNER),
    														  	  new Data_Column('INSPECTION_COMPANY', $companyId)));
    				if ($old_customer) { //this is existing customer
    					$customerId = $old_customer['ID'];
    				} else {	//this is unknown building owner
    					$customerId = Model_Company::retrieve()->getUBO($companyId);
    	   			}
    			} else { //if existing inspection
    		 		$customerId = Model_Company::retrieve()->save($customer_record);
    			}
    			//Zend_Registry::getInstance()->logger->info('CustomerId = ' . $customerId);
    		  }
			} else if ($companyId != null && $companyId != ''){ // inspection created from tablet
				$customerId = Model_Company::retrieve()->getUBO($companyId);
			}
    		Zend_Registry::get('logger')->info('Customer loaded. Loading buildings...');
    	   
    		    		
    	    /* building record */
    		$buildingId ='';
        	foreach($xml->buildings as $building) {
    			$building_record=array(
    		    	'ID'        	=> $building->id . '',
    				'NAME'        	=> $building->name . '',
    		    	'ADDRESS_1'   	=> $building->address1 . '',
    		    	'ADDRESS_2'   	=> $building->address2 . '',
    		    	'CITY'        	=> $building->city . '',
    		    	'STATE'       	=> Model_Dictionary::getIdByItem($building->state, 'State') . '',
    		    	'COUNTRY'     	=> Model_Dictionary::getIdByItem($building->country, 'Country') . '', 
    		   	 	'ZIP'         	=> $building->zipcode . '',
    		    //	'SUMMARY'     	=> $building->summary . '',
    		    	'CUSTOMER_ID' 	=> $customerId ? $customerId : $building->customer_id . ''
    			);
    			$building_record = Helper::changeEmptyStrings($building_record);
    			
    			//if inspection is new, we must check if this building exist or create new
    			if ($isNewInspection) {
    				$old_building = Model_Building::retrieve()->fetchEntry(false, array('ID', new Data_Column('NAME', $building->name),
    														  	   new Data_Column('ADDRESS_1', $building->address1),
    														   	   new Data_Column('ADDRESS_2', $building->address2),
    														       new Data_Column('CITY', $building->city),
    														       new Data_Column('STATE', $building->state, Model_Building::retrieve(), 'ITEM'),
    														       new Data_Column('ZIP', $building->zipcode)));
    				if ($old_building) { //this is existing building
    					$buildingId = $old_building['ID'];
    				} else {	//create new building
    					unset($building_record['ID']);
    					$buildingId = Model_Building::retrieve()->save($building_record);
    				}
    			} else { //if exisitng inspection
    		 		$buildingId = Model_Building::retrieve()->save($building_record);
    			}
    			//Zend_Registry::getInstance()->logger->info('BuildingId = ' . $buildingId);	
    	   	}
			Zend_Registry::get('logger')->info('Buildings loaded. loading companies...');
    	   	
    	    /* inspection company record */
        	foreach($xml->inspection_company as $company) {
    	    	$company_record=array(
    	    		'ID'  		=> $companyId . '',
    		    	'NAME'  	=> $company->company_name . '',
    				'ADDRESS_1'	=> $company->company_address1 . '',
    				'ADDRESS_2'	=> $company->company_address2 . '',
    				'CITY'  	=> $company->company_city . '',
    				'STATE'  	=> Model_Dictionary::getIdByItem($company->company_state, 'State') . '',	
    				'ZIP'		=> $company->company_zipcode . '',
    	    		'TYPE'		=> Model_Company::INSPECTION_COMPANY . ''
     			);
     			$company_record = Helper::changeEmptyStrings($company_record);
     			
     			//find the company by company_id, taken from inspector
     			$old_company = Model_Company::retrieve()->fetchEntry(false, array('ID', new Data_Column('ID', $companyId)));
    			if ($old_company) { //this is existing company
    				$companyId = $old_company['ID'];
    			} else {
    				throw new Exception('Could not find inspection company');	
    			}
    			
    			//$companyId = Model_Company::retrieve()->save($company_record);
    			
    			//Zend_Registry::getInstance()->logger->info('CompanyId = ' . $companyId);
    	    }
    		Zend_Registry::get('logger')->info('Companies loaded. Loading inspections...');
			/*
			 * 
			 *  inspection record, now we can save it 
			 *  
			 *  */
        	foreach ($xml->inspections as $inspection) {
        		$signatureInspector = 'sign_inspector_'.$inspection->id.'.jpg';
				Helper::saveBase64File($inspection->signature_inspector, '/content/pictures/', $signatureInspector);
				$signatureBuilding = 'sign_building_'.$inspection->id.'.jpg';
				Helper::saveBase64File($inspection->signature_building, '/content/pictures/', $signatureBuilding);
        		$inspection_record=array(
    		    	'ID' 							=> $inspection->id . '',
    				'INSPECTION_DATE'  				=> Helper::getDate($inspection->inspect_date) . '',
    				'INSPECTION_COMPLETE_DATE'		=> Helper::getDate($inspection->inspect_completedate) . '',
    				'REINSPECT_DATE'				=> Helper::getDate($inspection->reinspect_date) . '',
    				'BUILDING_ID'  					=> $buildingId ? $buildingId : $inspection->building_id . '',
    				'COMPANY_ID'  					=> $companyId ? $companyId : $inspection->company_id . '',
    				'SIGNATURE_INSPECTOR'  			=> $signatureInspector,
    				'SIGNATURE_STROKES_INSPECTOR'	=> $inspection->signature_strokes_inspector . '',
    				'SIGNATURE_BUILDING'			=> $signatureBuilding,
    				'SIGNATURE_STROKES_BUILDING'	=> $inspection->signature_strokes_building . '',
    			    'SUMMARY'  						=> $inspection->summary . '',
    				'STATUS'						=> 0,
        			'TEMPLATE_ID'					=> $inspection->template_id,
        			'INSPECTOR_ID'					=> $inspectorId
     			);
     			$inspection_record = Helper::changeEmptyStrings($inspection_record);
     			
     			//inspection_id == 1 means that it is a new inspection
        		if ($inspection->id == '1') {
        			unset($inspection_record['ID']);
        		} 
        		
        		//calculate the status of inspection
     			if (!$inspection_record['SIGNATURE_INSPECTOR'] || !$inspection_record['SIGNATURE_BUILDING'])
     				$inspection_record['STATUS'] = Model_Inspection::INCOMPLETED;
     			else $inspection_record['STATUS'] = Model_Inspection::COMPLETED;

				//change template id from description to id
				$template = Model_Dictionary::retrieve()->fetchEntry(null, array('ID', new Data_Column('DESCRIPTION', $inspection->template_id)));
				if ($template){
					$inspection_record['TEMPLATE_ID'] = $template['ID'];
				} else $inspection_record['TEMPLATE_ID'] = Model_Inspection::DEFAULT_TEMPLATE;
     			
     			$inspectionId = Model_Inspection::retrieve()->save($inspection_record);
    			
     			//Zend_Registry::getInstance()->logger->info('InspectionId = ' . $inspectionId);
    	    }    			
			 		  		       		
			Zend_Registry::get('logger')->info('Inspections loaded. Loading doors...');
			/* door record */
       		$doors = array(); // this array contains pairs old_door_id => new_door_id
       		foreach($xml->doors as $door) {
       			Zend_Registry::get('logger')->info('Loading door ' . $door->number);
     			$inspectionId = nvl($inspectionId, $door->inspection_id . '');
    			$door_record=array(
    				'ID'   					=> $door->id . '',
    		    	'BUILDING_ID'			=> $buildingId ? $buildingId : $door->building_id,
    		    	'INSPECTION_ID'			=> $inspectionId,
    				'INSPECTOR_ID'			=> $inspectorId ? $inspectorId : $door->inspector_id,
    		    	'NUMBER'   				=> $door->number . '',
    		    	'DOOR_BARCODE'			=> $door->doorBarcode . '',
					'TYPE_OTHER'      		=> $door->door_type_other . '',
    			    'STYLE'    				=> Model_Dictionary::getIdByItem($door->door_style, 'Door Style') . '',
    			    'MATERIAL' 				=> Model_Dictionary::getIdByItem($door->door_material, 'Door Material') . '',
    			   	'MATERIAL_OTHER'		=> $door->door_material_other . '',
    			    'ELEVATION'    			=> Model_Dictionary::getIdByItem($door->door_elevation, 'Door Elevation') . '',
					'ELEVATION_OTHER'		=> $door->door_elevation_other . '',
    			    'FRAME_MATERIAL'		=>	Model_Dictionary::getIdByItem($door->frame_material, 'Frame Material') . '',
				    'FRAME_MATERIAL_OTHER'	=> $door->frame_material_other . '',
    			    'FRAME_ELEVATION'		=> Model_Dictionary::getIdByItem($door->frame_elevation, 'Frame Elevation') . '',
    			    'FRAME_ELEVATION_OTHER'	=> $door->frame_elevation_other. '',
    		 	   	'FIRE_RATING_1'			=> Model_Dictionary::getIdByItem($door->fire_rating, 'Fire-Rating 1') . '',
    				'FIRE_RATING_2'			=> Model_Dictionary::getIdByItem($door->fire_rating2, 'Fire-Rating 2') . '',
    				'FIRE_RATING_3'			=> Model_Dictionary::getIdByItem($door->fire_rating3, 'Fire-Rating 3') . '',
    				'FIRE_RATING_4'			=> Model_Dictionary::getIdByItem($door->fire_rating4, 'Fire-Rating 4') . '',
    				'TEMP_RISE'				=> Model_Dictionary::getIdByItem($door->temprise, 'Door Temperature Rise') . '',
    				'LOCATION'     			=> $door->location. '',
    				'REMARKS'     			=> $door->remarks. '',
    				'COMPLIANT'    			=> Model_Dictionary::getIdByItem($door->compliant, 'Logical') . '',
    				'MANUFACTURER'			=> $door->maker. '',
    				'MODEL'  				=> $door->model. '',
    				'FRAME_MANUFACTURER'	=> $door->framemaker. '',
    				'BARCODE'				=> $door->barcode. '',
    				'RFID'     				=> $door->RFID. '',
    				'LISTING_AGENCY' 		=> Model_Dictionary::getIdByItem($door->listingagency, 'Door Listing Agency') . '',
    				'LISTING_AGENCY_OTHER'	=> $door->listingagency_other. '',
    			  	'GAUGE'					=> $door->gauge . '',
    				'HANDING'   			=> Model_Dictionary::getIdByItem($door->handing, 'Handing') . ''
    			);
				$door_record = Helper::changeEmptyStrings($door_record);

    			//if inspection is new, we must make erase auto generated on a tablet door id to make sure new entry will be created
     				//try to find the door by its id. If found, replace id with id from DB
     				if ($door->id . '' != '') {
    					$old_door = Model_Door::retrieve()->fetchEntry($door->id . '', array('ID',
    						new Data_Column('INSPECTION_ID', $inspectionId . '')));
    					if ($old_door) $door_record['ID'] = $old_door['ID'];
    					else unset($door_record['ID']);
    				}
    				if (!array_key_exists('ID', $door_record)) {// find by number
    					$old_door = Model_Door::retrieve()->fetchEntry(null, array('ID',
    						new Data_Column('NUMBER', $door->number . ''),
    						new Data_Column('INSPECTION_ID', $inspectionId . '')));
    					if ($old_door) $door_record['ID'] = $old_door['ID'];
    					else unset($door_record['ID']);
    				}
    			if (!isset($door_record['ID']) || !isset($xml->overwrite_duplicates) || $xml->overwrite_duplicates == '1') {
     				if ($isNewInspection) {
	    				unset($door_record['ID']);
	    				$doors[$door->id . ''] = Model_Door::retrieve()->saveClean($door_record);
	    			} else { //if existing door, we just save it
	    				$doors[$door->id . ''] = Model_Door::retrieve()->saveClean($door_record);
	    			}
    			
	    			//Zend_Registry::getInstance()->logger->info('DoorId = ' . $doors[$door->id . '']);
					Zend_Registry::get('logger')->info('Emptying curent door data');
	    			
	    			//empty all relative tables
	    			Model_Audio::retrieve()->deleteEntries(array(new Data_Column('DOOR_ID', $doors[$door->id.''])));
	    			Model_DoorCode::retrieve()->deleteEntries(array(new Data_Column('DOOR_ID', $doors[$door->id.''])));
					Model_DoorNote::retrieve()->deleteEntries(array(new Data_Column('DOOR_ID', $doors[$door->id.''])));
					Model_DoorType::retrieve()->deleteEntries(array(new Data_Column('DOOR_ID', $doors[$door->id.''])));	   		  	
					Model_Floorplan::retrieve()->deleteEntries(array(new Data_Column('DOOR_ID', $doors[$door->id.''])));
					Model_Hardware::retrieve()->deleteEntries(array(new Data_Column('DOOR_ID', $doors[$door->id.''])));
					Model_Ink::retrieve()->deleteEntries(array(new Data_Column('DOOR_ID', $doors[$door->id.''])));
					Model_Picture::retrieve()->deleteEntries(array(new Data_Column('DOOR_ID', $doors[$door->id.''])));
					
					Zend_Registry::get('logger')->info('Emptying completed. Saving door types...');
					/* door type record */
		   		  	$types = explode(',', $door->door_type.'');
		   		  	foreach ($types as $type) {
		   		  		if (strlen(trim($type)) > 0) {
			   		  		$door_type_record=array(
		    					'DOOR_ID'  		=> $doors[$door->id.''] . '',
		    					'TYPE_ID'  		=> Model_Dictionary::getIdByItem(trim($type), 'Door Type') . ''
		    				);
		    				$door_type_record = Helper::changeEmptyStrings($door_type_record);
		    				Model_DoorType::retrieve()->save($door_type_record);
	    				}
		   		  	}
					Zend_Registry::get('logger')->info('Types saved');
    			}
    		}  

			//$this->_helper->layout->setLayout('http201created');
    		//return;
    		
    	    /* picture record */
    	    Zend_Registry::get('logger')->info('Loading pictures');
			$pictureId = 0;
        	foreach($xml->pictures as $picture) {
        		if (!array_key_exists($picture->door_id.'', $doors)) continue;
        		$pictureId++;
        		$pictureName = 'pict_door_'.$picture->door_id.'_'.$pictureId.'.jpg';
				Helper::saveBase64File($picture->image, '/content/pictures/', $pictureName);
    			$picture_record=array(
    			//	'ID'			=> $picture->id . '',
    		    	'DOOR_ID'  		=> $doors[$picture->door_id.''] . '',
    				'PICTURE_FILE'  => $pictureName,	
    				'CONTROL_NAME'	=> $picture->control_name . '',	
    				'ROTATION'		=> $picture->rotation . '',
    		    	'INK_STROKES'	=> $picture->inkstrokes . ''
    			);
    			$picture_record = Helper::changeEmptyStrings($picture_record);
    			Model_Picture::retrieve()->save($picture_record);
    			//Zend_Registry::getInstance()->logger->info('Picture saved for door ' . $doors[$picture->door_id.'']);
    			
    		}
    		Zend_Registry::get('logger')->info('Pictures loaded. Loading audio..');
    	    /* audio record */
			foreach($xml->audio as $audio) {
        		if (!array_key_exists($audio->door_id.'', $doors)) continue;
        		$audioName = 'audio_door_'.$audio->door_id.'_'.date('YmdHis').'.wav';
				Helper::saveBase64File($audio->image, '/content/audio/', $audioName);
    			$audio_record=array(
    			//	'ID'			=> $picture->id . '',
    		    	'DOOR_ID'  		=> $doors[$audio->door_id.''] . '',
    				'AUDIO_FILE'	=>  $audioName,
    				'CONTROL_NAME'	=> $audio->control_name . '',
    		    	'INK_STROKES'	=> $audio->inkstrokes . ''
    			);
      		  	$audio_record = Helper::changeEmptyStrings($audio_record);
    			Model_Audio::retrieve()->save($audio_record);
    		}
			Zend_Registry::get('logger')->info('Audio loaded. Loading details...');
			/* doordetail record */
			foreach ($xml->doordetail as $doordetail) {
    			if (!array_key_exists($doordetail->door_id.'', $doors)) continue;
        		$doordetail_record=array(
    				'ID'						=> $doors[$doordetail->door_id.''] . '',
    				'INK_STROKES'   			=> $doordetail->ink_strokes . '',
    				'HINGE_HEIGHT' 				=> Model_Dictionary::getIdByItem($doordetail->hinge_height, 'Hinge Height') . '',
    				'HINGE_THICKNESS'			=> Model_Dictionary::getIdByItem($doordetail->hinge_thickness, 'Hinge Thickness') . '',
    				'HINGE_HEIGHT1'   			=> $doordetail->hinge_height1 . '',
    				'HINGE_HEIGHT2'   			=> $doordetail->hinge_height2 . '',
    				'HINGE_HEIGHT3'   			=> $doordetail->hinge_height3 . '',
    				'HINGE_HEIGHT4'   			=> $doordetail->hinge_height4 . '',
	    			'HINGE_FRACTION1'   		=> Model_Dictionary::getIdByItem($doordetail->hinge_fraction1, 'Hinge Fraction1') . '',
	    			'HINGE_FRACTION2'   		=> Model_Dictionary::getIdByItem($doordetail->hinge_fraction2, 'Hinge Fraction2') . '',
	    			'HINGE_FRACTION3'   		=> Model_Dictionary::getIdByItem($doordetail->hinge_fraction3, 'Hinge Fraction3') . '',
	    			'HINGE_FRACTION4'   		=> Model_Dictionary::getIdByItem($doordetail->hinge_fraction4, 'Hinge Fraction4') . '',
	    			'HINGE_BACKSET'   			=> Model_Dictionary::getIdByItem($doordetail->hinge_backset, 'Hinge Backset') . '',
	    			'HINGE_MANUFACTURER'   		=> $doordetail->hinge_manufacturer . '',
	    			'HINGE_MANUFACTURER_NO'   	=> $doordetail->hinge_manufacturer_no . '',
	    			'TOP_TO_CENTERLINE'   		=> $doordetail->top_to_centerline . '',
	    			'TOP_TO_CENTERLINE_FRACTION'=> Model_Dictionary::getIdByItem($doordetail->top_to_centerline_fraction, 'Top To Centerline Fraction') . '',
	    			'LOCK_BACKSET'   			=> Model_Dictionary::getIdByItem($doordetail->lock_bracket, 'Lock Backset') . '',
	    			'FRAME_BOTTOM_TO_CENTER'   	=> $doordetail->frame_bottom_to_center . '',
	    			'STRIKE_HEIGHT'   			=> Model_Dictionary::getIdByItem($doordetail->strike_height, 'Strike Height') . '',
	    			'PREFIT_DOOR_SIZE_X'   		=> $doordetail->prefit_door_size_x . '',
	    			'PREFIT_FRACTION_X'   		=> Model_Dictionary::getIdByItem($doordetail->prefit_fraction_x, 'Prefit Fraction X') . '',
	    			'PREFIT_DOOR_SIZE_Y'   		=> $doordetail->prefit_door_size_y . '',
	    			'PREFIT_FRACTION_Y'   		=> Model_Dictionary::getIdByItem($doordetail->prefit_fraction_y, 'Prefit Fraction Y') . '',
	    			'FRAME_OPENING_SIZE_X'   	=> $doordetail->frame_opening_size_x . '',
	    			'FRAME_OPENING_FRACTION_X'  => Model_Dictionary::getIdByItem($doordetail->frame_opening_fraction_x, 'Frame Opening Fraction X') . '',
	    			'FRAME_OPENING_SIZE_Y'   	=> $doordetail->frame_opening_size_y . '',
    				'FRAME_OPENING_FRACTION_Y'  => Model_Dictionary::getIdByItem($doordetail->frame_opening_fraction_y, 'Frame Opening Fraction Y') . '',
    				'LITE_CUTOUT_SIZE_X'   		=> $doordetail->lite_cutout_size_x . '',
    				'LITE_CUTOUT_FRACTION_X'   	=> Model_Dictionary::getIdByItem($doordetail->lite_cutout_fraction_x, 'Lite Cutout Fraction X') . '',
    				'LITE_CUTOUT_SIZE_Y'   		=> $doordetail->lite_cutout_size_y . '',
    				'LITE_CUTOUT_FRACTION_Y'   	=> Model_Dictionary::getIdByItem($doordetail->lite_cutout_fraction_y, 'Lite Cutout Fraction Y') . '',
    				'LOCKSTILE_SIZE'   			=> $doordetail->lockstile_size . '',
    				'LOCKSTILE_FRACTION'   		=> Model_Dictionary::getIdByItem($doordetail->lockstile_fraction, 'Lockstile Fraction') . '',
    				'TOPRAIL_SIZE'   			=> $doordetail->toprail_size . '',
    				'TOPRAIL_FRACTION'   		=> Model_Dictionary::getIdByItem($doordetail->toprail_fraction, 'Top Rail Fraction') . ''
    			);
				$doordetail_record = Helper::changeEmptyStrings($doordetail_record);
    		  	Model_Door::retrieve()->saveCLean($doordetail_record);
    		  	//Zend_Registry::getInstance()->logger->info('Doordetail saved for door  ' . $doors[$doordetail->door_id.'']);	
    		}
			Zend_Registry::get('logger')->info('Details loaded. Loading frames');
    			
    	   	/* framedetail record */
			foreach($xml->framedetail as $framedetail) {
    			if (!array_key_exists($framedetail->door_id.'', $doors)) continue;
       			$framedetail_record=array(
    			//	'ID'			=> $framedetail->id,
    		    	'ID'			=> $doors[$framedetail->door_id.''],
    			    'FRAME_INK_STROKES'	=> $framedetail->ink_strokes,
    				'A'				=> $framedetail->a,
    				'A_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->afraction, 'Frame Fraction') . '',
    				'B'				=> $framedetail->b,
    				'B_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->bfraction, 'Frame Fraction') . '',
    				'C'				=> $framedetail->c,
    				'C_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->cfraction, 'Frame Fraction') . '',
    				'D'				=> $framedetail->d,
    				'D_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->dfraction, 'Frame Fraction') . '',
    				'E'				=> $framedetail->e,
    				'E_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->efraction, 'Frame Fraction') . '',
    				'F'				=> $framedetail->f,
    				'F_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->ffraction, 'Frame Fraction') . '',
    				'G'				=> $framedetail->g,
    				'G_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->gfraction, 'Frame Fraction') . '',
    				'H'				=> $framedetail->h,
    				'H_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->hfraction, 'Frame Fraction') . '',
    				'I'				=> $framedetail->i,
    				'I_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->ifraction, 'Frame Fraction') . '',
    				'J'				=> $framedetail->j,
    				'J_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->jfraction, 'Frame Fraction') . '',
    				'K'				=> $framedetail->k,
    				'K_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->kfraction, 'Frame Fraction') . '',
    				'L'				=> $framedetail->l,
    				'L_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->lfraction, 'Frame Fraction') . '',
    				'M'				=> $framedetail->m,
    				'M_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->mfraction, 'Frame Fraction') . '',
    				'N'				=> $framedetail->n,
    				'N_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->nfraction, 'Frame Fraction') . '',
    				'O'				=> $framedetail->o,
    				'O_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->ofraction, 'Frame Fraction') . '',
    				'P'				=> $framedetail->p,
    				'P_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->pfraction, 'Frame Fraction') . '',
    				'Q'				=> $framedetail->q,
    				'Q_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->qfraction, 'Frame Fraction') . '',
    				'R'				=> $framedetail->r,
    				'R_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->rfraction, 'Frame Fraction') . '',
    				'S'				=> $framedetail->s,
    				'S_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->sfraction, 'Frame Fraction') . '',
    				'T'				=> $framedetail->t,
    				'T_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->tfraction, 'Frame Fraction') . '',
    				'U'				=> $framedetail->u,
    				'U_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->ufraction, 'Frame Fraction') . '',
    				'V'				=> $framedetail->v,
    				'V_FRACTION'	=> Model_Dictionary::getIdByItem($framedetail->vfraction, 'Frame Fraction') . ''			
    			);
    			$framedetail_record = Helper::changeEmptyStrings($framedetail_record);
    		  	Model_Door::retrieve()->saveClean($framedetail_record);
    		  	//Zend_Registry::getInstance()->logger->info('Framedetail saved for door  ' . $doors[$framedetail->door_id.'']);
    			
    		}
			Zend_Registry::get('logger')->info('Frames loaded. Loading floorplanes...');
    		/* floorplan record */
			foreach($xml->floorplan as $floorplan) {
    			if (!array_key_exists($floorplan->door_id.'', $doors)) continue;
      			$floorplan_record=array(
    		    //	'ID'			=> $floorplan->id,	
    				'DOOR_ID'		=> $doors[$floorplan->door_id.''],
    			    'INK_STROKES'	=> $floorplan->ink_strokes
    			);
    			$floorplan_record = Helper::changeEmptyStrings($floorplan_record);
      		  	Model_Floorplan::retrieve()->save($floorplan_record);
    		}
			Zend_Registry::get('logger')->info('Floor plans loaded. Loading HW...');
			/* hardwareset record */
			foreach($xml->hardwareset as $hardware) {
   				if (!array_key_exists($hardware->door_id.'', $doors)) continue;
     			$hardware_record=array(
   				//	'ID'				=> $hardware->id . '',
   		    		'DOOR_ID'			=> $doors[$hardware->door_id.''] . '',
   				//	'HARDWARE_GROUP'	=> $hardware->hardware_group . '',
   				//	'HARDWARE_SET'		=> $hardware->hardware_set . '',
   					'ITEM_ID'			=> $hardware->item_id . '',
   					'VERIFY' 			=> $hardware->verify . '',
					'QTY'  				=> substr($hardware->qty, 0, 10),
   					'ITEM'  			=> substr($hardware->item, 0, 42),
   					'PRODUCT'  			=> substr($hardware->product, 0, 55),
   					'MFG'  				=> substr($hardware->mfg, 0, 10),
   					'FINISH'  			=> substr($hardware->finish, 0,  10)
   				);	   					
   				$hardware_record = Helper::changeEmptyStrings($hardware_record);
   				Model_Hardware::retrieve()->save($hardware_record);
				Zend_Registry::get('logger')->info('HW saved, door id: ' . $hardware->door_id);
   				Model_Door::retrieve()->saveClean(array('ID' 			=> $doors[$hardware->door_id.''] . '',
   												   'HARDWARE_GROUP'	=> $hardware->hardware_group . '',
   												   'HARDWARE_SET'	=> $hardware->hardware_set . ''));
				Zend_Registry::get('logger')->info('Door saved');
     		}
			Zend_Registry::get('logger')->info('HW loaded.. Loading inks');
    		/* ink record */
        	foreach($xml->ink as $ink) {
    			if (!array_key_exists($ink->door_id.'', $doors)) continue;
    			$ink_record=array(
    			//	'ID'  			=> $ink->id,
    		    	'DOOR_ID'  		=> $doors[$ink->door_id.''],
					'INK_STROKE'	=> $ink->inkstroke,
    				'FORM_NUM'		=> $ink->formnum,
    				'CONTROL_NAME'	=> $ink->control_name
    			);
    			$ink_record = Helper::changeEmptyStrings($ink_record);
    			Model_Ink::retrieve()->save($ink_record);
    		}
			Zend_Registry::get('logger')->info('Ink loaded. Loading notes...');
    	    /* door_note record */
			foreach($xml->notes as $note) {
    			if (!array_key_exists($note->door_id.'', $doors)) continue;
   				$note_record=array(
    			//	'ID'	  		=> $note->id,
    		    	'DOOR_ID'  		=> $doors[$note->door_id.''],
					'NOTE'  		=> $note->note,
    				'CONTROL_NAME'  => $note->control_name
    			);
    			$note_record = Helper::changeEmptyStrings($note_record);
    			Model_DoorNote::retrieve()->save($note_record);
    		}
			Zend_Registry::get('logger')->info('Notes loaded. Loading codes...');
    		
    	   	/* door_to_code record */
			// get list of all codes from xml
			$codes = array();
			foreach ($xml->codes as $code) {
				$codeId = $code->id . '';
				$codes[$codeId] = $code;
			}
			
			$incompliantsaved = array();
    		foreach($xml->door_to_code as $door_to_code) {
    			if (!array_key_exists($door_to_code->door_id.'', $doors)) continue;
  				$doorId = $doors[$door_to_code->door_id.''];
    			if (!array_key_exists($doorId, $incompliantsaved)) {
    				Model_Door::retrieve()->saveClean(array(
						'ID' => $doorId,
						'COMPLIANT' => Model_Dictionary::getIdByItem('No', 'Logical')));
					$incompliantsaved[$doorId] = true;
				}
        		$door_to_code_record=array(
        		//	'ID'			=> $door_to_code->id,
					'DOOR_ID'  		=> $doorId,
    				'CODE_ID'		=> $door_to_code->code_id,
    				'ACTIVE'  		=> Helper::getBool($door_to_code->active),
    				'CONTROL_NAME'	=> $door_to_code->control_name
    			);
    			$door_to_code_record = Helper::changeEmptyStrings($door_to_code_record);
    			Model_DoorCode::retrieve()->save($door_to_code_record);
				
				// verify if it's an "Other" value
				$doorCode = $door_to_code->code_id . '';
				if (Model_Dictionary::retrieve()->getDescriptionById($doorCode) == 'Other') {
				/*	$other = Model_Inspection_Other::retrieve()->fetchEntry(null, array(
						new Data_Column('INSPECTION_ID', $inspection->id . ''),
						new Data_Column('OTHER_ID', $doorCode)
					));
					if (!$other) { // this other doesn't exist yet for the inspection
						*/Model_Inspection_Other::retrieve()->save(array(
							'OTHER_ID'      => $doorCode, 
				  			'OTHER_VALUE'   => $codes[$doorCode]->code_label . '',
				  			'INSPECTION_ID' => $inspection->id . ''));
				//	}
				}	
    		}
			Zend_Registry::get('logger')->info('Codes loaded. All door info is loaded');
    		
    		// commit transaction
    		$db->commit();
    		
    		// in case of success return a HTTP 201 Created headed response
    	    $this->_helper->layout->setLayout('http201created');
    	    
    		//return inspection ID in HTTP201Created header
    		$this->view->placeholder('location')->set($this->view->fullBaseUrl . '/inspectionservice?id=' . $inspectionId);
    		
    		//set status for XML logging
    		$status = 'succeeded';
    	} 
    	catch(Exception $e) {
    		//rollback transaction
    		$db->rollBack();
    		
    		//add record to log
    		$error = $e->getMessage() . '\n' . $e->getTraceAsString(); 	
    		Zend_Registry::getInstance()->logger->err($error);
    		$this->view->placeholder('error_message')->set($error);
    		
    		//set status for XML logging
    		$status = 'failed';
    	}
    	
    	//save xml log record
		Helper::saveXmlLog($raw_post, $inspectionId, 'Inspection Service', $status);
    }
    
    protected function put() {
    	
    }
        
    protected function delete() {
    	
    }
    

}
