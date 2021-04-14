<?
require_once APPLICATION_PATH . '/components/data/Filter.php';

class Data_Filter_In extends Data_Filter {
	
    public function __construct($value, $column=null, $not=null) {
    	if (!count($value)) $value = null;
    	
    	parent::__construct($value, $column, $not);
    }
	
	public function getCondition() {
		if ($this->_not)
            return $this->_value === null ? ' is not null ' : ' not in (?) ';
        else
            return $this->_value === null ? ' is null ' : ' in (?) ';
    }
    
    // apparently Zend_Db_Adapter requires array in this case, so no need for any magic on our end
    //public function getValue() {
        //return '(' . implode(',', $this->_value) . ')';
    //}
    
 	public function setValue($value, $order = null) {
    	// check that boundary is scalar
        if (is_array($value) || is_object($value))
            throw new Exception('Boundary must be either number, date or string.' 
                . ' Arrays or objects are not supported');
                
        // assign value
        $this->_value[$order] = $value;
    }
}