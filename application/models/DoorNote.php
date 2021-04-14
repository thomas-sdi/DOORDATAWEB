<?
require_once APPLICATION_PATH . '/models/DBTable/DoorNote.php';
require_once APPLICATION_PATH . '/models/Door.php';
require_once APPLICATION_PATH . '/models/Abstract.php';

class Model_DoorNote extends Model_Abstract{
	
	protected function _init(){
        $this->_table = new DBTable_DoorNote();
    	$this->addReferenceModel('DOOR_ID', Model_Door::retrieve());
    	
        parent::_init();
	}
	
	public static function retrieve($class=null) {
		return parent::retrieve(__CLASS__);
	}
}