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

class ContentController extends Controller_Abstract {
	
	protected $_fileName;
        
    public function init() {
    	parent::init();
		
    	// no view will be defined for this action
		$this->_helper->ViewRenderer->setNoRender(true);
		
		// stream file to a client
    	$this->_helper->layout->setLayout("file");
		
		// get the file name which is being retrieved 
		$this->_fileName	= $this->_params['id'];     
    }  
    
    public function reportsAction() {
		$this->view->placeholder('fileName')->set(ROOT_PATH . '/content/reports/' . $this->_fileName);
		//$this->view->placeholder('disposition')->set('inline');
    }
	
	public function audioAction() {
		$this->view->placeholder('fileName')->set(ROOT_PATH . '/content/audio/' . $this->_fileName);
    }
	
	public function picturesAction() {
		$this->view->placeholder('fileName')->set(ROOT_PATH . '/content/pictures/' . $this->_fileName);
    }
	
	public function userdataAction() {
		$this->view->placeholder('fileName')->set(ROOT_PATH . '/content/userdata/' . $this->getUser() . '/' . $this->_fileName);
    }
}