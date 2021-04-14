<?

class Validation_Rule {
	
	const ERROR = 0;
	const WARN  = 1;
	protected $_severity;
	protected $_message;
	protected $_fields;
	protected $_id;
	
	/**
	 * @var Model_Abstract
	 */
	protected $_model;
	
	public function __construct($severity=self::ERROR, $message=null) {		
		// assign rule severity
		if ($severity != self::ERROR && $severity != self::WARN)
		    throw new Exception('Severity level ' . $severity . ' is not supported');
		$this->_severity = $severity;
		$this->_message = $message;
	}
	
	public function setModel($model) {
        $this->_model = $model;
	}
	
    public function setId($id) {
        $this->_id = $id;
    }
    
    public function getId() {
    	return $this->_id;
    }
	
	public function validate($item) {
	}
	
	public function getMessage($translateFields=null) {
		return $this->_message;
	}
	
	public function getSeverity() {
		return $this->_severity;
	}
	
	public function getFieldsString($translateMap) {
		$result = ''; $n = count($this->_fields);
		for ($i=0; $i<$n; $i++ ) {
			// translate a field according to the map
			$field = strtoupper($this->_fields[$i]);
			
			$field = '<b>' . (array_key_exists($field, $translateMap) ?
			             $translateMap[$field] : $field) . '</b>';
            
            // add the field into the result string
			if ($i == $n - 1 && $i > 0) // at the end of the array
			    $result .= ' and ' . $field;
			elseif ($i == 0) // we're at the beginning
			    $result .= $field;
			else // somewhere in the middle or beginning
			    $result .= ', ' . $field;
		}
		
		return $result;
	}
}