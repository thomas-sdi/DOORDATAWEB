<?
require_once APPLICATION_PATH . '/models/DBTable/DoorType.php';
require_once APPLICATION_PATH . '/models/Door.php';
require_once APPLICATION_PATH . '/models/Dictionary.php';
require_once APPLICATION_PATH . '/models/Abstract.php';

class Model_DoorType extends Model_Abstract{
    
    protected function _init(){
        $this->_table = new DBTable_DoorType();
        $this->addReferenceModel('DOOR_ID', Model_Door::retrieve());
        $this->addReferenceModel('TYPE_ID', Model_Dictionary::retrieve());
        
        parent::_init();
    }

    public static function retrieve($class=null) {
        return parent::retrieve(__CLASS__);
    }
}