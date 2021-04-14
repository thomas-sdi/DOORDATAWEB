<?
require_once APPLICATION_PATH . '/models/DBTable/Download.php';
require_once APPLICATION_PATH . '/models/Abstract.php';

class Model_Download extends Model_Abstract{
	
	protected function _init(){
        $this->_table = new DBTable_Download();
    	$this->addReferenceModel('Downloaded', Model_Dictionary::retrieve()); 

    	$this->addValidationRule(new Validation_Rule_Required('EMAIL'));
		
        parent::_init();
	}
	
	public static function retrieve($class=null) {
		return parent::retrieve(__CLASS__);
	}
}
