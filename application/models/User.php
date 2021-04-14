<?

require_once APPLICATION_PATH . '/models/DBTable/User.php';
require_once APPLICATION_PATH . '/models/Role.php';
require_once APPLICATION_PATH . '/models/Employee.php';
require_once APPLICATION_PATH . '/models/Abstract.php';
require_once APPLICATION_PATH . '/components/validation/Unique.php';

class Model_User extends Model_Abstract{
	
	protected function _init(){
        $this->_table = new DBTable_User();
    	$this->addValidationRule(new Validation_Rule_Unique('LOGIN'));
           
        parent::_init();
	}
	
	public static function retrieve($class=null) {
		return parent::retrieve(__CLASS__);
	}
	
	/**
     * Enhances insert and update scenarios by making sure
     * any user has respective 1-1 employee
     */
	
    public function save($data, $ignored = null) {
        // password encryption
        if (array_key_exists('PASSWORD', $data)) {
        	$data['PASSWORD'] = md5($data['PASSWORD']);
        }
        
        //if update user, then we must update user folder
        if (array_key_exists('ID', $data)) {
        	if (array_key_exists('LOGIN' , $data)) {
        		$old_login = $this->fetchEntry($data['ID'], array('LOGIN'));
        	}
        }
        
        $result = parent::save($data); 
        
        //if create new user, then we must create separate user folder to store files
        if ($result) {
        	//if create user
        	if (!array_key_exists('ID', $data)) {
        		if (array_key_exists('LOGIN' , $data)) {
               		mkdir(ROOT_PATH . '/content/userdata/' . $data['LOGIN']);
        		}
        	} 
        	//if change user
        	else {	
        		if (array_key_exists('LOGIN' , $data)) {
        			//if such directory exists
        			if (is_dir(ROOT_PATH . '/content/userdata/' . $old_login['LOGIN'])) {
        				rename(ROOT_PATH . '/content/userdata/' . $old_login['LOGIN'], ROOT_PATH . '/content/userdata/' . $data['LOGIN']);
        			} else {	//no such directory
        				mkdir(ROOT_PATH . '/content/userdata/' . $data['LOGIN']);
        			}
        		}
        	}
        }
        
        return $result;
    }  
     
	/**
	 * firstly delete user folder
	 */
    public function delete($id, $ignored=null) {
   		$old_login = $this->fetchEntry($id, array('LOGIN'));
		$result = parent::delete($id, $ignored);
		
		if ($result) {
			$this->deleteFolder(ROOT_PATH . '/content/userdata/' . $old_login['LOGIN']);
		}
		
		return $result;
    }
    
    /**
     * recursively delete folder and all files in it
     *
     * @param	string $path
     */
    private function deleteFolder($path) {
    	if ($handle = opendir($path)) {
    		while (false !== ($file = readdir($handle))) {
        		if ($file != "." && $file != "..") {
            		if (is_dir($path . '/' . $file)) {
            			$this->deleteFolder($path . '/' . $file);
            		} else {
            			unlink($path . '/' . $file);
            		}
        		}
    		}
    		closedir($handle);
		}
		rmdir($path);
    }
	
	public function currentUser() {
    	$currentUser = Zend_Auth::getInstance()->getIdentity();
		if (!$currentUser) return array();
		
    	$user = $this->fetchEntry(null, array('id', 'login' => new Data_Column('login', $currentUser)));
    	if (!$user) {
    		throw new Exception("User $currentUser is logged in but doesn't exist in the database");
    		return;
    	}
    	return $user;
    }
}
