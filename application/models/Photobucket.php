<?
require_once APPLICATION_PATH . '/models/DBTable/Photobucket.php';
require_once APPLICATION_PATH . '/models/Inspection.php';
require_once APPLICATION_PATH . '/models/Abstract.php';

class Model_Photobucket extends Model_Abstract{
    
    protected function _init(){
        $this->_table = new DBTable_Photobucket();
        $this->addReferenceModel('INSPECTION_ID', Model_Inspection::retrieve());
        
        parent::_init();
    }
    
    public static function retrieve($class=null) {
        return parent::retrieve(__CLASS__);
    }
    
    public function save($data, $ignored=null) {
    	if ($data['URL']) {
    		$data['IMAGE'] = $this->parseUrl($data['URL']);
    	}
    	
    	parent::save($data, $ignored);
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
				return $url;
			}
		}
		return null;
	}
}