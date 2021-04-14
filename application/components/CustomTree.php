<?
require_once APPLICATION_PATH . '/components/Component.php';
require_once APPLICATION_PATH . '/components/data/Column.php';
require_once APPLICATION_PATH . '/components/data/FilterIn.php';
/**
 * Class for Custom_Tree component
 *
 */
class Cmp_Custom_Tree extends Component {

	/**
     * Model storing nodes parent-child info 
     * @var Model_Abstract
     */
	protected $_relModel;
	
	/**
     * Field in $_relModel referencing parent model
     * @var string
     */
	protected $_relParent;
	
	/**
     * Field in $_relModel referencing child model
     * @var string
     */
	protected $_relChild;
	
	/**
	 * Initializes a tree assigning relevant data models
	 */
	public function __construct($id, $model, $relModel, $relParent, $relChild) {
		parent::__construct($id, $model);
		
		// check if rel model can actually be connected to the main model
		if ($relModel) {
		  if (!$relModel->getLink($model, $relParent))
		      throw new Exception('Model "' . $relModel . '" cannot be related to model "'
		              . $model . '" by "' . $relParent . '" field');
		  if (!$relModel->getLink($model, $relChild))
                throw new Exception('Model "' . $relModel . '" cannot be related to model "'
                    . $model . '" by "' . $relChild . '" field');
		}
		  
        $this->_relModel  = $relModel ? $relModel : $model;
		$this->_relParent = $relParent;
		$this->_relChild  = $relChild ? $relChild : 'ID';
	}
	
	/**
	 * Retrieves data in dojo tree format:
	 *  {
	 *   identifier: 'id',
     *   label:      'name',
     *   items: [{name:'', id:'', children:[{_reference:''}], parent: {_reference:''}}]
     *  } 
	 */
	public function fetchJson($queryParams=null) {
		// first fetch plain information about all tree nodes 
		$nodes = $this->_model->fetchEntries();
		
		// if model keeping parent/child info differs from main, get this info as well
		$rels = $this->_relModel == $this->_model ? $nodes : $this->_relModel->fetchEntries(); 
		
		$treeItems = array(); // to store nodes information
		$hashList = array();  // special array storing node.id => treeItems.key for fast access to treeItem by id 
        
		// get names of ID and NAME columns
		$fId = $this->_model->getId();
		$fName = $this->_model->getName();
		
        // first fill the list with all existing nodes
        foreach ($nodes as $node) {
            array_push($treeItems, array('name' => $node[$fName], 'type' => 'top', 'id' => $node[$fId]));
            $hashList[$node[$fId]] = count($treeItems) - 1;
        }
        
        // now mark those having children
        foreach ($rels as $rel) {
        	if( $rel[$this->_relParent] == '') continue;
        	
            // add children to the identified parent element
            $parentItem = &$treeItems[$hashList[$rel[$this->_relParent]]];
            $parentItem['children'][] = array('_reference' => $rel[$this->_relChild]);

            // mark children as not being on the top anymore
            $childItem = &$treeItems[$hashList[$rel[$this->_relChild]]];
            $childItem['type'] = 'normal';
            $childItem['parent'] = array('_reference' => $parentItem['id']);
        }
        
        return Zend_Json::encode(array('identifier' => 'id', 'label' => 'name', 'items' => $treeItems));
	}
	
	
	/**
     * Gets tree hierarchy from a starting point
     * @return array of id's starting from startWithId and down the hierarchy
     */
	public function fetchHierarchy($startWithIds) {
		$hierarchy = array();
		$this->_fetchHierarchy($startWithIds, $hierarchy);
		return $hierarchy;
	}
    
	/**
	 * Infinite loop safe hierarchy builder
	 */
	protected function _fetchHierarchy($startWithIds, &$hierarchy) {
		// first add the starting element(s)
		$hierarchy = array_merge($hierarchy, is_array($startWithIds) ? $startWithIds : array($startWithIds));
		
		// get list of all 1st level children
		$childIds = array();
		$entries = $this->_relModel->fetchEntries(array(
		      $this->_relChild,
		      new Data_Column($this->_relParent, $startWithIds, $this->_relModel)));
		foreach ($entries as $entry) {
			$id = $entry[$this->_relChild];
			// check if ID has repeated
			if (array_search($id, $hierarchy) !== false)
			    throw new Exception('Infinite loop in hierarchy of "' . get_class($this->_relModel) .
			             '" by "' . $this->_relParent . '" = "' . $this->_relChild . '"');
			$childIds[] = $id;
		}
		
		// if there are any children, recursively ask them about their hierarchy
		if (count($childIds)) $this->_fetchHierarchy($childIds, $hierarchy);
	}
}