<?
require_once APPLICATION_PATH . '/models/DBTable/Inspectionother.php';
require_once APPLICATION_PATH . '/models/Abstract.php';

class Model_Inspection_Other extends Model_Abstract{
    
    protected function _init(){
    	$this->_table = new DBTable_Inspection_Other();
        
        parent::_init();
    }
    
    public static function retrieve($class=null) {
        return parent::retrieve(__CLASS__);
    }
}