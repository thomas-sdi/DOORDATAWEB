<?
require_once APPLICATION_PATH . '/models/DBTable/Recomendation.php';
require_once APPLICATION_PATH . '/models/Door.php';
require_once APPLICATION_PATH . '/models/Abstract.php';

class Model_Recomendation extends Model_Abstract{
    
    protected function _init(){
        $this->_table = new DBTable_Recomendation();
        $this->addReferenceModel('DOOR_ID', Model_Door::retrieve());
        parent::_init();
    }
   
    public function save($data, $ignored=null){
        /*
        $code = $this->fetchEntry(false, array('ID' , new Data_Column('DOOR_ID', $data['DOOR_ID']), new Data_Column('CODE_ID', $data['CODE_ID'])));
        if ($code)
            $data['ID'] = $code['ID'];
        */
        $id = parent::save($data , $ignored);
        $code = Model_Recomendation::retrieve()->fetchEntry($id);
        //Model_Door::retrieve()->save(array('ID' => $code['DOOR_ID'], 'COMPLIANT' => Model_Dictionary::getIdByItem('No', 'Logical')));
        return $id;
    }
    
    public static function retrieve($class=null) {
        return parent::retrieve(__CLASS__);
    }
}