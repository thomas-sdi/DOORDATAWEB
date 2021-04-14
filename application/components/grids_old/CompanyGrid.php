<?

require_once APPLICATION_PATH . '/components/CustomGrid.php';
require_once APPLICATION_PATH . '/models/Dictionary.php';

class CompanyGrid extends Cmp_Custom_Grid {

	public function __construct($id, $parent = null, $parentLink = null) {
		
		$this->_usePaginator = true;
		
		$columns = array(
			'id'		 => array('Field'		   => 'id',
								   'Visible'	   => '000',
								   'Id'			   => 'ID'),
			'NAME' => array('Id' => 'NAME', 
				'Link' => "/company/owner",
				'Filter' => new Data_Filter('Unknown Building Owner', null, true)),
			'INSPECTION_COMPANY' => array(
				'Id'		=> 'INSPECTION_COMPANY',
				'Display' => 'Name',
				'DropdownFilter' => new Data_Column('TYPE', 'Inspection Company', Model_Company::retrieve(), 'ITEM'),
				'Title' => 'Inspection Company', 
				'Width' => '200px'),
			'PRIMARY_CONTACT' => array(
				'Id'	=> 'PRIMARY_CONTACT',
				'Title' => 'Main Contact',
				'Display' => 'LAST_NAME',
				'Visible' => '111',
				'DropdownFilter' => array(new Data_Column('COMPANY_ID', '$ID') /*show only employees for this owner */)
			),
			array(
				'Id' => 'PRIMARY_CONTACT_EMAIL',
				'Field' => 'PRIMARY_CONTACT',
				'Display' => 'EMAIL',
				'Visible' => '000'),
			array(
				'Id'	=> 'PRIMARY_CONTACT_PHONE',
				'Field' => 'PRIMARY_CONTACT',
				'Display' => 'PHONE',
				'Title' => 'Phone',
				'Editable' => false,
				'Visible' => '100'),
			'ADDRESS_1' => array('Id' => 'ADDRESS_1'), 
			'ADDRESS_2' => array('Id' => 'ADDRESS_2'), 
			'CITY' => array('Id' => 'CITY'),
			'COUNTRY' => array(
				'Id' 				=> 'country',
				'DropdownFilter' 	=> new Data_Column('category', 'Country'),
				'Visible'			=> '011',
				'Default'			=> Model_Dictionary::COUNTRY_USA
			),
			'STATE' => array(
				'Id'				=> 'STATE',
				'Title'				=> 'State / Province',
            	'ParentColumns' 	=> array('country'),
            	'DropdownFilter'	=> array(new Data_Column('CATEGORY', 'State'),new Data_Column('PARENT_ID', '$country'))
			),
			'TYPE' => array(
				'Id'	=> 'TYPE',
				'Filter' => 'Building Owner',
				'Editable' => false,
				'Default' => '1001',
				'Visible' => '000'
			),
			array('Title' => 'Actions', 'Calculated' => array($this, "calculateCompanyActions"), 'Visible' => '100')
		);

		$acl = Zend_Registry::get('_acl');
		$user = Zend_Auth::getInstance()->getIdentity();

		$employee = Model_Employee::retrieve()->fetchEntry(false, array(
			'ID', 'COMPANY_ID', new Data_Column('USER_ID', $user, Model_Employee::retrieve(), 'LOGIN')));

		/*
		 * set filter for Inspectors
		 * can see only building owners which are created by current inspection company
		 */
		if ($acl->inheritsRole($user, 'Inspection Company Employees')) {
			$columns['INSPECTION_COMPANY'] = array('Filter' => $employee['COMPANY_ID'],
				'Editable' => false,
				'Default' => $employee['COMPANY_ID'],
				'Visible' => '000');
		}

		parent::__construct($id, Model_Company::retrieve(), $parent, $parentLink, $columns);
	}

	public function calculateCompanyActions($entry) {
		//get Primary Contact ID for currently selected company
		$email = $entry['PRIMARY_CONTACT_EMAIL'];
		$actions = "";
		if ($email) {
			$address = strstr($email, 35); //35 is an ascii code for #
			$address = str_replace('#', '', $address);
			if ($address)
				$actions = "<a href='mailto:" . $address . "'>Email</a> | ";
		}
		
		$gridId = $this->getId();

		$actions = $actions .
				//"<a href='javascript: cmp_company.showDetailed()'>Edit</a> | " .
				//"<a href='javascript: cmp_company.deleteItems()'>Delete</a>";
				
				'<a data-original-title="Edit" title="" data-placement="top" class="btn btn-sm hover-blue-alt tooltip-button" 
						href="javascript: cmp_' . $gridId . '.showEditDialog('. $entry['ID'] .')"><i class="fa fa-edit">edit4</i>edit4</a>' .
    			'<a data-original-title="Remove" title="" data-placement="top" class="btn btn-sm hover-red tooltip-button" 
						href="javascript: cmp_' . $gridId . '.deleteItem('. $entry['ID'] .')"><i class="glyph-icon icon-remove"></i></a>';
				
		return $actions;
	}
}