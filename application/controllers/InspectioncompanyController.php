<?
require_once APPLICATION_PATH . '/controllers/Component.php';
require_once APPLICATION_PATH . '/models/Employee.php';
require_once APPLICATION_PATH . '/models/Company.php';
require_once APPLICATION_PATH . '/models/Dictionary.php';
require_once APPLICATION_PATH . '/models/User.php';
require_once APPLICATION_PATH . '/models/Inspection.php';
require_once APPLICATION_PATH . '/models/UserRole.php';
require_once APPLICATION_PATH . '/models/Role.php';
require_once APPLICATION_PATH . '/components/grids/EmployeeGrid.php';
require_once APPLICATION_PATH . '/components/grids/InspectionCompanyGrid.php';
require_once APPLICATION_PATH . '/components/forms/InspectionCompanyProfileForm.php';

class InspectioncompanyController extends Controller_Component {    
	
	public function init() {
		$this->_helper->layout->setLayout('html');
		
		//when browsed by the administrator, specific inspection company id
		$companyId = $this->getParam('_super');
		
		//array will contain permissions for	 current user to grids in current tab
		$access = array();
		
		$inspectioncompanyGrid = $this->addGrid(new InspectionCompanyGrid('inspection_company'));
		
		
        //get permissions for current user for current grid
		$access['inspection_company'] = $this->getAccessControl()->getGridPermissions('inspection_company');
		
		/* emplpoyees grid (dependent for inspection companies grid) - for doordata administrators */
    	//$empGrid = $this->addGrid(new EmployeeGrid('emp', $inspectioncompanyGrid, 'COMPANY_ID', 'inspection'));
		$empGrid = $this->addGrid(new EmployeeGrid('emp', null, null, 'inspection', $companyId));
		
		/* employees grid - to be used by inspection companies employees in admin section*/
		$allemployeesGrid = $this->addGrid(new EmployeeGrid('all_employees',null, null, null,null));
		$this->view->allEmpGrid = $allemployeesGrid;
		
        //get permissions for current user for current grid
		$access['emp'] = $this->getAccessControl()->getGridPermissions('emp');
		
		$this->view->access = $access;
		
    	// logo upload form
		$this->addForm(new InspectionCompanyProfileForm('inspectionCompanyProfile'));
		
		parent::init();
	}
	
	public function employeeAction() {
		$this->_detailedView('emp');
		
		$super = $this->getParam('_super');
		
		// get some details about user associated to an employee
		$this->view->user = Model_Employee::retrieve()->fetchEntry($this->getParam('_parent'), array(
			'LOGIN'   => new Data_Column('USER_ID', null, Model_Employee::retrieve(), 'LOGIN'),
			'COMPANY_ID',
			'ROLE_ID' => new Data_Column(
				array('USER_ID' => Model_User::retrieve(),
					'$USER_ID' => Model_User_Role::retrieve()),
				null, Model_Employee::retrieve(), 'ROLE_ID'
			)));
		
		// if this user is web user, prohibit changing its role
		$acl = Zend_Registry::get('_acl'); $user = $this->getUser();
		if ($acl->inheritsRole($user, 'Web Users'))
		{ // web users cannot change his or other's roles
	$this->view->roleEditDisabled = true;
} else $this->view->roleEditDisabled = false;

		// set default role to current user's role
$role = Model_User_Role::retrieve()->fetchEntry(null, array(
	new Data_Column('USER_ID', $this->getUser(), Model_User_Role::retrieve(), 'LOGIN'), 'ROLE_ID'));
$role = $role['ROLE_ID'];
$this->view->userRole = $this->view->user ? $this->view->user['ROLE_ID'] : $role;

$this->view->companyId = $this->view->user['COMPANY_ID'] ? $this->view->user['COMPANY_ID'] : ( $super ? $super : App::companyId());

		// get list of possible roles the user can participate in
$this->view->roles = Model_Role::retrieve()->fetchEntries(array(
	'ID', 'NAME', new Data_Column('NAME', new Data_Filter_In(array(
		'Field Inspectors', 'Inspectors', 'Web Users', 'Inspection Company Admins')))),
null, true, 'ORDER_BY');
}

public function allemployeesAction(){
	$this->_detailedView('all_employees');
	
    	// get some details about user associated to an employee
	$this->view->user = Model_Employee::retrieve()->fetchEntry($this->getParam('_parent'), array(
		'LOGIN'   => new Data_Column('USER_ID', null, Model_Employee::retrieve(), 'LOGIN'),
		'ROLE_ID' => new Data_Column(
			array('USER_ID' => Model_User::retrieve(),
				'$USER_ID' => Model_User_Role::retrieve()),
			null, Model_Employee::retrieve(), 'ROLE_ID'
		)));
	
		// if this user is web user, prohibit changing its role
	$acl = Zend_Registry::get('_acl'); $user = $this->getUser();
	if ($acl->inheritsRole($user, 'Web Users'))
		{ // web users cannot change his or other's roles
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
		'Field Inspectors', 'Inspectors', 'Web Users', 'Inspection Company Admins')))),
null, true, 'ORDER_BY');

$this->view->companyId = App::companyId();
}



public function detailviewAction() {
    	$this->_detailedView('inspection_company'); //this action is a detailed view for the 'inspection_company' grid
    	$companyId = $this->getParam("_parent");
    	$company = Model_Company::retrieve()->fetchEntry($companyId);
    	$allEmployees = Model_Employee::retrieve()->fetchEntries(
    		array('ID', 'FIRST_NAME', 'LAST_NAME',
    			new Data_Column('COMPANY_ID', $companyId))
    	);
    	
    	$this->view->grid = $this->_components['inspection_company'];
    	$this->view->allEmployees = $allEmployees;
    	$this->view->primaryContact = $company['PRIMARY_CONTACT'];
    	$this->view->logoBranding = $company['BRANDING'] == Model_Company::BRANDING_LOGO || $company['BRANDING'] == Model_Company::BRANDING_ALL;
    	$this->view->colorBranding = $company['BRANDING'] == Model_Company::BRANDING_COLOR || $company['BRANDING'] == Model_Company::BRANDING_ALL;
    	$this->view->isAdmin = Zend_Registry::get('_acl')->inheritsRole(Zend_Auth::getInstance()->getIdentity(), 'Administrators');

    }
    
    public function adminAction(){
    	$session = new Zend_Session_Namespace('default');
    	$this->view->companyName = $session->companyName;
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
    
    public function profileAction() {
    	$this->_helper->layout->setLayout('form');
    	
    	// get the company data
    	$session = new Zend_Session_Namespace('default');
    	$companyData = Model_Company::retrieve()->fetchEntry($session->companyId);
    	
		// populate the form with the data for the existing company
    	$form = $this->getForm('inspectionCompanyProfile');
    	$form->setValues(array(
    		'id'              => $session->companyId,
    		'address_1'       => $companyData["ADDRESS_1"],
    		'address_2'       => $companyData["ADDRESS_2"],
    		'city' 	          => $companyData["CITY"],
    		'state'    		  => $companyData["STATE"],
    		'country'		  => $companyData['COUNTRY'],
    		'zip'             => $companyData["ZIP"],
    		'primary_contact' => $companyData["PRIMARY_CONTACT"],
    		'logo_file' 	  => $companyData["LOGO_FILE"],
    		'theme'           => $companyData["COLOR_THEME"]
    	));
    	
    	$this->view->placeholder('data')->form = $form;
    	
		// themes
    	$this->view->THEME_BLUE   = Model_Inspection::THEME_BLUE;
    	$this->view->THEME_CHROME = Model_Inspection::THEME_CHROME;
    	$this->view->THEME_RED    = Model_Inspection::THEME_RED;
    	$this->view->THEME_GREEN  = Model_Inspection::THEME_GREEN;
    	$this->view->THEME_BROWN  = Model_Inspection::THEME_BROWN;
    	
    	// pass to the view the whole list of the countries and company employees
    	$this->view->allCountries = Model_Dictionary::retrieve()->fetchEntries(array('ID', 'ITEM', new Data_Column('CATEGORY', 'Country')), null, true);
    	$this->view->allEmployees = Model_Employee::retrieve()->fetchEntries(array('ID', 'LAST_NAME', new Data_Column('COMPANY_ID', $session->companyId)), null, true);
    	
		// access rights
    	$this->view->canChangeTheme = Model_Company::retrieve()->brandingAllowsThemeChange($companyData['BRANDING']);
    	$this->view->canChangeLogo  = Model_Company::retrieve()->brandingAllowsLogoChange($companyData['BRANDING']); 
    }

    public function providersAction(){
		/*$this->view->grid = $this->getComponent('inspection_company');
		
		$this->view->gridParams = array(
			'page' 	 		=> nvl($this->getParam('page'), 0),
			'sortBy' 		=> nvl($this->getParam('sort_by'), 'NAME'),
			'sortDirection' => nvl($this->getParam('sort_direction'), 1),
			'rowsPerPage'	=> nvl($this->getParam('rows_per_screen'), Zend_Registry::getInstance()->configuration->paginator->page),
			'selectAll'		=> nvl($this->getParam('select_all'), false)
		);*/
		
		$this->_initializeBratiliusGrid(array(
			'gridId' => 'inspection_company',
			'sortBy' => 'NAME'
		));
	}
	
	public function provideremployeesAction(){
		$this->_initializeBratiliusGrid(array(
			'gridId' => 'emp',
			'sortBy' => 'LAST_NAME'
		));
	}
	
	public function employeesAction(){
		
		$this->_initializeBratiliusGrid(array(
			'gridId' => 'all_employees',
			'sortBy' => 'LAST_NAME'
		));
	}

	public function excelexportAction(){
		return parent::excelAction($this->_getParam('gridId'));
	}


	public function saveAction($arg=null) {

		$model = $this->getRequest()->_model; 

		if ($model == 'emp'){
			if(isset($this->getRequest()->id) && ($this->getRequest()->new_password == '' || $this->getRequest()->new_password == null) ){
				parent::saveAction(false);
			}else{ 
				parent::saveAction(true);
			}
		}else if ($model == 'all_employees'){
			// if(isset($this->getRequest()->id)){
			// }else{ 
			parent::saveAction(true);
			// }
		}else if ($model == 'inspection_company'){
			parent::saveAction(true);
		}


		$imgremove = ($this->_getParam('imgRemove'))? $this->_getParam('imgRemove') :'';
		
		if($imgremove == '1'){
			//remove img
			$user_id = $this->_getParam('USER_ID');
			$path = ROOT_PATH . '/public/logos/profile/' . $user_id .'.png';
			if (file_exists($path)) 
			{
				unlink($path);
			}
		} 



		/*######### image Upload ##########*/

		// $this->_helper->ViewRenderer->setNoRender(true);
		try {

			if ( $this->_getParam('USER_ID') != '' && isset($_FILES["myfile"]) ) {
				$this->_helper->layout->setLayout('iframe');
				if ($_FILES["myfile"]["error"] == 0) {

					$user_id = $this->_getParam('USER_ID');

					move_uploaded_file($_FILES["myfile"]['tmp_name'], 
						ROOT_PATH . '/public/logos/profile/' . $user_id .'.png');	

					return true;
				}
			}
		} catch(Exception $e) {
			return true;
		}

		return true;
	}

}
