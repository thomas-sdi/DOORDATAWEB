<?
require_once APPLICATION_PATH . '/models/DBTable/Audio.php';
require_once APPLICATION_PATH . '/models/Door.php';
require_once APPLICATION_PATH . '/models/Abstract.php';

class Model_Audio extends Model_Abstract{
    
    protected function _init(){
    	$this->_table = new DBTable_Audio();
       	$this->addReferenceModel('DOOR_ID', Model_Door::retrieve());
        
        parent::_init();
    }
    
    public static function retrieve($class=null) {
        return parent::retrieve(__CLASS__);
    }
    
    /**
     * Before deleting record delete the file
     */
    public function delete($id, $ignored=null) {
    	try {
    		$audio = $this->fetchEntry($id);
    		if (file_exists(ROOT_PATH . $audio['AUDIO_FILE'])) unlink(ROOT_PATH . $audio['AUDIO_FILE']);
    	} catch (Exception $e) {
    		Zend_Registry::getInstance()->logger->err($e->getMessage() . '\n' . $e->getTraceAsString());
    	}
 		return parent::delete($id, $ignored);
    }
    
}