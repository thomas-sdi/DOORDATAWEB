<?

include_once APPLICATION_PATH . '/controllers/Helper.php';
include_once APPLICATION_PATH . '/components/adapters/Mico.php';
include_once APPLICATION_PATH . '/controllers/RESTService.php';
include_once APPLICATION_PATH . '/models/Inspection.php';

class DataimportController extends Controller_Abstract {

	protected $_serviceUrl;
	protected $inspection_id;

	public function indexAction() {
		// no view will be defined for this action
		$this->_helper->ViewRenderer->setNoRender(true);

		//return JSON
		$this->_helper->layout->setLayout("iframe");

		//By default all errors which occur during execution are set as client errors and will be shown in dialog
		$code = '400';

		//get propeties from configuration file
		$config = Zend_Registry::getInstance()->configuration;
		
		$doordataFormat = false;

		//get service url
		switch ($this->_getParam('service_type')) {
			case '0': $this->_serviceUrl = $config->service->avaware;
				break;
			case '1': $this->_serviceUrl = $config->service->comsense;
				break;
			case '2': $this->_serviceUrl = $config->service->protech;
				break;
			case '3': $this->_serviceUrl = $config->service->specworks;
				break;
			case '5': $this->_serviceUrl = $config->service->softwareforhardware;
				break;
			case '6': $this->_serviceUrl = $config->service->excel;
				break;
			case '4': $this->_serviceUrl = null;
				break;
			case '7': $doordataFormat = true;
				break;
			default : $this->_serviceUrl = null;
		}

		//get inspection id
		$this->inspection_id = $this->_getParam("inspection_id");

		$db = Zend_Registry::getInstance()->dbAdapter;		
		try {
			if (!$doordataFormat && !$this->_serviceUrl) {
				throw new Exception('Please specify file format');
			}

			if (!isset($_FILES["myfile"])) {
				throw new Exception('No file was uploaded');
			}

			//read from temporary file
			if ($_FILES["myfile"]["error"] == 0) {
				$myfile = $_FILES["myfile"]['tmp_name'];
				$fp = fopen($myfile, "r");
				$content = fread($fp, filesize($myfile));
				fclose($fp);
			} else {
				throw new Exception('File was uploaded with error ' . $_FILES["myfile"]["error"]);
			}

			// form the URL
			if (!$doordataFormat) {
				$this->sendXMLToService($content);
			} else {
    			// start transaction
    			$db->beginTransaction();
				set_time_limit(1200); ignore_user_abort(1);
				Model_Inspection::retrieve()->import($content, $this->inspection_id, $this->_getParam('overwrite_duplicates') == 1);
				$db->commit();
			}
			
			// return a success response 
			$this->view->placeholder('content')->set(Zend_Json::encode(array('status' => 'succeeded')));
			
		} catch (Exception $e) {
			if ($doordataFormat) {
				$db->rollback();
			}
			
			//add record to log
			$error = $e->getMessage() . '\n' . $e->getTraceAsString();
			Zend_Registry::getInstance()->logger->err('Received exception during importing: ' . $error);

			//save status for xml logging
			$status = 'failed';

			$data = array('status' => $status, 'code' => $code, 'error' => $e->getMessage());
			$data = Zend_Json::encode($data);
			$this->view->placeholder('content')->set($data);
		}
	}

	function sendXMLToService($content) {
		// update the fire rated service info
		if ($this->_getParam('fire_rated') == '1')
			$this->_serviceUrl .= '&fire_rated_only=true';
		else
			$this->_serviceUrl .= '&fire_rated_only=false';
		
		// add new mandatory attribute "overwrite_duplicates"
		if ($this->_getParam('overwrite_duplicates') == '1')
			$this->_serviceUrl .= '&overwrite_duplicates=true';
		else
			$this->_serviceUrl .= '&overwrite_duplicates=false';
			
		Zend_Registry::get('logger')->info('Sending content to service ' . $this->_serviceUrl);
	
		// send content to service
		$client = new Zend_Http_Client($this->_serviceUrl, array('timeout' => 100));
		$response = $client->setRawData($content)->request('POST');
	
		// if response was successful
		if ((int) $response->getStatus() != 200) {
			throw new Exception($response->getStatus() . '\n' . $response->getBody());
		}

		Zend_Registry::get('logger')->info('Xml from service ' . $this->_serviceUrl . ' was retrieved');
		Zend_Registry::get('logger')->info('Sending xml to inspection service');

		// get xml
		$xml = $response->getBody();
		Helper::saveXmlLog($xml, $this->inspection_id, null, null, null);
		Zend_Registry::get('logger')->info('Xml saved in the log file');

		// format response suitable for inspection service
		if ($this->_getParam('overwrite_duplicates') == '1')
			$params = array('overwrite_duplicates' => '1');
		else
			$params = array('overwrite_duplicates' => '0');

		$xml = $this->extractDoorData($xml, $params);

		// send xml to inspection service
		Zend_Registry::get('logger')->info('Executing the import...');
		set_time_limit(1200); ignore_user_abort(1);
		$client = new Zend_Http_Client($this->view->fullBaseUrl . '/inspectionservice', array('timeout' => 1200));
		$response = $client->setRawData($xml)->request('POST');
		Zend_Registry::get('logger')->info('Import executed: ' . $response->getStatus());

		//if request was unsuccessful
		if ($response->getStatus() != 201) {
			throw new Exception($response->getStatus() . '\n' . $response->getBody());
		}
	}

	/**
	 * Adds field inspection_id to each door.
	 *
	 * @param string $content	xml, retrieved from services
	 * @return string	xml in format suitable for inspection service
	 */
	public function extractDoorData($content, $params) {
		$inspection = Model_Inspection::retrieve()->fetchEntry($this->inspection_id);
		$xml = simplexml_load_string($content);
		$doordata = $xml->children('urn:schemas-microsoft-com:xml-diffgram-v1')->children();
		foreach ($doordata->children() as $door) {
			$door->addChild('inspection_id', $inspection['ID']);
			$door->addChild('building_id', $inspection['BUILDING_ID']);
			$door->addChild('inspector_id', $inspection['INSPECTOR_ID']);
			//$door->id = null;
		}

		foreach ($params as $key => $val) {
			$doordata->addChild($key, $val);
		}

		if ($doordata) {
			return $doordata->asXML();
		} else {
			return $content;
		}
	}

}