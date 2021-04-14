<?
require_once APPLICATION_PATH . '/models/Door.php';
require_once APPLICATION_PATH . '/models/Picture.php';
require_once APPLICATION_PATH . '/models/Audio.php';
require_once APPLICATION_PATH . '/models/Inspection.php';
require_once APPLICATION_PATH . '/controllers/Component.php';

class DoorController extends Controller_Component {
        
    public function init() {
    	$this->addGrid('door',  Model_Door::retrieve());  

   		$this->addGrid('door_picture',
		      Model_Picture::retrieve(), 'door', 'door_id');
		$this->addGrid('door_audio',
		      Model_Audio::retrieve(), 'door', 'door_id');
    	
        parent::init();
    }
	
	public function savedoorAction(){
		$this->_helper->layout->setLayout('json');
        $this->_helper->ViewRenderer->setNoRender(true);
		
		$json = $this->getParam('json');
		
		$data = Zend_Json::decode($json);
		
		//DOOR_TYPE has to be saved separately
		
		$fieldIds = array(
			'ID', 'NUMBER', 'DOOR_BARCODE', 'HANDING', 'STYLE', 'TYPE_OTHER', 'MATERIAL', 'MATERIAL_OTHER', 'ELEVATION', 'ELEVATION_OTHER',
			'FRAME_MATERIAL', 'FRAME_MATERIAL_OTHER', 'FRAME_ELEVATION', 'FRAME_ELEVATION_OTHER', 'LOCATION', 'FIRE_RATING_1', 'FIRE_RATING_2', 'FIRE_RATING_3',
			'FIRE_RATING_4', 'TEMP_RISE', 'LISTING_AGENCY', 'LISTING_AGENCY_OTHER', 'BARCODE', 'GAUGE', 'MANUFACTURER', 'MODEL', 'REMARKS',
			'HINGE_HEIGHT', 'HINGE_THICKNESS', 'HINGE_HEIGHT1', 'HINGE_HEIGHT2', 'HINGE_HEIGHT3', 'HINGE_HEIGHT4',
			'HINGE_FRACTION1', 'HINGE_FRACTION2', 'HINGE_FRACTION3', 'HINGE_FRACTION4', 'HINGE_BACKSET',
			'HINGE_MANUFACTURER', 'HINGE_MANUFACTURER_NO', 'TOP_TO_CENTERLINE', 'TOP_TO_CENTERLINE_FRACTION', 'LOCK_BACKSET', 
			'FRAME_BOTTOM_TO_CENTER', 'STRIKE_HEIGHT',
			'PREFIT_DOOR_SIZE_X', 'PREFIT_FRACTION_X', 'PREFIT_DOOR_SIZE_Y', 'PREFIT_FRACTION_Y', 'FRAME_OPENING_SIZE_X', 'FRAME_OPENING_FRACTION_X',
			'FRAME_OPENING_SIZE_Y', 'FRAME_OPENING_FRACTION_Y', 'LITE_CUTOUT_SIZE_X', 'LITE_CUTOUT_FRACTION_X', 'LITE_CUTOUT_SIZE_Y', 'LITE_CUTOUT_FRACTION_Y',
			'LOCKSTILE_SIZE', 'LOCKSTILE_FRACTION', 'TOPRAIL_SIZE', 'TOPRAIL_FRACTION',
			'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V',
			'A_FRACTION', 'B_FRACTION', 'C_FRACTION', 'D_FRACTION', 'E_FRACTION', 'F_FRACTION', 'G_FRACTION', 'H_FRACTION', 'I_FRACTION', 'J_FRACTION', 'K_FRACTION', 
			'L_FRACTION', 'M_FRACTION', 'N_FRACTION', 'O_FRACTION', 'P_FRACTION', 'Q_FRACTION', 'R_FRACTION', 'S_FRACTION', 'T_FRACTION', 'U_FRACTION', 'V_FRACTION',
			'HARDWARE_GROUP', 'HARDWARE_SET', 'COMPLIANT'
		);
		
		$columns = array();
		foreach($fieldIds as $fieldId){
			switch($fieldId){
				case 'COMPLIANT':
				case 'FIRE_RATING_1': 
				case 'FIRE_RATING_2': 
				case 'FIRE_RATING_3':
				case 'FIRE_RATING_4':
				case 'TEMP_RISE':
				case 'LISTING_AGENCY':
				case 'HINGE_HEIGHT':
				case 'HINGE_THICKNESS':
				case 'HANDING':
				case 'HINGE_FRACTION1':
				case 'HINGE_FRACTION2':
				case 'HINGE_FRACTION3':
				case 'HINGE_FRACTION4':
				case 'HINGE_BACKSET':
				case 'TOP_TO_CENTERLINE_FRACTION':
				case 'STRIKE_HEIGHT':
				case 'PREFIT_FRACTION_X':
				case 'PREFIT_FRACTION_Y':
				case 'FRAME_OPENING_FRACTION_X':
				case 'FRAME_OPENING_FRACTION_Y':
				case 'LITE_CUTOUT_FRACTION_X':
				case 'LITE_CUTOUT_FRACTION_Y':
				case 'LOCKSTILE_FRACTION':
				case 'TOPRAIL_FRACTION':
				case 'LOCK_BACKSET':
				case 'A_FRACTION': case 'B_FRACTION': case 'C_FRACTION': case 'D_FRACTION': case 'E_FRACTION': case 'F_FRACTION': 
				case 'G_FRACTION': case 'H_FRACTION':
				case 'I_FRACTION': case 'J_FRACTION': case 'K_FRACTION': case'L_FRACTION': case 'M_FRACTION': case 'N_FRACTION': case 'O_FRACTION': 
				case 'P_FRACTION': case 'Q_FRACTION': case 'R_FRACTION': case 'S_FRACTION': case 'T_FRACTION': case 'U_FRACTION': case 'V_FRACTION':
					if($data[$fieldId] != '') 
						$columns[$fieldId] = $data[$fieldId];
					else 
						$columns[$fieldId] = null;
					break;
				default: $columns[$fieldId] = $data[$fieldId];
			}
			
		}

		if(!$columns['ID']) return; //can only work with the existing door
		
		//TODO: add checks for user rights and inspection status
		
		//First, save the door itself
		$doorId = Model_Door::retrieve()->save($columns);
		if (!$doorId) return;
		
		$door = Model_Door::retrieve()->fetchEntry($doorId, array('INSPECTION_ID'));
		
		
		//Now, let's update the door codes
		Model_Door::retrieve()->cleanCodes($doorId);
		$codes = $data['DOOR_CODES'];
		Model_Door::retrieve()->setCodes($doorId, $codes);
		
		//Now, let's update the door type
		Model_Door::retrieve()->cleanDoorTypes($doorId);
		$types = $data['DOOR_TYPE'];
		Model_Door::retrieve()->setDoorTypes($doorId, $types);
		
		//now, let's update the inspection other
		Model_Inspection::retrieve()->cleanInspectionOther($door['INSPECTION_ID']);
		$others = $data['INSPECTION_OTHER'];
		Model_Inspection::retrieve()->setInspectionOther($door['INSPECTION_ID'], $others);
		
		$this->view->placeholder('data')->set(Zend_Json::encode(array('status' => 'success')));

	}
}