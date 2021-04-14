<?
require_once APPLICATION_PATH . '/components/CustomGrid.php';

class InspectionGrid extends Cmp_Custom_Grid {
	public function __construct($id, $parent=null, $parentLink=null) {
		
		$this->_usePaginator = true;
		
		// define columns
		$columns = array(
			'ID'	=> array('Id' => 'ID', 'Visible' => '000'),
    		'TEMPLATE_ID'              => array(
    			'Id'			=> 'TEMPLATE',
				'Visible'        => '010', 
    			'Title'          => 'Template',
    			'DropdownFilter' => new Data_Column('CATEGORY', 'Template', Model_Dictionary::retrieve()),
				'Default'		 => Model_Inspection::DEFAULT_TEMPLATE),
					
		    'BUILDING_ID'        => array('Title' => 'Building',
		    							  'Display' => 'NAME',
		    							  'Id' => 'BUILDING',
		    							  'DropdownFilter' => array(),
										  'Width' => '33%',
										  'Size' => '2'),
			'building_address'    => array('Field' => array(
    	    							'BUILDING_ID' => Model_Building::retrieve()
    								),
    							  'Display' => 'ADDRESS_1',
    							  'Id'	=> 'BUILDING_ADDRESS',
    							  'Editable' => false,
    						      'Title' => 'Building Address', 
    						      'Width' => '33%',
    							  'Visible' => '100'
    		),
    		
			'building_owner'    => array('Field' => array(
    	    							'BUILDING_ID' => Model_Building::retrieve(),
    									'CUSTOMER_ID' => Model_Company::retrieve()
    								),
    							  'Display' => 'NAME',
    							  'Id'	=> 'BUILDING_OWNER',
    							  'Editable' => false,
    						      'Title' => 'Building Owner', 
    						      'Width' => '33%',
    							  'Visible' => '100'
    		),
    		

			
		    'COMPANY_ID'         => array(
				'Title'          => 'Inspection Company',
		        'Display'        => 'NAME',
				'Width'		     => '33%',
			    'Id'	         => 'company',
		        'DropdownFilter' => array(new Data_Column('TYPE', Model_Company::INSPECTION_COMPANY)),
		        'Size'		 => 2,
		        'Visible'		 => '011'
			),
				
            'INSPECTION_DATE'          => array('Title' => 'Start Date', 'Width' => '72px', 'View' => Data_Column_Grid::DATE, 'Id' => 'INSPECTION_DATE'),
			
            'INSPECTION_COMPLETE_DATE' => array('Title' => 'Completed', 'Width' => '72px', 'View' => Data_Column_Grid::DATE, 'Id'=>'INSPECTION_COMPLETE_DATE'),
			
            'REINSPECT_DATE'		   => array('Title' => 'Reinspect', 'Width' => '72px', 'Id'=>'REINSPECT_DATE'),
			
            'SIGNATURE_INSPECTOR' 	   => array('Hidden' => true, 'View' => Data_Column_Grid::IMAGE, 'Visible' => '001'),
			
            'SIGNATURE_BUILDING'       => array('Hidden' => true, 'View' => Data_Column_Grid::IMAGE, 'Visible' => '001'),
			
            'STATUS'                   => array(
				'Id' => 'status',
				'DropdownFilter' => array(new Data_Column('category', 'Inspection Status', Model_Dictionary::retrieve())),
		        'Default' => '1080', 'Editable' => true, 'Visible' => '111', 'Width' => '65px'),
				 
            'SUMMARY' 			       => array(
            	'Id'	=> 'SUMMARY',
            	'View' => Data_Column_Grid::MEMO, 
            	'Visible' => '011'
			),
			
    		'INSPECTOR_ID'             => array(
				'Title' 	     => 'Inspector',
				'Id'			 =>	'INSPECTOR',
        		'Display' 	     => 'LAST_NAME',
				'Width'		     => '33%', 
				'ParentColumns'  => array('COMPANY'),
				'DropdownFilter' => array(
						new Data_Column(array(
								'USER_ID'	=> Model_User::retrieve(),
								'$USER_ID' => Model_User_Role::retrieve(),
								'ROLE_ID'	=> Model_Role::retrieve()
    	            		), array('Field Inspectors', 'Inspectors'), Model_Employee::retrieve(), 'NAME')
    	         ),
    	         'Size'		=> '2',
    	         'Visible' => '011'
    	     ),
    	     
		    array('Title' => 'Inspector', 'Calculated' => array($this, 'calculateInspectorName'), 'Width' => '33%'),
					 
			array('Title' => 'Actions', 'Calculated' => array($this, "calculateInspectionActions"), 'Width' => '300px', 'Size' => '2'),
			//array('Id' => 'Editable', 'Title' => 'Access Rights', 'Calculated' => array($this, "calculateEditable"), 'Visible' => '100', 'Width' => '200px')
		);
		
		$acl  = Zend_Registry::get('_acl');
		$user = Zend_Auth::getInstance()->getIdentity();
		
		// get user's company information
		$employee = Model_Employee::retrieve()->fetchEntry(false, array(
			'COMPANY_ID', new Data_Column('USER_ID', $user, Model_Employee::retrieve(), 'LOGIN'),
			'COMPANY_NAME' => new Data_Column('COMPANY_ID', null, Model_Employee::retrieve(), 'NAME')));
    	
    	// specific grants for building owners and their employees
		if ($acl->inheritsRole($user, 'Building Owner Employees')) {
			$inspectionCompany = Model_Company::retrieve()->fetchEntry($employee['COMPANY_ID']);
						
    		// we should only show inspections for current building owner
        	$columns[] = array('Field' 		=> array(
										'BUILDING_ID' => Model_Building::retrieve(),
										'CUSTOMER_ID' => Model_Company::retrieve()),
        					   'Filter'	  	=> $employee['COMPANY_ID'],
        					   'Editable'	=> false, 'Visible'	=> '000');
			// also we should only show buildings belonging to current building owner 
        	$columns['BUILDING_ID']['DropdownFilter'][] = new Data_Column(
				'CUSTOMER_ID', $employee['COMPANY_ID']);
        	$columns['COMPANY_ID']['Editable'] = false;
        	$columns['COMPANY_ID']['Default'] = $inspectionCompany['INSPECTION_COMPANY'];
        	$columns['COMPANY_ID']['Searchable']= false; 
    		$columns['COMPANY_ID']['SearchDefault']=  $inspectionCompany['INSPECTION_COMPANY']; 
			$columns['INSPECTOR_ID']['DropdownFilter'][] = new Data_Column('COMPANY_ID', $inspectionCompany['INSPECTION_COMPANY']);
        }
        
		// special grants for inspection companies and their employees
    	if ($acl->inheritsRole($user, 'Inspection Company Employees')) {
    		// filter inspections by current inspection company
			$columns['COMPANY_ID']['Filter']   	= $employee['COMPANY_NAME'];
    		$columns['COMPANY_ID']['Editable'] 	= false;
    		$columns['COMPANY_ID']['Default'] 	= $employee['COMPANY_ID'];
    		$columns['COMPANY_ID']['Searchable']= false; 
    		$columns['COMPANY_ID']['SearchDefault']= $employee['COMPANY_ID']; 
    		    		
        	// filter buildings only belonging to current inspection company's clients
        	$columns['BUILDING_ID']['DropdownFilter'][] = new Data_Column(
					'CUSTOMER_ID', $employee['COMPANY_ID'], Model_Building::retrieve(), 'INSPECTION_COMPANY');
			
			$columns['INSPECTOR_ID']['DropdownFilter'][] = new Data_Column(
					'COMPANY_ID', $employee['COMPANY_ID']);
			
    	}

    	//in case we are on specific building owner, show only their buildings
		if ($parent != null){
			if ( (is_array($parentLink)==true && array_key_exists('CUSTOMER_ID', $parentLink)) || $parentLink == 'CUSTOMER_ID')
				$columns['BUILDING_ID']['DropdownFilter'][] = new Data_Column('CUSTOMER_ID', $parent);
		}

		parent::__construct($id, Model_Inspection::retrieve(), $parent, $parentLink, $columns);
	}

	public function calculateInspectorName($entry){
		$inspectorId = $entry['INSPECTOR_ID'];
		
		if (!$inspectorId) return '';
		
		$employee = Model_Employee::retrieve()->fetchEntry($inspectorId);
		
		return substr($employee['FIRST_NAME'], 0, 1) . '. ' . $employee['LAST_NAME'];
	}

	public function calculateEditable($entry){
		$gridId = $this->getId();
    	$statusColumn = current($this->getColumnsByField('STATUS'));
    	$idColumn = current($this->getColumnsByField('ID'));
    	$inspectionStatus = $entry[$statusColumn->getId() . '_ID']; //a digit
    	$inspectionId = $entry[$idColumn->getId()];
		
		$acl  = Zend_Registry::get('_acl');
		$user = Zend_Auth::getInstance()->getIdentity();
		
		$result = false;
		
		if ($acl->inheritsRole($user, 'Building Owner Employees')) {
			switch ($inspectionStatus){
	    		case Model_Inspection::PENDING: 
	    			//this is a New inspection, you can do everything except of unlock since the inspection is not locked
	    			$result = true;
	    			break;
	    		case Model_Inspection::INCOMPLETED: 
	    			//this is incompleted inspection, you can do everything except of unlock (inspection is not locked)
	    			$result = true;
	    			break;
				default: break;
	    	}
		}
		elseif ($acl->inheritsRole($user, 'Web Users')) {
			switch ($inspectionStatus){
	    		case Model_Inspection::PENDING: 
	    			//this is a New inspection, you can do everything except of unlock since the inspection is not locked
	    			$result = true;
	    			break;
	    		case Model_Inspection::INCOMPLETED: 
	    			//this is incompleted inspection, you can do everything except of unlock (inspection is not locked)
	    			$result = true;
	    			break;
				default: break;
	    	}
		}
		else {
			switch ($inspectionStatus){
	    		case Model_Inspection::PENDING: 
	    			//this is a New inspection, you can do everything except of unlock since the inspection is not locked
	    			$result = true;
	    			break;
	    		case Model_Inspection::INCOMPLETED: 
	    			//this is incompleted inspection, you can do everything except of unlock (inspection is not locked)
	    			$result = true;
	    			break;
				default: break;
	    	}
		}
		
		return $result;
	}
	
	public function calculateInspectionActions($entry){
		
		$gridId = $this->getId();
    	$statusColumn = current($this->getColumnsByField('STATUS'));
    	$idColumn = current($this->getColumnsByField('ID'));
    	$inspectionStatus = $entry[$statusColumn->getId() . '_ID']; //a digit
    	$inspectionId = $entry[$idColumn->getId()];
    	
    	$acl  = Zend_Registry::get('_acl');
		$user = Zend_Auth::getInstance()->getIdentity();
		
		$actionHead = '<div class="dropdown action-dropdown">
  <a data-toggle="dropdown">
   -Select-
     <i class="glyph-icon icon-chevron-down"></i>
  </a><div class="dropdown-menu float-right"><div class="">';
		$actionFoot = '</div></div></div>';

		$edit 	= '<div class="pad5A button-pane button-pane-alt text-center"><a data-original-title="Edit" title="Edit" data-placement="top" class="btn btn-sm hover-blue-alt tooltip-button" 
						href="javascript: cmp_' . $gridId . '.showEditDialog('. $entry['ID'] .')"><i class="fa fa-edit">Edit</i>Edit</a></div>';
		$editDisabled 	= '<div class="pad5A button-pane button-pane-alt text-center"><a data-original-title="Edit" title="Edit" data-placement="top" class="btn btn-sm tooltip-button" style="color: gray;"
						href="#"><i class="fa fa-edit">edita</i>edita</a></div>';				
	
		$view   = '<div class="pad5A button-pane button-pane-alt text-center"><a data-original-title="View" title="View" data-placement="top" class="btn btn-sm hover-blue-alt tooltip-button" 
						href="javascript: cmp_' . $gridId . '.showEditDialog('. $entry['ID'] .')"><i class="fa fa-edit">editb</i>editb</a></div>';
		
		$viewPDF = '<div class="pad5A button-pane button-pane-alt text-center"><a data-original-title="View PDF" title="View PDF" data-placement="top" class="btn btn-sm hover-blue-alt tooltip-button" 
						href="javascript: openDialog(\'/inspection/pdf?_parent=' . $inspectionId .'\')"><i class="glyph-icon icon-print"></i></a></div>';
		$viewPDFDisabled = '<div class="pad5A button-pane button-pane-alt text-center"><a data-original-title="View PDF" title="View PDF" data-placement="top" class="btn btn-sm tooltip-button" style="color: gray;"
						href="#"><i class="glyph-icon icon-print"></i></a></div>';
		
		$import = '<div class="pad5A button-pane button-pane-alt text-center"><a data-original-title="Import" title="Import" data-placement="top" class="btn btn-sm hover-blue-alt tooltip-button" 
						href="javascript: openDialog(\'/inspection/import?_parent=' . $inspectionId .'\')"><i class="glyph-icon icon-sign-in"></i></a></div>';
		
		$importDisabled = '<div class="pad5A button-pane button-pane-alt text-center"><a data-original-title="Import" title="Import" data-placement="top" class="btn btn-sm tooltip-button" style="color: gray;"
						href="javascript: openDialog(\'/inspection/import?_parent=' . $inspectionId .'\')"><i class="glyph-icon icon-sign-in"></i></a></div>';
		
		$assign = "<div class='pad5A button-pane button-pane-alt text-center'><a class='' href='javascript: cmp_" . $gridId . ".assignInspection()'>Assign</a></div>";
		$delete = '<div class="pad5A button-pane button-pane-alt text-center"><a data-original-title="Delete" title="Delete" data-placement="top" class="btn btn-sm hover-red tooltip-button" 
						href="javascript: cmp_' . $gridId . '.deleteItem('. $entry['ID'] .')"><i class="glyph-icon icon-remove"></i></a></div>';
		$deleteDisabled = '<div class="pad5A button-pane button-pane-alt text-center"><a data-original-title="Delete" title="Delete" data-placement="top" class="btn btn-sm tooltip-button" style="color: gray;"
						href="#"><i class="glyph-icon icon-remove"></i></a></div>';
						
		$unlock = '<div class="pad5A button-pane button-pane-alt text-center"><a data-original-title="Unlock" title="" data-placement="top" class="btn btn-sm hover-blue-alt tooltip-button" 
						href="javascript: cmp_' . $gridId . '.unlockInspection(' . $inspectionId . ')"><i class="glyph-icon icon-lock"></i></a></div>';
						
		$unlockDisabled = '<div class="pad5A button-pane button-pane-alt text-center"><a data-original-title="Unlock" title="Unlock" data-placement="top" class="btn btn-sm tooltip-button" style="color: gray;"
						href="#"><i class="glyph-icon icon-lock"></i></a></div>';
		
		$reinspect = '<div class="pad5A button-pane button-pane-alt text-center"><a data-original-title="Reinspect" title="Reinspect" data-placement="top" class="btn btn-sm hover-blue-alt tooltip-button" 
						href="javascript: openDialog(\'/inspection/reinspect?inspectionId=' . $inspectionId .'\')"><i class="glyph-icon icon-retweet"></i></a></div>';
		$reinspectDisabled = '<div class="pad5A button-pane button-pane-alt text-center"><a data-original-title="Reinspect" title="Reinspect" data-placement="top" class="btn btn-sm tooltip-button" style="color: gray;"
						href="#"><i class="glyph-icon icon-retweet"></i></a></div>';
		
			
		if ($acl->inheritsRole($user, 'Building Owner Employees')) {
			switch ($inspectionStatus){
	    		case Model_Inspection::PENDING: 
	    			//this is a New inspection, you can do everything except of unlock since the inspection is not locked
	    			//$actions = "$edit | $viewPDF | Assign";
					$actions = $actionHead . $edit . $viewPDF . $actionFoot;
		
	    			break;
				case Model_Inspection::SUBMITTING: 
					//inspection is being assigned, you can not do anything	at all
	    			//$actions = "Edit | View PDF | Assign";
	    			$actions = $actionHead . $editDisabled . $viewPDFDisabled . $actionFoot;
	    		case Model_Inspection::SUBMITTED: 
	    			//this is an assigned inspection, you can only view PDF or unlock
	    			//$actions = "$view | $viewPDF | Assign";
	    			$actions = $actionHead . $view . $viewPDF . $actionFoot;
	    			break;
	    		case Model_Inspection::INCOMPLETED: 
	    			//this is incompleted inspection, you can do everything except of unlock (inspection is not locked)
	    			//$actions = "$edit | $viewPDF | Assign";
					$actions = $actionHead . $edit . $viewPDF . $actionFoot;
	    			break;
	    		case Model_Inspection::COMPLETED: 
	    			//this is an assigned inspection, you can only view PDF or unlock
	    			//$actions = "$view | $viewPDF | Assign";
					$actions = $actionHead . $view . $viewPDF . $actionFoot;
	    			break;
	    	}
		}
		elseif ($acl->inheritsRole($user, 'Web Users')) {
			switch ($inspectionStatus){
	    		case Model_Inspection::PENDING: 
	    			//this is a New inspection, you can do everything except of unlock since the inspection is not locked
	    			//$actions = "$edit | $viewPDF | $import | $assign | Delete | Unlock | Reinspect";
					$actions = $actionHead .  $edit . $viewPDF . $import . $deleteDisabled . $unlockDisabled . $reinspectDisabled . $actionFoot;
	    			break;
				case Model_Inspection::SUBMITTING: 
					//inspection is being assigned, you can not do anything	at all
	    			//$actions = "$view | View PDF | Import | Assign | Delete | $unlock | Reinspect";
					$actions = $actionHead . $view . $viewPDFDisabled . $importDisabled . $deleteDisabled . $unlock . $reinspectDisabled . $actionFoot;
	    			break;
	    		case Model_Inspection::SUBMITTED: 
	    			//this is an assigned inspection, you can only view PDF or unlock
	    			//$actions = "$view | $viewPDF | Import | Assign | Delete | $unlock | Reinspect";
					$actions = $actionHead .  $view . $viewPDF . $importDisabled . $delete . $unlock . $reinspectDisabled . $actionFoot;
	    			break;
	    		case Model_Inspection::INCOMPLETED: 
	    			//this is incompleted inspection, you can do everything except of unlock (inspection is not locked)
	    			//$actions = "$edit | $viewPDF | $import | $assign | Delete | Unlock | Reinspect";
					$actions = $actionHead . $edit . $viewPDF . $import . $deleteDisabled . $unlockDisabled . $reinspectDisabled . $actionFoot;
	    			break;
	    		case Model_Inspection::COMPLETED: 
	    			//this is an assigned inspection, you can only view PDF or unlock
	    			//$actions = "$view | $viewPDF | Import | Assign | Delete | $unlock | $reinspect";
	    			$actions = $actionHead . $view . $viewPDF . $importDisabled . $deleteDisabled . $unlock . $reinspect . $actionFoot;
	    			break;
	    	}
		}
		else {
			switch ($inspectionStatus){
	    		case Model_Inspection::PENDING: 
	    			//this is a New inspection, you can do everything except of unlock since the inspection is not locked
	    			//$actions = "$edit | $viewPDF | $import | $assign | $delete | Unlock | Reinspect";
	    			$actions = $actionHead . $edit . $viewPDF . $import . $delete . $unlockDisabled . $reinspectDisabled . $actionFoot;
	    			break;
				case Model_Inspection::SUBMITTING: 
					//inspection is being assigned, you can not do anything	at all
	    			//$actions = "$view | View PDF | Import | Assign | Delete | $unlock | Reinspect";
	    			$actions = $actionHead . $view . $viewPDF . $importDisabled . $deleteDisabled . $unlock . $reinspectDisabled . $actionFoot;
	    			break;
	    		case Model_Inspection::SUBMITTED: 
	    			//this is an assigned inspection, you can only view PDF or unlock
	    			//$actions = "$view | $viewPDF | Import | Assign | Delete | $unlock | Reinspect";
	    			$actions = $actionHead .  $view . $viewPDF . $importDisabled . $deleteDisabled . $unlock . $reinspectDisabled . $actionFoot;
	    			break;
	    		case Model_Inspection::INCOMPLETED: 
	    			//this is incompleted inspection, you can do everything except of unlock (inspection is not locked)
	    			//$actions = "$edit | $viewPDF | $import | $assign | $delete | Unlock | Reinspect";
					$actions = $actionHead . $edit . $viewPDF . $import . $delete . $unlockDisabled . $reinspectDisabled . $actionFoot;
	    			break;
	    		case Model_Inspection::COMPLETED: 
	    			//this is an assigned inspection, you can only view PDF or unlock
	    			//$actions = "$view | $viewPDF | Import | Assign | Delete | $unlock | $reinspect";
	    			$actions = $actionHead . $view . $viewPDF . $importDisabled . $deleteDisabled . $unlock . $reinspect . $actionFoot;
	    			break;
	    	}
		}
		return $actions;
    }
}
