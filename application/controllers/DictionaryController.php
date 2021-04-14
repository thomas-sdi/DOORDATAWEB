<?
require_once APPLICATION_PATH . '/models/Dictionary.php';
require_once APPLICATION_PATH . '/controllers/Component.php';

class DictionaryController extends Controller_Component {
        
    public function init() {
    	
    	//array will contain permissions for current user to grids in current tab
    
    	$access = array();
    	
    	$this->addGrid('dictionary',  Model_Dictionary::retrieve());  

   
    	
    	//get permissions for current user for current grid
    	$access['dictionary'] = $this->getAccessControl()->getGridPermissions('dictionary');
 		
    	$this->view->access = $access;
    	
        parent::init();

    }

     public function dictionarysAction() {

        $this->view->dictionary="";
        if ($this->_getParam("_parent") > 0){
            $company = Model_Dictionary::retrieve()->fetchEntry($this->_getParam("_parent"));
            $this->view->dictionary = $company['NAME'];
        }

        $this->_initializeBratiliusGrid(array(
            'gridId' => 'dictionary',
            'sortBy' => 'ID'
        ));
        
        $this->view->dictionary = $this->_components['dictionary'];
    }
 public function excelexportAction(){
    return parent::excelAction($this->_getParam('gridId'));
}


}