<?
require_once APPLICATION_PATH . '/components/validation/Exception.php';
require_once APPLICATION_PATH . '/components/validation/Rule.php';
require_once APPLICATION_PATH . '/components/validation/Required.php';

abstract class Model_Abstract
{   
    /**
     * Main DBTable representing the model's data
     * @var Zend_Db_Table
     */
    protected $_table;
    
    /**
     * Primary key field 
     * @var string
     */
    protected $_id = 'ID';
    
    /**
     * User-friendly unique identifier or model's row 
     * @var string
     */
    protected $_name;
    
    /**
     * An array of models linked to the current by some field specified as key
     *
     * @var array[$refField=>$refModel]
     */
    protected $_referenceModels = array();

    /**
     * An array of validation rules which are applied whenever any data is saved to the model
     * 
     * @var array[$ruleId =>$rule], where $rule is an instance of Validation_Rule
     */
    protected $_validationRules = array();

    /**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
    protected function __construct() {
    }

    /**
     * Singleton pattern implementation makes "clone" unavailable
     *
     * @return void
     */
    protected function __clone()
    {}
    
    protected function _init(){
        // automatically add rules for all columns being required in database
        $requiredColumns = array();
        foreach ($this->getColumns() as $columnName) {
            $columnName = strtoupper($columnName);
            if ($this->getColumnRequired($columnName) && $columnName != 'ID')
              $requiredColumns[] = $columnName;
      }
      if (count($requiredColumns) > 0)
        $this->addValidationRule(new Validation_Rule_Required($requiredColumns));

    if(!$this->_name)
        $this->_name = 'ID';
}

public static function retrieve($class) {
  if( !array_key_exists($class, Zend_Registry::getInstance()->models)) {
     $model = new $class();
     Zend_Registry::getInstance()->models[$class] = $model;
     $model->_init();
 }
 else
     $model = Zend_Registry::getInstance()->models[$class];

 return $model;
}

public final function getTable(){
    return $this->_table;
}

    /**
     * Validates a row using predefined rules.
     * @param array $data    Row to be validated
     * @param array $ignored List of warnings to be ignored
     * @return array List of problems (can be null if no problems were found)
     */
    public function validate($data, $ignored) {
        $allProblems = array();
        foreach ($this->_validationRules as $rule) {
            // don't bother if the rule is warning and ignored
            if ($rule->getSeverity() == Validation_Rule::WARN &&
                $ignored && array_search($rule->getId(), $ignored) !== false)
                continue;

            // get all problems for $data and append them to the list of all problems
            try {
                $rule->validate($data);
            } catch (Validation_Exception $v) {
                $allProblems = array_merge($allProblems, $v->getProblems());
            }
        }
        return count($allProblems) ? $allProblems : null;
    }
    
    /**
     * Inserts or updates a row in the table after performing validation checks
     * @param array $data    The row to be saved
     * @param array $ignored List of warnings asked to be ignored
     * @return string New id (if this was an insert) or existing (if update)   
     */
    public function save($data, $ignored=null){
        // perform validation of $data
        $problems = $this->validate($data, $ignored);
        if ($problems) throw new Validation_Exception($problems);
        
        //check if record with such data already exists. In that case just return its id
        /*$filter = array('ID');
        foreach ($data as $key=>$value) {
            $filter[] = new Data_Column($key, $value);
        }
        
        $rows = $this->fetchEntries($filter);
        if ($rows->count() > 0) {
            $row = $rows->getItem(1);
            $data['ID'] = $row['ID'];
        }*/
        
        // figure out if it's save (id presented) or insert (no id) operation
        if(array_key_exists($this->getId(), $data)) {
         $id = $data[$this->getId()];
        //   unset($data['ID']);
     } else $id = null;  

        // use native table operations to update data
     if ( $id == null ) {
       $i = $this->getTable()->insert( $data );
       return $i;   
   } elseif (count($this->getTable()->find($id)) > 0) {
       $this->getTable()->update( $data, "ID=" . $id );
       return $id;
   } else {
       return $this->getTable()->insert( $data );
   }
}

public function delete($id, $ignored=null) {
        // TODO: support delete validation
   return $this->_table->delete( "ID=" . $id );
}

    /**
     * This function searches records in DB using fetchEntries and deletes these records
     *
     * @param array $columns Requested columns to search: columnId/empty => Data_Column/columnName
     *                                           If not specified - all columns are returned
     */
    public function deleteEntries($columns=null) {
        if (!array_key_exists('ID', $columns)) {
            $columns[] = 'ID';
        }
        $records = $this->fetchEntries($columns);
        foreach ($records as $record) {
            $this->delete($record['ID']);
        }
    }

    /**
     * Queries database and returns filtered records for main model's table
     * as well as requested columns for referenced ones
     *
     * @param array               $columns       Requested columns to show: columnId/empty => Data_Column/columnName
     *                                           If not specified - all columns are returned
     * @param int                 $start         Number of row from where to start fetch
     * @param boolean             $allRows       If true, returns all rows in one page. If false - every 25 rows in page
     * @param string|Data_Column  $sortBy        Column to sort the resultset by
     * @param boolean             $sortDirection Direction: true=ascending, false=descending
     * @return array Entries from database
     */
    public function fetchEntries($columns=null, $start=null, $allRows=false, $sortBy=null, $sortDirection=true, $rowsPerPage=20) {

        // start constructing db query
        $db = Zend_Registry::getInstance()->dbAdapter;
        $select = $db->select();
        

        // if no columns are specified, retrieve all columns within the model
        if (!$columns) $columns = $this->getColumns();
        
        // specify main table to start fetch from
        $thisTableName = $this->_table->info('name');
        $select->from($thisTableName, '');


        
        // we need to maintain history of tables being joined not to joint excessively
        $joinHistory = array();
        
        $strMatchQuery = ' ';

        // figure out sortBy column name
        
        if($sortBy == 'BUILDING' || $sortBy == 'BUILDING_ADDRESS' || $sortBy == 'BUILDING_OWNER' || $sortBy == 'INSPECTOR_NAME'){
            $sortByField = $sortBy;
        }else{

        $sortByField = $sortBy instanceof Data_Column ? $sortByField = $sortBy->getField() : $sortBy;
        }






        // add additional tables into 'from' if required
        foreach ($columns as $columnId => $column) {

            if ($column instanceof Data_Column) {
                $columnName  = $column->getField();
                $filter      = $column->getFilter();
                $linkPath    = $column->getLinkPath();
                $columnAlias = is_numeric($columnId) ? 'column_' . $columnId : $columnId;
                $display     = $column->getDisplay();
            }
            else {
              $columnName  = strtoupper($column);
              $targetModel = $filter = $linkPath = $display = null;
              $columnAlias = $columnName;
          }

            // if a column is from main table, add it into 'FROM' statement
          if (!$display) {
            $select->columns(array($columnAlias => $columnName));
            if($filter)
            {
                // $select->where($thisTableName . '.' . $columnName .
                   // $filter->getCondition(), $filter->getValue());

                $like = strpos($filter->getValue(), '%') !== false;
                $cond = strpos($filter->getCondition(), 'in') !== false;

                if($columnName != ''){

                    if(!$like || $cond ){
                        $select->where($thisTableName . '.' . $columnName .
                            $filter->getCondition(), $filter->getValue());
                    }else{
                        if(strlen($strMatchQuery) <= 1) 
                        {   
                            $strMatchQuery .= "(".$thisTableName . '.' . $columnName ." like '". $filter->getValue()."')";
                        }else{
                            $strMatchQuery .= " OR (".$thisTableName . '.' . $columnName ." like '". $filter->getValue()."')";
                        }
                            // $select->orWhere($refTableAlias . '.' . $display .
                                // $filter->getCondition(), $filter->getValue());
                    }

                }


            }


        }
            // check if the column is referencial and Display attribute is specified
        else {
            $currentTable = $thisTableName;

                // join tables as needed to reach target model's table
            $targetModel = end($linkPath);

            
            foreach ($linkPath as $refColumnName => $refModel) {
                    // get reftable's name and calculate aliases for it and refcolumn
                $refTableName  = $refModel->getTable()->info('name');
                    //$refTableAlias = '_' . $columnName . '_' . $refModel->__toString();
                $refTableAlias = '_' . $refColumnName . '_' . $refModel->__toString();

                    $fetchColumn = $fetchColumnId = array(); // by default no additional columns are needed
                    // if we're in the end of linking chain, then ask to fetch display column from target table
                    if ($refModel === $targetModel) {
                        $fetchColumn   = array( $columnAlias => $display);
                        $fetchColumnId = $columnAlias == 'ID' ? array() : array( $columnAlias . '_ID' => 'ID');
                    }

                    // check if join is needed or table already joined and just the column should be fetched
                    if (array_key_exists($refTableAlias, $joinHistory)) {
                        $select->columns($fetchColumn,   $refTableAlias); // displayed value
                        if ($columnAlias != 'ID') // otherwise it is already sufficient
                            $select->columns($fetchColumnId, $refTableAlias); // ID from referenced model
                        }
                        else {
                        // save history
                           $joinHistory[$refTableAlias] = $refTableAlias;

                        // evaluate join condition: eiher our_table.$columnName = ref_table.ID
                        // or in case if $columnName = $NNN: our_table.ID = ref_table.NNN
                        $condition = substr($refColumnName, 0, 1) == '$' ? // means column belongs to refTable
                        $refTableAlias . '.' . substr($refColumnName, 1) . ' = ' . $currentTable . '.ID': 
                        $refTableAlias . '.ID = ' . $currentTable . '.' . $refColumnName;

                        // perform inner or outer join, depending on column status
                        if ($column->getRequired())
                            $select->join(
                                array( $refTableAlias => $refTableName),
                                $condition, array_merge($fetchColumn, $fetchColumnId));
                        else
                            $select->joinLeft(
                                array( $refTableAlias => $refTableName),
                                $condition, array_merge($fetchColumn, $fetchColumnId));
                    }

                    // apply filter if we're in the end of the linking chain and filter for display column exists
                    


                    if ($refModel === $targetModel && $filter) {
                        $like = strpos($filter->getValue(), '%') !== false;
                        if(!$like){
                            $select->where($refTableAlias . '.' . $display .
                                $filter->getCondition(), $filter->getValue());
                        }else{
                            if(strlen($strMatchQuery) <= 1) 
                            {   
                                $strMatchQuery .= "(".$refTableAlias . '.' . $display ." like '". $filter->getValue()."')";
                            }else{
                                $strMatchQuery .= " OR (".$refTableAlias . '.' . $display ." like '". $filter->getValue()."')";
                            }
                            // $select->orWhere($refTableAlias . '.' . $display .
                                // $filter->getCondition(), $filter->getValue());

                            $strMatchQuery = str_replace("(_CUSTOMER_ID_company.ID like 'Array')","",$strMatchQuery);

                        } 
                    }


                    $currentTable = $refTableAlias;
                }
            }
        }


        if(strlen($strMatchQuery) > 3)
            $select->where($strMatchQuery);


        if($thisTableName == 'inspection' && $sortByField=='BUILDING'){
            $thisTableName = '_BUILDING_ID_building';
            $sortByField='NAME';
        }else if($thisTableName == 'inspection' && $sortByField=='BUILDING_ADDRESS'){
            $thisTableName = '_BUILDING_ID_building';
            $sortByField='ADDRESS_1';
        }else if($thisTableName == 'inspection' && $sortByField=='BUILDING_OWNER'){
            $thisTableName = '_CUSTOMER_ID_company';
            $sortByField='NAME';
        }else if($thisTableName == 'inspection' && $sortByField=='INSPECTOR_NAME'){
            $thisTableName = '_INSPECTOR_ID_employee';
            $sortByField='FIRST_NAME';
        }


        // order by condition
        if ($sortByField != null) {
         $ordering = array(); $sortDir = $sortDirection ? 'ASC' : 'DESC'; 
         if ($sortBy && is_object($sortBy) && $sortBy->getNaturalSort()) {
                //$ordering[] = "length($thisTableName.$sortByField) $sortDir"; 
            $ordering[] = "CAST($thisTableName.$sortByField AS UNSIGNED) $sortDir"; 
                //Zend_Registry::get('logger')->info('sort direction: ' . $sortDir);
        }

        $ordering[] = "$thisTableName.$sortByField $sortDir";

        $select->order($ordering);

        // echo "<br>****************";
        // echo $strMatchQuery;
        // echo "<br>".$select->__toString();

    }


    $paginator = Zend_Paginator::factory($select);

        //get number of items per page from config file
        // $itemsPerPage = Zend_Registry::getInstance()->configuration->paginator->page;
    $itemsPerPage = $rowsPerPage;

    $paginator->setItemCountPerPage($allRows ? $paginator->getTotalItemCount() : $itemsPerPage);

        // if start row was not specified, load the first page 
        $page = ($start !== null) ? @(($start+$itemsPerPage) / $itemsPerPage) : 0; //$page = $paginator->count();
        $paginator->setCurrentPageNumber($page);
        return $paginator;
    }
    
    public function fetchToArray($columns=null, $start=null, $allRows=false, $sortBy=null, $sortDirection=true) {
        $entries = $this->fetchEntries($columns, $start, $allRows, $sortBy, $sortDirection);
        $items = array();
        $i = 0;
        foreach ($entries as $entry) {
            $items[$i] = array();
            foreach ($columns as $columnId => $column) {        
                if (!($column instanceof Data_Column)) {
                 $columnName  = strtoupper($column);
                 $items[$i][$columnName] = $entry[$columnName];
             }
         }
         $i++;
     }
     return $items;
 }

 public function fetchReferenceValues($column, $start) {
        /*$refModel = $this->getReferenceModel($refLinkColumn);
        if (!$refModel )
        return array();*/
        
        $columns = array('ID', $column->getDisplay() => $column);
        
        $values = array();
        foreach ($this->fetchEntries($columns, $start) as $entry) {
            $values[$entry['ID']] = $entry[$column->getDisplay()];
        }
        
        return $values;
    }
    
    /**
     * Returns arrays of table column names in upper case
     *
     * @return array
     */
    public function getColumns(){
        $columns = array();
        foreach ($this->_table->info(Zend_Db_Table_Abstract::COLS) as $id => $column)
        {
            $columns[$id] = strtoupper($column);
        }  
        return $columns;
    }
    
    /**
     * Returns a column's type from the table's metadata
     *
     * @param string $column
     * @return string type of the column
     */
    public function getColumnType($column){
        $column = strtoupper($column);
        $metadata = $this->getMetadata();
        if (array_key_exists($column, $metadata))
            return $metadata[$column]["DATA_TYPE"];
        else return null; // calculable columns, for example
    }
    
    /**
     * Returns table's metadata with all column names set to uppercase
     */
    public function getMetadata() {
        $metadata = array();
        foreach ($this->getTable()->info("metadata") as $column => $data)
        {
            $metadata[strtoupper($column)] = $data;
        }
        return $metadata;
    }
    
    /**
     * Returns a column's default value from the table's metadata
     *
     * @param string $column
     * @return string default value of the column
     */
    public function getColumnDefault($column){
        $column = strtoupper($column);
        
        // if this column is referential, return null
        if (array_key_exists($column, $this->_referenceModels))
            return null;

        $metadata = $this->getMetadata();
        return $metadata[$column]["DEFAULT"];
    }
    
    /**
     * Returns whether the column is required from the table's metadata
     *
     * @param string $column
     * @return boolean Required or not
     */
    public function getColumnRequired($column){
        $column = strtoupper($column);
        $metadata = $this->getMetadata();
        return $metadata[$column]["NULLABLE"] ? false : true;
    }

    
    /**
     * Adds a pointer to the new model referenced by the specified field
     *
     * @param string         $refField The name of a column in current model which is a reference
     * @param Model_Abstract $refModel The pointer to a model instance being referenced
     */
    public function addReferenceModel($refField, $refModel){
        $refField = strtoupper($refField);
        
        // check if the reference field is an actual column
        if (array_search($refField, $this->getColumns()) === false) {
            throw new Exception("The provided column " . $refField .
                " is not listed in current model's columns");
        }
        
        $this->_referenceModels[$refField] = $refModel;
    }
    

    public function getReferenceModel($refColumnName) {
        $refColumnName = strtoupper($refColumnName);
        return array_key_exists($refColumnName, $this->_referenceModels) ?
        $this->_referenceModels[$refColumnName] : null;
    }
    
    public function getReferenceModels() {
        return $this->_referenceModels;
    }
    
    public function getReferenceColumns() {
        return array_keys($this->_referenceModels);
    }
    
    /**
     * Searches which of the columns of this model reference the target model, recursively
     * 
     * @param  targetModel Model we need to find a (recursively) referenced column to
     * @param  refColumn   Column the search is started from (if null - searches within all columns)  
     * @return array       Link path to the target model: array of linkColumn => parentModel 
     */
    public function getLink($targetModel, $refColumn=null) {
        $refColumn = strtoupper($refColumn);
        
        // nail down the search to specific model if refColumn specified
        if ($refColumn) {
            $refModel = $this->getReferenceModel($refColumn);
            if(!$refModel) {
                return null;
            }
            $refModels = array($refColumn => $refModel);
        } else $refModels = $this->getReferenceModels();

        // first search within reference models of this model
        foreach ($refModels as $column => $refModel) {
           if ($refModel == $targetModel)
              return array($column => $targetModel);
      }

        // if not found, get each of the reference models and search in their reference models 
      foreach ($refModels as $column => $refModel) {
            // infinite loop safe TODO: make more generic
          if ($this == $refModel)
              continue;

          $link = $refModel->getLink($targetModel);
          if( $link ) {
             $result = array($column => $refModel);
             return array_merge($result, $link);
         }
     }

        // if still not found, try many-to-many relatioship

     return null;
 }

 public function __toString() {
        //return get_class($this);
  return $this->getTable()->info('name');
}

public function getId() {
   return $this->_id;
}

public function getName() {
   return $this->_name;
}

    /**
     * Finds a column between existing of this model
     * If not found - then searches all referenced models recursively 
     */
    public function getColumnPath($name, $fromColumn = null) {
        $fromColumn = strtoupper($fromColumn);
        
        if ($fromColumn) {
           $refModel = $this->getReferenceModel($fromColumn);
           if (!$refModel) throw new Exception('Column "' . $fromColumn .
            '" does not have any reference model associated');
               return array_merge(array($fromColumn => $refModel), $refModel->getColumnPath($name));
       }
       $dummy = array(); 
       return $this->_getColumnPath($name, $dummy);
   }

    /**
     * Infinite loop safe - ignores those Models already searched
     */
    protected function _getColumnPath($name, &$models) {
        // first look through columns of current model
        foreach ($this->getColumns() as $column) {
            if ($column == $name)
              return array();
      }

        // if not found, search within referenced models
      foreach ($this->getReferenceModels() as $columnName => $model) {
            // eliminate infinite loop
          if (array_search($model, $models) !== false) continue;
          array_push($models, $model);

          $found = $model->_getColumnPath($name, $models);
          if ($found !== null) return array_merge(array($columnName => $model), $found);
      }

      return null;
  }

    /**
     * Adds a new validation rule to the model
     * 
     * @param Validation_Rule $rule 
     */
    public function addValidationRule($rule) {
        $rule->setModel($this);
        $rule->setId(count($this->_validationRules));
        $this->_validationRules[] = $rule;
    }
    
    /**
     * Returns an array containing column values for a particular record.
     * If there is no record, null is returned
     */
    public function fetchEntry($id=null, $columns=null) {
        if ($id === null && !$columns) return null;
        
        if (!$columns) $columns = $this->getColumns();

        // put a filter by ID
        if ($id) {
            $columns[$this->getId()] = new Data_Column($this->getId(), $id, $this);
        }
        $entries = $this->fetchEntries($columns);

        if (!count($entries)) return null;
        else return $entries->getItem(1);
    }
}