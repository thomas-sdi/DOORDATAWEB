<?
include_once APPLICATION_PATH . '/controllers/Abstract.php';
include_once APPLICATION_PATH . '/models/Photobucket.php';

class PhotobucketController extends Controller_Abstract 
{
	public function downloadAction()
	{
		// no view will be defined for this action
		$this->_helper->ViewRenderer->setNoRender(true);
		
		//return FILE
    	$this->_helper->layout->setLayout("file");
    	
    	try {
			//get all parameters of request
			$inspectionId = $this->_params['_parent'];
			
			if (strlen($inspectionId) == 0) {
				throw new Exception('Inspection id is undefined');
			}
			
			//get all photobuckets for current inspection id
			$photobuckets = Model_Photobucket::retrieve()->fetchEntries(array('URL', new Data_Column('INSPECTION_ID', $inspectionId)));
			
			//for each photobucket add picture to zip archive
			$zip = new ZipArchive();
			$filename = ROOT_PATH . "/public/photobucket.zip";
			unlink($filename);
			if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) 
    			throw new Exception("Cannot open $filename");
			
    		foreach ($photobuckets as $photobucket) {
				$url = $this->parseUrl($photobucket['URL']);
				$name = basename($url);
				$fileContent = file_get_contents($url);
				//$zip->addFile($url, $name);
				$zip->addFromString($name, $fileContent);
			}
			
			$zip->close();
			
			//send file name to layout
			$this->view->placeholder('fileName')->set($filename);
		} catch (Exception $e) {
    		//add record to log
    		$error = $e->getMessage() . '\n' . $e->getTraceAsString(); 	
    		Zend_Registry::getInstance()->logger->err($error);
    	}
 		
	}
	
	/**
	 * Retrieves url from string
	 *
	 * @param string $longUrl	
	 */
	protected function parseUrl($longUrl) {
		$start = strpos($longUrl, ' src=');
		if ($start) {
			$start = $start + 6;
			$end = strpos($longUrl, '"', (int)$start);
			if ($end) {
				$url = substr($longUrl, (int)$start, (int)$end - (int)$start);
				Zend_Registry::getInstance()->logger->info('Url = ' . $url);
				return $url;
			}
		}
		return null;
	}
}