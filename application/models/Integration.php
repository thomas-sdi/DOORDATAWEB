<?
require_once APPLICATION_PATH . '/models/DBTable/Integration.php';
require_once APPLICATION_PATH . '/models/Inspection.php';
require_once APPLICATION_PATH . '/models/Dictionary.php';
require_once APPLICATION_PATH . '/models/Abstract.php';

class Model_Integration extends Model_Abstract{
    
    protected function _init(){
        $this->_table = new DBTable_Integration();
        $this->addReferenceModel('INSPECTION_ID', Model_Inspection::retrieve());
        $this->addReferenceModel('TYPE', Model_Dictionary::retrieve());
        $this->addReferenceModel('STATUS', Model_Dictionary::retrieve());
        
        parent::_init();
    }
   
    public static function retrieve($class=null) {
        return parent::retrieve(__CLASS__);
    }
}