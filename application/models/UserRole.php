<?
require_once APPLICATION_PATH . '/models/DBTable/UserRole.php';
require_once APPLICATION_PATH . '/models/User.php';
require_once APPLICATION_PATH . '/models/Abstract.php';
require_once APPLICATION_PATH . '/components/validation/Unique.php';

class Model_User_Role extends Model_Abstract{
	
	protected function _init(){
        $this->_table = new DBTable_User_Role();
    	$this->addReferenceModel('USER_ID', Model_User::retrieve());
    	$this->addReferenceModel('ROLE_ID', Model_Role::retrieve());
    	
    	// add uniqueness validation
        $this->addValidationRule(new Validation_Rule_Unique(
           array('USER_ID', 'ROLE_ID'), Validation_Rule::ERROR,
           'The user already belongs to this role'));
    	
        parent::_init();
	}
	
	public static function retrieve($class=null) {
		return parent::retrieve(__CLASS__);
	}
}