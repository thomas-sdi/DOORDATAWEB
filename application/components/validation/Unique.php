<?
require_once APPLICATION_PATH . '/components/validation/Rule.php';
require_once APPLICATION_PATH . '/components/data/Column.php';

class Validation_Rule_Unique extends Validation_Rule {
    
    public function __construct($ukFields, $severity=self::ERROR, $message=null) { 
        parent::__construct($severity, $message);
        $this->_fields = is_array($ukFields) ? $ukFields : array($ukFields);
    }
    
    public function validate($item) {
    	$columns = array();
    	
    	// if it's a save operation, retrieve current record state first and filter it out
        if (array_key_exists('ID', $item)) {
        	$ukfields = $this->_fields;
        	array_push($ukfields, new Data_Column('ID', $item['ID']));
        	$entries = $this->_model->fetchEntries($ukfields);
        	$entry = $entries->getItem(1);
        	$columns[] = new Data_Column('ID', new Data_Filter($item['ID'], null, true));
        }
         
        // search if there is any other record except to ours with same ukFields
        foreach ($this->_fields as $field) {
        	$field = strtoupper($field);
        	$filter = array_key_exists($field, $item) ? $item[$field] : $entry[$field];
        	$columns[] = new Data_Column($field, $filter);
        }
        if ($this->_model->fetchEntries($columns)->count())
            throw new Validation_Exception($this, $this->_fields);
        else return true;
    }
    
    public function getMessage($translateFields=null) {
    	if ($this->_message)
    	    return parent::getMessage($translateFields);
    	else {
    	   $fields = $this->getFieldsString($translateFields);
    	   return count($this->_fields) > 1 ?
    	       'Combination of fields ' . $fields . ' must be unique' :
               'Field ' . $fields . ' must be unique';
    	}
    }
    
}