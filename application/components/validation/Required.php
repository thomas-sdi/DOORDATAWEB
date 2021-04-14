<?
require_once APPLICATION_PATH . '/components/validation/Rule.php';
require_once APPLICATION_PATH . '/components/validation/Exception.php';

class Validation_Rule_Required extends Validation_Rule {
	
	protected $_requiredFields;
	protected $_or;
	
	/**
	 * Validates whether $fields are filled
	 * @param array|string $fields Specify one field or array of fields to be checked
	 * @param boolean      $or     If true, then at least one of the fields must be filled
	 *                             (otherwise all of them)
	 */
	public function __construct($fields, $or = false, $severity=self::ERROR) { 
		parent::__construct($severity);
		//Zend_Registry::get('logger')->info('Required fields: ' . var_export($fields, true));
		$this->_or = $or;
		$this->_requiredFields = is_array($fields) ? $fields : array($fields);
		$this->_message = is_array($fields) && count($fields) > 1 ?
		             ($or ? 'At least one of these fields must be filled' :
		                    'All these fields are required'):
	           'This field is required';
	}
	
	public function validate($item) {
		
		// list of fields in $item violated the rule
		$violated = array(); 
		
		// marker to track if at least one field is filled (not relevant if $or = false)
		$one = false;
		
		// get current state of the record
		$entry = array();
		if (array_key_exists('ID', $item)) {
            $entry = $this->_model->fetchEntry($item['ID'], $this->_requiredFields);
		}
		
		// first check if all filled required elements within $item do not have empty value
		foreach ($item as $field => $value) { 
			if (array_search($field, $this->_requiredFields) !== false) {
				if ($value === null || $value == '') {
                    $violated[] = $field;
				}
                else $one = true;
			}
		}
		
		// now check if any of required field is missing in $item
		foreach ($this->_requiredFields as $field) {
			$field = strtoupper($field);
			if (!array_key_exists($field, $item)
			 	&& ($entry[$field] == '' || $entry[$field] === null)) {
			    $violated[] = $field;
			}
		}
		
		if ($this->_or && $one || !count($violated)) // all ok
		    return true;
		else
		    throw new Validation_Exception($this, $violated);
	}
	
}