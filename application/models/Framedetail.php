<?

require_once APPLICATION_PATH . '/models/DBTable/Framedetail.php';
require_once APPLICATION_PATH . '/models/Door.php';
require_once APPLICATION_PATH . '/models/Abstract.php';

class Model_Framedetail extends Model_Abstract{
	
	protected function _init(){
        $this->_table = new DBTable_Framedetail();
        $this->addReferenceModel('DOOR_ID', Model_Door::retrieve());
           
        parent::_init();
	}
	
	public static function retrieve($class=null) {
		return parent::retrieve(__CLASS__);
	}
}
