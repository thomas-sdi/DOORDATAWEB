<?
/**
 * Data_Column filter object, this is the main class implementing a simple '=' filter
 * 
 */
class Data_Filter {
	/**
	 * @var Data_Column The column this filter is applicable for
	 */
	protected $_column;
	
	protected $_value;
	protected $_not;
	
	public function __construct($value, $column=null, $not=false) {
		if ($value === "") $value = null;
		$this->_not = $not;
		$this->_column = $column;
		$this->_value  = $value;
	}
	
	public function getColumn() {
		return $this->_column;
	}
	
	public function getCondition() {
		$like = strpos($this->getValue(), '%') !== false;
		
		if ($this->_not) {
		    if ($like) return ' not like ? ';
		    else return $this->_value === null ? ' is not null ' : ' <> ? ';
		}
		else {
		    if ($like) return ' like ? ';
		    else return $this->_value === null ? ' is null ' : ' = ? ';
		}
	}
	
	public function getValue() {
		return str_replace('*', '%', $this->_value);
	}
	
	public function setValue($value) {
		$this->_value = $value;
	}
}