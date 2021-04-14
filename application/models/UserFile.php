<?
require_once APPLICATION_PATH . '/models/DBTable/UserFile.php';
require_once APPLICATION_PATH . '/models/Abstract.php';
require_once APPLICATION_PATH . '/models/User.php';

class Model_UserFile extends Model_Abstract{
	
	protected function _init(){
        $this->_table = new DBTable_User_File();
    	$this->addReferenceModel('USER_ID', Model_User::retrieve());
    	   
        parent::_init();
	}
	
	public static function retrieve($class=null) {
		return parent::retrieve(__CLASS__);
	}
	
	/**
     * Enhances insert and update scenarios by saving
     * current file
     */
	public function save($data, $ignored=null) {
		//if update file
		if (array_key_exists('ID', $data)) {
			if (array_key_exists('FILE_NAME', $data)) {
				$file = $this->fetchEntry($data['ID']);
				$user = Model_User::retrieve()->fetchEntry($file['USER_ID'], array('LOGIN'));
				
				rename(ROOT_PATH . '/content/userdata/' . $user['LOGIN'] . '/' . $file['FILE_NAME'],
					   ROOT_PATH . '/content/userdata/' . $user['LOGIN'] . '/' . $data['FILE_NAME']);
			}
		}
		
		return parent::save($data, $ignored);
	}
	
	/**
	 * enhances delete by deleting current file from user folder
	 */
	public function delete($id, $ignored=null) {
		$file = $this->fetchEntry($id);
   		$user = Model_User::retrieve()->fetchEntry($file['USER_ID'], array('LOGIN'));
		
   		unlink(ROOT_PATH . '/content/userdata/' . $user['LOGIN'] . '/' . $file['FILE_NAME']);
   		
   		return parent::delete($id, $ignored);
    }
}
