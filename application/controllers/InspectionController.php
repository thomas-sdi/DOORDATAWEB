<?
require_once APPLICATION_PATH . '/models/Inspection.php';
require_once APPLICATION_PATH . '/models/Inspect.php';
require_once APPLICATION_PATH . '/models/Employee.php';
require_once APPLICATION_PATH . '/models/Building.php';
require_once APPLICATION_PATH . '/models/User.php';
require_once APPLICATION_PATH . '/models/Door.php';
require_once APPLICATION_PATH . '/models/DoorCode.php';
require_once APPLICATION_PATH . '/models/DoorType.php';
require_once APPLICATION_PATH . '/models/Hardware.php';
require_once APPLICATION_PATH . '/models/Company.php';
require_once APPLICATION_PATH . '/models/Picture.php';
require_once APPLICATION_PATH . '/components/data/Column.php';
require_once APPLICATION_PATH . '/controllers/Component.php';
require_once APPLICATION_PATH . '/components/adapters/Mico.php';
require_once APPLICATION_PATH . '/controllers/plugins/AccessControl.php';
require_once APPLICATION_PATH . '/components/grids/InspectionGrid.php';
require_once APPLICATION_PATH . '/components/grids/InspectsGrid.php';
require_once APPLICATION_PATH . '/components/grids/DoorGrid.php';
require_once APPLICATION_PATH . '/components/grids/PhotobucketGrid.php';
require_once APPLICATION_PATH . '/components/forms/ReinspectForm.php';

class InspectionController extends Controller_Component {

  public function init() {
   $this->_helper->layout->setLayout('html');

   /* inspection grid */
   $inspectionGrid = $this->addGrid(new InspectionGrid('inspection'));

   $inspectionId = $this->getParam('_super');

   /* changes history grid */
		//$inspectsGrid = $this->addGrid(new InspectsGrid('inspection_inspects', $inspectionGrid, 'INSPECTION_ID'));
   $inspectsGrid = $this->addGrid(new InspectsGrid('inspection_inspects', null, null, $inspectionId));

   /* door grid */
		//$doorGrid = $this->addGrid(new DoorGrid('inspection_door', $inspectionGrid, 'inspection_id'));
   $doorGrid = $this->addGrid(new DoorGrid('inspection_door', null, null, $inspectionId));

   /* door hardware grid */
		$hardwareGrid = $this->addGrid($doorGrid->getHardwareGrid('hardware')); //###
		

		/* photobucket grid */
		$photobucketGrid  = $this->addGrid(new PhotobucketGrid('inspection_photobucket', $inspectionGrid, 'INSPECTION_ID'));
		
		// reinspection form
		$this->addForm(new ReinspectForm('reinspect'));
		
    parent::init();
  }
  
  public function xmlAction() {
    	// make sure no specific view is associated with this action 
   $this->_helper->layout->setLayout('json');
   $this->_helper->ViewRenderer->setNoRender(true);
   
   $errorMsg = 'Error during inspection assignment: ';
   $id = $this->getParam('_id');

   $continue = true;

   while ($continue === true) {
     $inspection = Model_Inspection::retrieve()->fetchEntry($id);

     if (!$inspection){
      $result = $errorMsg . 'inspection not found';
      $continue = false;
      break;
    }

			//if inspector is not populated on the inspection, we can not continue the assign process

    if(!$inspection['INSPECTOR_ID']){
      $result = $errorMsg . 'please fill in the Inspector field on the inspection header.';
      $continue = false;
      break;
    }

    $inspector_pag = Model_Employee::retrieve()->fetchEntries(array(
      'USER_ID', 
      'ID' => new Data_Column('ID', $inspection['INSPECTOR_ID']),
      'FIRST_NAME', 'LAST_NAME'
    ));


    if ($inspector_pag->getTotalItemCount() > 0) 
      $inspector = $inspector_pag->getItem(1);
    else {
      $result = $errorMsg . 'inspector does not have assigned employee!';
      $continue = false;
      break;
    }

    $user_pag = Model_User::retrieve()->fetchEntries(array(
      'LOGIN', 
      'USER_ID' => new Data_Column('ID', $inspector['USER_ID'])
    ));

    if ($user_pag->getTotalItemCount() > 0) 
      $user = $user_pag->getItem(1);
    else {
      $result = $errorMsg . 'inspector does not have assigned system user (login)!';
      $continue = false;
      break;
    }

    if($inspection['STATUS'] == Model_Inspection::COMPLETED){
      $result = $errorMsg . 'inspection is already completed; please unlock it first.';
      $continue = false;
      break;
    }
			if($inspection['STATUS'] == Model_Inspection::SUBMITTED){ //Assigned
				$result = $errorMsg . 'inspection is already assigned to ' . $inspector['FIRST_NAME'] . ' ' . $inspector['LAST_NAME'];
				$continue = false;
				break;
			}
			if($inspection['STATUS'] == Model_Inspection::SUBMITTING){ //Assigning
				$result = $errorMsg . 'assign process in progress, can not terminate';
				$continue = false;
				break;
			}
			
			//App::log($id);
			
			//$mico = new Adapter_Mico(Zend_Registry::getInstance()->configuration->service->mico->url . '?name=' . urlencode($user['LOGIN']));
        	//$result = $mico->create($id);
			
			//App::log($result);
			
			Model_Inspection::retrieve()->save(array(
				'ID'=> $id,
				'STATUS'=> Model_Inspection::SUBMITTED,
				'INSPECTOR_ID'	=> $inspector['ID']
			));
			
			$result = 'Inspection successfully assigned to ' .$inspector['FIRST_NAME'] . ' ' . $inspector['LAST_NAME'];
			$continue = false;
     break;
   }

   $this->view->placeholder('data')->set($result);
 }

 public function unlockAction(){
    	// make sure no specific view is associated with this action 
   $this->_helper->layout->setLayout('json');
   $this->_helper->ViewRenderer->setNoRender(true);

   $result = "";

        //get inspection id parameter
   $inspectionId = $this->getParam("_id");
   $inspection = Model_Inspection::retrieve()->fetchEntry($inspectionId);
   if (!$inspection ){
    $result = "Inspection does not exist, please refresh the page.";
    $this->view->placeholder('data')->set($result);
    return;
  }

  $data = array();
  switch($inspection['STATUS']){
    case Model_Inspection::PENDING:
    $result = "This inspection is not locked, inspection status is 'New'.";
    break;
    case Model_Inspection::SUBMITTING:
    			//$result = "This inspeciton is being assigned right now, can not unlock it. Please try again later.";
    $data['ID'] = $inspectionId;
    $data['STATUS'] = Model_Inspection::INCOMPLETED;
    Model_Inspection::retrieve()->save($data, null);
    $result = "Inspection was successfully unlocked. You can now make changes in the inspection and its doors.";
    break;
    case Model_Inspection::SUBMITTED:
    $data['ID'] = $inspectionId;
    $data['STATUS'] = Model_Inspection::INCOMPLETED;
    Model_Inspection::retrieve()->save($data, null);
    $result = "Inspection was successfully unlocked. You can now make changes in the inspection and its doors.";
    break;
    case Model_Inspection::INCOMPLETED:
    $result = "This inspeciton is not locked, inspection status is 'Incomplete'.";
    break;
    case Model_Inspection::COMPLETED:
    $data['ID'] = $inspectionId;
    $data['STATUS'] = Model_Inspection::INCOMPLETED;
    Model_Inspection::retrieve()->save($data, null);
    $result = "Inspection was successfully unlocked. You can now make changes in the inspection and its doors.";
    break;
  }
  $this->view->placeholder('data')->set($result);
}

public function doorAction() {
		// get existing door codes and type
  $doorId = $this->getParam('_parent');
  $inspectionId = $this->getParam('_super');

  $fireRating2 = null;

  $grid = $this->_components['inspection_door'];
  $grid->setSecurityOptions($inspectionId);

  $this->_detailedView('inspection_door'); 
    	$this->view->hardwareGrid = $this->_components['hardware']; //###
    	$this->view->doorGridId = 'inspection_door';

      if ($grid->isReadonly()) $this->view->hardwareGrid->setReadonly();

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
		// $building = Model_Inspection::retrieve()->fetchEntry(null,
      // array('BUILDING_ID', new Data_Column('ID', $inspectionId)));
		
    $building = Model_Door::retrieve()->fetchEntry(null,
      array('BUILDING_ID', new Data_Column('ID', $doorId)));

		$building = Model_Building::retrieve()->fetchEntry($building['BUILDING_ID']);

		$this->view->buildingName = $building['NAME'];
  }
  
  public function doorsearchAction(){
   $this->view->grid = $this->_components['inspection_door'];
   $fireRating2All = Model_Dictionary::retrieve()->fetchEntries(
     array('ID', 'ITEM', 'VALUE_ORDER', new Data_Column('CATEGORY', 'Fire-Rating 2')), null, null, 'VALUE_ORDER');

   $this->view->fireRating2All = $fireRating2All;
 }

 public function photobucketAction() {
   $this->_detailedView('inspection_photobucket'); 
   $photobucketId = $this->getParam('_parent');

   if ($photobucketId > 0) {
    $this->view->photobucket = Model_Photobucket::retrieve()->fetchEntry($photobucketId);
    $grid = $this->_components['inspection_photobucket'];
    if ($grid->isReadonly($this->view->photobucket['INSPECTION_ID'])) $grid->setReadonly();
  } else {
    $this->view->photobucket = array();
  }
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
  $this->_getParam('picture_notes'),
  $this->getParam('picture_order'));
 $result['picture'] = current($picture) != '' ? $this->view->baseUrl . current($picture) : null;
			//Zend_Registry::get('logger')->info('Picture: ' . current($picture));
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

public function deletepictureAction() {
  $this->_helper->ViewRenderer->setNoRender(true);
  $this->_helper->layout->setLayout("json");

  $result = array();
  try {
    if (strlen($this->_getParam('picture')) > 0) {
     Model_Picture::retrieve()->delete($this->_getParam('picture'));
     $result['status'] = 'ok';
   } else {
     throw new Exception('Picture id is undefined');
   }
 } catch (Exception $e) {
  $result['status'] = 'failed';
  $result['message'] = $e->getMessage();
}
$this->view->placeholder('data')->set(Zend_Json::encode($result));
}

public function deleteaudioAction() {
  $this->_helper->ViewRenderer->setNoRender(true);
  $this->_helper->layout->setLayout("json");

  $result = array();
  try {
    if (strlen($this->_getParam('audio')) > 0) {
     Model_Audio::retrieve()->delete($this->_getParam('audio'));
     $result['status'] = 'ok';
   } else {
     throw new Exception('Audio id is undefined');
   }
 } catch (Exception $e) {
  $result['status'] = 'failed';
  $result['message'] = $e->getMessage();
}
$this->view->placeholder('data')->set(Zend_Json::encode($result));
}

public function rotatepictureAction() {
  $this->_helper->ViewRenderer->setNoRender(true);
  $this->_helper->layout->setLayout("json");

  $result = array();
  try {
    if (strlen($this->_getParam('picture')) > 0) {
     $picture = Model_Picture::retrieve()->fetchEntry($this->_getParam('picture'));
     if (strlen($picture['PICTURE_FILE']) > 0) {
      $ext = strtolower(array_pop(explode('.', $picture['PICTURE_FILE'])));
      $source_pic = ROOT_PATH . '/content/pictures/' . $picture['PICTURE_FILE'];
    				// Load
      switch ($ext) {
       case ($ext == 'gif'):
       $source = imagecreatefromgif($source_pic); break;
       case ($ext == 'jpeg' || $ext == 'jpg'):
       $source = imagecreatefromjpeg($source_pic); break;
       case ($ext == 'png'):
       $source = imagecreatefrompng($source_pic); break;
     }

     $degrees = -90;

    				// Rotate
     $rotate = imagerotate($source, $degrees, 0);

     $picFile = 'pict_door_' . $picture['DOOR_ID'] . '_' . date('YmdHis') . '.' . $ext;
     $dest_pic = ROOT_PATH . '/content/pictures/' . $picFile;
     unlink($source_pic);
					// Output
     switch ($ext) {
       case ($ext == 'gif'):
       imagegif($rotate, $dest_pic); break;
       case ($ext == 'jpeg' || $ext == 'jpg'):
       imagejpeg($rotate, $dest_pic); break;
       case ($ext == 'png'):
       imagepng($rotate, $dest_pic); break;
     }
     $picture['PICTURE_FILE'] = $picFile;
     $picture['ROTATION'] = '0';
     Model_Picture::retrieve()->save($picture);
   }
   $result['status'] = 'ok';
   // $result['picture'] = $this->view->baseUrl . '/content/pictures?id=' . $picFile;
   $result['picture'] = $this->view->baseUrl . '/content/pictures/' . $picFile;
 } else {
   throw new Exception('Picture id is undefined');
 }
} catch (Exception $e) {
  $result['status'] = 'failed';
  $result['message'] = $e->getMessage();
}
$this->view->placeholder('data')->set(Zend_Json::encode($result));
}

public function hardwareAction()
{  
  $doorId = $this->_getParam('doorId');
  $db = Zend_Registry::getInstance()->dbAdapter;
  $sql = "select * from hardware where DOOR_ID=".$doorId." order by ID asc";
  $this->view->hardwareRows = $db->fetchAll($sql);
  $this->view->doorId = $doorId;
}

public function saveAction($arg=false) {

  $model = $this->getRequest()->_model; 

  if ($model == 'inspection_door' && (!isset($this->getRequest()->id) && $this->getRequest()->id == '') ) {
    parent::saveAction(true);
  }else{
    parent::saveAction(false);
  }

}




public function deletedoorAction() {
 $this->_helper->ViewRenderer->setNoRender(true);
 $this->_helper->layout->setLayout("json");

 Model_Door::retrieve()->delete($this->_params['_id']);
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
    		//$this->log('new inspection id: '.$this->view->inspectionId);
}
$this->view->grid = $this->_components['inspection'];
$this->_detailedView('inspection');
}

public function pdfAction(){
		//Fill CONTROL_NAME field in all pictures
  Model_Picture::retrieve()->setControlNames();

  $pdfFolderURl = Zend_Controller_Front::getInstance()->getBaseUrl() . Zend_Registry::getInstance()->configuration->service->pdf->folder;

  $this->view->inspectionId = $this->_getParam('_parent');
  $inspection = Model_Inspection::retrieve()->fetchEntry($this->_getParam('_parent'));

  $doors = Model_Door::retrieve()->fetchEntries(array('ID', new Data_Column('INSPECTION_ID', $this->_getParam('_parent'))), null, true);
  $doors_number = $doors->getTotalItemCount();
  if ($doors_number > 0) {
    $this->view->doors_number_from = 1;
    $this->view->doors_number = $doors_number;
    $this->view->doors_number10 = ($doors_number % 10 == 0) ? ceil($doors_number / 10) + 1 : ceil($doors_number / 10);
    $this->view->doors_number16 = ($doors_number % 16 == 0) ? ceil($doors_number / 16) + 1 : ceil($doors_number / 16);
  }
  else
    $this->view->doors_number_from = $this->view->doors_number = 
  $this->view->doors_number10    = $this->view->doors_number16 = '';

    	//retrieve previous pdf and select its status
  $report = $inspection['PDF'];
  if (strlen($report) == 0) {
    $this->view->reportGenerated = false;
    $this->view->reportRetrieved = false;
  } else if (strpos($report, 'http://') === 0 || strpos($report, 'https://') === 0) {
    $this->view->reportGenerated = true;
    $this->view->reportRetrieved = false;
  } else if (strpos($report, '/') === 0) {
    $this->view->reportGenerated = true;
    $this->view->reportRetrieved = true;
    $report = $pdfFolderURl . $report;
  }
  $this->view->report = $report;
}

public function importAction(){
 $this->view->inspectionId = $this->_getParam('_parent');
}

public function dropdownAction() {
    	//must show only inspectors from current inspection company
 if ($this->getParam('_model') == 'inspection' && $this->getParam('_column') == 'column_10') {
  $column = $this->_components['inspection']->getColumnById('column_10');
  $dropdownFilter = $column->getDropdownFilter();
  $dropdownFilter['parent'] = new Data_Column('COMPANY_ID', $this->getParam('_parentColumn'), Model_Inspection::retrieve(), 'NAME');
  $column->setDropdownFilter($dropdownFilter);
}

parent::dropdownAction();
}

public function testAction() {

  $http = new Zend_Http_Client(Zend_Registry::get('configuration')->service->mico->url . '?name=' . urlencode($this->getUser()), array('timeout' => 600));
  $xml =file_get_contents(ROOT_PATH . '/public/test.xml');		
  $response = $http->setRawData($xml, 'application/xml')->request('POST');
  echo $response->asString('<br>');
}

public function reinspectAction() {
  $this->_helper->layout->setLayout('form');

  $form = $this->getForm('reinspect'); 
  $form->setValues(array('id' => $this->getParam('inspectionId'), 'option' => '1'));

  $this->view->placeholder('data')->form = $form;
}

public function inspectionsAction(){		
  $this->_initializeBratiliusGrid(array(
   'gridId' => 'inspection',
   'sortBy' => 'INSPECTION_DATE'
 ));
}

public function doorsAction(){

  $this->_initializeBratiliusGrid(array(
   'gridId' => 'inspection_door',
   'sortBy' => 'NUMBER',
   'sortDirection'=>1
 ));

}

public function changesAction(){

  $this->_initializeBratiliusGrid(array(
    'gridId' => 'inspection_inspects',
    'sortBy' => 'ASSIGNED_DATE'
  ));
  
}

public function changeAction(){
  $this->_detailedView('inspection_inspects');
  $this->view->grid = $this->_components['inspection_inspects'];
}

public function excelexportAction(){
  return parent::excelAction($this->_getParam('gridId'));
}

public function signatureuploadAction(){
  
  $inspection = $this->_getParam('inspection');

  $targetVideo= ROOT_PATH ."/content/pictures/";

		if($_FILES['signature']){
			
			$originalName = basename($_FILES['signature']['name']);

      $newName = pathinfo($originalName, PATHINFO_BASENAME);

			$newName = $inspection.'_'.$newName;

		//	array_push($temparr, $newName);

			$targetVideo=$targetVideo.$newName;
			
			chmod($targetVideo , 0777);
      $uplaod_success=move_uploaded_file($_FILES['signature']['tmp_name'],$targetVideo);
      $this->view->result = $newName;
    }else{
      $this->view->result = '';
    }
    

}

}
