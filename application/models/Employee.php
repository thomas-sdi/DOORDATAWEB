<?
require_once APPLICATION_PATH . '/models/DBTable/Employee.php';
require_once APPLICATION_PATH . '/models/Abstract.php';
require_once APPLICATION_PATH . '/models/User.php';
require_once APPLICATION_PATH . '/models/Company.php';
require_once APPLICATION_PATH . '/models/Inspection.php';
require_once APPLICATION_PATH . '/models/Email.php';
require_once APPLICATION_PATH . '/components/tools/Mailer.php';

class Model_Employee extends Model_Abstract{
	
	protected function _init(){
		$this->_table = new DBTable_Employee();

		$this->addReferenceModel('USER_ID', Model_User::retrieve());
		$this->addReferenceModel('COMPANY_ID', Model_Company::retrieve());

        // add uniqueness validation
		$this->addValidationRule(new Validation_Rule_Unique(
			array('USER_ID'), Validation_Rule::ERROR,
			'The employee related to that login already exists'));

        // $this->_name = 'LAST_NAME';
		$this->_name = 'FIRST_NAME';

    	// add uniqueness validation
     //   $this->addValidationRule(new Validation_Rule_Unique(
     //     array('USER_ID'), Validation_Rule::ERROR,
     //      'The employee related to that login already exists'));

		parent::_init();
	}
	
	public static function retrieve($class=null) {
		return parent::retrieve(__CLASS__);
	}
	
	public function getEmployeeIdbyUserId($userId){
		$employees = Model_Employee::retrieve()->fetchEntries(
			array('ID', new Data_Column('USER_ID', $userId))
		);
		
		foreach($employees as $employee){
			return $employee['ID'];
		}
		
		return null;
	}
	
	public static function randomString(){
		$rand = mt_rand(0x000000, 0xffffff); // generate a random number between 0 and 0xffffff
		$rand = dechex($rand & 0xffffff); // make sure we're not over 0xffffff, which shouldn't happen anyway
		$rand = str_pad($rand, 6, '0', STR_PAD_LEFT); // add zeroes in front of the generated string
		return $rand;
	}

	// make sure to delete related system user and user's role
	public function delete($id, $ignored=null) {
		Zend_Registry::get("logger")->info("trying to delete employee");
		$employee = $this->fetchEntry($id);
		
		
		//all inspections that were assigned to this employee need to be reassigned
		$inspections = Model_Inspection::retrieve()->fetchEntries(
			array('ID', 'STATUS', new Data_Column('INSPECTOR_ID', $id)),
			null, true
		);
		
		//preserve the inspection status with exception to Assigned and Assigning statuses - then it will be Incomplete
		foreach ($inspections as $inspection) {
			$inspectionStatus = $inspection['STATUS'];
			if ($inspection['STATUS'] == Model_Inspection::SUBMITTED || $inspection['STATUS'] == Model_Inspection::SUBMITTING){
				$inspectionStatus = Model_Inspection::INCOMPLETED;
			}
			Model_Inspection::retrieve()->save(array(
				'ID' => $inspection['ID'],
				'INSPECTOR_ID' => null,
				'STATUS' => $inspectionStatus
			));
		}
		
		$userId = $employee['USER_ID'];
		if (parent::delete($id) && $userId != '') {
			Zend_Registry::get("logger")->info("will now delete user " . $userId);
			$deleted = Model_User_Role::retrieve()->deleteEntries(array(new Data_Column('USER_ID', $userId)));
			$deleted = Model_User::retrieve()->delete($userId);
		}
	}
	
	/**
	 * Saves employee and an associated user
	 */
	public function save($data, $ignored=null){
	    // find current employee's user
		$usrId = null; $isNew = true;
		Zend_Registry::get("logger")->info(json_encode($data));

		$usrRecord = array();
		if (array_key_exists('ID', $data)) {
			$isNew = false; 
			$employee = $this->fetchEntry($data['ID'], array('ID', 'FIRST_NAME', 'LAST_NAME', 'EMAIL', 'USER_ID'));
			$user = Model_User::retrieve()->fetchEntry($employee['USER_ID'], array('LOGIN'));
			$usrId = $employee['USER_ID'];
			$usrRecord['ID'] = $usrId;
		}

	    //this variables will be used when sending email
		$willSendEmail = false;
		$emailAddress = '';
		$login = '';
		$firstName = '';
		$lastName = '';
		$body = '';
		$template = null;

	    // start filling user record for update
		if (array_key_exists('LOGIN', $data)) {
			$login = $data['LOGIN'];
			$usrRecord['LOGIN'] = $data['LOGIN'];
			unset ($data['LOGIN']);
		}

		unset ($data['imgRemove']);
		
		//check that Role is populated for new user
		if ($isNew == true && !array_key_exists('role', $data)){
			$e = new Validation_Rule(Validation_rule::ERROR, 'Please specify user role for this employee');
			$e->setId(-1); //this would be general error;
			throw new Validation_Exception($e, array('Role')); 				
		}




		if (isset($data['passwordType']))
			switch ($data['passwordType']){
	    	case 'manual': //this is a manual password reset/generation
	    		//for a new employee password should always be specified
	    	if ($isNew == true && !array_key_exists('new_password', $data))
	    		throw new Validation_Exception(
	    			new Validation_Rule(Validation_rule::ERROR, 'Please specify password for this employee'), 
	    			array('new_password')
	    		);

	    	if (array_key_exists('new_password', $data)) {
	    		if ($data['new_password'] != $data['new_password2'] || $data['new_password'] == '')
	    			throw new Validation_Exception(
	    				new Validation_Rule(Validation_rule::ERROR, 'Passwords do not match'), 
	    				array('new_password', 'new_password2'));
	    		else {
	    			$usrRecord['PASSWORD'] = $data['new_password'];
	    			unset ($data['new_password']); unset ($data['new_password2']);
	    		}
	    	}
	    	break;
	    	case 'auto': //password will be generated automatically and sent to user via email		    		
	    	if (array_key_exists('EMAIL', $data)) $emailAddress = $data['EMAIL'];
	    	elseif ($isNew == false) $emailAddress = $employee['EMAIL'];

	    	if (array_key_exists('FIRST_NAME', $data)) $firstName = $data['FIRST_NAME'];
	    	elseif ($isNew == false) $firstName = $employee['FIRST_NAME'];

	    	if (array_key_exists('LAST_NAME', $data)) $lastName = $data['LAST_NAME'];
	    	elseif ($isNew == false) $lastName = $employee['LAST_NAME'];


	    	if ($login == '' && $isNew == false)
	    		$login = $user['LOGIN'];

	    		//check that login field was populated
	    	if ($login == ''){
	    		throw new Validation_Exception(
	    			new Validation_Rule(Validation_rule::ERROR, 'Please specify login for this employee'), 
	    			array('LOGIN')
	    		);
	    	}
	    		//check that last name was populated
	    	if ($lastName == '')
	    		throw new Validation_Exception(
	    			new Validation_Rule(Validation_rule::ERROR, 'Please specify last name for this employee'), 
	    			array('LAST_NAME')
	    		);

	    		//check that email address is correct    		
	    	if ($emailAddress == '')
	    		throw new Validation_Exception(
	    			new Validation_Rule(Validation_rule::ERROR, 'Please specify email for this employee'), 
	    			array('email')
	    		);
	    	$validator = new Zend_Validate_EmailAddress();
	    	if(!$validator->isValid($emailAddress))
	    		throw new Validation_Exception(
	    			new Validation_Rule(Validation_rule::ERROR, 'This is not a valid email address'), 
	    			array('email')
	    		);

	    		//generate random password
	    	$password = $this->randomString();	

	    		//get email template from the database
	    	$template = Model_Email::retrieve()->fetchEntry(
	    		null, 
	    		array(new Data_Column('IDENTITY', 'password generation'),'SUBJECT','MESSAGE')
	    	);

	    		//personalize the mesasge
	    	$body = str_replace('[==NAME==]', $firstName.' '.$lastName, $template['MESSAGE']);
	    	$body = str_replace('[==LOGIN==]', $login, $body);
	    	$body = str_replace('[==PASSWORD==]', $password, $body);

            	//encrypt password
	    	$usrRecord['PASSWORD'] = $password;

            	//indicate that email should be sent
	    	$willSendEmail = true;
	    	break;
	    }
	    unset($data['passwordType']);
	    
	    Zend_Registry::get('logger')->info(json_encode($usrRecord));
	    
        // update the user
	    $usrId = Model_User::retrieve()->save($usrRecord);

        // update user's role
	    if (array_key_exists('role', $data)) {
	    	if (!$isNew) Model_User_Role::retrieve()->deleteEntries(array(new Data_Column('USER_ID', $usrId)));
	    	Model_User_Role::retrieve()->save(array('USER_ID' => $usrId, 'ROLE_ID' => $data['role']));
	    	unset ($data['role']);
	    }

 	    // update the employee
	    $data['USER_ID'] = $usrId;
	    $empId = parent::save($data, $ignored);

       	//send email
	    if ($willSendEmail == true) {
	    	$mailer  = new Mailer(Zend_Registry::getInstance()->configuration->smtp);
	    	$mailer->send(
            	$emailAddress, //To
            	$template['SUBJECT'], //Subject
            	$body, //Body
            	Zend_Registry::getInstance()->configuration->smtp->from //From
            );
	    }

	    return $empId;
	}
}