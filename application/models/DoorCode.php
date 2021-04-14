<?
require_once APPLICATION_PATH . '/models/DBTable/DoorCode.php';
require_once APPLICATION_PATH . '/models/Door.php';
require_once APPLICATION_PATH . '/models/Dictionary.php';
require_once APPLICATION_PATH . '/models/Abstract.php';

class Model_DoorCode extends Model_Abstract{
    
    protected function _init(){
        $this->_table = new DBTable_DoorCode();
        $this->addReferenceModel('DOOR_ID', Model_Door::retrieve());
        $this->addReferenceModel('CODE_ID', Model_Dictionary::retrieve());
        
        $this->addValidationRule(new Validation_Rule_Unique(array('DOOR_ID', 'CODE_ID'), Validation_Rule::WARN, 'Repeating codes for door'));
		
        parent::_init();
    }
   
	public function save($data, $ignored=null){
		/*
    	$code = $this->fetchEntry(false, array('ID' , new Data_Column('DOOR_ID', $data['DOOR_ID']), new Data_Column('CODE_ID', $data['CODE_ID'])));
    	if ($code)
    		$data['ID'] = $code['ID'];
    	*/
    	$id = parent::save($data , $ignored);
    	$code = Model_DoorCode::retrieve()->fetchEntry($id);
    	//Model_Door::retrieve()->save(array('ID' => $code['DOOR_ID'], 'COMPLIANT' => Model_Dictionary::getIdByItem('No', 'Logical')));
    	
    	return $id;
	}
	
    public static function retrieve($class=null) {
        return parent::retrieve(__CLASS__);
    }
}