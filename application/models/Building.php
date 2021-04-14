<?
require_once APPLICATION_PATH . '/models/DBTable/Building.php';
require_once APPLICATION_PATH . '/models/Abstract.php';
require_once APPLICATION_PATH . '/models/Company.php';
require_once APPLICATION_PATH . '/models/Dictionary.php';
require_once APPLICATION_PATH . '/models/Employee.php';

class Model_Building extends Model_Abstract{
    
    protected function _init() {
        $this->_table = new DBTable_Building();
        $this->addReferenceModel('CUSTOMER_ID', Model_Company::retrieve());
        $this->addReferenceModel('STATE', Model_Dictionary::retrieve());
        $this->addReferenceModel('COUNTRY', Model_Dictionary::retrieve());
        $this->addReferenceModel('PRIMARY_CONTACT', Model_Employee::retrieve());
        $this->_name = 'NAME';
        
        parent::_init();
    }
    
    public static function retrieve($class=null) {
        return parent::retrieve(__CLASS__);
    }
    
}