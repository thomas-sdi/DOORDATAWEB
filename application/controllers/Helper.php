<?
require_once APPLICATION_PATH . '/models/Integration.php';
//require_once APPLICATION_PATH . '/models/Abstract.php';

class Helper
{
	/**
	 * changes 'Yes' and 'No' to 1 and 0
	 *
	 * @param string $item	param = 'Yes' or 'No'
	 * @return int	1 or 0
	 */
	public static function getBool($item) {
		$item = strtolower(trim($item));
		if (strcmp(strtolower($item), 'yes') == 0) {
			return true;
		} else if (strcmp(strtolower($item), 'no') == 0) {
			return FALSE; 
		} else {
			return NULL;
		}
	}
	
	/**
	 * deletes all records from table $model with door_id equal to $items->item->door_id  
	 *
	 * @param array 			$items		array containing items with door_id
	 * @param Model_Abstract 	$model		model associated with table
	 */
	public static function deleteDoorIds($items, $model) {
		$deleteDoorIds = array();
    	foreach($items as $item) {
    		array_push($deleteDoorIds, (string)$item->door_id);
    	}
      	$deleteDoorIds = array_unique($deleteDoorIds);
      	$model->deleteEntries(array(new Data_Column('DOOR_ID', $deleteDoorIds)));
      			
	}
	
    /**
     * Decodes base64binary to binary and saves to file
     *
     * @param base64 $data		file in format base64
     * @param string $dirPath	relativefolder path
     * @param string $fileName	file name
     * @return string			relative full file name of saved file				
     */
    public static function saveBase64File($data, $dirPath = null, $fileName) {
    	if ($dirPath === null) {
    		$dirPath = '/public/';
    	}
    	
    	if ($data != '') {
    		$fileName = $dirPath . $fileName;
    		$fp = fopen(ROOT_PATH . $fileName,'w+');
    		fwrite($fp, base64_decode($data));
    		fclose($fp);
    		return $fileName;
    	} else {
    		return 0;
    	}
    	
    }
    
    /**
     * Saves data to file
     *
     * @param string $data		to save 
     * @param string $dirPath	relative folder path
     * @param string $fileName	file name
     * @return string			relative full file name of saved file				
     */
    public static function saveFile($data, $dirPath = null, $fileName) {
    	if ($dirPath === null) {
    		$dirPath = '/public/';
    	}
    	
    	if ($data != '') {
    		$fileName = $dirPath . $fileName;
    		$fp = fopen(ROOT_PATH . $fileName,'w+');
    		fwrite($fp, $data);
    		fclose($fp);
    		return $fileName;
    	} else {
    		return 0;
    	}
    	
    }

    /**
     * Reads file
     *
     * @param string $fileName	relative path to file
     * @return string			file data				
     */
    public static function readFile($fileName) {
    	if ($fileName != '') {
    		$fileName = ROOT_PATH . $fileName;
    		$fp = fopen($fileName,'rb');
    		$content = fread($fp, filesize($fileName));
    		fclose($fp);
    		return $content;
    	} else {
    		return null;   	
    	}
    }
    
    /**
     * deletes all records with empty values
     *
     * @param array $arr	array to change	
     * @return array with deleted empty values
     */
    public static function deleteEmptyStrings($arr) {
    	$temp = array();
    	foreach ($arr as $key=>$value) {
    		if (strlen($value) != 0) {
    			$temp[$key] = htmlspecialchars(utf8_encode($value), ENT_NOQUOTES);
    		}
     	}
    	return $temp;
    }
    
    /**
     * changes all records with empty values to null
     *
     * @param array $arr	array to change	
     * @return array with cahnged empty values
     */
    public static function changeEmptyStrings($arr) {
    	foreach ($arr as $key=>$value) {
    		if (strlen($value) == 0) {
    			$arr[$key] = NULL;
    		} else {
    			$arr[$key] = htmlspecialchars_decode(utf8_decode($value));
    		} 
     	}
    	return $arr;
    }
    
    /**
     * Retrieves date from string
     *
     * @param string $date	string containing date in format "yyyy-mm-ddThh:mm:ss-hh:mm"
     * @return string date in format "yyyy-mm-dd"
     */
    public static function getDate($date) {
      	if ($date === null || trim($date) == '') {
    		return null;
    	} else if (strlen($date) < 10) {
    		throw new Exception('Wrong format of date ' . $date);
    	} else {
    		return substr($date, 0, 10);
    	}
    }
    
    /**
     * Add/update record to XML log
     *
     * @param string 	$xml 			data to save
     * @param int 		$inspectionId 	number of inspection
     * @param string 	$type			type of record: 'Inspection Service', 'Mico Service', 'PDF Service',
     * 									or http response (in case of update)
     * @param string 	$status			status of xml: 'succeeded', 'failed', 'waiting'
     * @param string 	$update_record  integration record id to update
     * 
     * @return  string 	id of record that was successfully added/updated, or null in case of error 
     */
    public function saveXmlLog($xml, $inspectionId, $type, $status, $update_record=null) {
		try {
    		if ($update_record == null)
				$integration_record = array(
    				'DATE'			=> date('Y-m-d') . '',
    				'TIME'			=> date('H:i:s') . '',
    				'INSPECTION_ID'	=> $inspectionId . '',
    				'TYPE'			=> Model_Dictionary::getIdByItem($type, 'Integration Type') . '',
    				'REQUEST'		=> Helper::saveFile($xml, '/logs/integration/', 'insp'.date('YmdHis').'.xml') . '',
    				'RESPONSE'		=> '',
    				'STATUS'  		=> Model_Dictionary::getIdByItem($status, 'Integration Status') . ''
    			);
			else
				$integration_record = array(
    				'ID'			=> $update_record . '',
    				'RESPONSE'		=> $type . '',
    				'STATUS'  		=> Model_Dictionary::getIdByItem($status, 'Integration Status') . ''
    			);
			$integration_record = Helper::deleteEmptyStrings($integration_record);
			return Model_Integration::retrieve()->save($integration_record); // return record's id
		}
		catch (Exception $e) {
			$error = $e->getMessage() . '\n' . $e->getTraceAsString();
			Zend_Registry::getInstance()->logger->err($error);
		}
		return null;
    }
}