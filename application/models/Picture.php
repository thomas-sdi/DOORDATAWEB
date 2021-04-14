<?
require_once APPLICATION_PATH . '/models/DBTable/Picture.php';
require_once APPLICATION_PATH . '/models/Door.php';
require_once APPLICATION_PATH . '/models/Abstract.php';
//require_once APPLICATION_PATH . '/components/tools/FtpClient/FtpClient.php';

class Model_Picture extends Model_Abstract{
    
    protected function _init(){
        $this->_table = new DBTable_Picture();
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
 		//$config = Zend_Registry::getInstance()->configuration;
    	try {
    		$picture = $this->fetchEntry($id);
			
    		unlink(ROOT_PATH . '/content/pictures/' . $picture['PICTURE_FILE']);
			
			//also delete the picture from the PDFServer
			/*$ftp = new \FtpClient\FtpClient();
			$ftp->connect($config->pdfserver->ftp->host, true);
			$ftp->login($config->pdfserver->ftp->username, $config->pdfserver->ftp->password);
			$ftp->pasv(true);
			$ftp->remove($picture['PICTURE_FILE']);
			$ftp->close();*/
			
			
    	} catch (Exception $e) {
    		Zend_Registry::getInstance()->logger->err($e->getMessage() . '\n' . $e->getTraceAsString());
    	}
 		return parent::delete($id, $ignored);
    }
	
	/*
	public function updatePictureOnPDFServer($newPicture, $oldPicture = null){
		$config = Zend_Registry::getInstance()->configuration;

		
		//only upload files to FTP if configured to do so in the application settings
		if ($config->pdfserver->ftp->enabled != "yes") return;

		
		//if we decided that we need to upload, go ahead
		try{
			$ftp = new \FtpClient\FtpClient();
			$ftp->connect($config->pdfserver->ftp->host, true);
			$ftp->login($config->pdfserver->ftp->username, $config->pdfserver->ftp->password);
			$ftp->pasv(true);
			
			//upload  the new picture to FTP server
			$ftp->putFromPath($newPicture);
			
			//check if the old picture is still there, and if yes, delete it
			if (!is_null($oldPicture)){
				$ftp->remove($oldPicture);
			}
			$ftp->close();
		}
		catch(FtpException $e){
			//if there was an error, write it to the log
			App::log('Was not able to upload this file to FTP: ' . $filePath . ', error message: ' . $e->message);
		}
		
	}*/
	
    
    public function setControlNames() {
    	$doors = array();
    	foreach (self::retrieve()->fetchEntries(array('DOOR_ID')) as $picture) {
    		$doors[$picture['DOOR_ID']] = true;
    	}
    	foreach ($doors as $doorId => $value) {
    		$controls = self::retrieve()->fetchEntries(array('ID', 'CONTROL_NAME', new Data_Column('DOOR_ID', $doorId)));
    		if ($controls->getTotalItemCount() > 0) {
    			$control = $controls->getItem(1);
    			$record = array('ID' => $control['ID'], 'CONTROL_NAME' => 'Camera1_1');
    			Zend_Registry::getInstance()->logger->info(var_export($record, true));
    			self::retrieve()->save($record);
    		}
    		if ($controls->getTotalItemCount() > 1) {
    			$control = $controls->getItem(2);
    			$record = array('ID' => $control['ID'], 'CONTROL_NAME' => 'Camera2_1');
    			Zend_Registry::getInstance()->logger->info(var_export($record, true));
    			self::retrieve()->save($record);
    		}
    	}
    }
}