<?
include_once APPLICATION_PATH . '/controllers/Helper.php';
include_once APPLICATION_PATH . '/components/adapters/Mico.php';
include_once APPLICATION_PATH . '/controllers/RESTService.php';
include_once APPLICATION_PATH . '/models/Inspection.php';

class PdfController extends Controller_Abstract 
{
	protected $_serviceUrl;
	protected $_frequency;
	protected $_attempt;
	
	public function init() {
		
		// no view will be defined for this action
		$this->_helper->ViewRenderer->setNoRender(true);
		
		//return JSON
		$this->_helper->layout->setLayout("json");
		
		//get propeties from configuration file
		$config = Zend_Registry::getInstance()->configuration;
		
		//get service url
		$this->_serviceUrl 	= $config->service->pdf->url;
		
		//set how often method will try to send GET request
		$this->_frequency 	= $config->service->pdf->frequency;
		$this->_attempt   	= $config->service->pdf->attempt;
		
		parent::init();
	}
	
	public function generatejavaAction() {

		
		$inspectionId = $this->getNum('inspection_id');
		Zend_Registry::get('logger')->info('Generating PDF for inspection ' . $inspectionId);
		
		try {
			if (!$inspectionId){
				throw new Exception('Inspection Id is invalid');
			}
			
			$message = new stdClass();

			$message->INSPECTION_ID = $inspectionId;
			

			$message->LOCAL_TIMEZONE = $this->getStr('localtimezone'). '';

			
			$message->FORMAT = $this->getStr('reportFormat') . '';
			
			
			if ($message->FORMAT == 'Legacy'){
				// form 1
				$message->FORM1 = new stdClass();
				$message->FORM1->COPIES = $this->getNum('form1_copies');
				
				// form 2
				$message->FORM2 = new stdClass();
				$message->FORM2->COPIES 	  		= $this->getNum('form2_copies') . '';
				$message->FORM2->ALL 		  		= $this->getRadio('form2_pages', 'all') == 'false' ? '0' : '1';
				$message->FORM2->FROM		  		= $this->getNum('form2_from_page') . '';
				$message->FORM2->TO 		  		= $this->getNum('form2_to_page') . '';
				$message->FORM2->NONCOMPLIANT 		= $this->getCheckbox('form2_non_compliant') == 'false' ? '0' : '1'; 
				// $message->FORM2->INSPECTION_DATES 	= $this->getCheckbox('form2_inspection_dates') == 'false' ? '0' : '1'; 
				
				
				// form 3
				$message->FORM3 = new stdClass();
				$message->FORM3->COPIES 	  = $this->getNum('form3_copies') . '';
				$message->FORM3->ALL	      = $this->getRadio('form3_pages', 'all') == 'false' ? '0' : '1';
				$message->FORM3->FROM 		  = $this->getNum('form3_from_page') . '';
				$message->FORM3->TO 		  = $this->getNum('form3_to_page') . '';
				$message->FORM3->NONCOMPLIANT = $this->getCheckbox('form3_non_compliant') == 'false' ? '0' : '1';
				$message->FORM3->PICTURES	  = $this->getCheckbox('form3_with_pictures') == 'false' ? '0' : '1';
				$message->FORM3->DOORDETAIL	  = $this->getCheckbox('form3_door_detail') == 'false' ? '0' : '1';
				$message->FORM3->FRAMEDETAIL  = $this->getCheckbox('form3_frame_detail') == 'false' ? '0' : '1';
				$message->FORM3->HARDWARE     = $this->getCheckbox('form3_hardware') == 'false' ? '0' : '1';
				$message->FORM3->DEFINITIONS  = $this->getCheckbox('form3_definitions') == 'false' ? '0' : '1';
				$message->FORM3->INSPECTION_DATES 	= $this->getCheckbox('form3_inspection_dates') == 'false' ? '0' : '1'; 
				$message->FORM3->RECOMMENDATION  = $this->getCheckbox('form3_recommendation') == 'false' ? '0' : '1';
				
				// form 4
				$message->FORM4 = new stdClass();
				$message->FORM4->COPIES 	  = $this->getNum('form4_copies');
				$message->FORM4->ALL 		  = $this->getRadio('form4_pages', 'all') == 'false' ? '0' : '1';
				$message->FORM4->FROM 		  = $this->getNum('form4_from_page') . '';
				$message->FORM4->TO 		  = $this->getNum('form4_to_page') . '';
				$message->FORM4->NONCOMPLIANT = $this->getCheckbox('form4_non_compliant') == 'false' ? '0' : '1';
				$message->FORM4->PICTURES  	  = $this->getCheckbox('form4_with_pictures') == 'false' ? '0' : '1';
				$message->FORM4->DOORDETAIL   = $this->getCheckbox('form4_door_detail') == 'false' ? '0' : '1';
				$message->FORM4->FRAMEDETAIL  = $this->getCheckbox('form4_frame_detail') == 'false' ? '0' : '1';
				$message->FORM4->HARDWARE     = $this->getCheckbox('form4_hardware') == 'false' ? '0' : '1';
				$message->FORM4->DEFINITIONS  = $this->getCheckbox('form4_definitions') == 'false' ? '0' : '1';
				$message->FORM4->INSPECTION_DATES 	= $this->getCheckbox('form4_inspection_dates') == 'false' ? '0' : '1'; 
				$message->FORM4->RECOMMENDATION  = $this->getCheckbox('form4_recommendation') == 'false' ? '0' : '1';

			}

			if ($message->FORMAT == 'DoorData'){
				// Summary Form
				$message->SUMMARY = new stdClass();
				$message->SUMMARY->COPIES = $this->getNum('summary_copies');
				
				// form 2
				$message->FIRE = new stdClass();
				$message->FIRE->COPIES 	  		= $this->getNum('fire_door_copies') . '';
				$message->FIRE->PICTURES  	  	= $this->getCheckbox('fire_door_pictures') == 'false' ? '0' : '1';
				$message->FIRE->INSPECTION_DATES= $this->getCheckbox('fire_inspection_dates') == 'false' ? '0' : '1';
				$message->FIRE->RECOMMENDATION	= $this->getCheckbox('fire_recommendations') == 'false' ? '0' : '1';
				$message->FIRE->NONCOMPLIANT 	= $this->getCheckbox('fire_door_non_compliant') == 'false' ? '0' : '1';
				$message->FIRE->HARDWARE     	= $this->getCheckbox('fire_door_hardware') == 'false' ? '0' : '1';
				$message->FIRE->DOORDETAIL   	= $this->getCheckbox('fire_door_door_detail') == 'false' ? '0' : '1';
				$message->FIRE->FRAMEDETAIL  	= $this->getCheckbox('fire_door_frame_detail') == 'false' ? '0' : '1';
			}


			$client = new Zend_Http_Client($this->_serviceUrl, array('timeout' => 24000));
			$messageText = json_encode($message);


			Zend_Registry::get('logger')->info('PDF request: ' . $messageText);
			$client->setParameterPost('requestJSON', $messageText);


			$response = $client->request('POST');
			
			


			if (!$response->isError()) {
				// save the report location
				$reportLocation = $response->getBody();
				Model_Inspection::retrieve()->save(array('ID'  => $inspectionId, 'PDF' => $reportLocation));
				
				//return json response 
				$this->view->placeholder('data')->set(
					Zend_Json::encode(array('location' => $reportLocation, 'status' => 'succeeded'))
				);
			} else {
				//if request was unsuccessfull 
				throw new Exception($response->getStatus() . ' ' . $response->getMessage() . ' ' . $response->getBody());
			}
		} 
		catch (Exception $e) {
    		//add record to log
			$error = $e->getMessage() . '\n' . $e->getTraceAsString(); 	
			Zend_Registry::get('logger')->info('Error during PDF generation: ' . $error);
			
			$data = array('location' => '', 'status' => 'failed', 'code' => '500', 'error' => $e->getMessage());
			$data = Zend_Json_Encoder::encode($data);
			$this->view->placeholder('data')->set($data);	
		}
		
	}
	
	public function generatemicoAction() 
	{
		try {
			
    		//By default all errors which occur during execution are set as client errors and will be shown in dialog
			$code = '400';
			
			//get all post variables and put them into vars array
			$vars = array();
			$vars['inspection_id'] 		= $this->getNum('inspection_id');
			$vars['form1_copies'] 		= $this->getNum('form1_copies');
			
			$vars['form2_copies'] 		= $this->getNum('form2_copies');
			$vars['form2_all'] 			= $this->getRadio('form2_pages', 'all');
			$vars['form2_from'] 		= $this->getRadio('form2_pages', 'from');
			$vars['form2_from_page']	= $this->getNum('form2_from_page');
			$vars['form2_to_page'] 		= $this->getNum('form2_to_page');
			$vars['form2_non_compliant']= $this->getCheckbox('form2_non_compliant');
			$vars['form2_with_pictures']= $this->getCheckbox('form2_with_pictures');
			
			$vars['form3_copies'] 		= $this->getNum('form3_copies');
			$vars['form3_all'] 			= $this->getRadio('form3_pages', 'all');
			$vars['form3_from'] 		= $this->getRadio('form3_pages', 'from');
			$vars['form3_from_page'] 	= $this->getNum('form3_from_page');
			$vars['form3_to_page'] 		= $this->getNum('form3_to_page');
			$vars['form3_non_compliant']= $this->getCheckbox('form3_non_compliant');
			$vars['form3_with_pictures']= $this->getCheckbox('form3_with_pictures');
			$vars['form3_definitions']	= $this->getCheckbox('form3_definitions');
			
			$vars['form4_copies'] 		= $this->getNum('form4_copies');
			$vars['form4_all'] 			= $this->getRadio('form4_pages', 'all');
			$vars['form4_from'] 		= $this->getRadio('form4_pages', 'from');
			$vars['form4_blanks'] 		= $this->getRadio('form4_pages', 'blanks');
			$vars['form4_from_page'] 	= $this->getNum('form4_from_page');
			$vars['form4_to_page'] 		= $this->getNum('form4_to_page');
			$vars['form4_non_compliant']= $this->getCheckbox('form4_non_compliant');
			$vars['form4_with_pictures']= $this->getCheckbox('form4_with_pictures');
			$vars['form4_door_detail']  = $this->getCheckbox('form4_door_detail');
			$vars['form4_frame_detail'] = $this->getCheckbox('form4_frame_detail');
			$vars['form4_hardware']     = $this->getCheckbox('form4_hardware');

			$vars['form5_copies'] 		= $this->getNum('form5_copies');
			$vars['form5_all'] 			= $this->getRadio('form5_pages', 'all');
			$vars['form5_from'] 		= $this->getRadio('form5_pages', 'from');
			$vars['form5_blanks'] 		= $this->getRadio('form5_pages', 'blanks');
			$vars['form5_from_page'] 	= $this->getNum('form5_from_page');
			$vars['form5_to_page'] 		= $this->getNum('form5_to_page');
			$vars['form5_non_compliant']= $this->getCheckbox('form5_non_compliant');
			
			//create XML
			$xml = '<PDFPrintOptions xmlns="http://www.logicalpro.com/doordata">';
			
			$xml .= '<DoorData>';
			
					//geneate xml with data using mico adapter
			$mico = new Adapter_Mico();
			$this->log("inspection id: " . $vars['inspection_id']);
			$doorData = $mico->generateXML($vars['inspection_id']);
			if (!strpos($doorData, '<inspections>')) {
				$code = "400";
				throw new Exception($doorData);
			} else {
				$xml .= $doorData;
			}
			
			$xml .= '</DoorData>';
			
			$xml .= '<Form1>';
			$xml .= '<Copies>' 				. $vars['form1_copies'] 		. '</Copies>';
			$xml .= '<AllPages>' 			. 'true' 						. '</AllPages>';
			$xml .= '<StartPage>'			. '0'							. '</StartPage>';
			$xml .= '<EndPage>'				. '0'							. '</EndPage>';	
			$xml .= '<IncludeBlanks>' 		. 'false' 						. '</IncludeBlanks>';
			$xml .= '<IncludeNonCompliant>' . 'false' 						. '</IncludeNonCompliant>';
			$xml .= '<IncludePictures>' 	. 'false' 						. '</IncludePictures>';
			$xml .= '<IncludeDefinitions>' 	. 'false' 						. '</IncludeDefinitions>';
			$xml .= '<IncludeDoorDetail>' 	. 'false' 						. '</IncludeDoorDetail>';
			$xml .= '<IncludeFrameDetail>' 	. 'false' 						. '</IncludeFrameDetail>';
			$xml .= '<IncludeHardware>' 	. 'false' 						. '</IncludeHardware>';
			$xml .= '</Form1>';	
			
			$xml .= '<Form2>';
			$xml .= '<Copies>' 				. $vars['form2_copies'] 		. '</Copies>';
			$xml .= '<AllPages>' 			. $vars['form2_all'] 			. '</AllPages>';
			$xml .= '<StartPage>' 			. $vars['form2_from_page'] 		. '</StartPage>';
			$xml .= '<EndPage>' 			. $vars['form2_to_page'] 		. '</EndPage>';	
			$xml .= '<IncludeBlanks>' 		. 'false' 						. '</IncludeBlanks>';
			$xml .= '<IncludeNonCompliant>' . $vars['form2_non_compliant'] 	. '</IncludeNonCompliant>';
			$xml .= '<IncludePictures>' 	. $vars['form2_with_pictures'] 	. '</IncludePictures>';
			$xml .= '<IncludeDefinitions>' 	. 'false' 						. '</IncludeDefinitions>';
			$xml .= '<IncludeDoorDetail>' 	. 'false' 						. '</IncludeDoorDetail>';
			$xml .= '<IncludeFrameDetail>' 	. 'false' 						. '</IncludeFrameDetail>';
			$xml .= '<IncludeHardware>' 	. 'false' 						. '</IncludeHardware>';
			$xml .= '</Form2>';		
			
			$xml .= '<Form3>';
			$xml .= '<Copies>' 				. $vars['form3_copies'] 		. '</Copies>';
			$xml .= '<AllPages>' 			. $vars['form3_all'] 			. '</AllPages>';
			$xml .= '<StartPage>' 			. $vars['form3_from_page'] 		. '</StartPage>';
			$xml .= '<EndPage>' 			. $vars['form3_to_page'] 		. '</EndPage>';	
			$xml .= '<IncludeBlanks>' 		. 'false' 						. '</IncludeBlanks>';
			$xml .= '<IncludeNonCompliant>' . $vars['form3_non_compliant'] 	. '</IncludeNonCompliant>';
			$xml .= '<IncludePictures>' 	. $vars['form3_with_pictures'] 	. '</IncludePictures>';
			$xml .= '<IncludeDefinitions>' 	. $vars['form3_definitions'] 	. '</IncludeDefinitions>';
			$xml .= '<IncludeDoorDetail>' 	. 'false' 						. '</IncludeDoorDetail>';
			$xml .= '<IncludeFrameDetail>' 	. 'false' 						. '</IncludeFrameDetail>';
			$xml .= '<IncludeHardware>' 	. 'false' 						. '</IncludeHardware>';
			$xml .= '</Form3>';	
			
			$xml .= '<Form4>';
			$xml .= '<Copies>' 				. $vars['form4_copies'] 		. '</Copies>';
			$xml .= '<AllPages>' 			. $vars['form4_all'] 			. '</AllPages>';
			$xml .= '<StartPage>' 			. $vars['form4_from_page'] 		. '</StartPage>';
			$xml .= '<EndPage>' 			. $vars['form4_to_page'] 		. '</EndPage>';	
			$xml .= '<IncludeBlanks>' 		. $vars['form4_blanks'] 		. '</IncludeBlanks>';
			$xml .= '<IncludeNonCompliant>' . $vars['form4_non_compliant'] 	. '</IncludeNonCompliant>';
			$xml .= '<IncludePictures>' 	. $vars['form4_with_pictures'] 	. '</IncludePictures>';
			$xml .= '<IncludeDefinitions>' 	.'false' 						. '</IncludeDefinitions>';
			$xml .= '<IncludeDoorDetail>' 	. $vars['form4_door_detail'] 	. '</IncludeDoorDetail>';
			$xml .= '<IncludeFrameDetail>' 	. $vars['form4_frame_detail'] 	. '</IncludeFrameDetail>';
			$xml .= '<IncludeHardware>' 	. $vars['form4_hardware'] 		. '</IncludeHardware>';
			$xml .= '</Form4>';	
			
			$xml .= '<Form5>';
			$xml .= '<Copies>' 				. $vars['form5_copies'] 		. '</Copies>';
			$xml .= '<AllPages>' 			. $vars['form5_all'] 			. '</AllPages>';
			$xml .= '<StartPage>' 			. $vars['form5_from_page'] 		. '</StartPage>';
			$xml .= '<EndPage>' 			. $vars['form5_to_page'] 		. '</EndPage>';	
			$xml .= '<IncludeBlanks>' 		. $vars['form5_blanks'] 		. '</IncludeBlanks>';
			$xml .= '<IncludeNonCompliant>' . $vars['form5_non_compliant'] 	. '</IncludeNonCompliant>';
			$xml .= '<IncludePictures>' 	. 'false' 						. '</IncludePictures>';
			$xml .= '<IncludeDefinitions>' 	. 'false' 						. '</IncludeDefinitions>';
			$xml .= '<IncludeDoorDetail>' 	. 'false' 						. '</IncludeDoorDetail>';
			$xml .= '<IncludeFrameDetail>' 	. 'false' 						. '</IncludeFrameDetail>';
			$xml .= '<IncludeHardware>' 	. 'false' 						. '</IncludeHardware>';
			$xml .= '</Form5>';	
			
			$xml .= '</PDFPrintOptions>';
			
			//send XML to service generatePDF
			$client = new Zend_Http_Client($this->_serviceUrl, array('timeout' => 6000));
			$response = $client->setRawData($xml, 'text/xml')->request('POST');
			
			
			if ((int)$response->getStatus() == 201) {
				//if request was successful
				$report = $response->getHeader('Location');
				
				//extract only Location=<report_location>
				$reportLocation = strstr($report, '?');
				$report = $this->_serviceUrl . $reportLocation;
				
    	    	//save to inspection returned report_id
				Model_Inspection::retrieve()->save(array('ID'  => $vars['inspection_id'], 'PDF' => $report));
				
				//save status for xml logging
				$status = 'succeeded';
				
				//return json response 
				$data = array('location' => $report, 'status' => $status);
				$data = Zend_Json::encode($data);
				$this->view->placeholder('data')->set($data);
			} else {
				//if request was unsuccessfull 
				$code = "500";
				throw new Exception($response->getStatus() . ' ' . $response->getMessage() . ' ' . $response->getBody());
			}
		} 
		catch (Exception $e) {
    		//add record to log
			$error = $e->getMessage() . '\n' . $e->getTraceAsString(); 	
			Zend_Registry::getInstance()->logger->err($error);
			
    		//save status for xml logging
			$status = 'failed';
			
			$data = array('location' => '', 'status' => $status, 'code' => $code, 'error' => $e->getMessage());
			$data = Zend_Json_Encoder::encode($data);
			$this->view->placeholder('data')->set($data);	
		}
		
		//write xml log
		Helper::saveXmlLog($xml, $vars['inspection_id'], 'Pdf service', $status);
	}
	
	public function retrieveAction() {
		try {
			
			//By default all errors which occur during execution are set as client errors and will be shown in dialog
			$code = '400';
			
			//retrieve PDF
			
			//get report location and inspection id from parameters
			$reportLocation = $this->getRequest()->getQuery('report_location');
			$inspectionId = $this->getRequest()->getQuery('inspection_id');
			
			//if we don't have report id
			if (strlen($reportLocation) == 0) {
				$code = "400";
				throw new Exception('Report location is not specified');
			}
			
			//set report id as parameter
			$reportLocation = str_replace(' ' , '%20', htmlspecialchars_decode($reportLocation));
			Zend_Registry::get('logger')->info('Report location: ' . $reportLocation);
			$client = new Zend_Http_Client($reportLocation);
			$response = $client->request('GET');
			
			//iterativily try to get pdf from service 
			$attempts  = (int)$this->_attempt;  
			while (($attempts > 0) && ((int)$response->getStatus() != 200)) {
				
				//wait some seconds and try to send request again
				sleep((int)$this->_frequency);
				$response = $client->request('GET');
				$attempts--;
			}
			
			if ((int)$response->getStatus() == 200) {
				//if request was successful
				//save to inspection address of report
				$report = 'report' . date('YmdHis') . '.pdf';
				$inspection_record=array(
					'ID'	=> $inspectionId . '',
					'PDF'	=> Helper::saveFile($response->getBody(), '/content/reports/', $report) . ''
				);
				Model_Inspection::retrieve()->save($inspection_record);
				
				//return response
				$data = array('status' => 'succeeded', 'report_location' => $this->view->baseUrl . '/content/reports?id=' . $report);
				$data = Zend_Json::encode($data);
				$this->view->placeholder('data')->set($data);
			} else {
				$code = "500";
				throw new Exception($response->getStatus() . ' ' . $response->getMessage() . ' ' . $response->getBody());
			}
		}
		catch (Exception $e) {
    		//add record to log
			$error = $e->getMessage() . '\n' . $e->getTraceAsString(); 	
			Zend_Registry::getInstance()->logger->err($error);
			
			$data = array('status' => 'failed', 'code' => $code, 'error' => $e->getMessage());
			$data = Zend_Json_Encoder::encode($data);
			$this->view->placeholder('data')->set($data);		
		}
	}
	
	/**
	 * Returns post variable and checks if its numeric
	 *
	 * @param string $post	variable, retrieved by method post
	 * @return int	variable of type int, or exception
	 */
	protected function getNum($post)
	{
		if (strlen($this->_params[$post]) == 0) {
			return 0;
		}
		if (is_numeric($this->_params[$post])) {
			$num = $this->_params[$post];
			if ((int)$num >= 0) {
				return $num;
			} else {
				throw new Exception($post . 'must be positive number');
			}
		} else {
			throw new Exception('Incorrect format of ' . $post . '. Must be numeric');
		}
		
	}
	
	/**
	 * Returns post variable as a string
	 *
	 * @param string $post	variable, retrieved by method post
	 * @return string	variable of type string, or exception
	 */
	protected function getStr($post)
	{
		if (strlen($this->_params[$post]) == 0) {
			return '';
		}	
		return $this->_params[$post];	
	}
	
	/**
	 * Returns post variable and checks if its boolean
	 *
	 * @param string $post	variable, retrieved by method post
	 * @return string	'True' or 'False'
	 */
	protected function getCheckbox($post)
	{
		if ($this->_params[$post] == '1') {
			return 'true';
		} else {
			return 'false';
		}
	}
	
	/**
	 * Gets the value of radiobutton, and compares it with item
	 *
	 * @param string $post	variable, retrieved by method post
	 * @param string $item 	value to compare with
	 * @return string	'True', if post.value == item or 'False'
	 */
	protected function getRadio($post, $item)
	{
		if ($this->_params[$post] == $item) {
			return 'true';
		} else {
			return 'false';
		}
	}
}