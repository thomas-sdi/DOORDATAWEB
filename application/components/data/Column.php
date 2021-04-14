<?
require_once APPLICATION_PATH . '/components/data/Filter.php';
require_once APPLICATION_PATH . '/components/data/FilterBetween.php';
require_once APPLICATION_PATH . '/components/data/FilterIn.php';
class Data_Column {
	/**
	 * @var Model_Abstract The model having values for this column
	 */
	protected $_model;    // dbname of the column
	
	/**
     * @var array  The model storing the data for $_display 
     */
	protected $_refModel; 
	
    protected $_linkPath; // path to a referenced model to display a column from
	
	protected $_field;    // dbname of the column from original model 
	protected $_type;     // datatype of the $_field in database, for example "date"
	protected $_default;  // default value of $_field in database
	protected $_required; // whether $_field is required in database
	protected $_display;  // column to be fetched from refTable
	protected $_naturalSort;  // sort by length first, then by value 
	

	/**
	 * @var Data_Filter The filter applied to the column
	 */
	protected $_filter;   // Filter:  rules to filter out some of the rows basing on column's value
	
	/**
	 * Constructor for the column
	 * 
	 * @param fieldPath string|array       Name of db column or link path to a Display column
	 * @param $filter   string|Data_Filter Filter value for the $field 
	 * @param $model    Model_Abstract     Model the $field belongs to (or the link path starts from)
	 * @param $display  string             Column found by fieldPath to fetch 
	 */
	public function __construct($fieldPath, $filter=null, $model=null, $display=null) {
		//if (!$fieldPath) throw new Exception('Cannot create an empty column');
		if ($display) $display = strtoupper($display);
        
		// parse out original db field and link path
		if (is_array($fieldPath)) { // it is actually a path to the refModel
			if (!$model) throw new Exception('Cannot create a column without model having a link path');
			$field = key($fieldPath);
			if (substr($field, 0, 1) != '$') // otherwise a column does not belong to this model
			     $this->_field = strtoupper(key($fieldPath));
			
			// now enrich linkPath with references to models if not specified
			$curModel = $model; $path = array();
			foreach ($fieldPath as $fieldName => $refModel) {
				if (is_string($refModel)) // then it is actually just the name of column from previous model
					$path[$refModel]  = $curModel;
			    else // normal case
			        $path[$fieldName] = $refModel;
			    $curModel = $refModel;
			}
			
			$this->_linkPath = $path;
		} elseif ($model && $display) { // $fieldPath is a starting column in linkPath to find $display
			// then we still need to search among referenced models to guess the path
			$path = $model->getColumnPath($display, $fieldPath);
			if (!$path) throw new Exception('Column "' . $display .
			         '" cannot be found within the reference models of "' . $model . '"');
			$this->_linkPath = $path;
			$this->_field    = $fieldPath;
		} else {
			$this->_field = $fieldPath;
			$this->_refModel = $model;
		}
		
	    // assign other parameters
	    $this->_model    = $model;
        
        if ($this->_linkPath) {
        	$this->_refModel = end($this->_linkPath);
        	$this->_display  = $display ? $display : 'ID';
        }
        
        // identify column's type
       	$mdl = $display ? $this->_refModel : $this->_model;
        $fld = $display ? $this->_display  : $this->_field;
        if ($model) {
        	$this->_type = $mdl->getColumnType($fld);
        }
       
	    $this->_default  = ($this->_field && $this->_default === null && $model) ?
	                              $model->getColumnDefault($this->_field) : $this->_default;
	    $this->_required = ($this->_field && $this->_required === null && $model) ?
                                  $model->getColumnRequired($this->_field) : $this->_required;
	    $this->setFilter($filter);
	}
	
	public function setFilter($value, $second_value=null) {
		if ($second_value != null)
		    $this->_filter = new Data_Filter_Between($value, $second_value, $this);
		elseif ($value instanceof Data_Filter)
            $this->_filter = $value;
        elseif (is_array($value))
        	$this->_filter = new Data_Filter_In($value, $this);
        elseif ($value === null)
            $this->filter = null;
        else
            $this->_filter = new Data_Filter($value . '', $this);
	}
	
	public function getFilter() {
		return $this->_filter;
	}
	
    public function getModel() {
        return $this->_model;
    }
	
	public function getType() {
		return $this->_type;
	}
	
	public function getDefault() {
		if ($this->_default !== null && $this->_default !== '')
		    return $this->_default;
		elseif ($this->getType() == 'date' || $this->getLinkPath()) {
		    return null;
		}
	    else return null;
	}
	
	public function setDefault($value) {
		$this->_default = $value;	
	}
	
    public function getRequired() {
        return $this->_required;
    }
	
	public function getField() {
		return strtoupper($this->_field);
	}
	
    public function getDisplay() {
        return strtoupper($this->_display);
    }
    
    public function getRefModel() {
    	return $this->_refModel;
    }
	
    public function getLinkPath() {
    	if ($this->_linkPath)
    	   reset($this->_linkPath);
    	
        return $this->_linkPath;
    }
	
	public function getNaturalSort() {
		return $this->_naturalSort;
	}
}