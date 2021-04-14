<?
require_once APPLICATION_PATH . '/components/Component.php';
require_once APPLICATION_PATH . '/components/data/Column.php';
require_once APPLICATION_PATH . '/components/data/FilterIn.php';
/**
 * Class for Drop Down component
 *
 */
class Cmp_Drop_Down extends Component {
	
	/**
	 * @var Data_Column
	 */
	protected $_dataColumn;
	protected $_dropdownFilter;
	
	/**
	 * @param string      $id
	 * @param Data_Column $dataColumn
	 */
	public function __construct($id, $dataColumn, $parent=null, $parentLink=null, $dropdownFilter) {
		parent::__construct($id, $dataColumn->getModel(), $parent, $parentLink);
		
		$this->_dataColumn = $dataColumn;
		$this->_dropdownFilter = $dropdownFilter;
	}
	
	/**
     * Retrieves data in dojo dropdownlist format:
     *  {
     *   identifier: 'id',
     *   items: [{name:'', id:'',label:''}]
     *  } 
     */
    public function fetchJson($nameFilter='*', $parentFilter=null, $start=0, $noEmptyChoice=false, $dropdownFilter=null, $byId=false) {    
    	// start preparing json compatible result array sorted by name
        $result['identifier'] = 'ID';
        $result['items']      = array();
        
        // add empty row if needed
        if (!$noEmptyChoice && !$byId) {
            $result['items'][] = array('ID'=>null, 'name'=>'', 'label'=>'');
        }
        foreach ($this->fetchEntries($dropdownFilter, $nameFilter, $parentFilter, $start, $byId) as $entry) {

            if(isset($entry['LAST_NAME'])){
                $result['items'][] = array('ID'=>$entry['ID'], 'name' => $entry['NAME']." ".$entry['LAST_NAME'], 'label' => $entry['NAME']);
            }else{
                $result['items'][] = array('ID'=>$entry['ID'], 'name' => $entry['NAME'], 'label' => $entry['NAME']);
            }

        }

        return Zend_Json::encode($result);
    }
    
    public function fetchEntries($dropdownFilter=null, $nameFilter='*', $parentFilter=null, $start=0, $byId=false) {
        // don't bother if filter will not be met for sure 
        if ($nameFilter == '') {
            $result['items'] = array();
            return $result;
        }
        if (!$dropdownFilter) $dropdownFilter = $this->_dropdownFilter;

        // apply value filter to name
        if (!$byId) {
           $this->_dataColumn->setFilter($nameFilter);
           $columns = array('ID', 'NAME' => $this->_dataColumn);
       }
       else {
           $columns = array('ID'   => new Data_Column('ID', $nameFilter),
            'NAME' => $this->_dataColumn);
       }

        // if there is a parent we should try and filter reference values
        /*if ($this->getParent() && $parentFilter) {
            $parentModel = $this->getParent()->getModel();
            $parentPath  = $this->getModel()->getLink($parentModel);
            if ($parentPath) {
                array_push($columns, new Data_Column($parentPath, $parentFilter, $this->getModel()));
            }
        }*/
        
        // add dropdown filter
        if ($dropdownFilter != null) {
        	if (!is_array($dropdownFilter)) $dropdownFilter = array($dropdownFilter);
        	foreach ($dropdownFilter as $dropdownFilterColumn){
        		// if there are dependent columns, update filter values
        		if ($parentFilter) {
        			$filterValues = $dropdownFilterColumn->getFilter()->getValue();
        			if (!is_array($filterValues)) $filterValues = array($filterValues);

                    foreach ($filterValues as $order => $filterValue) {


                        $pattern = "/Inspectors/i";
                        if(preg_match($pattern, $filterValue)){

                            $columns['FIRST_NAME']='LAST_NAME';

                            print_r($filterValue);
                            // print_r($columns);
                            // die();

                        }


                        $depColumnName = substr($filterValue, 1);
                        if (substr($filterValue, 0, 1) == '$' && array_key_exists($depColumnName, $parentFilter))
                         $dropdownFilterColumn->setFilter($parentFilter[$depColumnName], $order);
                 }

             }

             array_push($columns, $dropdownFilterColumn);
         }

     }

     $sortBy = null;
     if ($this->getModel() == Model_Dictionary::retrieve()) $sortBy = 'VALUE_ORDER';
     else $sortBy = $this->_dataColumn->getField();

     return $this->getModel()->fetchEntries($columns, $start, true, $sortBy);
 }

 public function getDisplayedValue($id) {    	
     if ($id == null || $id == '') return null;

    	// get the last model the column references
     $refModel = $this->_dataColumn->getRefModel();
     if (!$refModel) throw new Exception('No model: ' . $this->_dataColumn->getField());
     $displayColumn = $this->_dataColumn->getDisplay();
     if (!$displayColumn) $displayColumn = $this->_dataColumn->getField(); 	

     $entry = $refModel->fetchEntry($id, array($displayColumn));
     return $entry ? $entry[$displayColumn] : null;
 }
}