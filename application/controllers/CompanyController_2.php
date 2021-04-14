<?
require_once APPLICATION_PATH . '/models/Company.php';
require_once APPLICATION_PATH . '/models/Building.php';
require_once APPLICATION_PATH . '/models/Employee.php';
require_once APPLICATION_PATH . '/models/User.php';
require_once APPLICATION_PATH . '/models/Role.php';
require_once APPLICATION_PATH . '/models/UserRole.php';
require_once APPLICATION_PATH . '/models/Inspection.php';
require_once APPLICATION_PATH . '/models/Hardware.php';
require_once APPLICATION_PATH . '/models/DoorCode.php';
require_once APPLICATION_PATH . '/models/DoorType.php';
require_once APPLICATION_PATH . '/models/Picture.php';
require_once APPLICATION_PATH . '/models/Audio.php';
require_once APPLICATION_PATH . '/models/Building.php';
require_once APPLICATION_PATH . '/controllers/Component.php';
require_once APPLICATION_PATH . '/components/grids/CompanyGrid.php';
require_once APPLICATION_PATH . '/components/grids/BuildingGrid.php';
require_once APPLICATION_PATH . '/components/grids/EmployeeGrid.php';
require_once APPLICATION_PATH . '/components/grids/InspectionGrid.php';
require_once APPLICATION_PATH . '/components/grids/DoorGrid.php';
require_once APPLICATION_PATH . '/components/grids/InspectsGrid.php';
require_once APPLICATION_PATH . '/components/grids/PhotobucketGrid.php';

class CompanyController extends Controller_Component {
        
    public function init() {
    	$this->_helper->layout->setLayout('html');
    	$parent = $this->getParam('_parent');
		
		$inspectionId = $this->getParam('_super');
    	
    	//array will contain permissions for current user to grids in current tab
    	$access = array();
    	
    	$companyGrid = $this->addGrid(new CompanyGrid('company'));    	
    	//get permissions for current user for current grid
    	$access['company'] = $this->getAccessControl()->getGridPermissions('company');
 	
       	/* company buildings grid */
		$companyBuildingGrid = $this->addGrid(new BuildingGrid('company_buildings', $parent, 'CUSTOMER_ID'));
    	//get permissions for current user for current grid
    	$access['company_buildings'] = $this->getAccessControl()->getGridPermissions('company_buildings');
    	
    	/* company employees grid*/
    	// $companyEmployeeGrid = $this->addGrid(new EmployeeGrid('company_employees', $parent, null, 'owner', 'COMPANY_ID'));

        $companyEmployeeGrid = $this->addGrid(new EmployeeGrid('company_employees', $parent,'COMPANY_ID', 'owner',App::companyId()));


    	$access['company_employees'] = $this->getAccessControl()->getGridPermissions('company_employees');
    	   	    	
    	/* inspection grid */
    	$companyInspectionsGrid = $this->addGrid(new InspectionGrid('company_inspections',
    		$parent, array('BUILDING_ID' => Model_Building::retrieve(), 'CUSTOMER_ID' => Model_Company::retrieve())));
    	
		//get permissions for current user for current grid
    	$access['company_inspections'] = $this->getAccessControl()->getGridPermissions('company_inspections');
 
    	/* doors grid */
    	//$companyDoorsGrid = $this->addGrid(new DoorGrid('company_doors', $companyInspectionsGrid, 'INSPECTION_ID'));
		$companyDoorsGrid = $this->addGrid(new DoorGrid('company_doors', null, null, $inspectionId));
    	
    	/* history grid*/
    	$companyHistoryGrid = $this->addGrid(new InspectsGrid('company_history', $companyInspectionsGrid, 'INSPECTION_ID'));
    	
    	$hardwareGrid = $this->addGrid($companyDoorsGrid->getHardwareGrid('hardware')); //###

		/* photobucket grid */
		$photobucketGrid  = $this->addGrid(new PhotobucketGrid('company_photobucket', $companyInspectionsGrid, 'INSPECTION_ID'));
		
    	$this->view->access = $access;
    	parent::init();
    }  
    
    public function employeeAction() { 
		$this->_detailedView('company_employees');
		
		//company id under which the employee will be created
		$this->view->companyId = '';
		
		if ($this->getParam('_parent')){
			// get some details about user associated to an employee
			$this->view->user = Model_Employee::retrieve()->fetchEntry($this->getParam('_parent'), array(
				'LOGIN'   => new Data_Column('USER_ID', null, Model_Employee::retrieve(), 'LOGIN'),
				'COMPANY_ID',
				'ROLE_ID' => new Data_Column(
					array('USER_ID' => Model_User::retrieve(),
					      '$USER_ID' => Model_User_Role::retrieve()),
					null, Model_Employee::retrieve(), 'ROLE_ID'
			)));
			$this->view->companyId = $this->view->user['COMPANY_ID'];
		}
		if ($this->getParam('_super')) $this->view->companyId = $this->getParam('_super');
		
		// if this user is inspection company employee, prohibit changing its role
		$acl = Zend_Registry::get('_acl'); $user = $this->getUser();
		if (!$acl->inheritsRole($user, 'Building Owners') &&
			 $acl->inheritsRole($user, 'Building Owner Employees'))
		{ // employees cannot change other's roles
			$this->view->roleEditDisabled = true;
		} else $this->view->roleEditDisabled = false;
		
		// set default role to current user's role
		$role = Model_User_Role::retrieve()->fetchEntry(null, array(
			new Data_Column('USER_ID', $this->getUser(), Model_User_Role::retrieve(), 'LOGIN'), 'ROLE_ID'));
		$role = $role['ROLE_ID'];
		$this->view->userRole = $this->view->user ? $this->view->user['ROLE_ID'] : $role; 	
		
		// get list of possible roles the user can participate in
		$this->view->roles = Model_Role::retrieve()->fetchEntries(array(
			'ID', 'NAME', new Data_Column('NAME', new Data_Filter_In(array(
					'Building Owners', 'Building Owner Employees')))));
						
    }
    
    
    public function companyAction() {
        $this->_detailedView('company');
    }
    
    public function photobucketAction() {
    	$this->_detailedView('company_photobucket'); 
    	$photobucketId = $this->getParam('_parent');
    	
    	if ($photobucketId > 0) {
    		$this->view->photobucket = Model_Photobucket::retrieve()->fetchEntry($photobucketId);
    	} else {
    		$this->view->photobucket = array();
    	}
    }
    
    public function detailviewAction() {
    	$this->_detailedView('company'); //this action is a detailed view for the 'company' grid
        $companyId = $this->getParam("_parent");
        $company = Model_Company::retrieve()->fetchEntry($companyId);
        $allEmployees = Model_Employee::retrieve()->fetchEntries(
        					array('ID', 'FIRST_NAME', 'LAST_NAME',
        					 	  new Data_Column('COMPANY_ID', $companyId))
        				);
   	
       	$this->view->grid = $this->_components['company'];
       	$this->view->allEmployees = $allEmployees;
       	$this->view->primaryContact = $company['PRIMARY_CONTACT'];

    }
    
    public function buildingdetailviewAction() {
    	$column = $this->_components['company_buildings']->getColumnById('OWNER');
    	$column->setDefault($this->_getParam("_super"));
    	$column->setEditable(false);
    	
    	$this->_detailedView('company_buildings'); //this action is a detailed view for the 'company_buildings' grid
    	$this->view->grid = $this->_components['company_buildings'];
    	
        $buildingId = $this->getParam("_parent");
        $companyId = $this->getParam('_super');
		
		$this->view->companyId = $companyId;
 
        if ($buildingId > 0){
        	$building = Model_Building::retrieve()->fetchEntry($buildingId);       
        
        	$allEmployees = Model_Employee::retrieve()->fetchEntries(
        						array('ID', 'FIRST_NAME', 'LAST_NAME',
        						 	  new Data_Column('COMPANY_ID', $building['CUSTOMER_ID']))
        					);
        	$this->view->allEmployees = $allEmployees;
       		$this->view->primaryContact = $building['PRIMARY_CONTACT'];
        }
       	elseif ($companyId > 0){
       		$allEmployees = Model_Employee::retrieve()->fetchEntries(
        						array('ID', 'FIRST_NAME', 'LAST_NAME',
        						 	  new Data_Column('COMPANY_ID', $companyId))
        		);
        	$this->view->allEmployees = $allEmployees;
       		$this->view->primaryContact = null;
       	}
       	else {
        	$this->view->allEmployees = $allEmployees;
       		$this->view->primaryContact = null;       			
       	}
    }
    
    public function saveadminAction() {
    	$this->_helper->ViewRenderer->setNoRender(true);
		$this->_helper->layout->setLayout('json');
		$changeSet = array();
		$fields = array('ID', 'ADDRESS_1', 'ADDRESS_2', 'CITY', 'STATE', 'COUNTRY', 'ZIP', 'PRIMARY_CONTACT');
		$result = array();
		try {
			foreach ($fields as $field) {
				if (strlen($this->_getParam(strtolower($field))) > 0) {
					$changeSet[strtoupper($field)] = $this->_getParam(strtolower($field));
				}
			}
			if (array_key_exists('COUNTRY', $changeSet) && !array_key_exists('STATE', $changeSet)) $changeSet['STATE'] = null;
			$this->log(var_export($changeSet));
			Model_Company::retrieve()->save($changeSet);
			$result['status'] = 'Ok';
       	} catch (Exception $e) {
    		Zend_Registry::getInstance()->logger->err($e->getMessage() . '\n' . $e->getTraceAsString());
    		$result['status'] = 'failed';
    		$result['message'] = $e->getMessage();
    	} 
    	$this->view->placeholder('data')->set(Zend_Json::encode($result));
    	
	}
    
    public function ownerAction() {
    	//user clicked on the row in the grid and selected company id was sent as _parent parameter
    	$this->view->companyId = $this->_getParam('_parent');
		
    	if ($this->getAcl()->inheritsRole($this->getUser(), 'Building Owner Employees')){
    		//this is building owner and we can only show them their own company details only
    		$employee = Model_Employee::retrieve()->fetchEntry(null,array(
    	    	'ID', 'COMPANY_ID', new Data_Column('USER_ID', $this->getUser(), Model_Employee::retrieve(), 'LOGIN')));
    		$this->view->companyId = $employee["COMPANY_ID"];
    	}
		
		$company = Model_Company::retrieve()->fetchEntry($this->view->companyId);
		$this->view->companyName = $company['NAME'];
    }
    
    public function adminAction(){
    	$companyId = $this->_getParam('_parent');
    	//now we know company Id and can get company name from the database
    	if ($companyId){
    		$companyData = Model_Company::retrieve()->fetchEntry($companyId);
    	
    		//pass to the view all company current data
    		$this->view->companyId = $companyId;
    		$this->view->companyName = $companyData["NAME"];
    		$this->view->address1 = $companyData["ADDRESS_1"];
    		$this->view->address2 = $companyData["ADDRESS_2"];
    		$this->view->city = $companyData["CITY"];
			$this->view->country = $companyData["COUNTRY"];
    		$this->view->state = $companyData["STATE"];
    		$this->view->zip = $companyData["ZIP"];
    		$this->view->primaryContact = $companyData["PRIMARY_CONTACT"];
    	
    		//pass to the view the whole list of the countries, states and company employees
			$states = Model_Dictionary::retrieve()->fetchEntries(array('ID', 'ITEM', 'PARENT_ID', new Data_Column('CATEGORY', 'State')), null, true, 'VALUE_ORDER');
			$allStates = array();
			foreach($states as $state){
				array_push($allStates, array('ID' => $state['ID'], 'ITEM' => $state['ITEM'], 'PARENT_ID' => $state['PARENT_ID']));	
			}
			$this->view->allStates = Zend_Json::encode($allStates);
			
			$this->view->allCountries = Model_Dictionary::retrieve()->fetchEntries(array('ID', 'ITEM', new Data_Column('CATEGORY', 'Country')), null, true);
    		$this->view->allEmployees = Model_Employee::retrieve()->fetchEntries(array('ID', 'LAST_NAME', 'FIRST_NAME', new Data_Column('COMPANY_ID', $companyId)), null, true);
    	}
    		
    	$acl  = Zend_Registry::get('_acl');
		$user = Zend_Auth::getInstance()->getIdentity();
		$this->view->readonly = ($acl->inheritsRole($user, 'Building Owner Employees', true)) ? true : false;
    }
    
    public function buildingsAction() {
    	$this->view->companyName="";
    	if ($this->_getParam("_parent") > 0){
    		$company = Model_Company::retrieve()->fetchEntry($this->_getParam("_parent"));
    		$this->view->companyName = $company['NAME'];
			$this->view->companyId = $this->_getParam("_parent");
    	}
		
		$this->_initializeBratiliusGrid(array(
			'gridId' => 'company_buildings',
			'sortBy' => 'NAME'
		));
    }
    
    public function inspectionsAction() {
    	$this->view->companyName="";
    	if ($this->_getParam("_parent") > 0){
    		$company = Model_Company::retrieve()->fetchEntry($this->_getParam("_parent"));
    		$this->view->companyName = $company['NAME'];
    	}

		$this->_initializeBratiliusGrid(array(
			'gridId' => 'company_inspections',
			'sortBy' => 'ID'
		));
		
    	$this->view->doorsGrid = $this->_components['company_doors'];
    	$this->view->historyGrid = $this->_components['company_history'];
    	$this->view->photobucketGrid = $this->_components['company_photobucket'];
    	    	
    }
    public function employeesAction() {

    	$this->view->companyName="";
    	if ($this->_getParam("_parent") > 0){
    		$company = Model_Company::retrieve()->fetchEntry($this->_getParam("_parent"));
    		$this->view->companyName = $company['NAME'];
    	}
		
		$this->_initializeBratiliusGrid(array(
			'gridId' => 'company_employees',
			'sortBy' => 'LAST_NAME'
		));
    }
    
    public function saveAction($arg=false) {
    	if ($this->getRequest()->_model == 'company_employees')
    		return parent::saveAction(true);
        else return parent::saveAction(false);
	}
	 
	public function inspectionAction() {
		
		if ($this->_getParam('_parent')){
    		//this is an existing inspection, we will need to show its details
    		$this->view->inspectionId = $this->_getParam('_parent');
    	}
    	else{
    		$this->_components['company_inspections']->setReadonly();
    		
      		//get inspection company information (name and address on the inspection detailed view screen)
        	$employee = Model_Employee::retrieve()->fetchEntry(null, array(new Data_Column('USER_ID', $this->getUser(), Model_Employee::retrieve(), 'LOGIN'), 'COMPANY_ID'));
        	$company  = Model_Company::retrieve()->fetchEntry($employee['COMPANY_ID'], array('ID', 'TYPE', 'INSPECTION_COMPANY', 'NAME', 'ADDRESS_1', 'ADDRESS_2'));

        	$inspectionCompany = null;
			if ($company['TYPE'] == Model_Company::INSPECTION_COMPANY){
				$inspectionCompanyId = $company['ID'];
			}
			elseif($company['INSPECTION_COMPANY']){
				$company = Model_Company::retrieve()->fetchEntry($company["INSPECTION_COMPANY"], array('ID', 'NAME', 'ADDRESS_1', 'ADDRESS_2'));
				$inspectionCompanyId = $company['ID'];
			}
			
			//this is a new inspection. Let's create a record for it in the database
    		$this->view->inspectionId = Model_Inspection::retrieve()->save(array('INSPECTION_DATE' => date('Ymd'), 
    																			 'COMPANY_ID' => $inspectionCompanyId));
    	}
    	
    	$acl  = Zend_Registry::get('_acl');
		$user = Zend_Auth::getInstance()->getIdentity();
		
		$this->view->grid = $this->_components['company_inspections'];
    	$this->view->grid->setEditable();
    	if ($acl->inheritsRole($user, 'Building Owner Employees') && ($this->view->inspectionId > 0)){
    		$this->view->grid->setReadonly();
    	} 
		else $this->view->grid->setEditable();
		$this->_detailedView('company_inspections');
    }

   
    public function changesAction(){
        
        $this->view->grid = $this->getComponent('company_inspects');
        
        $this->view->gridParams = array(
            'page'          => nvl($this->getParam('page'), 0),
            'sortBy'        => nvl($this->getParam('sort_by'), 'ASSIGNED_DATE'),
            'sortDirection' => nvl($this->getParam('sort_direction'), 1),
            'rowsPerPage'   => nvl($this->getParam('rows_per_screen'), Zend_Registry::getInstance()->configuration->paginator->page),
            'selectAll'     => nvl($this->getParam('select_all'), false)
        );
    }
    
    public function changeAction(){
        $this->_detailedView('company_inspects');
        $this->view->grid = $this->_components['company_inspects'];
    }


	public function doorsAction(){
		
		$this->_initializeBratiliusGrid(array(
			'gridId' => 'company_doors',
			'sortBy' => 'NUMBER'
		));
		
	}
    
	public function doorAction() {
		$doorId = $this->getParam('_parent');		
		$inspectionId = $this->getParam('_super');
		
		$grid = $this->_components['company_doors'];
		$grid->setSecurityOptions($inspectionId);
		
		$this->_detailedView('company_doors'); 
    	$this->view->hardwareGrid = $this->_components['hardware']; //###
    	$this->view->doorGridId = 'company_doors';
    	$this->view->showMode = $this->getParam('_showMode');
    	$fireRating2 = null;
    	
    	if ($grid->isReadonly()) $this->view->hardwareGrid->setReadonly();
    	else $this->view->hardwareGrid->setEditable();
		
		// get existing door codes and type
		$this->view->codes = Model_Door::getCodes($doorId);
		$this->view->types = Model_Door::getTypes($doorId);
		$this->view->other = Model_Door::getOthers($doorId, $inspectionId);
		$this->view->pictures = Model_Door::retrieve()->getPictures($doorId);
		$this->view->audio    = Model_Door::retrieve()->getAudio($doorId);
		$this->view->showMode = $this->getParam('_showMode') ? $this->getParam('_showMode') : 'ordinary';
			
		if ($doorId < 0) { // new door
			// because of UI complexity, create this door
			$doorId = Model_Door::retrieve()->save(array('NUMBER' => '000', 'INSPECTION_ID' => $inspectionId));
			$this->view->record = null; //$this->view->grid->fetchEntry($doorId);
			$this->view->new = true;
		} else{
			$fireRating2 = Model_Door::retrieve()->fetchEntry(null, array('FIRE_RATING_2', new Data_Column('ID', $doorId)));
		}

		
		$fireRating2All = Model_Dictionary::retrieve()->fetchEntries(
			array('ID', 'ITEM', 'VALUE_ORDER', new Data_Column('CATEGORY', 'Fire-Rating 2')), null, null, 'VALUE_ORDER');
		
		$this->view->fireRating2All = $fireRating2All;
		$this->view->fireRating2 = $fireRating2['FIRE_RATING_2'];
		$this->view->doorId = $doorId;
		
		//find building name based on the inspection id
		$building = Model_Inspection::retrieve()->fetchEntry(null,
				array('BUILDING_ID', new Data_Column('ID', $inspectionId)));
		
		$building = Model_Building::retrieve()->fetchEntry($building['BUILDING_ID']);
		$this->view->buildingName = $building['NAME'];

    }
    
    public function doorsearchAction(){
   		$this->view->grid = $this->_components['company_doors'];
    	$fireRating2All = Model_Dictionary::retrieve()->fetchEntries(
			array('ID', 'ITEM', 'VALUE_ORDER', new Data_Column('CATEGORY', 'Fire-Rating 2')), null, null, 'VALUE_ORDER');
		
		$this->view->fireRating2All = $fireRating2All;
    }
    
    public function savepictureAction() {
		$this->_helper->ViewRenderer->setNoRender(true);
    	$this->_helper->layout->setLayout("iframe");
    	    	
		$doorId = $this->_getParam('door_id');
		$result = array();
    	
    	// check user errors
		if ($_FILES["picture_file"]["error"] != 0 &&
		    $_FILES["picture_file"]["error"] != 4) {
			$result['status'] = 'failed';
    		$result['message'] = 'Client error: ' . $_FILES["picture_file"]["error"];
		} else try {
			$picture = Model_Door::retrieve()->savePicture(
				$doorId, $_FILES["picture_file"],
				$this->_getParam('picture_id'),
				$this->_getParam('picture_notes'));
			$result['picture'] = current($picture) != '' ? $this->view->baseUrl . current($picture) : null;
			Zend_Registry::get('logger')->info('Picture: ' . current($picture));
	    	$result['picture_id'] = key($picture);
    		$this->view->placeholder('content')->set(Zend_Json::encode($result));	
		} catch (Exception $e) {
			Zend_Registry::getInstance()->logger->err($e->getMessage() . '\n' . $e->getTraceAsString());
    		$result['status'] = 'failed';
    		$result['message'] = $e->getMessage();
		}
		
		$this->view->placeholder('content')->set(Zend_Json::encode($result));
	}
	
	public function saveaudioAction() {
		$this->_helper->ViewRenderer->setNoRender(true);
    	$this->_helper->layout->setLayout("iframe");
    	    	
		$doorId = $this->_getParam('door_id');
		$result = array();
    	
    	// check user errors
		if ($_FILES["audio_file"]["error"] != 0 &&
		    $_FILES["audio_file"]["error"] != 4) {
			$result['status'] = 'failed';
    		$result['message'] = 'Client error: ' . $_FILES["audio_file"]["error"];
		} else try {
			$audio = Model_Door::retrieve()->saveAudio(
				$doorId, $_FILES["audio_file"],
				$this->_getParam('audio_id'),
				$this->_getParam('audio_notes'));
			$result['audio'] = current($audio) != '' ? $this->view->baseUrl . current($audio) : null;
	    	$result['audio_id'] = key($audio);	
		} catch (Exception $e) {
    		Zend_Registry::getInstance()->logger->err($e->getMessage() . '\n' . $e->getTraceAsString());
    		$result['status'] = 'failed';
    		$result['message'] = $e->getMessage();
		}

    	$this->view->placeholder('content')->set(Zend_Json::encode($result));	
	}

	/*public function dropdownAction() {
    	//must show only inspectors from current inspection company
    	if ($this->getParam('_model') == 'company_inspections' && $this->getParam('_column') == 'column_10') {
    		$column = $this->_components['company_inspections']->getColumnById('column_10');
    		$dropdownFilter = $column->getDropdownFilter();
    		$dropdownFilter['parent'] = new Data_Column('COMPANY_ID', $this->getParam('_parentColumn'), Model_Inspection::retrieve(), 'NAME');
    		$column->setDropdownFilter($dropdownFilter);
    	}
    	
    	parent::dropdownAction();
    }*/
    
    public function createuboAction() {
    	$this->_helper->ViewRenderer->setNoRender(true);
    	$this->_helper->layout->setLayout("html");
    	Model_Company::retrieve()->createAllUBO();
    	$this->view->placeholder("content")->set('Unknown Building Owners were successfully added');
    }
	
	public function ownersAction(){
		
		$this->_initializeBratiliusGrid(array(
			'gridId' => 'company',
			'sortBy' => 'NAME'
		));
	}
}
