<?
require_once APPLICATION_PATH . '/models/Dictionary.php';
require_once APPLICATION_PATH . '/controllers/Component.php';
require_once APPLICATION_PATH . '/components/adapters/Mico.php';
require_once APPLICATION_PATH . '/components/grids/DictionaryGrid.php';


class AdminController extends Controller_Component {    

	public function init() {
		
		$this->_helper->layout->setLayout('html');
		$access = array();

		$dictionaryGrid = $this->addGrid(new DictionaryGrid('dictionary'));    
    	//get permissions for current user for current grid
		$access['dictionary'] = $this->getAccessControl()->getGridPermissions('dictionary');

		$this->view->access = $access;

		parent::init();
	}
		
	public function dictionarysAction() {
		$this->_initializeBratiliusGrid(array(
			'gridId' => 'dictionary',
			'sortBy' => 'ID'
		));
	}


	public function sendlinkAction() {
		$this->_helper->layout->setLayout('html');
	}
	
	public function uploadAction() {
		$this->_helper->layout->setLayout('html');
	}
}