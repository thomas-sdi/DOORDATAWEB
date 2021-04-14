<?
require_once APPLICATION_PATH . '/models/DBTable/Company.php';
require_once APPLICATION_PATH . '/models/Abstract.php';
require_once APPLICATION_PATH . '/models/Dictionary.php';
require_once APPLICATION_PATH . '/models/Employee.php';

class Model_Company extends Model_Abstract{

	const INSPECTION_COMPANY = 1002;
	const BUILDING_OWNER     = 1001;
	
	const NO_BRANDING = 0;
	const BRANDING_COLOR = 1;
	const BRANDING_LOGO = 2;
	const BRANDING_ALL = 3;
	
 protected function _init(){
  $this->_table = new DBTable_Company();

        //add reference models for look-up columns
  $this->addReferenceModel('COUNTRY', Model_Dictionary::retrieve());
  $this->addReferenceModel('STATE', Model_Dictionary::retrieve());
  $this->addReferenceModel('TYPE', Model_Dictionary::retrieve()); 
  $this->addReferenceModel('INSPECTION_COMPANY', $this);
  $this->addReferenceModel('PRIMARY_CONTACT', Model_Employee::retrieve()); 
		// validation
  $this->addValidationRule(new Validation_Rule_Required('NAME'));

  parent::_init();
}

public static function retrieve($class=null) {
  return parent::retrieve(__CLASS__);
}


public function save($data, $ignored=null) {
        // retrieve branding information
  $logo = array_value('logo', $data); if ($logo) unset($data['logo']);
  $color = array_value('color', $data); if ($color) unset($data['color']);
  if (array_value('bogus', $data)) unset($data['bogus']); 
        // this is there to ensure empty checkbox are being sent



		    
        $data['PRIMARY_CONTACT_id']="ignore";
        $data['country_id']="ignore";
        $data['STATE_id']="ignore";

        if (isset($data['PRIMARY_CONTACT_id'])) unset($data['PRIMARY_CONTACT_id']); // this is there to

        if (isset($data['country_id'])) unset($data['country_id']); // this is there to 
        
        if (isset($data['STATE_id'])) unset($data['STATE_id']); // this is there to 

		    // check if this user is an admin
        $isAdmin = Zend_Registry::get('_acl')->inheritsRole(Zend_Auth::getInstance()->getIdentity(), 'Administrators');
        
        if ($isAdmin) {
          $branding = self::NO_BRANDING;
          if ($logo && $color) $branding = self::BRANDING_ALL;
          elseif ($logo) $branding = self::BRANDING_LOGO;
          elseif ($color) $branding = self::BRANDING_COLOR;
          $data['BRANDING'] = $branding;
        }

        if($data['TYPE'] == self::BUILDING_OWNER && !array_key_exists('INSPECTION_COMPANY', $data)){
         $data['INSPECTION_COMPANY'] = App::inspectionCompanyId();
       }




       Zend_Registry::get('logger')->info('company: ' . json_encode($data));

       $companyId = parent::save($data, $ignored);

    	// for new inspection companies create Unknown Building Owner
       if (!array_key_exists('ID', $data)) {
        if ($data['TYPE'] == Model_Company::INSPECTION_COMPANY) {
         Model_Company::retrieve()->getUBO($companyId);
       }
     }

     return $companyId;
   }

    /**
     * Returns id of existing or new Unknown Building Owner for current inspection company
     *
     * @param string $companyId	Id of inspection company
     * @return 	returns the id of Unknown Building Owner, or creates new
     */
    public function getUBO($companyId) {
    	$ubo = Model_Company::retrieve()->fetchEntry(false, array('ID', 
        new Data_Column('NAME', 'Unknown Building Owner'), 
        new Data_Column('TYPE', Model_Company::BUILDING_OWNER),
        new Data_Column('INSPECTION_COMPANY', $companyId)));
    	if ($ubo)
    		return $ubo['ID'];
    	else
    		return Model_Company::retrieve()->save(array('NAME' =>'Unknown Building Owner',
         'TYPE' => Model_Company::BUILDING_OWNER,
         'INSPECTION_COMPANY' => $companyId));
    }
    
    public function createAllUBO() {
    	$companies = Model_Company::retrieve()->fetchEntries(array('ID', new Data_Column('TYPE', Model_Company::INSPECTION_COMPANY)));
    	//foreach inspection company create Unknown Building Owner
    	foreach ($companies as $company) {
    		Model_Company::retrieve()->getUBO($company['ID']); 
    	}
    	
    	//foreach building where CUSTOMER_ID == null, find in which inspections this building was inspected, 
    	//find UBO for this inspection company and set it to building CUSTOMER_ID
    	$buildings = Model_Building::retrieve()->fetchEntries(array('ID', new Data_Column('CUSTOMER_ID', '')));
    	foreach ($buildings as $building) {
    		$inspection = Model_Inspection::retrieve()->fetchEntry(false, array('COMPANY_ID', new Data_Column('BUILDING_ID', $building['ID'])));
    		if ($inspection) {
    			$building_record = array('ID' => $building['ID'], 
            'CUSTOMER_ID' => $this->getUBO($inspection['COMPANY_ID']));
    			Model_Building::retrieve()->save($building_record);
    		}
    	}
    }

    static public function brandingAllowsThemeChange($branding) {
      return $branding == self::BRANDING_ALL || $branding == self::BRANDING_COLOR;
    }

    static public function brandingAllowsLogoChange($branding) {
      return $branding == self::BRANDING_ALL || $branding == self::BRANDING_LOGO;
    }
  }