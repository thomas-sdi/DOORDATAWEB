<?
require_once APPLICATION_PATH . '/models/DBTable/Email.php';
require_once APPLICATION_PATH . '/models/Abstract.php';

class Model_Email extends Model_Abstract{
    
	
   protected function _init(){
        $this->_table = new DBTable_Email();
        parent::_init();
    }
    
    public static function retrieve($class=null) {
        return parent::retrieve(__CLASS__);
    }
}