<?
require_once APPLICATION_PATH . '/controllers/Helper.php';
require_once APPLICATION_PATH . '/models/Dictionary.php';
require_once APPLICATION_PATH . '/models/Inspection.php';
require_once APPLICATION_PATH . '/models/Inspect.php';
require_once APPLICATION_PATH . '/models/Company.php';
require_once APPLICATION_PATH . '/models/Building.php';
require_once APPLICATION_PATH . '/models/Door.php';
require_once APPLICATION_PATH . '/models/Picture.php';
require_once APPLICATION_PATH . '/models/Audio.php';
require_once APPLICATION_PATH . '/models/Ink.php';
require_once APPLICATION_PATH . '/models/DoorNote.php';
require_once APPLICATION_PATH . '/models/DoorCode.php';
require_once APPLICATION_PATH . '/models/DoorType.php';
require_once APPLICATION_PATH . '/models/Employee.php';
require_once APPLICATION_PATH . '/models/Framedetail.php';
require_once APPLICATION_PATH . '/models/Floorplan.php';
require_once APPLICATION_PATH . '/models/Hardware.php';
require_once APPLICATION_PATH . '/models/Inspectionother.php';

class Adapter_Mico {
	
	protected $_httpSession;
	protected $_errorMsg = 'DOORDATA entry error: ';
	
	public function __construct($serviceUrl) {
		$this->_httpSession = new Zend_Http_Client($serviceUrl, array('timeout' => 6000));
	}

	// retrieve item from dictionary
	private function getItem($data) {
		if ($data == null) return null;
		$item_pag = Model_Dictionary::retrieve()->fetchEntries(array('ITEM', new Data_Column('ID', $data)));
		if ($item_pag->getTotalItemCount() > 0) {
			$item = $item_pag->getItem(1);
			return $item['ITEM']; 
		} else return 'item not found';
	}

	// read and encode file to MIME base64
	private function getFile($path) {
		if ($path == null) return null;
		$handle = fopen(ROOT_PATH . $path, 'rb');
		$contents = fread($handle, filesize(ROOT_PATH . $path));
		fclose($handle);
		if ($contents == null) return null;
			else return base64_encode($contents);
	}

	// add usual xml node
	public function addNode($tag, $data) {
		if ($data == null) return '<' . $tag . ' />';
			else return '<' . $tag . '>' . htmlspecialchars(utf8_encode($data), ENT_NOQUOTES, 'UTF-8') . '</' . $tag . '>';
	}

	// add date xml node (skip empty tags)
	public function addDateNode($tag, $data) {
		if ($data == null) return '';
		else return '<' . $tag . '>' . self::reformat_date($data, "Y-m-d") . "T00:00:00Z" . '</' . $tag . '>';
	}
	
	// add boolean xml node (Yes/No)
	public function addBoolNode($tag, $data) {
		if ($data == null) return '<' . $tag . ' />';
		else {
			if (var_export($data, true) != '\'\' . "\0" . \'\'')
				return '<' . $tag . '>' . 'Yes' . '</' . $tag . '>';
			else
				return '<' . $tag . '>' . 'No' . '</' . $tag . '>';
		}
	}

	// main function: fill in XML body
	public function generateXML($id, $noSignatures=false) {

		// get required data
		$inspection_pag = Model_Inspection::retrieve()->fetchEntries(array(
			'ID', 'INSPECTION_DATE', 'INSPECTION_COMPLETE_DATE', 'REINSPECT_DATE', 'INSPECTOR_ID',
			'BUILDING_ID', 'COMPANY_ID', 'SIGNATURE_INSPECTOR', 'SIGNATURE_STROKES_INSPECTOR',
			'SIGNATURE_BUILDING', 'SIGNATURE_STROKES_BUILDING', 'SUMMARY',
			'TEMPLATE' => new Data_Column('TEMPLATE_ID', null, Model_Inspection::retrieve(), 'DESCRIPTION'),
			new Data_Column('ID', $id)));
		if ($inspection_pag->getTotalItemCount() > 0) $inspection = $inspection_pag->getItem(1);
			else return $this->_errorMsg . 'inspection does not exist!';
		/* check inspection template */
		if (!array_key_exists('TEMPLATE', $inspection) || $inspection['TEMPLATE'] == "")
			return $this->_errorMsg . 'inspection template is not defined!';
			
		/*$inspect_pag = Model_Inspect::retrieve()->fetchEntries(array(
			'ID', 'INSPECTOR_ID', 'INSPECTION_ID',
			new Data_Column('INSPECTION_ID', $id)));
		if ($inspect_pag->getTotalItemCount() > 0) $inspect = $inspect_pag->getItem(1);
			else return $this->_errorMsg . 'inspection does not have assigned inspector!';*/
			
		/* check inspector */
		if (!array_key_exists('INSPECTOR_ID', $inspection) || $inspection['INSPECTOR_ID'] == "")
			return $this->_errorMsg . 'inspection does not have assigned inspector!';
		$inspector_pag = Model_Employee::retrieve()->fetchEntries(array(
			'ID', 'FIRST_NAME', 'LAST_NAME', 'LICENSE_NUMBER', 'EXPIRATION_DATE',
			new Data_Column('ID', $inspection['INSPECTOR_ID'])));
		if ($inspector_pag->getTotalItemCount() > 0) $inspector = $inspector_pag->getItem(1);
			else return $this->_errorMsg . 'inspector does not have assigned employee!';
		
		/* check building */
		if(!array_key_exists('BUILDING_ID', $inspection) || $inspection['BUILDING_ID'] == "")
			return $this->_errorMsg . 'inspection does not have assigned building!';
		$building_pag = Model_Building::retrieve()->fetchEntries(array(
			'ID', 'NAME', 'ADDRESS_1', 'ADDRESS_2', 'CITY', 'STATE',
			'COUNTRY', 'ZIP', 'SUMMARY', 'CUSTOMER_ID',
			new Data_Column('ID', $inspection['BUILDING_ID'])));
		if ($building_pag->getTotalItemCount() > 0) $building = $building_pag->getItem(1);
			else return $this->_errorMsg . 'inspection does not have assigned building!';
			
		/* check building owner */
		if(!array_key_exists('CUSTOMER_ID', $building) || $building['CUSTOMER_ID'] == "")
			return $this->_errorMsg . 'building does not have assigned owner!';
		$customer_pag = Model_Company::retrieve()->fetchEntries(array(
			'ID', 'NAME', new Data_Column('ID', $building['CUSTOMER_ID'])));
		if ($customer_pag->getTotalItemCount() > 0) $customer = $customer_pag->getItem(1);
			else return $this->_errorMsg . 'building does not have assigned owner!';
			
		/* check inspection company */
		if (!array_key_exists('COMPANY_ID', $inspection) || $inspection['COMPANY_ID'] == "")
			return $this->_errorMsg . 'inspection does not have assigned inspection company!';
		$company_pag = Model_Company::retrieve()->fetchEntries(array(
			'ID', 'NAME', 'ADDRESS_1', 'ADDRESS_2', 'CITY', 'STATE', 'ZIP',
			new Data_Column('ID', $inspection['COMPANY_ID'])));
		if ($company_pag->getTotalItemCount() > 0) $company = $company_pag->getItem(1);
			else return $this->_errorMsg . 'inspection does not have assigned inspection company!';
		
		$doors = Model_Door::retrieve()->fetchEntries(array(
			// doors
			'ID', 'BUILDING_ID', 'NUMBER', 'DOOR_BARCODE', 'TYPE_OTHER', 'STYLE',
			'MATERIAL', 'MATERIAL_OTHER', 'ELEVATION', 'ELEVATION_OTHER', 'FRAME_MATERIAL',
			'FRAME_MATERIAL_OTHER', 'FRAME_ELEVATION', 'FRAME_ELEVATION_OTHER', 'LOCATION',
			'FIRE_RATING_1', 'FIRE_RATING_2', 'FIRE_RATING_3', 'FIRE_RATING_4', 'TEMP_RISE',
			'MANUFACTURER', 'BARCODE', 'REMARKS', 'COMPLIANT', 'MODEL', 'FRAME_MANUFACTURER',
			'RFID', 'LISTING_AGENCY', 'LISTING_AGENCY_OTHER', 'GAUGE', 'HANDING',
			// doordetail
			'INK_STROKES', 'HINGE_HEIGHT', 'HINGE_THICKNESS', 'HINGE_HEIGHT1', 'HINGE_HEIGHT2',
			'HINGE_HEIGHT3', 'HINGE_HEIGHT4', 'HINGE_FRACTION1', 'HINGE_FRACTION2',
			'HINGE_FRACTION3', 'HINGE_FRACTION4', 'HINGE_BACKSET', 'HINGE_MANUFACTURER',
			'HINGE_MANUFACTURER_NO', 'TOP_TO_CENTERLINE', 'TOP_TO_CENTERLINE_FRACTION',
			'LOCK_BACKSET', 'FRAME_BOTTOM_TO_CENTER', 'STRIKE_HEIGHT', 'PREFIT_DOOR_SIZE_X',
			'PREFIT_FRACTION_X', 'PREFIT_DOOR_SIZE_Y', 'PREFIT_FRACTION_Y', 'FRAME_OPENING_SIZE_X',
			'FRAME_OPENING_FRACTION_X', 'FRAME_OPENING_SIZE_Y', 'FRAME_OPENING_FRACTION_Y',
			'LITE_CUTOUT_SIZE_X', 'LITE_CUTOUT_FRACTION_X', 'LITE_CUTOUT_SIZE_Y', 'LITE_CUTOUT_FRACTION_Y',
			'LOCKSTILE_SIZE', 'LOCKSTILE_FRACTION', 'TOPRAIL_SIZE', 'TOPRAIL_FRACTION', 
			'A', 'A_FRACTION', 'B', 'B_FRACTION', 'C', 'C_FRACTION', 'D', 'D_FRACTION', 'E', 'E_FRACTION',
		 	'F', 'F_FRACTION', 'G', 'G_FRACTION', 'H', 'H_FRACTION', 'I', 'I_FRACTION', 'J', 'J_FRACTION', 
		 	'K', 'K_FRACTION', 'L', 'L_FRACTION', 'M', 'M_FRACTION', 'N', 'N_FRACTION', 'O', 'O_FRACTION',
		 	'P', 'P_FRACTION', 'Q', 'Q_FRACTION', 'R', 'R_FRACTION', 'S', 'S_FRACTION', 'T', 'T_FRACTION',
		 	'U', 'U_FRACTION', 'V', 'V_FRACTION', 'FRAME_INK_STROKES',
			new Data_Column('INSPECTION_ID', $id)), null, true);

		$all_pictures = $all_audio_files = $all_ink_strokes = $all_notes = $all_codes
		              = $all_door_codes = $all_hardwaresets
		              = array();
		foreach ($doors as $door) {
			$all_pictures[] = Model_Picture::retrieve()->fetchEntries(array(
				'ID', 'DOOR_ID', 'PICTURE_FILE', 'CONTROL_NAME', 'ROTATION', 'INK_STROKES',
				new Data_Column('DOOR_ID', $door['ID'])), null, true);
			$all_audio_files[] = Model_Audio::retrieve()->fetchEntries(array(
				'ID', 'DOOR_ID', 'AUDIO_FILE', 'CONTROL_NAME', 'INK_STROKES',
				new Data_Column('DOOR_ID', $door['ID'])), null, true);
			$all_ink_strokes[] = Model_Ink::retrieve()->fetchEntries(array(
				'ID', 'DOOR_ID', 'INK_STROKE', 'FORM_NUM', 'CONTROL_NAME',
				new Data_Column('DOOR_ID', $door['ID'])), null, true);
			$all_notes[] = Model_DoorNote::retrieve()->fetchEntries(array(
				'ID', 'DOOR_ID', 'NOTE', 'CONTROL_NAME',
				new Data_Column('DOOR_ID', $door['ID'])), null, true);
			$all_door_codes[] = Model_DoorCode::retrieve()->fetchEntries(array(
				'ID', 'DOOR_ID', 'CODE_ID', 'ACTIVE','CONTROL_NAME',
				new Data_Column('DOOR_ID', $door['ID'])), null, true);
//			$all_framedetails[] = Model_Framedetail::retrieve()->fetchEntries(array(
//				'ID', 'DOOR_ID', 'INK_STROKES',
//				new Data_Column('DOOR_ID', $door['ID'])), null, true);
			$all_floorplans[] = Model_Floorplan::retrieve()->fetchEntries(array(
				'ID', 'DOOR_ID', 'INK_STROKES',
				new Data_Column('DOOR_ID', $door['ID'])), null, true);
			$all_hardwaresets[] = Model_Hardware::retrieve()->fetchEntries(array(
				'ID', 'DOOR_ID', 'ITEM_ID',
				'VERIFY', 'QTY', 'ITEM', 'PRODUCT', 'MFG', 'FINISH',
				'HARDWARE_GROUP' => new Data_Column('DOOR_ID', null, Model_Hardware::retrieve(), 'HARDWARE_GROUP'),
				'HARDWARE_SET' => new Data_Column('DOOR_ID', null, Model_Hardware::retrieve(), 'HARDWARE_SET'),
				new Data_Column('DOOR_ID', $door['ID'])), null, true);
		}
		$code_categories = Model_Dictionary::retrieve()->fetchEntries(array('ID', 'ITEM',
			new Data_Column('CATEGORY', 'Code Category')), null, true);
		foreach ($code_categories as $code_category)
			$all_codes[] = Model_Dictionary::retrieve()->fetchEntries(array(
				'ID', 'ITEM', 'DESCRIPTION', new Data_Column('CATEGORY', $code_category['ITEM'])), null, true);
				
		// get other codes
		$otherCodes = array();
		foreach(Model_Inspection_Other::retrieve()->fetchEntries(array(
			new Data_Column('INSPECTION_ID', $id), 'OTHER_ID', 'OTHER_VALUE'), null, true) as $otherCode){
			$otherCodes[$otherCode['OTHER_ID']] = $otherCode['OTHER_VALUE'];				
		}


		// start XML body
		$xml = '';

		// customers
		$xml .= '<customers>';
		$xml .= $this->addNode('id', $customer['ID']);
		$xml .= $this->addNode('name', $customer['NAME']);
		$xml .= '</customers>';

		// buildings
		$xml .= '<buildings>';
		$xml .= $this->addNode('id', $building['ID']); 
		$xml .= $this->addNode('customer_id', $building['CUSTOMER_ID']); 
		$xml .= $this->addNode('name', $building['NAME']); 
		$xml .= $this->addNode('address1', $building['ADDRESS_1']); 
		$xml .= $this->addNode('address2', $building['ADDRESS_2']); 
		$xml .= $this->addNode('city', $building['CITY']); 
		$xml .= $this->addNode('state', $this->getItem($building['STATE']));
		$xml .= $this->addNode('zipcode', $building['ZIP']); 
		$xml .= $this->addNode('country', $this->getItem($building['COUNTRY'])); 
		$xml .= $this->addNode('summary', $building['SUMMARY']); 
		$xml .= '</buildings>';

		// doors
		foreach ($doors as $door) {
			$xml .= '<doors>';
			$xml .= $this->addNode('id', $door['ID']);
			$xml .= $this->addNode('building_id', $door['BUILDING_ID']);
			$xml .= $this->addNode('number', $door['NUMBER']);
			$xml .= $this->addNode('doorBarcode', $door['DOOR_BARCODE']);
			$door_types_str = '';
			$door_types = Model_DoorType::retrieve()->fetchEntries(array(
				'TYPE_ID', new Data_Column('DOOR_ID', $door['ID'])), null, true);
			foreach ($door_types as $door_type) {
				if ($door_types_str != '') $door_types_str .= ', ';
				$door_types_str .= $this->getItem($door_type['TYPE_ID']);
			}
			$xml .= $this->addNode('door_type', $door_types_str);
			$xml .= $this->addNode('door_type_other', $door['TYPE_OTHER']);
			$xml .= $this->addNode('door_style', $this->getItem($door['STYLE']));
			$xml .= $this->addNode('door_material', $this->getItem($door['MATERIAL']));
			$xml .= $this->addNode('door_material_other', $door['MATERIAL_OTHER']);
			$xml .= $this->addNode('door_elevation', $this->getItem($door['ELEVATION']));
			$xml .= $this->addNode('door_elevation_other', $door['ELEVATION_OTHER']);
			$xml .= $this->addNode('frame_material', $this->getItem($door['FRAME_MATERIAL']));
			$xml .= $this->addNode('frame_material_other', $door['FRAME_MATERIAL_OTHER']);
			$xml .= $this->addNode('frame_elevation', $this->getItem($door['FRAME_ELEVATION']));
			$xml .= $this->addNode('frame_elevation_other', $door['FRAME_ELEVATION_OTHER']);
			$xml .= $this->addNode('fire_rating', $this->getItem($door['FIRE_RATING_1']));
			$xml .= $this->addNode('fire_rating2', $this->getItem($door['FIRE_RATING_2']));
			$xml .= $this->addNode('fire_rating3', $this->getItem($door['FIRE_RATING_3']));
			$xml .= $this->addNode('fire_rating4', $this->getItem($door['FIRE_RATING_4']));
			$xml .= $this->addNode('temprise', $this->getItem($door['TEMP_RISE']));
			$xml .= $this->addNode('location', $door['LOCATION']);
			$xml .= $this->addNode('remarks', $door['REMARKS']);
			$xml .= $this->addNode('compliant', $this->getItem($door['COMPLIANT']));
			$door_codes_str = '';
			$door_codes = Model_DoorCode::retrieve()->fetchEntries(array(
				'CODE_ID', new Data_Column('DOOR_ID', $door['ID'])), null, true);
			foreach ($door_codes as $door_code) {
				if ($door_codes_str != '') $door_codes_str .= ', ';
				$door_codes_str .= $this->getItem($door_code['CODE_ID']);
			}
			$xml .= $this->addNode('codes', $door_codes_str);
			$xml .= $this->addNode('maker', $door['MANUFACTURER']);
			$xml .= $this->addNode('model', $door['MODEL']);
			$xml .= $this->addNode('framemaker', $door['FRAME_MANUFACTURER']);
			$xml .= $this->addNode('barcode', $door['BARCODE']);
			$xml .= $this->addNode('RFID', $door['RFID']);
			$xml .= $this->addNode('listingagency', $this->getItem($door['LISTING_AGENCY']));
			$xml .= $this->addNode('listingagency_other', $door['LISTING_AGENCY_OTHER']);
			$xml .= $this->addNode('gauge', $door['GAUGE']);
			$xml .= $this->addNode('handing', $this->getItem($door['HANDING']));
			$xml .= '</doors>';
		}

		// pictures
		foreach ($all_pictures as $pictures)
			foreach ($pictures as $pic) {
				$xml .= '<pictures>';
				$xml .= $this->addNode('id', $pic['ID']);
				$xml .= $this->addNode('door_id', $pic['DOOR_ID']);
				$xml .= $this->addNode('control_name', $pic['CONTROL_NAME']);
				$file = $pic['PICTURE_FILE'] == '' ? '' : $this->getFile('/content/pictures/' . $pic['PICTURE_FILE']);
				$xml .= $this->addNode('image', $file);
				if (str_replace(" ", "", $pic['ROTATION']) == "") $pic['ROTATION'] = '0';
				$xml .= $this->addNode('rotation', $pic['ROTATION']);
				$xml .= $this->addNode('inkstrokes', $pic['INK_STROKES']);
				$xml .= '</pictures>';
			}

		// audio files
		foreach ($all_audio_files as $audio_files)
			foreach ($audio_files as $audio) {
				if ($audio != null){
					$xml .= '<audio>';
					$xml .= $this->addNode('id', $audio['ID']);
					$xml .= $this->addNode('door_id', $audio['DOOR_ID']);
					$file = $audio['AUDIO_FILE'] == '' ? '' : $this->getFile('/content/audio/' . $audio['AUDIO_FILE']);
					$xml .= $this->addNode('audio', $file);
					$xml .= $this->addNode('control_name', $audio['CONTROL_NAME']);
					$xml .= $this->addNode('inkstrokes', $audio['INK_STROKES']);
					$xml .= '</audio>';
				}
			}

		// ink strokes
		foreach ($all_ink_strokes as $ink_strokes)
			foreach ($ink_strokes as $ink) {
				$xml .= '<ink>';
				$xml .= $this->addNode('id', $ink['ID']);
				$xml .= $this->addNode('door_id', $ink['DOOR_ID']);
				$xml .= $this->addNode('inkstroke', $ink['INK_STROKE']);
				$xml .= $this->addNode('formnum', $ink['FORM_NUM']);
				$xml .= $this->addNode('control_name', $ink['CONTROL_NAME']);
				$xml .= '</ink>';
			}

		// notes
		foreach ($all_notes as $notes)
			foreach ($notes as $note) {
				$xml .= '<notes>';
				$xml .= $this->addNode('id', $note['ID']);
				$xml .= $this->addNode('door_id', $note['DOOR_ID']);
				$xml .= $this->addNode('note', $note['NOTE']);
				$xml .= $this->addNode('control_name', $note['CONTROL_NAME']);
				$xml .= '</notes>';
			}

		// inspection_company
		$xml .= '<inspection_company>';
		$xml .= $this->addNode('id', $company['ID']);
		$xml .= $this->addNode('company_name', $company['NAME']);
		$xml .= $this->addNode('company_address1', $company['ADDRESS_1']);
		$xml .= $this->addNode('company_address2', $company['ADDRESS_2']);
		$xml .= $this->addNode('company_city', $company['CITY']);
		$xml .= $this->addNode('company_state', $this->getItem($company['STATE']));
		$xml .= $this->addNode('company_zipcode', $company['ZIP']);
		$xml .= '</inspection_company>';

		// inspections
		$xml .= '<inspections>';
		$xml .= $this->addNode('id', $inspection['ID']);
		$xml .= $this->addNode('building_id', $inspection['BUILDING_ID']);
		$xml .= $this->addNode('company_id', $inspection['COMPANY_ID']);
		$xml .= $this->addDateNode('inspect_date', $inspection['INSPECTION_DATE']);
		$xml .= $this->addDateNode('inspect_completedate', $inspection['INSPECTION_COMPLETE_DATE']);
		$xml .= $this->addDateNode('reinspect_date', $inspection['REINSPECT_DATE']);
		$xml .= $this->addNode('summary', $inspection['SUMMARY']);
		$file = $inspection['SIGNATURE_INSPECTOR'] == '' ? '' : $this->getFile('/content/pictures/' . $inspection['SIGNATURE_INSPECTOR']);
		$xml .= $this->addNode('signature_inspector', $noSignatures ? null : $file);
		$xml .= $this->addNode('signature_strokes_inspector', $noSignatures ? null : $inspection['SIGNATURE_STROKES_INSPECTOR']);
		$file = $inspection['SIGNATURE_BUILDING'] == '' ? '' : $this->getFile('/content/pictures/' . $inspection['SIGNATURE_BUILDING']);
		$xml .= $this->addNode('signature_building', $this->getFile('/content/pictures/' . $inspection['SIGNATURE_BUILDING']));
		$xml .= $this->addNode('signature_strokes_building', $inspection['SIGNATURE_STROKES_BUILDING']);
		$xml .= $this->addNode('template_id', $inspection['TEMPLATE']);
		$xml .= '</inspections>';

		// inspectors
		$xml .= '<inspectors>';
		$xml .= $this->addNode('id', $inspector['ID']);
		$xml .= $this->addNode('firstname', $inspector['FIRST_NAME']);
		$xml .= $this->addNode('lastname', $inspector['LAST_NAME']);
		$xml .= $this->addNode('license_number', $inspector['LICENSE_NUMBER']);
		$xml .= $this->addDateNode('expiration_date', $inspector['EXPIRATION_DATE']);
		$xml .= '</inspectors>';

		/*// inspection_to_inspector
		$xml .= '<inspection_to_inspector>';
		$xml .= $this->addNode('id', $inspect['ID']);
		$xml .= $this->addNode('inspection_id', $inspect['INSPECTION_ID']);
		$xml .= $this->addNode('inspector_id', $inspect['INSPECTOR_ID']);
		$xml .= '</inspection_to_inspector>';*/

		// codes
		foreach ($all_codes as $codes)
			foreach ($codes as $code) {
				$xml .= '<codes>';
				$xml .= $this->addNode('id', $code['ID']);
				$xml .= $this->addNode('code', $code['ITEM']);
				if (array_key_exists($code['ID'], $otherCodes)) {
					$xml .= $this->addNode('code_label', $otherCodes[$code['ID']]);
				}
				else {
					$xml .= $this->addNode('code_label', $code['DESCRIPTION']);
				}
				
				$xml .= '</codes>';
			}

		// door_to_code
		foreach ($all_door_codes as $door_codes)
			foreach ($door_codes as $door_code) {
				$xml .= '<door_to_code>';
				$xml .= $this->addNode('id', $door_code['ID']);
				$xml .= $this->addNode('door_id', $door_code['DOOR_ID']);
				$xml .= $this->addNode('code_id', $door_code['CODE_ID']);
				$xml .= $this->addNode('active', '0');
				$xml .= $this->addNode('control_name', $door_code['CONTROL_NAME']);
				$xml .= '</door_to_code>';
			}

		// doordetail
		foreach ($doors as $door) {
			$xml .= '<doordetail>';
			$xml .= $this->addNode('id', $door['ID']);
			$xml .= $this->addNode('door_id', $door['ID']);
			$xml .= $this->addNode('ink_strokes', $door['INK_STROKES']);
			$xml .= $this->addNode('hinge_height', $this->getItem($door['HINGE_HEIGHT']));
			$xml .= $this->addNode('hinge_thickness', $this->getItem($door['HINGE_THICKNESS']));
			$xml .= $this->addNode('hinge_height1', $door['HINGE_HEIGHT1']);
			$xml .= $this->addNode('hinge_height2', $door['HINGE_HEIGHT2']);
			$xml .= $this->addNode('hinge_height3', $door['HINGE_HEIGHT3']);
			$xml .= $this->addNode('hinge_height4', $door['HINGE_HEIGHT4']);
			$xml .= $this->addNode('hinge_fraction1', $this->getItem($door['HINGE_FRACTION1']));
			$xml .= $this->addNode('hinge_fraction2', $this->getItem($door['HINGE_FRACTION2']));
			$xml .= $this->addNode('hinge_fraction3', $this->getItem($door['HINGE_FRACTION3']));
			$xml .= $this->addNode('hinge_fraction4', $this->getItem($door['HINGE_FRACTION4']));
			$xml .= $this->addNode('hinge_backset', $this->getItem($door['HINGE_BACKSET']));
			$xml .= $this->addNode('hinge_manufacturer', $door['HINGE_MANUFACTURER']);
			$xml .= $this->addNode('hinge_manufacturer_no', $door['HINGE_MANUFACTURER_NO']);
			$xml .= $this->addNode('top_to_centerline', $door['TOP_TO_CENTERLINE']);
			$xml .= $this->addNode('top_to_centerline_fraction', $this->getItem($door['TOP_TO_CENTERLINE_FRACTION']));
			$xml .= $this->addNode('lock_bracket', $this->getItem($door['LOCK_BACKSET']));
			$xml .= $this->addNode('frame_bottom_to_center', $door['FRAME_BOTTOM_TO_CENTER']);
			$xml .= $this->addNode('strike_height', $this->getItem($door['STRIKE_HEIGHT']));
			$xml .= $this->addNode('prefit_door_size_x', $door['PREFIT_DOOR_SIZE_X']);
			$xml .= $this->addNode('prefit_fraction_x', $this->getItem($door['PREFIT_FRACTION_X']));
			$xml .= $this->addNode('prefit_door_size_y', $door['PREFIT_DOOR_SIZE_Y']);
			$xml .= $this->addNode('prefit_fraction_y', $this->getItem($door['PREFIT_FRACTION_Y']));
			$xml .= $this->addNode('frame_opening_size_x', $door['FRAME_OPENING_SIZE_X']);
			$xml .= $this->addNode('frame_opening_fraction_x', $this->getItem($door['FRAME_OPENING_FRACTION_X']));
			$xml .= $this->addNode('frame_opening_size_y', $door['FRAME_OPENING_SIZE_Y']);
			$xml .= $this->addNode('frame_opening_fraction_y', $this->getItem($door['FRAME_OPENING_FRACTION_Y']));
			$xml .= $this->addNode('lite_cutout_size_x', $door['LITE_CUTOUT_SIZE_X']);
			$xml .= $this->addNode('lite_cutout_fraction_x', $this->getItem($door['LITE_CUTOUT_FRACTION_X']));
			$xml .= $this->addNode('lite_cutout_size_y', $door['LITE_CUTOUT_SIZE_Y']);
			$xml .= $this->addNode('lite_cutout_fraction_y', $this->getItem($door['LITE_CUTOUT_FRACTION_Y']));
			$xml .= $this->addNode('lockstile_size', $door['LOCKSTILE_SIZE']);
			$xml .= $this->addNode('lockstile_fraction', $this->getItem($door['LOCKSTILE_FRACTION']));
			$xml .= $this->addNode('toprail_size', $door['TOPRAIL_SIZE']);
			$xml .= $this->addNode('toprail_fraction', $this->getItem($door['TOPRAIL_FRACTION']));
			$xml .= '</doordetail>';
		}

		// framedetail
		foreach ($doors as $door) {
			$xml .= '<framedetail>';
			$xml .= $this->addNode('id', $door['ID']);
			$xml .= $this->addNode('door_id', $door['ID']);
			$xml .= $this->addNode('ink_strokes', $door['FRAME_INK_STROKES']);
			$xml .= $this->addNode('a', $door['A']);
			$xml .= $this->addNode('afraction', $this->getItem($door['A_FRACTION']));
			$xml .= $this->addNode('b', $door['B']);
			$xml .= $this->addNode('bfraction', $this->getItem($door['B_FRACTION']));
			$xml .= $this->addNode('c', $door['C']);
			$xml .= $this->addNode('cfraction', $this->getItem($door['C_FRACTION']));
			$xml .= $this->addNode('d', $door['D']);
			$xml .= $this->addNode('dfraction', $this->getItem($door['D_FRACTION']));
			$xml .= $this->addNode('e', $door['E']);
			$xml .= $this->addNode('efraction', $this->getItem($door['E_FRACTION']));
			$xml .= $this->addNode('f', $door['F']);
			$xml .= $this->addNode('ffraction', $this->getItem($door['F_FRACTION']));
			$xml .= $this->addNode('g', $door['G']);
			$xml .= $this->addNode('gfraction', $this->getItem($door['G_FRACTION']));
			$xml .= $this->addNode('h', $door['H']);
			$xml .= $this->addNode('hfraction', $this->getItem($door['H_FRACTION']));
			$xml .= $this->addNode('i', $door['I']);
			$xml .= $this->addNode('ifraction', $this->getItem($door['I_FRACTION']));
			$xml .= $this->addNode('j', $door['J']);
			$xml .= $this->addNode('jfraction', $this->getItem($door['J_FRACTION']));
			$xml .= $this->addNode('k', $door['K']);
			$xml .= $this->addNode('kfraction', $this->getItem($door['K_FRACTION']));
			$xml .= $this->addNode('l', $door['L']);
			$xml .= $this->addNode('lfraction', $this->getItem($door['L_FRACTION']));
			$xml .= $this->addNode('m', $door['M']);
			$xml .= $this->addNode('mfraction', $this->getItem($door['M_FRACTION']));
			$xml .= $this->addNode('n', $door['N']);
			$xml .= $this->addNode('nfraction', $this->getItem($door['N_FRACTION']));
			$xml .= $this->addNode('o', $door['O']);
			$xml .= $this->addNode('ofraction', $this->getItem($door['O_FRACTION']));
			$xml .= $this->addNode('p', $door['P']);
			$xml .= $this->addNode('pfraction', $this->getItem($door['P_FRACTION']));
			$xml .= $this->addNode('q', $door['Q']);
			$xml .= $this->addNode('qfraction', $this->getItem($door['Q_FRACTION']));
			$xml .= $this->addNode('r', $door['R']);
			$xml .= $this->addNode('rfraction', $this->getItem($door['R_FRACTION']));
			$xml .= $this->addNode('s', $door['S']);
			$xml .= $this->addNode('sfraction', $this->getItem($door['S_FRACTION']));
			$xml .= $this->addNode('t', $door['T']);
			$xml .= $this->addNode('tfraction', $this->getItem($door['T_FRACTION']));
			$xml .= $this->addNode('u', $door['U']);
			$xml .= $this->addNode('ufraction', $this->getItem($door['U_FRACTION']));
			$xml .= $this->addNode('v', $door['V']);
			$xml .= $this->addNode('vfraction', $this->getItem($door['V_FRACTION']));
			$xml .= '</framedetail>';
		}

		// hardwareset
		foreach ($all_hardwaresets as $hardwaresets) { 
			$hwcount = 1;
			foreach ($hardwaresets as $hardwareset) {
				//Zend_Registry::get('logger')->info('Hardware: ' . var_export($hardwareset, true));
				$xml .= '<hardwareset>';
				$xml .= $this->addNode('id', $hardwareset['ID']);
				$xml .= $this->addNode('item_id', $hwcount);
				$xml .= $this->addNode('door_id', $hardwareset['DOOR_ID']);
				$xml .= $this->addNode('hardware_group', $hardwareset['HARDWARE_GROUP']);
				$xml .= $this->addNode('hardware_set', $hardwareset['HARDWARE_SET']);
				//$xml .= $this->addNode('item_id', $hardwareset['ITEM_ID']);
				$xml .= $this->addNode('verify', $hardwareset['VERIFY'] ? $hwcount : '');
				$xml .= $this->addNode('qty', $hardwareset['QTY']);
				$xml .= $this->addNode('item', $hardwareset['ITEM']);
				$xml .= $this->addNode('product', $hardwareset['PRODUCT']);
				$xml .= $this->addNode('mfg', $hardwareset['MFG']);
				$xml .= $this->addNode('finish', $hardwareset['FINISH']);
				//$xml .= "<hardware_group/><hardware_set/>";
				$xml .= '</hardwareset>';
				$hwcount ++;
			}
		}
		

		// floorplan
		if ($all_floorplans) foreach ($all_floorplans as $floorplans) {
			foreach ($floorplans as $floorplan) {
				$xml .= '<floorplan>';
				$xml .= $this->addNode('id', $floorplan['ID']);
				$xml .= $this->addNode('door_id', $floorplan['DOOR_ID']);
				$xml .= $this->addNode('ink_strokes', $floorplan['INK_STROKES']);
				$xml .= '</floorplan>';
			}
		}

		return $xml;
	}

	/**
	 * Creates a new Mi-Co session out from an inspection in the database
	 * @param int $id ID of an inspection record in the database
	 */
	public function create($id) {

		// change inspection status to 'Submitting'
		Model_Inspection::retrieve()->save(array(
    		'ID'		=> $id . '',
    		'STATUS'  	=> Model_Inspection::SUBMITTING . ''
		));

		// create header
		$xml = '<?xml version="1.0" encoding="utf-8"?>';
		$xml .= '<DoorData>';

		// add XML data
		$xml_data = $this->generateXML($id, true);
		
		if (substr($xml_data, 0, strlen($this->_errorMsg)) == $this->_errorMsg) {
			// change inspection status back to 'Pending'
			Model_Inspection::retrieve()->save(array(
    			'ID'		=> $id . '',
    			'STATUS'  	=> Model_Inspection::PENDING . ''
			));
			return $xml_data;
		}
		$xml .= $xml_data;

		// add tail
		$xml .= '</DoorData>';

		// log outgoing xml
		$xmlLogId = Helper::saveXmlLog($xml, $id, 'Mico Service', 'waiting');

        // send the request to the server
        try {
        	$response = $this->_httpSession->setRawData($xml, 'application/xml')->request('POST');
        } catch (Exception $e) {
        	// change inspection status back to 'Pending'
			Model_Inspection::retrieve()->save(array(
    			'ID'		=> $id . '',
    			'STATUS'  	=> Model_Inspection::PENDING . ''
			));
			Zend_Registry::get('logger')->info('Error during assigning the inspection: ' . $e->getMessage());
			return 'Sorry, a server error occured';
        }
		if ($response->isError()) {
			Helper::saveXmlLog(null, null, $response->asString(), 'failed', $xmlLogId);
			// change inspection status back to 'Pending'
			Model_Inspection::retrieve()->save(array(
    			'ID'		=> $id . '',
    			'STATUS'  	=> Model_Inspection::PENDING . ''
			));
			return $response->asString('<br>');
		} else {
			Helper::saveXmlLog(null, null, $response->asString(), 'succeeded', $xmlLogId);
			// change inspection status to 'Submitted'
			Model_Inspection::retrieve()->save(array(
    			'ID'		=> $id . '',
    			'STATUS'  	=> Model_Inspection::SUBMITTED . '' 
			));
			return 'ok';
		}
	}
	
	public function reformat_date($date, $format){
    	return date($format, strtotime($date));
	}
}