<?
require_once APPLICATION_PATH . '/controllers/Component.php';
require_once APPLICATION_PATH . '/models/Employee.php';
require_once APPLICATION_PATH . '/models/Company.php';
require_once APPLICATION_PATH . '/models/User.php';

class EmployeesinspectionController extends Controller_Component {    
    
	public function init() {
		
		//array will contain permissions for current user to grids in current tab
    	$access = array();
       
     	/* inspection company employees grid */
    	$empGrid = $this->addGrid('emp',  Model_Employee::retrieve());
     	
        $columns = array(
        	'first_name'       => array('Title'    => 'First Name'),
        	'last_name'        => array('Title'    => 'Last Name'),

            				      array('Field'    => array(
            				      						'COMPANY_ID' => Model_Company::retrieve(),
            				      						'TYPE'		 => Model_Dictionary::retrieve()),
            				      		'Filter'    => 'Inspection Company',
            				      		'Display'	=> 'ITEM',
            				      		'Editable'	=> false,
            				      		'Hidden'	=> true,
            				      		'Visible'	=> '000'), 											        
        	'last_login'       => array('Title'   => 'Last login',
        	                            'Editable'=> False),
        	'license_number'   => array('Title'   => 'License Number', 'Maxlength' => '6'),
        	'expiration_date'  => array('Title'   => 'Expiration Date'),
        	'user_id'          => array('Field'   => 'user_id',
                                        'Display' => 'login',
                                        'Title'   => 'Login name',
                                        'DropdownFilter' => new Data_Column('ID', new Data_Filter_In(Model_Employee::retrieve()->fetchToArray(array('USER_ID')),'ID', true), Model_User::retrieve()),
            'COMPANY_ID'       => array('Display' => 'Company',
    						            'Title' => 'Employer')				       
    					) 
        );
        
        
	    //set filter for Inspection companies employees
   		$employee = Model_Employee::retrieve()->fetchEntry(null,array(
    	    'ID', 'COMPANY_ID', new Data_Column('USER_ID', $this->getUser(), Model_Employee::retrieve(), 'LOGIN')));
    	if ($this->getAcl()->inheritsRole($this->getUser(), 'Inspection Company Employees')) {
       		$columns['COMPANY_ID'] = array('Filter' 	=> $employee['COMPANY_ID'], 
								   		   'Editable' 	=> false, 
        								   'Default'	=> $employee['COMPANY_ID'], 
								   		   'Visible'	=> '000');
        }
        $empGrid->setColumns($columns,true);
        
        //get permissions for current user for current grid
    	$access['emp'] = $this->getAccessControl()->getGridPermissions('emp');
    	
    	$this->view->access = $access;
    	
        parent::init();
	}
	
    public function employeeAction() {
    	// make sure no specific view is associated with this action 
    	$this->_helper->layout->setLayout('html');
    	$this->view->grid = $this->_components['emp'];
    	$this->view->empId = $this->_getParam('_parent');
    }
    
	public function saveAction() {
	    if ($this->getRequest()->_model == 'emp')
            return parent::saveAction(true);
        else return parent::saveAction(false);
    }
}