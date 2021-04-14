<?
include_once APPLICATION_PATH . '/controllers/Helper.php';
include_once APPLICATION_PATH . '/controllers/RESTService.php';
include_once APPLICATION_PATH . '/models/Download.php';
require_once APPLICATION_PATH . '/models/Email.php';
require_once APPLICATION_PATH . '/components/tools/Mailer.php';

class DownloadController extends Controller_Abstract 
{
	protected $_serviceUrl;
	protected $inspection_id;
	
	public function indexAction()
	{
		// no view will be defined for this action
		$this->_helper->ViewRenderer->setNoRender(true);
		
		//return JSON
    	$this->_helper->layout->setLayout("file");
    	
    	//get properties from configuration file
		$config = Zend_Registry::getInstance()->configuration;
		
		try {
			//get all parameters of request
			$email 	= $this->_params['email'];
			$hash 	= $this->_params['hash'];
			$id		= $this->_params['id'];
			
			$files = scandir(ROOT_PATH . $config->download->dir);
			$i = 0;
			while (!is_file(ROOT_PATH . $config->download->dir . $files[$i])) $i++;
			
			if (!is_file(ROOT_PATH . $config->download->dir . $files[$i]))
				throw new Exception('Could not find the Doordata Client file');
				
			$filePath = ROOT_PATH . $config->download->dir . $files[$i];
			
			//calculate hash
			$calculateHash = md5($email . $config->download->secret);
			
			$download = Model_Download::retrieve()->fetchEntry($id);
						
			//check if calculated has matches old hash
			if ($calculateHash != $hash) 
				throw new Exception('Calculated hash does not match with old hash');
			
			//check if calculated hash matches hash from DB
			if ($download['HASH'] != $hash) 
				throw new Exception('Calculated hash does not match with hash from DB');
			
			//check if file was not downloaded before
			if (Model_Dictionary::getItemById($download['DOWNLOADED']) != 'No') 
				throw new Exception('File was already downloaded');
			
			//check if file date expired
			if ((int)strtotime($download['CREATE_DATE']) + (int)$config->download->expire < (int)time())
				throw new Exception('Date to download file expired');
			
			if (!file_exists(ROOT_PATH . $config->download->file)) 
				throw  new Exception('The file doesn not exist'); 
			
			//send file name to layout
			$this->view->placeholder('fileName')->set($filePath);
			$download['DOWNLOADED'] = Model_Dictionary::getIdByItem('Yes', 'Logical');
			Model_Download::retrieve()->save($download);
		} catch (Exception $e) {
    		//return HTTP 500 Server Error if error occured
    		$this->_helper->layout->setLayout("http500servererror");
    		
			//add record to log
    		$error = $e->getMessage() . '\n' . $e->getTraceAsString(); 	
    		Zend_Registry::getInstance()->logger->err($error);
    		
    		$this->view->placeholder('error_message')->set($e->getMessage());
    	}
 		
	}
	
	public function sendAction() {
		// no view will be defined for this action
		$this->_helper->ViewRenderer->setNoRender(true);
		
		//return JSON
    	$this->_helper->layout->setLayout("json");
   		
		//get propeties from configuration file
		$config = Zend_Registry::getInstance()->configuration;
		
		try {
			//get all parameters of request
			$emailAddress 	= $this->_params['email'];
						
			//calculate hash
			$hash = md5($emailAddress . $config->download->secret);
			
			$download = array('EMAIL'	=> $emailAddress,
							  'HASH'	=> $hash,
							  'CREATE_DATE'	=> date('Y-m-d H:i:s'),
							  'DOWNLOADED'	=> Model_Dictionary::getIdByItem('No', 'Logical'));
			
			$id = Model_Download::retrieve()->save($download);
			
			//create link for downloading
			$link = $this->view->fullBaseUrl . '/download?id=' . $id . '&email=' . $emailAddress . '&hash=' . $hash;
			
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
	    		
	    	//get email template from the database
	    	$template = Model_Email::retrieve()->fetchEntry(
	    		null, 
	    		array(new Data_Column('IDENTITY', 'client download link'),'SUBJECT','MESSAGE')
	    	);
	    		
	    	//personalize the mesasge
	    	$body = str_replace('[==LINK==]', $link, $template['MESSAGE']);
	    	
	    	//send the message
	    	$mailer  = new Mailer($config->smtp);
	   		$mailer->send(
            	$emailAddress, //To
            	$template['SUBJECT'], //Subject
            	$body, //Body
            	$config->smtp->from //From
            );         	
			Zend_Registry::get('logger')->info('Link for downloading ' . $link . ' was send to email ' . $emailAddress);
            	
			$status = 'ok';
			
			//return json response 
			$data = array('status' => $status, 'link' => $link);
			$data = Zend_Json::encode($data);
			$this->view->placeholder('data')->set($data);
					
		} catch (Exception $e) {
    		
			//add record to log
    		$error = $e->getMessage() . '\n' . $e->getTraceAsString(); 	
    		Zend_Registry::getInstance()->logger->err($error);
    		
    		$status = 'failed';
    		
    		$data = array('status' => $status, 'error' => $e->getMessage());
			$data = Zend_Json::encode($data);
    		$this->view->placeholder('data')->set($data);	
 		}	
 	}
 	
 	public function uploadAction() {
 		
		$this->_helper->ViewRenderer->setNoRender(true);
		$this->_helper->layout->setLayout("iframe");
    	
    	//get propeties from configuration file
		$config = Zend_Registry::getInstance()->configuration;
		
		try {
			if (!is_uploaded_file($_FILES["myfile"]['tmp_name'])) {
				throw new Exception('No file was uploaded');
			}
				
			//delete all files from download directory
			$files = scandir(ROOT_PATH . $config->download->dir);
			foreach ($files as $file) {
				if (is_file(ROOT_PATH . $config->download->dir . $file)) {
					unlink(ROOT_PATH . $config->download->dir . $file);
				}
			}
			
			//move temporary file
			if ($_FILES["myfile"]["error"] == 0) {
				move_uploaded_file($_FILES["myfile"]['tmp_name'], ROOT_PATH . $config->download->dir . $_FILES["myfile"]['name']);			
				
				$data = array('status' => 'ok');
				$this->view->placeholder('content')->set(Zend_Json::encode($data));
			} else {
				throw new Exception('File uploaded with error');
			}
		} catch (Exception $e) {
			//add record to log
    		$error = $e->getMessage() . '\n' . $e->getTraceAsString(); 	
    		Zend_Registry::getInstance()->logger->err($error);
    		
    		$status = 'failed';
    		
    		$data = array('status' => $status, 'error' => $e->getMessage());
			$data = Zend_Json::encode($data);
    		$this->view->placeholder('content')->set($data);
    	}
 	}
}