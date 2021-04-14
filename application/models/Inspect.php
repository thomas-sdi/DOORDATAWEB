<?
require_once APPLICATION_PATH . '/models/DBTable/Inspect.php';
require_once APPLICATION_PATH . '/models/Inspection.php';
require_once APPLICATION_PATH . '/models/Employee.php';
require_once APPLICATION_PATH . '/models/Abstract.php';

class Model_Inspect extends Model_Abstract{
    
    protected function _init(){
        $this->_table = new DBTable_Inspect();
        $this->addReferenceModel('INSPECTION_ID', Model_Inspection::retrieve());
        $this->addReferenceModel('INSPECTOR_ID', Model_Employee::retrieve());
        
        parent::_init();
    }
	
	public function save($data, $ignored = NULL) {

		// if such record already exists, ignore
		if ($this->_checkIfExists($data['INSPECTION_ID'], $data['INSPECTOR_ID'], $data['ASSIGNED_DATE'])){
			//then only the notes are going to be saved
			if (array_key_exists('COMMENTS', $data) && array_key_exists('ID', $data)){
				$newData = array(
					'ID'	=> $data['ID'],
					'COMMENTS' => $data['COMMENTS']
				);
				parent::save($newData);
				return;			
			}

		}
		
		parent::save($data);
	}
    
    public static function retrieve($class=null) {
        return parent::retrieve(__CLASS__);
    }
	
	protected function _checkIfExists($inspectionId, $inspectorId, $date) {
		$record =  Model_Inspect::retrieve()->fetchEntries(array(
			new Data_Column('INSPECTION_ID', $inspectionId),
			new Data_Column('INSPECTOR_ID', $inspectorId),
			new Data_Column('ASSIGNED_DATE', $date)
		));
		
		return $record->getTotalItemCount() > 0;
	}
    
}