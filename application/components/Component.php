<?

abstract class Component {
    protected $_id;
    protected $_parent;
    
    /**
     * Keeps information about how two components are interrelated
     * Format: [parentColumnName => parentModel, parentModel.parentColumnName => parentModel.parentModel, ...]
     * until parentModel = parentComponent.model 
     * @var array 
     */
    protected $_parentLink;
    
    /**
     * Main model storing component data
     * @var Model_Abstract 
     */
    protected $_model;
    
    /**
     * Array of children components
     * @var array(Component)
     */
    protected $_children;
    
    public function __construct($id, $model, $parent=null, $parentLink=null) {
    	$this->_id     = $id;
    	$this->_model  = $model;
    	
    	if ($parent) {
    	    // if link to parent is not specified or is in form of column name, try finding it
            if ((!$parentLink || is_string($parentLink)) && is_object($parent)) {
                $parentLink = $model->getLink($parent->getModel(), $parentLink);
            }
            elseif (is_string($parentLink) && is_string($parent)) {
                $parentLink = array($parentLink => $this->_model->getReferenceModel($parentLink));
            }
            if (!$parentLink) {
              	throw new Exception(
                	   'When creating component "' . $id . '" ' .
                	   ' its parent was discarded as its model "' . $model .
                	   '" cannot be related with parent model "');
            }

            $this->_parent = $parent;
            $this->_parentLink = $parentLink;
            
            // setup parent-child relationship, if parent is an object
            if (is_object($parent))
                $this->_parent->addChild($this);
    	}
    }

    /**
     * Returns json data about the component
     */
    public abstract function fetchJson($filter=null);
    
    /**
     * Adds another grid as a child to the current one
     *
     * @param Component $child
     */
    public function addChild($child) {
        $this->_children[] = $child;
    }
    
    public function getChildren() {
        return $this->_children;
    }
    
    public function getId() {
        return $this->_id;
    }
    
    public function getModel() {
        return $this->_model;
    }
    
    public function getParent() {
        return $this->_parent;
    }
    
    public function getParentLink() {
        return $this->_parentLink;
    }
}