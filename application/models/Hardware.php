<?
require_once APPLICATION_PATH . '/models/DBTable/Hardware.php';
require_once APPLICATION_PATH . '/models/Door.php';
require_once APPLICATION_PATH . '/models/Abstract.php';

class Model_Hardware extends Model_Abstract{

	protected function _init(){
		$this->_table = new DBTable_Hardware();
		parent::_init();

		$this->addReferenceModel('DOOR_ID', Model_Door::retrieve());
	}

	public function save($data, $ignored=null){
		$id = parent::save($data , $ignored);
		return $id;
	}

	public static function retrieve($class=null) {
		return parent::retrieve(__CLASS__);
	}
}