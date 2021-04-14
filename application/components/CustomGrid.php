<?php

require_once APPLICATION_PATH . '/components/data/Filter.php';
require_once APPLICATION_PATH . '/components/data/ColumnGrid.php';
require_once APPLICATION_PATH . '/components/Component.php';

/**
 * Class for Custom_Grid component initialization and rendering
 *
 */
class Cmp_Custom_Grid extends Component {

	protected $_columns;
	protected $_selector;
	protected $_numbered;
	protected $_maxRows;
	protected $_caption;
	protected $_rowcaption;
	protected $_sortByColumn;
	protected $_sortByDirection;

	/**
	 * @var boolean
	 * If set to "yes", any changes in grid cells or detailed dialog input
	 * fields will be autosaved
	 */
	protected $_autosave = false;

	/**
	 * @var boolean
	 * If set to "false", all grid cells will be disabled
	 */
	protected $_editable = true;

	/**
	 * @var boolean
	 * If set to "true", grid will have paginator
	 */
	protected $_usePaginator = true;

	/**
	 * custom filter
	 * @var array of $filterId => Data_Column
	 */
	protected $_filter = array();
	
	protected $_hasRowSelector = false;
	
	protected $_detailedViewUrl = '';
	protected $_formActionUrl = '';

	/**
	 * Grid constructor
	 *
	 * @param string            $id               The Grid's javascript id
	 * @param Model_Abstract    $model            The Grid specific data model
	 * @param Component|string  $parent           A parent component or just a value of ID (static)
	 * @param string|array      $parentLinkField  A field name referencing the parent component
	 *                                            or a complete path to parent: [parentLinkField=>parentModel1, ...]
	 * @param array             $columns          [param=>value] - array of columns
	 * @param array             $selector         Name of a DB column to serve as a grid selector
	 * @param array				$params 		  Extra parameters as [param=>value]
	 * @return Cmp_Custom_Grid
	 */
	public function __construct($id, $model, $parent = null, $parentLink = null, $columns = null, $selector = null, $params = array()) {
		parent::__construct($id, $model, $parent, $parentLink);
		$this->_columns = array();

		if ($selector) {
			$this->_selector = $this->addColumn(array(
				'Field' => $selector, 'Title' => '&nbsp;&nbsp;&nbsp;&nbsp;',
				'Width' => '12px', 'View' => Data_Column_Grid::CHECKBOX));
		}

		// set grid columns - by default, all fields from DB except to ID
		$this->setColumns($columns, false);

		foreach ($params as $param => $value) {
			switch ($param) {
				case 'MaxRows': $this->_maxRows = $value;
				break;
				case 'UsePaginator': $this->_usePaginator = $value;
				break;
			}
		}
	}
	
	public function setHasRowSelector($value) {
		$this->_hasRowSelector = $value;
	}
	
	public function getHasRowSelector() {
		return $this->_hasRowSelector;
	}
	
	

	/**
	 * Changes client names of data columns into database-compliant
	 */
	public function convertDb($jsitem, $customValues = false) {
		$dbItem = array();
		foreach ($jsitem as $field => $value) {
			if ($value === '')
				$value = NULL;

			// get db name of the column
			$pl = $this->getParentLink();

			if ($field == '_parent' && is_array($pl) && count($pl) > 1)
				continue; // it's not safe to translate _parent into a field if the latter is array
			$columnName = $this->parseClientColumn($field);

			if (!$columnName) {
				if ($customValues && substr($field, 0, 1) != '_')
					$dbItem[$field] = $value;
				continue;
			}

			// if the column is referencial, parse out id value from id#displayed string
			$column = $this->getColumnById($field);
			if ($column && $column->getLinkPath()) {
				if (array_key_exists($field . '_id', $jsitem))
					$value = $jsitem[$field . '_id'];
				else {
					$pos = strpos($value, '#');
					if ($pos === false) {	
						$pos = strlen($value);
					}
					
					$value = substr($value, 0, $pos);
				}
				if ($value == 'null')
					$value = null; // weird case for IE8
			}
			elseif ($column && $column->getView() == Data_Column_Grid::CHECKBOX) {
				$value = ((!$value || $value === 'false' || $value === 0 || $value === 'Off') ? 0 : 1);
				//$value = (($value === 'true' || $value === 1 || $value === 'On' || $value='1') ? 1 : 0);
			}
			/*
			  elseif ($column && ($column->getView() == Data_Column_Grid::TIME ||
			  $column->getView() == Data_Column_Grid::DATE)) {
			  $datetime = new Zend_Date($value);
			} */ elseif ($column && ($column->getView() == Data_Column_Grid::TIME)) {
				$value = strlen($value) > 0 ? date('H:i:s', strtotime($value)) : null;
			}

			if ($value === '' || $value === false)
				$value = null;

			if (!key_exists($columnName, $dbItem))
				$dbItem[$columnName] = $value;
		}
		return $dbItem;
	}

	public function getIdColumnIndex() {
		return key($this->getColumnsByField('ID'));
	}

	public function parseClientColumn($column) {
		switch ($column) {
			case '_parent':
			return $this->getParentColumnField();
			case '_ID':
			case '_id': return 'ID';
			default:
			$columnObj = $this->getColumnById($column);
			if ($columnObj)
				return $columnObj->getField();
			else
				return null;
		}
	}

	public function getColumnHeaders() {
		$headers = array();
		foreach ($this->getColumns() as $column) {
			$headers[$column->getField()] = $column->getTitle();
		}
		return $headers;
	}

	public function getColumnTranslation() {
		$map = array();
		foreach ($this->getColumns() as $column) {
			$map[$column->getField()] = $column->getId();
		}
		return $map;
	}

	/**
	 * Saves changes in grid in the model
	 *
	 * @param array $changeSet
	 * @return array save results: "problems" and "created"
	 */
	public function save($changeSet, $customValues = false) {
		// all operations will be performed in one transaction
		Zend_Registry::getInstance()->dbAdapter->beginTransaction();

		$errors = $warnings = $created = null;
		
		if($changeSet && array_key_exists('changedItems', $changeSet))
			foreach (array_value('changedItems', $changeSet) as $item) {
				$ident = $item['_ident'];
				try {
					$ignored = array_key_exists('_ignored', $item) ? $item['_ignored'] : null;
					$result = $this->convertDb($item, $customValues);
				//Zend_Registry::get('logger')->info('Save object: ' . var_export($result, true));
					$this->_model->save($result, $ignored);
				} catch (Validation_Exception $v) {
					$map = $this->getColumnTranslation();
					$e = $v->getErrors($map);
					if ($e)
						$errors[$ident] = $e;
					$w = $v->getWarnings($map);
					if ($w)
						$warnings[$ident] = $w;
				} catch (Exception $e) {
					$errors[$ident][-1][] = $e->getMessage();
				}
			}

			if ($changeSet && array_key_exists('newItems', $changeSet))
				foreach (array_value('newItems', $changeSet) as $item) {
					$ident = $item['_ident'];
					try {
						$ignored = array_key_exists('_ignored', $item) ? $item['_ignored'] : null;
						$result = $this->convertDb($item, $customValues);
						$newID = $this->_model->save($result, $ignored);
				$created[$ident] = $newID; // update ID from database so grid can refresh itself
			} catch (Validation_Exception $v) {
				$map = $this->getColumnTranslation();
				$e = $v->getErrors($map);
				if ($e)
					$errors[$ident] = $e;
				$w = $v->getWarnings($map);
				if ($w)
					$warnings[$ident] = $w;
			} catch (Exception $e) {
				$errors[$ident][-1][] = $e->getMessage(); // -1 means 'non-specific problem'
			}
		}
		
		if ($changeSet && array_key_exists('deletedItems', $changeSet))
			foreach (array_value('deletedItems', $changeSet) as $item) {
				$ident = $item['_ident'];
				try {
					$ignored = array_key_exists('_ignored', $item) ? $item['_ignored'] : null;
					$this->_model->delete($item['_ID'], $ignored);
				} catch (Validation_Exception $v) {
					$map = $this->getColumnTranslation();
					$e = $v->getErrors($map);
					if ($e)
						$errors[$ident] = $e;
					$w = $v->getWarnings($map);
					if ($w)
						$warnings[$ident] = $w;
				} catch (Exception $e) {
					$errors[$ident][-1][] = $e->getMessage();
				}
			}

		// prepare response
			$result = null;
			if ($errors) {
				Zend_Registry::getInstance()->dbAdapter->rollBack();
				$result->errors = $errors;
			}
			if ($warnings) {
				$result->warnings = $warnings;
				Zend_Registry::getInstance()->dbAdapter->rollBack();
			}
			if ($created)
				$result->created = $created;

		// if all's OK, commit the transaction and return result
			if (!$errors && !$warnings)
				Zend_Registry::getInstance()->dbAdapter->commit();

			return $result;
		}

	/**
	 * Acts as save just input and output in JSON
	 *
	 * @param string $jsonChangeSet
	 * @return string JSON results
	 */
	public function saveJson($jsonChangeSet, $customValues = false) {
		return Zend_Json::encode(
			$this->save(
				Zend_Json::decode($jsonChangeSet), $customValues));
	}

	public function getColumns($visibility = null) {
		if ($visibility !== null) {
			$columns = array();
			foreach ($this->_columns as $columnId => $column) {
				if ($column->getVisible($visibility))
					$columns[$columnId] = $column;
			}
			return $columns;
		}
		else
			return $this->_columns;
	}

	/**
	 * Returns all columns with dbfield equal to specified
	 *
	 * @param $field dbname of the column
	 */
	public function getColumnsByField($field, $display = null) {
		$columns = array();
		// iterate all existing grid's columns and compare parameters
		foreach ($this->getColumns() as $columnId => $column) {
			if (strtoupper($column->getField()) == strtoupper($field)
				&& (!$display || $display == $column->getDisplay())) {
				$columns[$columnId] = $column;
		}
	}
	return $columns;
}

	/**
	 * Adds a new column into the grid with specified parameters
	 * @param params string|array|Data_Column_Grid Either name of the column or array of characteristics
	 */
	public function addColumn($params = null) {
		if ($params instanceof Data_Column_Grid) {
			$this->_columns[$params->getId()] = $params;
			return $params;
		}

		// if $params is not an array, then it's just name of the field
		if (!is_array($params)) {
			$params = array('Field' => strtoupper($params));
		}

		// Column autosaving property is inherited from the grid by default
		/* if (!array_key_exists('Autosave', $params))
		$params['Autosave'] = $this->_autosave; */

		if (!array_key_exists('Field', $params) && !array_key_exists('Calculated', $params))
			throw new Exception('Cannot create a new non-calculable column without "Field" specified');

		// for reference columns show drowdown unless other is specified
		if (!array_key_exists('Display', $params) && array_key_exists('Field', $params)) {
			$field = $params['Field'];
			if (is_array($field)) {
				$params['Display'] = end($field)->getName();
			} else {
				$refModel = $this->_model->getReferenceModel($field);
				if ($refModel)
					$params['Display'] = $refModel->getName();
			}
		}

		$newColumn = new Data_Column_Grid($this, $params);
		$this->_columns[$newColumn->getId()] = $newColumn;

		return $newColumn;
	}

	/**
	 * Sets grid's columns.
	 *
	 * @param array   $columns          COLUMN_NAME => [param=>value] or just [param=>value]
	 * @param boolean $removeAllOthers  If set to 'true', all existing columns in grid
	 *                                  will first be erased
	 */
	public function setColumns($columns = null, $removeAllOthers = false) {
		// check if not mentioned columns should be deleted
		if ($removeAllOthers) {
			$this->_columns = array();
		}

		if (!$columns) // populate columns from database if empty
		foreach ($this->_model->getColumns() as $columnName) {
				// don't show parent column
			if ($this->getParentColumnField() == $columnName)
				continue;

			$this->addColumn($columnName);
		}
		else
			foreach ($columns as $columnName => $columnAttrs) {
				// first check if a column is named correctly
				if (substr($columnName, 0, 1) == '#' || substr($columnName, 0, 1) == '_' || substr($columnName, 0, 1) == '$')
					throw new Exception('We apologize, but you cannot name columns starting with either "_", "$" or "#". ' .
						'Please consider renaming column "' . $columnName . '" in grid "' . $this->_id . '"');

				if ($columnAttrs instanceof Data_Column_Grid) {
					$this->addColumn($columnAttrs);
				}
				// If a client has specified name of the column as array index, check consistency
				if (is_array($columnAttrs) && is_string($columnName) && !array_key_exists('Field', $columnAttrs)) {
					$columnAttrs['Field'] = $columnName;
				}
				$this->addColumn($columnAttrs);
			}

		// column 'ID' must always exist
			if (!$this->getColumnsByField('ID'))
				$this->addColumn('ID');
		}

		public function fetchPage($params){
			$pageNumber = nvl(array_value('page', $params), 0) * 1;
			$rowsPerPage = ($params && array_key_exists('rowsPerPage', $params)) ? $params['rowsPerPage'] : Zend_Registry::getInstance()->configuration->paginator->page;
			$start = $pageNumber * $rowsPerPage;

			return $this->fetchEntries(array_value('filter', $params), $start, false, array_value('sortBy', $params), array_value('sortDirection', $params), array_value('rowsPerPage', $params));
		}

	/**
	 * Constructs and executes a query against relevant db tables
	 * and returns results as a query
	 *
	 * @param array   $filter        Filter to be applied to the grid columns
	 * @param number  $start         Row to start fetch with
	 * @param boolean $allrows       If true, returns single page containing all rows
	 * @param string  $sortBy        Column to sort the resultset by
	 * @param boolean $sortDirection Direction: true=ascending, false=descending
	 * @return Paginator Array of resulted rows organized in pages
	 */
	public function fetchEntries($filter = null, $start = 0, $allrows = false, $sortBy = null, $sortDirection = true, $rowsPerPage = null) {
		//Zend_Registry::get('logger')->info('Filter: ' . $filter);
		$parentCmp = $this->getParent();
		
		if ($filter === null) $filter = array();

		// make sure the filter always applies if parent exists
		// (unless being removed explicitly or filtering by ID)
		if ($parentCmp && (!$filter || !array_key_exists('_parent', $filter)) &&
			!array_key_exists($this->getIdColumnIndex(), $filter)) {
			$filter['_parent'] = is_string($parentCmp) ? $parentCmp : -1;
	}

		// apply filter by ID if _id key is specified
	if (array_key_exists('_id', $filter)) {
		$filter[$this->getIdColumnIndex()] = $filter['_id'];
		unset($filter['_id']);
	}

		// apply filter to the columns
	$fetchColumns = $this->getColumns();
	foreach ($filter as $columnId => $value) {
			//App::log('Filtering ' . $columnId . ' by ' . $value);
			// try parsing column object
		$column = $this->getColumnById($columnId);

			// if the column is referencial, parse out displayed value from id#displayed string
		if ($column && $column->getLinkPath()) {
			$pos = strpos($value, '#');
			$value = $pos ? substr($value, $pos + 1) : $value;
			if ($value == 'null')
					$value = null; // weird case for IE8
			}
			//Zend_Registry::get('logger')->info('Filter value: ' . $value);
			// replace _parent to the real parent link field name
			if (!$column && $columnId == '_parent') {

				// no filter in case if no value is specified for parent
				if ($value === null || $value === "")
					continue;

				// if parent is a tree, fetch hierarchically
				if ($parentCmp instanceof Cmp_Custom_Tree)
					$value = $parentCmp->fetchHierarchy($value);

				$column = new Data_Column(
					$this->_parentLink,
								// make sure parent is always an inner-join
					new Data_Filter($value, null, false, '=', false, false),
					$this->_model);
			}elseif ($column && $column->getType() == 'date' ||
					substr($columnId, strlen($columnId) - 3) == '_to' && !$column) { // this is a date column with one of the boundaries specified
				// check which boundary it is: true->from, false->to
				$boundary = ($column != null);

				// figure out actual column ID for "to" boundary
				if (!$column)
					$columnId = substr($columnId, 0, strlen($columnId) - 3);

				// if this column already exists, then apply new boundary to its filter
				$filter = null;
				if (array_key_exists($columnId, $fetchColumns)) {
					$column = $fetchColumns[$columnId];
					$filter = $column->getFilter();
				}
				if (!$filter) {
					$column = $this->getColumnById($columnId);
					$filter = new Data_Filter_Between(null, null, $column);
				}

				// set the boundary
				$filter->setValue($value, $boundary);
				$column->setFilter($filter);
			} elseif (!$column) {
				//throw new Exception('Trying to apply a filter to a non-existent column "' .
				//         $columnId . '" in grid "' . $this->_id);
				//Igor: if column not found in grid, just skip it
				continue;
			} else {
				// make filter an inner-join
				$columnFilter = new Data_Filter($value, null, false, '=', false, false);
				$column->setFilter($columnFilter);
				//$column->setFilter($value);
			}

			$fetchColumns[$columnId] = $column;
		}

		// convert sort column id (if exists) to column name & fetch entries from database
		
		$sortColumn = null;
		if ($sortBy != null) {

			$sortColumn = $this->getColumnById($sortBy);
			
			if (!$sortColumn)
				throw new Exception('Cannot sort by column "' . $sortBy . '": it does not exist');
			
			if (!$sortColumn->getField()) {
				$sortColumn = null;
			}

		} else {
			$sortColumn = $this->getSortColumn();
			$sortDirection = $this->getSortDirection();
		}

		 if($sortBy == 'BUILDING' || $sortBy == 'BUILDING_ADDRESS' || $sortBy == 'BUILDING_OWNER' || $sortBy == 'INSPECTOR_NAME'){
			$sortColumn = $sortBy;
        }

		// add grid filter
		$fetchColumns = array_merge($fetchColumns, $this->getFilter());

		return $this->_model->fetchEntries($fetchColumns, $start, $allrows, $sortColumn, $sortDirection, $rowsPerPage);
	}

	/**
	 * Constructs and executes a query against relevant db tables
	 * and returns results in json format
	 *
	 * @param filter parameters $queryParams
	 * @param number  $start         Row to start fetch with
	 * @param string  $sortBy        Column to sort the resultset by
	 * @param boolean $sortDirection Direction: true=ascending, false=descending
	 * @return string JSON result string
	 */
	public function fetchJson($queryParams = null, $start = 0, $sortBy = null, $sortDirection = true) {
		//fetch Entries
		$entries = $this->fetchEntries($queryParams, $start, false, $sortBy, $sortDirection);

		$itemsPerPage = Zend_Registry::getInstance()->configuration->paginator->page;

		// prepare data to be properly recognized on client
		foreach ($entries as $id => &$entry) {
			$entryId = null;
			foreach ($entry as $columnId => $value) {
				$column = $this->getColumnById($columnId);
				if (!$column)
					continue;
				if ($columnId == '_parent' || $columnId == '_parentId')
					unset($entry[$columnId]);

				if ($column->getField() == 'ID')
					$entryId = $value;
				elseif ($column->getType() == 'date' && $value != null) { // format date columns to ISO format
					$date = new Zend_Date($value, Zend_Date::ISO_8601);
					//$entry[$columnId] = array('_type' => 'Date', '_value' => $date->getIso());
					//$datePattern = Zend_Registry::get('configuration')->date->displayformat;
					$entry[$columnId] = array('_type' => 'Date', '_value' => $date->toString('yyyy-MM-dd'));
				}
				/* elseif ($column->getType() == 'time' && $value != null) { // format time columns to ISO format
				  $date = new Zend_Date($value, Zend_Date::ISO_8601);
				  $entry[$columnId] = array('_type' => 'Date', '_value' => $date->getIso());
				  } */ elseif ($column->getType() == 'time' && $value != null) { // format time columns
				  	$entry[$columnId] = date('H:i', strtotime($value));
				} elseif ($column->getLinkPath()) { // store reference id and displayed values
					$entry[$columnId] = $entry[$columnId . '_ID'] . '#' . $value;
					//unset ($entry[$columnId . '_ID']);
				} elseif ($column->getView() == Data_Column_Grid::CHECKBOX) { //Igor: convert 0/null to No, anything else to Yes
					$entry[$columnId] = $entry[$columnId] != false ? 'Yes' : 'No';
				} elseif ($value == null) {
					$entry[$columnId] = "";
				}
			}
			if ($this->getNumbered()) { // add line number column
				$entry['_number'] = ($entries->getCurrentPageNumber() - 1) * $itemsPerPage + $id + 1;
			}

			// add calculable columns
			foreach ($this->getColumns() as $column) {
				if ($column->getCalculated()) {
					$entry[$column->getId()] = $column->calculate($entry);
				}
			}

			// add ident column
			if (!$entryId)
				throw new Exception('There is no ID column in the resultset');
			$entry['_ident'] = $entryId;
		}

		// convert resultset to json
		$data = new Zend_Dojo_Data('_ident', $entries);

		// set a total number of rows (for grids to set scroller right)
		$data->setMetadata('_count', $entries->getTotalItemCount());

		$data->setMetadata('_query', Zend_Json::encode($queryParams));

		return $data->__toString();
	}

	// fetches a row in a client-friendly format
	public function fetchEntry($id) {
		// find an entry by ID
		$entries = $this->fetchEntries(array($this->getIdColumnIndex() => $id));
		if ($entries->getTotalItemCount() == 0)
			return null;
		$entry = $entries->getItem(1);

		// format the entry in a client-friendly format
		foreach ($entry as $columnId => $value) {
			$column = $this->getColumnById($columnId);
			if (!$column)
				continue;

			if ($column->getType() == 'date' && $value != null) { // format date columns to ISO format
				$date = new Zend_Date($value, Zend_Date::ISO_8601);
				$entry[$columnId] = $date->getIso();
			}
			/* elseif ($column->getType() == 'time' && $value != null) { // format time columns to ISO format
			  $date = new Zend_Date($value, Zend_Date::ISO_8601);
			  $entry[$columnId] = $date->getIso();
			  } */ elseif ($column->getView() == Data_Column_Grid::CHECKBOX) { // convert 0/null to false, anything else to true
			  	$entry[$columnId] = $entry[$columnId] != false;
			  } elseif ($value == null) {
			  	$entry[$columnId] = "";
			  }
			}

			return $entry;
		}

	/**
	 * Returns results in html table format
	 * which is recognizable by Excel
	 */
	public function fetchExcel($queryParams = null, $start = 0, $sortBy = null, $sortDirection = true) {
		$entries = $this->fetchEntries($queryParams, $start, false, $sortBy, $sortDirection);

		// put column headers
		$excel_table = '<table><tr>';
		foreach ($entries as $entry) {
			foreach ($entry as $columnId => $value) {
				// eliminate _parent columns
				if ($columnId == '_parent' || $columnId == '_parent_ID')
					continue;
				
				if ($columnId == 'INSPECTION_ID')
					continue;
				
				// get grid column object
				$column = $this->getColumnById($columnId);
				if (!$column)
					continue;

				//check excel property
				if (!$column->getExcel())
					continue;

				// eliminate ID columns
				if ($column->getField() == 'ID')
					continue;
				elseif ($column->getLinkPath())
					unset($entry[$columnId . '_ID']);

				// add column header
				$excel_table .= "<th>" . $column->getTitle() . "</th>";
			}
			break;
		}
		$excel_table .= "</tr>";

		// add data rows, but no more than a defined maximum
		$max_records = (int) Zend_Registry::getInstance()->configuration->excel->max_records;
		if ($max_records < 10)
			$max_records = 10; // min allowed value
		if ($max_records > 10000)
			$max_records = 10000; // max allowed value
		$total = min($entries->getTotalItemCount(), $max_records);
		$perPage = $entries->getItemCountPerPage();
		$max_page = (int) ($total / $perPage) + (fmod($total, $perPage) == 0 ? 0 : 1);
		for ($page = 1; $page <= $max_page; $page++) {
			$entries->setCurrentPageNumber($page);
			foreach ($entries as $entry) {
				$excel_table .= '<tr>';
				foreach ($entry as $columnId => $value) {
					// eliminate _parent columns
					if ($columnId == '_parent' || $columnId == '_parent_ID')
						continue;

					if ($columnId == 'INSPECTION_ID')
						continue;

					// get grid column object
					$column = $this->getColumnById($columnId);
					if (!$column)
						continue;

					//check excel property
					if (!$column->getExcel())
						continue;

					// eliminate ID columns
					if ($column->getField() == 'ID')
						continue;
					elseif ($column->getLinkPath())
						unset($entry[$columnId . '_ID']);

					//images
					if ($column->getView() == Data_Column_Grid::IMAGE && strlen($value) > 0) {

						//if this is relative path
						if ($value[0] == '/')
							$value = ROOT_PATH . $value;

						$height = 100;
						$width = 150;
						//get image size
						$size = getimagesize($value);
						if (is_array($size)) {
							//compress if needed
							$width = $size[0];
							$height = $size[1];

							/*
							  if ($height > 100) {
							  $ratio = $height / 100;
							  $height = 100;
							  $width = (int)round($width / $ratio);
							  }

							  if ($width > 150) {
							  $ratio = $width / 150;
							  $width = 150;
							  $height = (int)round($height / $ratio);
							  }
							 */
							}

							$value = '<img height=' . $height . ' src="' . $value . '">';
							$excel_table .= '<td height=' . $height . ' width=' . $width . ' >' . $value . '</td>';
					} else //$column->getView() == Data_Column_Grid::IMAGE && strlen($value) > 0
					$excel_table .= '<td>' . $value . '</td>';
				}
				$excel_table .= '</tr>';
			}
		}
		$excel_table .= '</table>';

		return $excel_table;
	}

	/**
	 * For the provided column which should reference a certain model
	 * provides list of all possible values this column may take, so
	 * a user can search within this list to change a certain cell value
	 * @param $column   string|Data_Column_Grid The column in the grid we need reference values for
	 * @param $parentId string                  If the grid has a parent component, this value designates
	 *                                          a current value of parent row, so we can filter ref values
	 * @param $start    int                     Number of a row we should start fetch from
	 *                                          (needed for paging of large amounts of data)
	 * @param $required
	 * @param $byId
	 * @return array JSON array of reference values
	 */
	public function fetchReferenceValuesJson($column, $filter, $parentId, $start, $required, $byId = false) {
		// get the column
		$c = $column;
		if (is_string($column))
			$column = $this->getColumnById($column);
		if (!$column) {
			throw new Exception('Cannot get dropdown: column ' . $c . ' is not found');
		}
		// check whether we should show empty option or not
		if ($required === null)
			$required = $column->getRequired();

		return $this->getDropDown($column)->fetchJson($filter, $parentId, $start, $required, null, $byId);
	}

	public function getDropDown($column) {
		// first get reference path for the given column
		$path = $column->getLinkPath();
		if (!$path)
			throw new Exception(
				'Trying to get reference values for a non-reference column "' . $column->getField() . '"');

		// get ref model and new path from refmodel
		$refModel = current($path);
		if (count($path) > 1) {
			$path = array_slice($path, 1);
			$display = $column->getDisplay();
		} else { // display column is directly from the reference model
			$path = $column->getDisplay();
			$display = null;
		}

		// check and see if there is a need to apply a parent for the dropdown
		$parent = null; // TODO: perhaps, configure column parent dependency when defining a column in a controller?
		// create dropdown and fetch its values filtered by parent and name
		return new Cmp_Drop_Down(null, new Data_Column($path, null, $refModel, $display),
			$parent, null, $column->getDropdownFilter());
	}

	public function getParentColumnField() {
		if ($this->_parentLink) {
			return key($this->_parentLink);
		}
		else
			return null;
	}

	public function getDisplayColumn($columnName) {
		$refColumns = $this->getReferenceColumns();
		return $refColumns[$columnName]->getDisplay();
	}

	public function getColumnById($id) {
		if (!array_key_exists($id, $this->_columns)) {
			return null;
		}
		else
			return $this->_columns[$id];
	}

	public function getIdColumn() {
		return current($this->getColumnsByField('ID'));
	}

	protected function _translate($problems, $map = null) {
		if (!$problems)
			return NULL;
		if (!$map)
			return $problems;

		foreach ($problems as &$fields) {
			foreach ($fields as $key => $field) {
				if (array_key_exists($field, $map))
					$fields[$key] = $map[$field];
			}
		}
		return $problems;
	}

	public function getFields($translateMap = null) {
		$fields = $this->_fields;
		foreach ($fields as $key => $field) {
			if (array_key_exists($field, $translateMap))
				$fields[$key] = $translateMap[$field];
		}
		return $fields;
	}

	/**
	 * Returns grid's column
	 * @param string|object $column Could be column object, column id or column field
	 * @return
	 */
	public function getColumn($column) {
		if (is_string($column)) { // try & get the column by its name
			$c = $this->getColumnById($column);
			if (!$c) {
				$column = $this->getColumnsByField($column);

				if (count($column) == 0)
					throw new Exception('Cannot get column ' . $column .
						'. No such column is found in the grid.');
				elseif (count($column) > 1)
					throw new Exception('Cannot get column ' . $column .
						'. Column name is ambiguous');
				$column = current($column);
			}
			else
				$column = $c;
			return $column;
		}
		else
			return $column;
	}

	/**
	 * Generates an HTML control basing on the field configuration.
	 * If either left or top are specified the control is positioned absolutely
	 * @param string|object $columnName Name of the data column
	 * @param string|object $value      Initial value of the control or an entry
	 * @param array         $params     Additional parameters (for example, styling)
	 * @param boolean	    $searchMode Whether the column is for search or display/edit purposes
	 */
	public function getFieldControl($column, $value = null, $params = array(), $searchMode = false) {
		$column = $this->getColumn($column);
		$res = $column->getFieldControl($value, $params, $searchMode);
		return $res;
	}

	/**
	 * Forms a string to be inserted into <div dojoType="ginger.GridForm.js">
	 * and its descendants in order for them to render properly
	 */
	public function getGridFormDivParams($jsId = null) {
		$model = $this->getId();
		$actionPath = Zend_Controller_Front::getInstance()->getBaseUrl() . '/' .
		Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
		$idColumnIndex = $this->getColumnsByField('ID');
		$formId = $jsId ? $jsId : 'form_' . $model;

		return "model='" . $model . "' actionPath='" . $actionPath .
		"' idColumnIndex = '" . $idColumnIndex . "' jsid = '" . $formId . "'";
	}

	public function getAutosave() {
		return $this->_autosave;
	}

	public function getSelector() {
		return $this->_selector;
	}

	public function getNumbered() {
		return $this->_numbered ? true : false;
	}

	public function getMaxRows() {
		return $this->_maxRows;
	}
	
	public function setReadonly() {
		$this->_editable = false;
	}

	public function log($text) {
		Zend_Registry::getInstance()->logger->info($text);
	}

	public function getSortColumn() {
		return nvl($this->_sortByColumn, $this->getIdColumn());
	}

	public function getSortDirection() {
		return nvl($this->_sortByDirection, true);
	}

	/**
	 * @param string  $columnId Id of the column being sorted
	 * @param boolean $asc true = ascending, false = descending
	 */
	public function setDefaultSorting($columnId, $asc = true) {
		$column = $this->getColumnById($columnId);
		if (!$column)
			throw new Exception("Cannot set grid sorting as column $columnId is not found");
		$this->_sortByColumn = $column;
		$this->_sortByDirection = $asc;
	}

	public function setAutosave($enabled) {
		$this->_autosave = $enabled;
	}

	public function setEditable($enabled = true) {
		$this->_editable = $enabled;
	}

	public function getEditable() {
		return $this->_editable;
	}
	
	public function isReadonly(){
		if ($this->_editable === false) return true;
		else return false;
	}

	public function getUsePaginator() {
		return $this->_usePaginator;
	}

	/**
	 * @return Zend_Acl
	 */
	public function getAcl() {
		return Zend_Controller_Front::getInstance()->getPlugin('Plugin_Security')->getAcl();
	}

	public function getUser() {
		return Zend_Auth::getInstance()->getIdentity();
	}

	public function getUserId() {
		// get user's ID
		$user = Model_User::retrieve()->fetchEntry(null, array(
			'ID' => 'ID',
			new Data_Column('LOGIN', $this->getUser())));

		if (!$user)
			return null;
		else
			return $user['ID'];
	}

	public function getSession() {
		return new Zend_Session_Namespace('default');
	}

	/**
	 * Gets latest search filter from the session state
	 */
	public function getSearchFilter() {
		$session = new Zend_Session_Namespace('default');
		if ($session->gridFilters && is_array($session->gridFilters) && array_key_exists($this->getId(), $session->gridFilters)) {
			$filter = $session->gridFilters[$this->getId()];
			unset($filter['start']);
			unset($filter['_sort']);
			unset($filter['_sortDir']);
			return $filter;
		}
		else
			return array();
	}

	public function getSearchFilterJson() {
		return Zend_Json::encode($this->getSearchFilter());
	}

	public function addFilter($filter = array()) {
		if (!is_array($filter))
			$filter = array($filter);
		$this->_filter = array_merge($this->_filter, $filter);
	}

	public function getFilter() {
		return $this->_filter;
	}

	public function selectionActions($entry) {
		static $index = -1;
		$index++; $gridId = 'cmp_' . $this->getId();
		return "<input type='checkbox' id='${gridId}_selector${index}' onclick='${gridId}.onSelectionChanged(${index})'/>";
	}

	public function getFormActionUrl() {
		return $this->_formActionUrl;
	}
	
	public function getDetailedViewUrl() {
		return $this->_detailedViewUrl;
	}
}