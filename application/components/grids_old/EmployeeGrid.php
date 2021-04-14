<?
require_once APPLICATION_PATH . '/components/CustomGrid.php';

class EmployeeGrid extends Cmp_Custom_Grid {
	
	public function __construct($id, $parent=null, $parentLink=null, $inspectionVSowner='inspection', $companyId) {
		
		$this->_usePaginator = true;
		
		// define columns
        $columns = array(
        	'id'			   => array('Id' => 'ID', 'Visible' => '000'),
        	'first_name'       => array('Title'    => 'First Name', 'Id' => 'FIRST_NAME'),
        	'last_name'        => array('Title'    => 'Last Name', 'Id' => 'LAST_NAME'),
        	'company_id'	   => array('Id' => 'COMPANY_ID', 'Visible' => '000'),
            'company_type'	   => array('Field'    => array( 'COMPANY_ID' => Model_Company::retrieve(), 'TYPE' => Model_Dictionary::retrieve()),
            				      		'Filter'    => 'Inspection Company',
            				      		'Display'	=> 'ITEM',
            				      		'Editable'	=> false,
            				      		'Hidden'	=> true,
            				      		'Visible'	=> '000'), 	
           	'PHONE'			   => array('Title'   => 'Phone'),	
           	'EMAIL'			   => array('Title'   => 'Email'),									        
        	'user_id'          => array('Field'   => 'user_id',
                                        'Display' => 'login',
                                        'Title'   => 'Login name',
										'Editable' => false,
										'Visible' => '110'),
							      array('Field'	  => array('USER_ID'  => Model_User::retrieve(), '$USER_ID' => Model_User_Role::retrieve(), 'ROLE_ID'  => Model_Role::retrieve()),
    						  		   'Display'  => 'NAME',
    								   'Title'	  => 'Role',
    							       'Editable' => false,
							      	   'Visible'  => '110',
    								   'DropdownFilter' => new Data_Column(
    								 					     	'ROLE_ID',
    							 								new Data_Filter_In(array('Inspection Company Employees')),
    							 								Model_User_Role::retrieve(),
    							 								'NAME')
                                	  ),
            'expiration_date'  => array('Visible' => '000'),
            'license_number'   => array('Visible' => '000'),				      
            'last_login'       => array('Title'   => 'Last login',
        	                            'Editable'=> False,
            				      		'Visible' => '010'),
    		array('Title' => 'Actions', 'Calculated' => array($this, "calculateEmployeeActions"), 'Visible' => '100')			
        );
        
		if ($inspectionVSowner == 'inspection'){
			//we should add two more columns - license number and expiration date
			$columns['expiration_date'] = array('Title'   => 'Expiration Date', 'Visible' => '001');
			$columns['license_number'] = array('Title'   => 'License Number', 'Maxlength' => '6', 'Visible' => '011'); 
			
			//when administrator is looking at the employee records, only one company at a time is studied
			$columns['company_id'] = array(
				'Id' => 'COMPANY_ID', 
				'Visible' => '000',
				'Filter' => $companyId
			);
		}
		elseif ($inspectionVSowner == 'owner'){
			$columns['company_type'] = array('Field'    => array(
            				      						'COMPANY_ID' => Model_Company::retrieve(),
            				      						'TYPE'		 => Model_Dictionary::retrieve()),
            				      		'Filter'    => 'Building Owner',
            				      		'Display'	=> 'ITEM',
            				      		'Editable'	=> false,
            				      		'Hidden'	=> true,
            				      		'Visible'	=> '000');
		}
        
        
		$acl  = Zend_Registry::get('_acl');
		$user = Zend_Auth::getInstance()->getIdentity();
    	
   		
		//get current employee data
   		$employee = Model_Employee::retrieve()->fetchEntry(null,array(
    	    'ID', 'COMPANY_ID', new Data_Column('USER_ID', $user, Model_Employee::retrieve(), 'LOGIN')));
   		
   		//set filter for Inspection companies employees
    	if ($acl->inheritsRole($user, 'Inspection Company Employees') && $inspectionVSowner == 'inspection') {
       		$columns['COMPANY_ID'] = array('Filter' 	=> $employee['COMPANY_ID'], 
								   		   'Editable' 	=> false, 
        								   'Default'	=> $employee['COMPANY_ID'], 
								   		   'Visible'	=> '000');
    	}
		
    	//set filter/settings for Building Owner employees
	    if ($acl->inheritsRole($user, 'Building Owner Employees')) {
        	$columns['COMPANY_ID'] = array('Filter' 	=> $employee['COMPANY_ID'], 
								   		   'Editable' 	=> false, 
        								   'Default'	=> $employee['COMPANY_ID'], 
								   		   'Visible'	=> '000');
        }
		
		parent::__construct($id, Model_Employee::retrieve(), $parent, $parentLink, $columns,$selector = null, $params = array());
	}
	
    public function calculateEmployeeActions($entry){
    	$actions = "";
    	$gridId = $this->getId();
    	
    	$acl  = Zend_Registry::get('_acl');
		$user = Zend_Auth::getInstance()->getIdentity();
		
		$editAction 	= '<a data-original-title="Edit" title="Edit" data-placement="top" class="btn btn-sm hover-blue-alt tooltip-button" 
						href="javascript: cmp_' . $gridId . '.showEditDialog('. $entry['ID'] .')"><i class="fa fa-edit">edit2</i>edit2</a>';
		$deleteAction = '<a data-original-title="Delete" title="Delete" data-placement="top" class="btn btn-sm hover-red tooltip-button" 
						href="javascript: cmp_' . $gridId . '.deleteItem('. $entry['ID'] .')"><i class="glyph-icon icon-remove"></i></a>';
		
		
		
		if ($acl->inheritsRole($user, 'Building Owner Employees', true)) {
			$login = current($this->getColumnsByField('user_id'));
			$login = $entry[$login->getId()];
			$login = substr($login, strpos($login,'#')+1, strlen($login));
			if ($user == $login) {
				$email = current($this->getColumnsByField('EMAIL'));
				$email = $entry[$email->getId()];
				if ($email) {
					$emailAction = '<a data-original-title="Email" title="Email" data-placement="top" class="btn btn-sm hover-blue-alt tooltip-button" 
						href="mailto:'. $email .'"><i class="glyph-icon icon-envelope-o"></i></a>';
						
					$actions = $emailAction;
				}
				
		    	$actions .= $editAction;
			}
		}
		else {
			$email = current($this->getColumnsByField('EMAIL'));
			$email = $entry[$email->getId()];
			if ($email){
				$emailAction = '<a data-original-title="Email" title="Email" data-placement="top" class="btn btn-sm hover-blue-alt tooltip-button" 
						href="mailto:'. $email .'"><i class="glyph-icon icon-envelope-o"></i></a>';
						
				$actions = $emailAction;
			} 
			
	    	$actions = $actions . $editAction . $deleteAction ;
		}
		
    	return $actions;
    }
}