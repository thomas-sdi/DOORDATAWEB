<?

require_once APPLICATION_PATH . '/components/CustomGrid.php';
require_once APPLICATION_PATH . '/models/Dictionary.php';

class InspectionCompanyGrid extends Cmp_Custom_Grid {

	public function __construct($id, $parent = null, $parentLink = null) {
		
		$this->_usePaginator = true;
		
		$columns = array ( 
            'ID' => array(
            	'Field' => 'ID', 
            	'Id' => 'ID', 
            	'Visible' => '011', 
            	'Editable' => false
			),
            'NAME' => array(
            	'Maxlength' => '40', 
            	'Id' => 'NAME'
			), 
            'PRIMARY_CONTACT' => array(
            	'Id'		  => 'PRIMARY_CONTACT',
            	'Title'       => 'Main Contact',
    			'Display'     => 'LAST_NAME',
	   			'DropdownFilter' => array(new Data_Column('COMPANY_ID', '$ID')) // show only employees for this owner
			), 
    		array(	'Field'   => 'PRIMARY_CONTACT',
    				'Display' => 'EMAIL',
    				'Id'      => 'PRIMARY_CONTACT_EMAIL',
    				'Visible' => '000'),
    		array(	'Field'   => 'PRIMARY_CONTACT',
    				'Display' => 'PHONE',
    				'Id'      => 'PRIMARY_CONTACT_PHONE',
    				'Title'   => 'Phone',
    				'Editable'=> false,
    				'Visible' => '100'),
            'ADDRESS_1' => array('Id' => 'ADDRESS_1'),
            'ADDRESS_2' => array('Visible' => '011', 'Id' => 'ADDRESS_2'), 
            'CITY' => array('Id' => 'CITY'),
            'COUNTRY' => array(
            	'Id' 				=> 'country',
				'DropdownFilter' 	=> new Data_Column('category', 'Country'),
				'Default'			=> Model_Dictionary::COUNTRY_USA
			),
            'STATE' => array(
            	'Id'			 => 'STATE',
            	'Title'		 	 => 'State / Province',
            	'ParentColumns'  => array('country'),
            	'DropdownFilter' => array(new Data_Column('CATEGORY', 'State'),new Data_Column('PARENT_ID', '$country'))
			),
    		'ZIP'	=> array(
    			'Visible'		=> '011',
    			'Id'			=> 'ZIP'
			),
            'TYPE'  => array(
            	'Filter' 	=> 'Inspection Company', 
            	'Editable' 	=> false,
                'Default' 	=> '1002', 
                'Visible' 	=> '000',
                'Id'		=> 'TYPE'	
			),
    		array('Title' => 'Actions', 'Calculated' => array($this, "calculateActions"), 'Visible' => '100') 
        );

		//set filter for Inspection companies employees
		$identity = App::userIdentity();
   		$employees = Model_Employee::retrieve()->fetchToArray(array('COMPANY_ID', new Data_Column('USER_ID', $identity, Model_Employee::retrieve(), 'LOGIN')));
    	$employee = $employees[0];
    	if (App::acl()->inheritsRole($identity, 'Inspection Company Employees')) {
        	$columns['ID'] = array('Filter' 	=> $employee, 
								   'Editable' 	=> false, 
								   'Hidden' 	=> true,
        						   'Visible'	=> '000');
        }
		
		parent::__construct($id, Model_Company::retrieve(), $parent, $parentLink, $columns);
	}

	public function calculateActions($entry){
		//get Primary Contact email for currently selected company
		$email = $entry['PRIMARY_CONTACT_EMAIL'];
		$actions = "";
		if ($email){	
			$address = strstr($email, 35); //35 is an ascii code for #
			$address = str_replace('#', '', $address);
			if ($address) $actions = "<a href='mailto:" . $address . "'>Email</a> | ";
		}
		
		$gridId = $this->getId();
			
		$actions = $actions .
				   //"<a href='javascript: cmp_inspection_company.showDetailed()'>Edit</a> | " .
    			   //"<a href='javascript: cmp_inspection_company.deleteItems()'>Delete</a>";
				   
				   '<a data-original-title="Edit" title="" data-placement="top" class="btn btn-sm hover-blue-alt tooltip-button" 
						href="javascript: cmp_' . $gridId . '.showEditDialog('. $entry['ID'] .')"><i class="fa fa-edit">edit1</i>edit1</a>' .
    				'<a data-original-title="Remove" title="" data-placement="top" class="btn btn-sm hover-red tooltip-button" 
						href="javascript: cmp_' . $gridId . '.deleteItem('. $entry['ID'] .')"><i class="glyph-icon icon-remove"></i></a>';
    	return $actions;
	}
}