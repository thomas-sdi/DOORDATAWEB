<?
/**
 * Data_Column filter object, this is the main class implementing a simple '=' filter
 * 
 */
class Data_Filter_Between extends Data_Filter {	
	/**
	 * Filter by from/to boundaries 
	 * @param string|date|number $value_from From boundary, can be null
	 * @param string|date|number $value_to   To boundary, can be null
	 */
	public function __construct($value_from, $value_to, $column=null) {
		$this->_column = $column;
		$this->setValue($value_from, true);
		$this->setValue($value_to, false); 
	}
	
	public function getCondition() {
	    //return " BETWEEN ? and ? "; // TODO: figure out how to use ? instead of hardcoded values
	    if (is_numeric($this->getValueFrom())) {
	       if ($this->getValueFrom() && $this->getValueTo())
	           return " BETWEEN '" . $this->getValueFrom() . "' AND '" . $this->getValueTo() . "'";
	       elseif($this->getValueFrom())
	           return " >= '" . $this->getValueFrom() . "'";
	       else
               return " <= '" . $this->getValueTo() . "'";
	    }
	    else {
	    	if ($this->getValueFrom() && $this->getValueTo())
	            return " BETWEEN CAST('" . $this->getValueFrom() . "' AS DATE)" . " AND CAST('" . $this->getValueTo() . "' AS DATE)";
	        elseif($this->getValueFrom())
	            return " >= CAST('" . $this->getValueFrom() . "' AS DATE)";
	        else
	            return " <= CAST('" . $this->getValueTo() . "' AS DATE)";
	    }
	}
	
	public function getValue() {
		return $this->_value;
	}

	public function getValueFrom() {
		return $this->_value[0];
	}

	public function getValueTo() {
        return $this->_value[1];
    }
     
    /**
     * Sets one of the boundaries
     * @param string|date|number $value
     * @param boolean            $which true == from, false == to
     */
    public function setValue($value, $which = null) {
    	// check that boundary is scalar
        if (is_array($value) || is_object($value))
            throw new Exception('Boundary must be either number, date or string.' 
                . ' Arrays or objects are not supported');
                
        // assign value
    	$which ? $this->_value[0] = $value : $this->_value[1] = $value;
    	
    	// check that both boundaries are presented
    	//if ($this->getValueFrom() === null && $this->getValueTo() === null)
            //throw new Exception('Cannot create FilterBetween: at least one boundary must be presented');
    }
}