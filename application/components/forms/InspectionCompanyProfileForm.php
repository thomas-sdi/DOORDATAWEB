<?
require_once APPLICATION_PATH . '/components/Form.php';
require_once APPLICATION_PATH . '/models/Company.php';
require_once APPLICATION_PATH . '/components/Image.php';

class InspectionCompanyProfileForm extends Ginger_Form {

	public function __construct($id) {
       
		parent::__construct($id);

		$this->setElements(array(
			'id'   			  => array('InputType' => 'hidden'),
			'logo' 			  => array('View' => Data_Column_Grid::FILE),
			'address_1'	      => array(),
			'address_2' 	  => array(),
			'city' 	 		  => array(),
			'country'		  => array(),
			'state' 		  => array(),
			'zip' 			  => array(),
			'primary_contact' => array(),
			'logo_file'		  => array(),
			'theme'		  	  => array()
		));
		
		$states = Model_Dictionary::retrieve()->fetchEntries(array('ID', 'ITEM', 'PARENT_ID', new Data_Column('CATEGORY', 'State')), null, true, 'VALUE_ORDER');
		$allStates = array();
		foreach($states as $state){
			array_push($allStates, array('ID' => $state['ID'], 'ITEM' => $state['ITEM'], 'PARENT_ID' => $state['PARENT_ID']));	
		}
		
		$session = new Zend_Session_Namespace('default');
    	$companyData = Model_Company::retrieve()->fetchEntry($session->companyId);

		$this->setParams(array(
			'Action' => '/inspectioncompany/submit',
			'IsCustomView' => true,
			'DojoType' => 'custom.forms.InspectionCompanyProfileForm',
			'IFrame' => true,
			'DojoParams' => array(
				'all_states' => Zend_Json::encode($allStates), 
				'state_id' => $companyData["STATE"] ? $companyData["STATE"] : '0'
			) 
		));
	}

	public function execute($data) {
		// save the basic company details
		$record = array(
			'ID' 		=> $data['id'],
			'ADDRESS_1' => $data['address_1'],
			'ADDRESS_2' => $data['address_2'],
			'CITY' 		=> $data['city'],
			'COUNTRY'	=> $data['country'],
			'STATE' 	=> nvl($data['state'], null),
			'ZIP' 		=> $data['zip'],
			'PRIMARY_CONTACT' => nvl($data['primary_contact'], null),
			'COLOR_THEME'     => $data['theme']
		);
		
		if (array_value('removeLogo', $data)) {
			$record['LOGO_FILE'] = '';
		}
		
		$companyId = Model_Company::retrieve()->save($record);
		
		// upload logo
		$urlPath = $this->_saveLogo($companyId, $_FILES['logo']);
			
		return array('logo_file' => $urlPath, 'theme' => $data['theme']);
	}

	protected function _saveLogo($companyId, $logo) {
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		
		// if no file was uploaded, do nothing
		if (!$logo || $logo['error'] == 4) return "$baseUrl/public/images/logo.png";
		
		if ($logo["error"] != 0 ) {
    		throw new Exception('Logo upload failed: ' . $logo["error"]);
		}
		
		// get the picture extension
		$ext = array_pop(explode('.', $logo['name']));
		
		// generate the file name
		$shortName = 'inspectionLogo' . $companyId . '_' . time() . '.' . $ext;
		
		// resize the image to make sure it's no taller than 100 px
		$image = new Image($logo['tmp_name']);
			if ($image->getHeight() > 100) {
			$image->resizeToHeight(100);
		}
		
		// copy the logo to the content folder
		$image->save(ROOT_PATH . '/public/logos/' . $shortName);
		
		// update the reference to the picture using HTTP-style URL
		$urlPath = "$baseUrl/public/logos/$shortName";
		Model_Company::retrieve()->save(array(
			'ID' => $companyId, 'LOGO_FILE' => $urlPath));
			
		return $urlPath;
	}
}
