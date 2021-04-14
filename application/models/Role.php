<?

require_once APPLICATION_PATH . '/models/DBTable/Role.php';
require_once APPLICATION_PATH . '/models/Abstract.php';

class Model_Role extends Model_Abstract{
	
	protected function _init(){
        $this->_table = new DBTable_Role();
    	$this->_name = 'NAME';
    	$this->addReferenceModel('PARENT_ROLE_ID', $this);
        parent::_init();
	}
	
	public static function retrieve($class = null) {
		return parent::retrieve(__CLASS__);
	}
}