<?
require_once APPLICATION_PATH . '/components/CustomGrid.php';
require_once APPLICATION_PATH . '/models/Dictionary.php';

class BuildingGrid extends Cmp_Custom_Grid {
	
	public function __construct($id, $parent=null, $parentLink=null) {
		
		$this->_usePaginator = true;
		
		// define columns
		$columns = array (
			'id'		 => array('Field'		   => 'id',
								   'Visible'	   => '000',
								   'Id'			   => 'ID'),
    	    'customer_id'=> array('Field'          => 'customer_id',
								  'Id'			   => 'OWNER',
    	                          'Display'        => 'name',
    	                          'Title'          => 'Owner',
    							  'DropdownFilter' => array(new Data_Column('type', 'Building Owner', 
    																   Model_Company::retrieve(), 'item'),
    														new Data_Column('NAME', new Data_Filter('Unknown Building Owner', null, true))),
    							  'Visible'  => '011'),
       		'name'       => array('Title'    => 'Name', 
       							  'Required' => true,
       							  'Id'		=> 'NAME',
    							  'Link' =>  "/building/inspections"),
    	    'company'    => array('Field' => array('CUSTOMER_ID' => Model_Company::retrieve(),
    											   'INSPECTION_COMPANY' => Model_Company::retrieve()
    											   ),
    							  'Display' => 'NAME',
    							  'Id'	=> 'INSPECTION_COMPANY',
    							  'Editable' => false,
    						      'Title' => 'Inspection Company', 'Width' => '200px'
    							  //,'View' =>	Data_Column_Grid::TEXT
    							  ),
    		'PRIMARY_CONTACT' => array('Title'			=> 'Main Contact',
    					    		   'Id'				=> 'PRIMARY_CONTACT_LAST_NAME',
    					    		   'Display'		=> 'LAST_NAME',
									   'ParentColumns'	=> array('OWNER'),
    							  	   'DropdownFilter' => array(
    							  			new Data_Column('COMPANY_ID', '$OWNER', Model_Employee::retrieve(), 'ID')
    							  	   )
    							 ),
    					    
    							 array('Field'   => 'PRIMARY_CONTACT',
    							  	   'Display' => 'EMAIL',
    							 	   'Id'      => 'PRIMARY_CONTACT_EMAIL',
    							       'Visible' => '000'),
    							 array('Field'   => 'PRIMARY_CONTACT',
                                        "Id"     => 'PRIMARY_CONTACT_PHONE',
    							 	   'Display' => 'PHONE',
    							 	   'Title'   => 'Phone',
    							 	   'Editable'=> false,
    							 	   'Visible' => '100'),
    	    'address_1'  => array('Title'          => 'Address 1', 'Id' => 'ADDRESS_1'),
    	    'address_2'  => array('Title'          => 'Address 2', 'Id' => 'ADDRESS_2'),
    	    'city'       => array('Title'          => 'City', 'Id' => 'CITY'),
    	    'country'    => array('Field'          => 'COUNTRY',
    	                          'Display'        => 'item',
    	                          'Id'             => 'country',
    	                          'Title'          => 'Country',
                                  'DropdownFilter' => new Data_Column('category', 'Country', Model_Dictionary::retrieve()),
                                  'Visible'        => '011',
								  'Default'			=> Model_Dictionary::COUNTRY_USA),
			'state'      => array('Field'          => 'state',
								  'Id'			   => 'STATE',
    	                          'Display'        => 'item',
    	                          'Title'          => 'State / Province',
    	                          'ParentColumns'  => array('country'),
    	                          'DropdownFilter' => array(new Data_Column('CATEGORY', 'State'),
								  							new Data_Column('PARENT_ID', '$country'))
								  ),
    		'zip'        => array('Title'          => 'ZIP / Postal Code', 'Id' => 'ZIP'),
    		'summary'    => array('Title'          => 'Summary',
    	                          'View'           => Data_Column_Grid::MEMO,
                                  'Visible'        => '011',
								  'Id' 				=> 'SUMMARY'),
            array('Title' => 'Actions', 'Calculated' => array($this, "calculateBuildingActions"), 'Visible' => '100')
    					 );
		
		$acl  = Zend_Registry::get('_acl');
		$user = Zend_Auth::getInstance()->getIdentity();
		
		$employees = Model_Employee::retrieve()->fetchToArray(array('COMPANY_ID', new Data_Column('USER_ID', $user, Model_Employee::retrieve(), 'LOGIN')));
    	$employee = $employees[0];
		
	    /*
    	 * set filter for Building owner employees
    	 * show only buildings owned by employee's building owner
    	 */
    	if ($acl->inheritsRole($user, 'Building Owner Employees')) {
        	$columns['customer_id'] = array('Filter' 	=> $employee, 
        					   				'Editable' 	=> false,
											'Id' 		=> 'OWNER',
        									'Default'	=> $employee['COMPANY_ID'],
        					   				'Visible'	=> '000');
        }
    	       
		/*
         * set filter for Inspectors
         * Inspectors can only see those buildings, which belong to building owners, which were created by 
         * current inspection company
         */
        if ($acl->inheritsRole($user, 'Inspection Company Employees')) {
        	//$building_owners = Model_Company::retrieve()->fetchToArray(array('NAME', new Data_Column('INSPECTION_COMPANY', $employee['COMPANY_ID']))); 
           	/*$columns['customer_id'] = array('Display'	=> 'name',
    	                          			'Title'     => 'Owner',
											'Id' 		=> 'OWNER',
        									//'Filter' 	=> $building_owners,
    							  			'DropdownFilter'  => array(new Data_Column('name', new Data_Filter_In($building_owners)),
           	 														   new Data_Column('NAME', new Data_Filter('Unknown Building Owner', null, true)))
											);*/
		    $columns['customer_id']['DropdownFilter'] = array(
					new Data_Column('inspection_company', $employee['COMPANY_ID']),
					new Data_Column('NAME', new Data_Filter('Unknown Building Owner', null, true)));
			$columns['company']['Default'] = $employee['COMPANY_ID'];
			unset ($columns['company']['DropdownFilter']);
			$columns[] = array('Field' => 'customer_id', 'Display' => 'inspection_company',
							   'Filter'  => $employee['COMPANY_ID'], 'Visible' => '000');							
        }
				
		parent::__construct($id, Model_Building::retrieve(), $parent, $parentLink, $columns);
	}
	
    public function calculateBuildingActions($entry){
    	//get Primary Contact ID for currently selected company
		$email = $entry['PRIMARY_CONTACT_EMAIL'];
		$actions = "";
		if ($email){	
			$address = strstr($email, 35); //35 is an ascii code for #
			$address = str_replace('#', '', $address);
			if ($address) $actions = "<a href='mailto:" . $address . "'>Email</a> | ";
		}

		$gridId = $this->getId();
		
		$acl  = Zend_Registry::get('_acl');
		$user = Zend_Auth::getInstance()->getIdentity();
		
		if (!$acl->inheritsRole($user, 'Building Owner Employees'))
            $actionHead = '<div class="dropdown action-dropdown">
           <a data-toggle="dropdown">
          -Select-
       <i class="glyph-icon icon-chevron-down"></i>
        </a><div class="dropdown-menu float-right"><div class="">';
        $actionFoot = '</div></div></div>'; 
			$actions .= 
				'<div class="pad5A button-pane button-pane-alt text-center"><a data-original-title="Edit" title="" data-placement="top" class="btn btn-sm hover-blue-alt tooltip-button" 
						href="javascript: cmp_' . $gridId . '.showEditDialog('. $entry['ID'] .')"><i class="fa fa-edit"></i>Edit</a></div>' .
    			'<div class="pad5A button-pane button-pane-alt text-center"><a data-original-title="Remove" title="" data-placement="top" class="btn btn-sm hover-red tooltip-button" 
						href="javascript: cmp_' . $gridId . '.deleteItem('. $entry['ID'] .')"><i class="glyph-icon icon-remove"></i>Delete</a></div>';
    	return $actionHead . $actions . $actionFoot;
    }
}