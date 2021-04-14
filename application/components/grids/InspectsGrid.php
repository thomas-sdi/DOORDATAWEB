<?
require_once APPLICATION_PATH . '/components/CustomGrid.php';
class InspectsGrid extends Cmp_Custom_Grid {
	
	public function __construct($id, $parent=null, $parentLink=null, $inspectionId = -1) {
		// define columns
			$this->_usePaginator = true;
		$columns = array(
			'ID' => array(
				'Visible' 	=> '000',
				'Id'		=> 'ID'
			),
			'INSPECTION_ID' => array(
				'Visible' => '000',
				'Id' => 'INSPECTION_ID',
				'Filter' => $inspectionId
			),
        	'INSPECTOR_ID' 	=> array(
        		'Id'		=> 'INSPECTOR_ID',
				'Title' 	=> 'Employee',
        		'Display' 	=> 'LAST_NAME', 
				'Editable' => false,
				'DropdownFilter' => new Data_Column(array(
						'USER_ID'	=> Model_User::retrieve(),
						'$USER_ID'=> Model_User_Role::retrieve(),
						'ROLE_ID'	=> Model_Role::retrieve()),
					'Inspectors', Model_Employee::retrieve(),'NAME')
			),		
			array(
				  'Id'		=> 'INSPECTOR_ROLE',
				  'Field'	=> array( 'INSPECTOR_ID' => Model_Employee::retrieve(), 'USER_ID'  => Model_User::retrieve(), '$USER_ID' => Model_User_Role::retrieve(), 'ROLE_ID'  => Model_Role::retrieve()),
				  'Display'  => 'NAME', 
				  'Title'	=> 'Role', 
				  'Editable' => false, 
				  'Visible'  => '100',
    			  'DropdownFilter' => new Data_Column('ROLE_ID', 'Inspection Company Employees', Model_User_Role::retrieve(),'NAME')
			),		
			'ASSIGNED_DATE'  => array(
				'Id'		=> 'ASSIGNED_DATE',
				'Title'    => 'Date', 
				'Editable' => false, 
				'View'     => Data_Column_Grid::DATE,
				'Width'	 => '80px'
			),
			'COMMENTS'		 => array(
				'Id'	=> 'COMMENTS',
				'Title' => 'Notes', 
				'View'  => Data_Column_Grid::MEMO
			),
			
            array(
            	'Title' => 'Actions', 
            	'Calculated' => array($this, "calculateInspectionHistoryActions"), 
            	'Visible' => '100'
			)
		);
		
		$acl  = Zend_Registry::get('_acl');
		$user = Zend_Auth::getInstance()->getIdentity();
		
		// get user's company information
		$employee = Model_Employee::retrieve()->fetchEntries(array(
			'COMPANY_ID', new Data_Column('USER_ID', $user, Model_Employee::retrieve(), 'LOGIN'),
			'COMPANY_NAME' => new Data_Column('COMPANY_ID', null, Model_Employee::retrieve(), 'NAME')));
    	$employee = $employee->getItem(1);
		
    	if ($acl->inheritsRole($user, 'Inspection Company Employees')) {
    		$columns['INSPECTOR_ID']['DropdownFilter'] = array(
				new Data_Column('COMPANY_ID', $employee['COMPANY_ID']),
    			$columns['INSPECTOR_ID']['DropdownFilter']);
    	} 
				
		parent::__construct($id, Model_Inspect::retrieve(), $parent, $parentLink, $columns);
	}
	
	public function calculateInspectionHistoryActions($entry){
         $actionHead = '<div class="dropdown action-dropdown">
  <a data-toggle="dropdown">
   -Select-
     <i class="glyph-icon icon-chevron-down"></i>
  </a><div class="dropdown-menu float-right"><div class="">';
		$actionFoot = '</div></div></div>';
		$edit 	= '<div class="pad5A button-pane button-pane-alt text-center"><a data-original-title="Edit" title="Edit" data-placement="top" class="btn btn-sm hover-blue-alt tooltip-button" 
						href="javascript: cmp_' . $this->getID()  . '.showEditDialog('. $entry['ID'] .')"><i class="fa fa-edit"></i>Edit</a></div>';
		
    	$actions = $edit;
    	return $actionHead . $actions  . $actionFoot;
    }
}