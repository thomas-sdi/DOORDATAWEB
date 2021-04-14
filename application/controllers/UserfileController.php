<?
require_once APPLICATION_PATH . '/models/User.php';
require_once APPLICATION_PATH . '/controllers/Component.php';
require_once APPLICATION_PATH . '/components/adapters/Mico.php';
require_once APPLICATION_PATH . '/controllers/plugins/AccessControl.php';
require_once APPLICATION_PATH . '/components/grids/UserFileGrid.php';




class UserfileController extends Controller_Component {

	public function init() {

		$this->_helper->layout->setLayout('html');
		$access = array();

		$userFileGrid = $this->addGrid(new UserFileGrid('user_file'));    
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

				$folder = ROOT_PATH . '/content/userdata/' . $this->getUser();
				if(!is_dir($folder)) mkdir($folder, 0700);

				move_uploaded_file($_FILES["myfile"]['tmp_name'], 
					ROOT_PATH . '/content/userdata/' . $this->getUser() . '/' . $_FILES["myfile"]['name']);			
				$user = Model_User::retrieve()->fetchEntry(false, array('ID', new Data_Column('LOGIN', $this->getUser())));
				
				$size = ((int)$_FILES["myfile"]['size'])/1024;
				if ($size < 1024) {
					$size = ceil($size) . ' Kb';
				} else {
					$size = ceil($size/1024) . 'Mb';
				}
				
				$record = array(
					'ID' =>(int)$this->_getParam('_parent') > 0 ? $this->_getParam('_parent') : null, 
					'USER_ID' 	=> $user['ID'], 
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
			$this->_detailedView('user_file');
		}
	}
	
	public function downloadAction(){
		$this->view->userfileId = $this->_getParam('_parent');
	}

	
	public function userfilesAction(){

		$this->view->grid = $this->getComponent('user_file');

		$this->_initializeBratiliusGrid(array(
			'gridId' => 'user_file',
			'sortBy' => 'FILE_NAME'
		));
		

	}

	
	public function excelexportAction(){
		return parent::excelAction($this->_getParam('gridId'));
	}
}