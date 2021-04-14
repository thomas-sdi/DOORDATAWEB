<?
require_once APPLICATION_PATH . '/controllers/Component.php';

class WelcomeController extends Controller_Abstract {

    public function init() {
    	parent::init();
		
    	   
    }  
    
    public function indexAction() {
		$this->_helper->ViewRenderer->setNoRender(false);
		$this->_helper->layout->setLayout('html');
    }

}