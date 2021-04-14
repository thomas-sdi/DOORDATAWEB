<?
require_once APPLICATION_PATH . '/models/User.php';
require_once APPLICATION_PATH . '/models/Door.php';
require_once APPLICATION_PATH . '/models/DoorCode.php';
require_once APPLICATION_PATH . '/models/DoorType.php';
require_once APPLICATION_PATH . '/models/Company.php';
require_once APPLICATION_PATH . '/models/UserFile.php';
require_once APPLICATION_PATH . '/components/data/Column.php';
require_once APPLICATION_PATH . '/controllers/Component.php';
require_once APPLICATION_PATH . '/components/adapters/Mico.php';
require_once APPLICATION_PATH . '/controllers/plugins/AccessControl.php';

class UserfileController extends Controller_Component {
        
    public function init() {
    	
    	$this->_helper->layout->setLayout('html');
    	//array will contain permissions for current user to grids in current tab
    	$access = array();
    	
    	/* inspection grid */
    	$userfileGrid = $this->addGrid('user_file',  Model_UserFile::retrieve());    

    	$columns = array(
    		'USER_ID'	 => array('Display'	=> 'LOGIN',
    							  'Filter'	=> $this->getUser(),
    							  'Visible'	=> '000'),
		    'FILE_NAME'  => array('Title' 	=> 'File', 'Id' => 'FILE_NAME'),
    		'FILE_SIZE'	 => array('Title'	=> 'Size',
    							  'Editable'=> false,
    							  'Width'	=> '150px'
    							 ),
    		'ADDED_ON'	 => array('Title'	=> 'Added On',
    							  'Editable'=> false,
    							  'View'	=> Data_Column_Grid::DATE,
    							  'Width'	=> '150px'),
    		'DESCRIPTION'=> array('Title' 	=> 'Description', 'View' => Data_Column_Grid::MEMO),
    		array('Title' => 'Actions', 'Calculated' => array($this, "calculateInspectionActions"), 'Visible' => '100')
						);
		
    	$userfileGrid->setColumns($columns, true);
		
		//get permissions for current user for current grid
    	$access['user_file'] = $this->getAccessControl()->getGridPermissions('user_file');
    	
    	$this->view->access = $access;
    	 
        parent::init();
    }  
    
    public function savefileAction() {
		$this->_helper->ViewRenderer->setNoRender(true);
		$this->_helper->layout->setLayout('iframe');
		
		try {
			if (!isset($_FILES["myfile"])) {
				throw new Exception('No file was uploaded');
			}
				
			//read from temporary file
			if ($_FILES["myfile"]["error"] == 0) {
				move_uploaded_file($_FILES["myfile"]['tmp_name'], 
								   ROOT_PATH . '/content/userdata/' . $this->getUser() . '/' . $_FILES["myfile"]['name']);			
				$user = Model_User::retrieve()->fetchEntry(false, array('ID', new Data_Column('LOGIN', $this->getUser())));
				
				$size = ((int)$_FILES["myfile"]['size'])/1024;
				if ($size < 1024) {
					$size = ceil($size) . ' Kb';
				} else {
					$size = ceil($size/1024) . 'Mb';
				}
				
				$record = array('USER_ID' 	=> $user['ID'], 
							  'FILE_NAME' 	=> $_FILES["myfile"]['name'], 
							  'FILE_SIZE'	=> $size . '',
							  'ADDED_ON'	=> date('Y-m-d'). '',
							  'DESCRIPTION' => $this->_getParam('description'));
				
				Model_UserFile::retrieve()->save($record);
				$data = array('status' => 'File successfully uploaded');
				$this->view->placeholder('content')->set(Zend_Json::encode($data));
			} else {
				throw new Exception('File uploaded with error');
			}
		} catch(Exception $e) {
			$error = $e->getMessage() . '\n' . $e->getTraceAsString(); 	
    		Zend_Registry::getInstance()->logger->err($error);
    		
    		$data = array('status' => 'File uploaded with error', 'Message' => $e->getMessage());
    		$this->view->placeholder('content')->set(Zend_Json::encode($data));
		}
	}
    
	public function userfileAction() {
		//this is new file
		if ((int)$this->_getParam('_parent') < 0) {
    		$this->_detailedView('user_file');
		} else {
			$this->_component = $this->_components['user_file'];
			$this->detailedAction();
		}
	}
	
    public function downloadAction(){
    	$this->view->userfileId = $this->_getParam('_parent');
    }
    
	public function calculateInspectionActions($entry){
    	$this->getUser();
    	$idColumn = current($this->_components['user_file']->getColumnsByField('ID'));
    	$userfileId = $entry[$idColumn->getId()];
    	$file = Model_UserFile::retrieve()->fetchEntry($userfileId);
    	$actionHead = '<div class="dropdown action-dropdown">
  <a data-toggle="dropdown">
   -Select-
     <i class="glyph-icon icon-chevron-down"></i>
  </a><div class="dropdown-menu float-right"><div class="">';
		$actionFoot = '</div></div></div>';

    	$actions = "<div class='pad5A button-pane button-pane-alt text-center'><a data-original-title='Edit' title='Edit' data-placement='top' class='btn btn-sm hover-blue-alt tooltip-button' href='javascript: cmp_user_file.showDetailed()'> <i class='fa fa-edit'></i> Edit</a> </div>" .
    	           "<div class='pad5A button-pane button-pane-alt text-center'><a data-original-title='Download' title='Download' data-placement='top' class='btn btn-sm hover-blue-alt tooltip-button' href='" . Zend_Controller_Front::getInstance()->getBaseUrl() .  
    	           				 "/content/userdata?id=" . rawurlencode($file['FILE_NAME']) . "' target=_self><i class='glyph-icon icon-download'				></i>Download</a></div> " .
    			   "<div class='pad5A button-pane button-pane-alt text-center'><a data-original-title='Delete' title='Delete' data-placement='top' class='btn btn-sm hover-red tooltip-button' href='javascript: cmp_user_file.deleteItems()'><i class='glyph-icon icon-remove'				></i>Delete</a></div>";
    	return $actionHead . $actions . $actionFoot;
    }
	
	public function userfilesAction(){
		$this->view->grid = $this->getComponent('user_file');
		
		$this->view->gridParams = array(
			'page' 	 		=> nvl($this->getParam('page'), 0),
			'sortBy' 		=> nvl($this->getParam('sort_by'), 'FILE_NAME'),
			'sortDirection' => nvl($this->getParam('sort_direction'), 1),
			'rowsPerPage'	=> nvl($this->getParam('rows_per_screen'), Zend_Registry::getInstance()->configuration->paginator->page),
			'selectAll'		=> nvl($this->getParam('select_all'), false)
		);
	}
	public function excelexportAction(){
    return parent::excelAction($this->_getParam('gridId'));
}
}