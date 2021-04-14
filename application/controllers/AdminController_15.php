<?
require_once APPLICATION_PATH . '/controllers/Component.php';

class AdminController extends Controller_Component {    
    
	public function init() {
		
		//array will contain permissions for current user to grids in current tab
    	$access = array();
    	  	
    	$this->view->access = $access;
    	
        parent::init();
	}
	
	public function sendlinkAction() {
 		$this->_helper->layout->setLayout('html');
	}
	
	public function uploadAction() {
 		$this->_helper->layout->setLayout('html');
	}
}