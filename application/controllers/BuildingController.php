<?
require_once APPLICATION_PATH . '/models/Building.php';
require_once APPLICATION_PATH . '/models/Inspection.php';
require_once APPLICATION_PATH . '/models/Employee.php';
require_once APPLICATION_PATH . '/models/Door.php';
require_once APPLICATION_PATH . '/models/DoorType.php';
require_once APPLICATION_PATH . '/models/DoorCode.php';
require_once APPLICATION_PATH . '/controllers/Component.php';
require_once APPLICATION_PATH . '/components/adapters/Mico.php';
require_once APPLICATION_PATH . '/components/grids/InspectionGrid.php';
require_once APPLICATION_PATH . '/components/grids/BuildingGrid.php';
require_once APPLICATION_PATH . '/components/grids/DoorGrid.php';
require_once APPLICATION_PATH . '/components/grids/InspectsGrid.php';
require_once APPLICATION_PATH . '/components/grids/PhotobucketGrid.php';

class BuildingController extends Controller_Component {

	public function init() {
		$this->_helper->layout->setLayout('html');
    	//array will contain permissions for current user to grids in current tab
		$access = array();

		$inspectionId = $this->getParam('_super');

		/* buildings grid */
		$buildingGrid = $this->addGrid(new BuildingGrid('building'));    	
    	//get permissions for current user for current grid
		$access['building'] = $this->getAccessControl()->getGridPermissions('building');

		/* inspections grid */
		$buildingInspectionsGrid = $this->addGrid(new InspectionGrid('building_inspection', $this->getParam('_parent'), 'BUILDING_ID'));    		
		//get permissions for current user for current grid
		$access['building_inspection'] = $this->getAccessControl()->getGridPermissions('building_inspection');

		/* doors grid */
		$buildingDoors = $this->addGrid(new DoorGrid('building_inspection_door',null, null, $inspectionId));

		/* changes history grid */
		$buildingHistoryGrid = $this->addGrid(new InspectsGrid('building_inspection_history', null, null, $inspectionId));

		
		$hardwareGrid = $this->addGrid($buildingDoors->getHardwareGrid('hardware')); //###

		/* photobucket grid */
		$photobucketGrid  = $this->addGrid(new PhotobucketGrid('building_inspection_photobucket', $buildingInspectionsGrid, 'INSPECTION_ID'));
		
		$this->view->access = $access;

		parent::init();
	}
	
	
	public function buildingsAction(){
		
		$this->_initializeBratiliusGrid(array(
			'gridId' => 'building',
			'sortBy' => 'NAME',
			'sortDirection'=>1
		));
	}


	public function doorsAction(){
		$this->_initializeBratiliusGrid(array(
			'gridId' => 'building_inspection_door',
			'sortBy' => 'NUMBER'
		));
	}
	
	public function changesAction(){
		
		$this->_initializeBratiliusGrid(array(
			'gridId' => 'building_inspection_history',
			'sortBy' => 'ASSIGNED_DATE'
		));
	}
	
	public function changeAction(){
		$this->_detailedView('building_inspection_history');
		$this->view->grid = $this->_components['building_inspection_history'];
	}


	public function doorAction() {
		$doorId = $this->getParam('_parent');
		$inspectionId = $this->getParam('_super');
		
		$grid = $this->_components['building_inspection_door'];
		$grid->setSecurityOptions($inspectionId);
		
		$this->_detailedView('building_inspection_door'); 
    	$this->view->hardwareGrid = $this->_components['hardware']; //###
    	$this->view->doorGridId = 'building_inspection_door';
    	$fireRating2 = null;
    	
    	if ($grid->isReadonly()) $this->view->hardwareGrid->setReadonly();

		// get existing door codes and type
    	$this->view->codes = Model_Door::getCodes($doorId);
    	$this->view->types = Model_Door::getTypes($doorId);
    	$this->view->other = Model_Door::getOthers($doorId, $inspectionId);
    	$this->view->pictures = Model_Door::retrieve()->getPictures($doorId);
    	$this->view->audio    = Model_Door::retrieve()->getAudio($doorId);
    	$this->view->showMode = $this->getParam('_showMode');

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
		$this->log("building name: " . $building['NAME']);
	}

	public function photobucketAction() {
		$this->_detailedView('building_inspection_photobucket'); 
		$photobucketId = $this->getParam('_parent');

		if ($photobucketId > 0) {
			$this->view->photobucket = Model_Photobucket::retrieve()->fetchEntry($photobucketId);
		} else {
			$this->view->photobucket = array();
		}
	}

	public function doorsearchAction(){
		$this->view->grid = $this->_components['building_inspection_door'];
		$fireRating2All = Model_Dictionary::retrieve()->fetchEntries(
			array('ID', 'ITEM', 'VALUE_ORDER', new Data_Column('CATEGORY', 'Fire-Rating 2')), null, null, 'VALUE_ORDER');
		
		$this->view->fireRating2All = $fireRating2All;
	}

	public function detailviewAction() {
    	$this->_detailedView('building'); //this action is a detailed view for the 'building' grid
    	$this->view->grid = $this->_components['building'];
    	
    	$buildingId = $this->getParam("_parent");
    	if ((int)$buildingId > 0){
    		$building = Model_Building::retrieve()->fetchEntry($buildingId); 

    		$allEmployees = Model_Employee::retrieve()->fetchEntries(
    			array('ID', 'FIRST_NAME', 'LAST_NAME',
    				new Data_Column('COMPANY_ID', $building['CUSTOMER_ID']))
    		);
    		$this->view->allEmployees = $allEmployees;
    		$this->view->primaryContact = $building['PRIMARY_CONTACT'];
    	}
    	else{
    		$this->view->allEmployees = null;
    		$this->view->primaryContact = null;	
    	}
    }
    
    public function searchAction() {
    	if ($this->getParam('_model') == 'building_inspection') {
    		$buildingColumn = $this->_components['building_inspection']->getColumnById('BUILDING');
    		$buildingColumn->setSearchable(false);
    		$buildingColumn->setSearchDefault($this->getParam('_super'));
    	}
    	
    	parent::searchAction();	
    }
    
    public function inspectionsAction(){
    	$this->view->buildingId = $this->_getParam('_parent');
    	
    	$buildingData = Model_Building::retrieve()->fetchEntry($this->view->buildingId, array("NAME"));
    	$this->view->buildingName = $buildingData["NAME"];
    	$this->view->inspectionsGrid = $this->_components['building_inspection'];
    	$this->view->doorsGrid = $this->_components['building_inspection_door'];
    	$this->view->historyGrid = $this->_components['building_inspection_history'];
    	$this->view->photobucketGrid = $this->_components['building_inspection_photobucket'];
    	$this->view->doorGridId = "building_inspection_door";
    }


    public function bldinspectionsAction(){
    	$this->view->buildingId = $this->_getParam('_parent');
    	
    	$this->_initializeBratiliusGrid(array(
    		'gridId' => 'building_inspection',
    		'sortBy' => 'INSPECTION_DATE'
    	));

    }
    
    public function inspectionAction() {
    	if ($this->_getParam('_parent')){
    		//this is an existing inspection, we will need to show its details
    		$this->view->inspectionId = $this->_getParam('_parent');
    	}
    	else{
    		
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
    	//set building default and not editable
    	$column = $this->_components['building_inspection']->getColumnById('BUILDING');
    	$column->setDefault($this->getParam('_super'));
    	$column->setEditable(false);

    	$this->view->grid = $this->_components['building_inspection'];
    	$this->_detailedView('building_inspection');
    }
    
    public function saveAction($arg=null) {
    	if ($this->getRequest()->_model == 'building_inspection_door' && (!isset($this->getRequest()->id) && $this->getRequest()->id == '') ) 
    		return parent::saveAction(true);
    	else 
    		return parent::saveAction(false);    	
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
		if ($this->getParam('_model') == 'building_inspection' && $this->getParam('_column') == 'INSPECTOR_LAST_NAME') {
	    	$column = $this->_components['building_inspection']->getColumnById($this->getParam('_column'));
	    	$dropdownFilter = $column->getDropdownFilter();
	    	$dropdownFilter['parent'] = new Data_Column('COMPANY_ID', $this->getParam('_parentColumn'), Model_Inspection::retrieve(), 'NAME');
	    	$column->setDropdownFilter($dropdownFilter);
	    }
	    else if (($this->getParam('_model') == 'building') && ($this->getParam('_column') == 'PRIMARY_CONTACT_LAST_NAME')) {
	    	$column = $this->_components['building']->getColumnById($this->getParam('_column'));
	    	$dropdownFilter = $column->getDropdownFilter();
	    	if (trim($this->getParam('_parentCol')) == '')
	    		 $dropdownFilter['parent'] = new Data_Column('COMPANY_ID', '-1', Model_Employee::retrieve(), 'ID');
	    	else $dropdownFilter['parent'] = new Data_Column('COMPANY_ID', $this->getParam('_parentCol'), Model_Employee::retrieve(), 'ID');
	    	$column->setDropdownFilter($dropdownFilter);
	    }
		else if (($this->getParam('_model') == 'building') && ($this->getParam('_column') == 'INSPECTION_COMPANY')) {
	    	$column = $this->_components['building']->getColumnById($this->getParam('_column'));
	    	$dropdownFilter = $column->getDropdownFilter();
	    	if (trim($this->getParam('_parentCol')) != '')
	    		 $dropdownFilter['parent'] = new Data_Column('ID', $this->getParam('_parentCol'));
	    	$column->setDropdownFilter($dropdownFilter);
	    }
    	
    	parent::dropdownAction();
    }*/
    
	/*public function dropdownAction() {
		if (($this->getParam('_model') == 'building') &&
			($this->getParam('_column') == 'column_3')) {
				
			$allEmployees = Model_Employee::retrieve()->fetchEntries(
        						array('ID', 'FIRST_NAME', 'LAST_NAME',
        						 	  new Data_Column('COMPANY_ID', $building['CUSTOMER_ID']))
        					);
				
			// set output format to json and disable view rendering
	        $this->_helper->layout->setLayout('json');
	        $this->_helper->ViewRenderer->setNoRender(true);
	        
	        $result = array();
	        $result['identifier'] = 'ID';
	        $result['items']      = array();
	        
	        // add empty row if needed
	        if (!$noEmptyChoice && !$byId) {
	            $result['items'][] = array('ID'=>null, 'name'=>'', 'label'=>'');
			}
			foreach ($this->fetchEntries($dropdownFilter, $nameFilter, $parentFilter, $start, $byId) as $entry) {
	            $result['items'][] = array(
	                'ID'=>$entry['ID'], 'name' => $entry['NAME'], 'label' => $entry['NAME']);
	        }
	
	        return Zend_Json::encode($result);
        
	        // get filter
			$byId = true; $filter =  $this->getParam('id');
			if (!$filter) {$byId = false; $filter =  $this->getParam('name');}
	        if (!$filter) $filter = '*';
	        $parent = $this->getParam('_parent');
	        
	        // insert dropdown values into the layout
	        $requiredParam = $this->getParam('_required');
	        $this->view->placeholder('data')->set(
	            $this->_component->fetchReferenceValuesJson(
	                $this->getParam('_column'), $filter, $parent, $this->_fetchStart, $requiredParam, $byId));
		}
		else {
	    	parent::dropdownAction();
		}
	}*/

	public function excelexportAction(){
		return parent::excelAction($this->_getParam('gridId'));
	}

}