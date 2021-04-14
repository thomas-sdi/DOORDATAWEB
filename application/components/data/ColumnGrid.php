<?

require_once APPLICATION_PATH . '/components/data/Column.php';

/**
 * Represents a grid column object
 *
 */
class Data_Column_Grid extends Data_Column {

	/**
	 * @var CustomGrid Reference to the grid this column is a part of
	 */
	protected $_grid;  // Grid:     reference to the Grid this column is part of
	protected $_gridId;
	protected $_id;   // Name of column within the grid. Usually column_x, where x is order number
	protected $_hidden;  // Hidden:   don't show the column in grid
	protected $_title;  // Title:    how the column will be named in grid
	protected $_editable; // Editable: whether the column can be edited or not
	protected $_searchable; // Searchabke: whether the column can be used in search
	protected $_searchdefault; // default value for search dialog
	protected $_boolean; // Boolean:  whether the column should be edited with the checkbox
	protected $_memo;  // Memo:     whether the column should be edited with textarea field
	protected $_image;  // Image:    whether the column contains a link to an image and this image should be displayed.
	protected $_link; // Link:     whether the column contents should be displayed as link
	protected $_calculated; // Calculated: whether the column contents should be calculated
	protected $_view;  // View: TEXT|DATE|MEMO|CHECKBOX|DROPDOWN|RADIO|IMAGE|AUDIO|VIDEO
	protected $_width;   // Width: column width in grid and in detailed dialogs 
	protected $_maxlength = ''; // maxlength attribute (default '')
	protected $_visible = array('GRID' => true, 'SEARCH' => true, 'EDIT' => true); //Visible: whether column can be seen in GRID, SEARCH, EDIT dialogs
	protected $_excel = true; //Boolean: whether to include in excel
	protected $_autosave = false; // Saving changes after a field is edited
	protected $_inputType = "text"; // InputType: 	type of input. Ex, text, password, hidden, etc.
	protected $_size = "1";//Size of the bootstrap column, default is 1 (col-sm-1)

	/* DropDown properties */
	protected $_dropdownfilter;  // Filter: dropdown filter for grids & dialogs (array)
	protected $_dropdowncaption;  // caption shown in dropdown for empty row
	protected $_parentColumns = array(); // ParentColumns: columnIds on which current column depends
	protected $_urlString = "";  // UrlString: 	replaces default url for dropdown store
	protected $_allowNewValues = false; // AllowNewValues: boolean, whether allow adding new values 
	protected $_radioOptions = array(); // Radiooptions: array {value, title] of radio options

		const TEXT = 'TEXT', SIMPLETEXT = 'SIMPLETEXT', DATE = 'DATE', MEMO = 'MEMO', CHECKBOX = 'CHECKBOX', DROPDOWN = 'DROPDOWN',LINE = 'LINE',
		RADIO = 'RADIO', TEXTAREA='TEXTAREA', SIGNATURE='SIGNATURE', IMAGE = 'IMAGE', AUDIO = 'AUDIO', VIDEO = 'VIDEO', FILE = 'FILE', TIME = 'TIME',
		GRID = 'GRID', SEARCH = 'SEARCH', EDIT = 'EDIT', CURRENCY = 'CURRENCY', REGEXP = 'REGEXP';

		public function __construct($grid, $params) {
			if (is_object($grid))
				$this->_grid = $grid;

		// generate column id
			if (array_key_exists('Id', $params)) {
				$this->_id = $params['Id'];
			}
			else
				$this->_id = 'column_' . count($this->_grid->getColumns());

			$display = $filter = $refModel = $field = null;
			foreach ($params as $param => $value) {
				switch ($param) {
					case 'Field' : $field = $value;
					break;
					case 'Default' : $this->_default = $value;
					break;
					case 'Filter' : $filter = $value;
					break;
					case 'Model' : $refModel = $value;
					break;
					case 'Display' : $display = $value;
					break;
					case 'Required' : $this->_required = $value;
					break;
					case 'Editable' : $this->_editable = $value;
					break;
					case 'Searchable' : $this->_searchable = $value;
					break;
					case 'SearchDefault' : $this->_searchdefault = $value;
					break;
					case 'DropdownFilter' : $this->setDropdownFilter($value);
					break;
					case 'DropdownCaption' : $this->_dropdowncaption = $value;
					break;
					case 'ParentColumns' : $this->setParentColumns($value);
					break;
					case 'Maxlength' : $this->_maxlength = $value;
					break;
					case 'Link' : $this->_link = $value;
					break;
					case 'Calculated' : $this->_calculated = $value;
					break;
					case 'Width' : $this->_width = $value;
					break;
					case 'Excel' : $this->_excel = $value;
					break;
					case 'Autosave' : $this->_autosave = $value;
					break;
					case 'InputType' : $this->_inputType = $value;
					break;
					case 'NaturalSort' : $this->_naturalSort = $value;
					break;
					case 'Size': $this->_size = $value;
					break;
				}
			}

		// invoke parent constructor
			$model = is_object($grid) ? $grid->getModel() : $refModel;

			parent::__construct($field, $filter, $model, $display, $refModel);

			$this->_gridId = is_object($grid) ? $grid->getId() : $grid;

		// Title: by default convert soME_thIng_anyTHiNg to Some Thing Anything
			$this->_title = array_key_exists('Title', $params) ? $params['Title'] :
			ucwords(strtolower(str_replace('_', ' ', $this->_field)));

		// Hidden - by default hide all ID fields
			$this->_hidden = array_key_exists('Hidden', $params) ?
			$params['Hidden'] : $this->_field == 'ID';

		// Type. Defaults to text with exception to date and dropdown columns
			$this->_view = array_key_exists('View', $params) ? $params['View'] :
			($this->getType() == 'date' ? Data_Column_Grid::DATE :
				($this->_display ? Data_Column_Grid::DROPDOWN : Data_Column_Grid::TEXT));

		// Editable: defaults to true
			$this->_editable = array_key_exists('Editable', $params) ?
			$params['Editable'] : true;

		// Searchable: defaults to true
			$this->_searchable = array_key_exists('Searchable', $params) ?
			$params['Searchable'] : true;

		//Visible: 'xyz' - x - see in GRID, y - see in SEARCH, z - see in EDIT. By default = '111'
			if (array_key_exists('Visible', $params)) {
				$this->_visible['GRID'] = $params['Visible'][0] == '1' ? true : false;
				$this->_visible['SEARCH'] = $params['Visible'][1] == '1' ? true : false;
				$this->_visible['EDIT'] = $params['Visible'][2] == '1' ? true : false;
			} else if (strtoupper($this->_field) == 'ID') {
				$this->_visible['GRID'] = false;
				$this->_visible['SEARCH'] = false;
				$this->_visible['EDIT'] = false;
			}
		}

		public function getHiddenText() {
			return $this->_hidden ? 'true' : 'false';
		}

		public function getHidden() {
			return $this->_hidden;
		}

		public function getTitle() {
			return $this->_title;
		}

		public function getEditable() {
			return $this->_editable;
		}

		public function getEditableText() {
			return $this->_editable ? 'true' : 'false';
		}

		public function getSearchable() {
			return $this->_searchable;
		}

		public function getSearchableText() {
			return $this->_searchable ? 'true' : 'false';
		}

		public function getSearchDefault() {
			return $this->_searchdefault;
		}

		public function getBoolean() {
			return $this->_boolean;
		}

		public function getId() {
			return $this->_id;
		}

		public function getMemo() {
			return $this->_memo;
		}

		public function getImage() {
			return $this->_image;
		}

		public function getLink() {
			return $this->_link;
		}

		public function getCalculated() {
			return $this->_calculated;
		}

		public function getDropdownFilter() {
			return $this->_dropdownfilter;
		}

		public function getView() {
			return $this->_view;
		}

		public function getMaxlength() {
			return $this->_maxlength;
		}

		public function getSize(){
			return $this->_size;
		}

		public function getWidth() {
			if ($this->_width)
				return $this->_width;

		// get column view type
			$view = $this->getView();
			if ($view == self::DATE)
				return '50px';
			$max = $this->getMaxLength();
			if ($max)
				return ($max * 10 ) . 'px';
			else
				return null;

		/*
		  // count all checkboxes & memo fields
		  $memoCount = $booleanCount = 0;
		  foreach ($this->_grid->getColumns() as $column) {
		  if     ($column->getView() == Data_Column_Grid::MEMO)     $memoCount++;
		  elseif ($column->getView() == Data_Column_Grid::CHECKBOX) $booleanCount++;
		  }

		  // total columns count
		  $colCount = count($this->_grid->getColumns());

		  // now get proportional width
		  return $view == Data_Column_Grid::MEMO ? (100/($colCount + $memoCount * 2 - $booleanCount / 2) * 3) :
		  (100/($colCount + $memoCount * 2 - $booleanCount / 2) /
		  ($view == Data_Column_Grid::CHECKBOX ? 2 : 1)); */
		}

		public function getVisible($scope) {
			return $this->_visible[$scope];
		}

		public function getVisibleText($scope) {
			return $this->_visible[$scope] ? 'true' : 'false';
		}

		public function getExcel() {
			return $this->_excel;
		}

		public function getGrid() {
			return $this->_grid;
		}

		public function setDropdownFilter($filter) {
		// dropdown filter should always be an array
			if (!is_array($filter))
				$filter = array($filter);

		// if filter contains any parent columns, record this
			$this->_parentColumns = array();
			foreach ($filter as $filterColumn) {
				$f = $filterColumn->getFilter();
				if (!$f)
					continue;
				$filterValues = $f->getValue();
				if (!is_array($filterValues))
					$filterValues = array($filterValues);
				foreach ($filterValues as $value) {
					if (substr($value, 0, 1) == '$') {
						$this->_parentColumns[] = substr($value, 1);
					}
				}
			}

		// set the filter
			$this->_dropdownfilter = $filter;
		}

		public function setEditable($editable = true) {
			$this->_editable = $editable;
		}

		public function setSearchable($searchable = true) {
			$this->_searchable = $searchable;
		}

		public function setSearchDefault($value) {
			$this->_searchdefault = $value;
		}

	//we will use this function if column type is calculated value
		public function calculate($entry) {
			return call_user_func($this->_calculated, $entry);
		}

		public function isCalculated() {
			return $this->_calculated != null;
		}

		public function getDropdownCaption() {
			return $this->_dropdowncaption;
		}

		public function getParentColumn() {
			return $this->_parentColumn;
		}

		public function getInputType() {
			return nvl($this->_inputType, 'text');
		}

		public function getFieldControl($value = null, $params = array(), $searchMode = false, $autosave = false) {
			if (!$params)
				$params = array();
		// get additional parameters
			$height = array_value('height', $params);
			$top = array_value('top', $params);
			$width = array_value('width', $params);
			$omit = array_value('omit', $params);
			$left = array_value('left', $params);

		// whether user input on the column is disabled
			if (array_key_exists('editable', $params)) {
				$disabled = $params['editable'] ? '' : 'disabled';
			} else {
				$disabled = $searchMode ? ($this->getSearchable() ? '' : 'disabled') : ($this->getEditable() ? '' : ' disabled ');
			}

		// current column look-n-feel
			$view = $this->getView();

		// style parameters
			$style = '';
			if ($left || $top)
				$style .= 'position: relative;';
			$style .= ($left ? 'left: ' . $left . (is_numeric($left) ? 'px' : '') . ';' : '')
			. ($top ? 'top: ' . $top . (is_numeric($top) ? 'px' : '') . ';' : '')
			. ($width ? 'width: ' . $width . (is_numeric($width) ? 'px' : '') . ';' : '')
			. ($height ? 'height: ' . $height . (is_numeric($height) ? 'px' : '') . ';' : '')
			. array_value('style', $params);

			$id = $this->getId();

		// get column value
			$record = null;
		if ($value && is_array($value)) { // the value is entire record
			$record = $value;
			if (!array_key_exists($id . '_ID', $value)) {
				if (!array_key_exists($id, $value)) {
					if ($this->isCalculated())
						$value = $this->calculate($value);
					else
						throw new Exception("Cannot get column value: column $id is not found in the entry");
				}
				else
					$value = htmlspecialchars($record[$id]);
			}
			else
				$value = htmlspecialchars($record[$id . '_ID']);
		} elseif ($value === null) { // show default value
			$value = $searchMode ? $this->getSearchDefault() : $this->getDefault();
		}

		switch ($view) {
			case Data_Column_Grid::DATE:
			return $this->_getDateControl($value, $disabled, $style, $params, $searchMode, $record);
			break;
			case Data_Column_Grid::IMAGE:
			return $this->_getImageControl($value, $disabled, $style, $params, $searchMode, $record);
			break;
			case Data_Column_Grid::SIGNATURE:
			return $this->_getSignatureControl($value, $disabled, $style, $params, $searchMode, $record);
			break;	
			case Data_Column_Grid::MEMO:
			return $this->_getMemoControl($value, $disabled, $style, $params, $searchMode, $record);
			break;
			case Data_Column_Grid::DROPDOWN:
			return $this->_getDropdownControl($value, $disabled, $style, $params, $searchMode, $record);
			break;
			case Data_Column_Grid::RADIO:
			return $this->_getRadioControl($value, $disabled, $style, $params, $searchMode, $record);
			break;
			case Data_Column_Grid::TEXTAREA:
			return $this->_getTextareaControl($value, $disabled, $style, $params, $searchMode, $record);
			break;
			case Data_Column_Grid::CHECKBOX:
			return $this->_getCheckboxControl($value, $disabled, $style, $params, $searchMode, $record);
			break;
			case Data_Column_Grid::SIMPLETEXT:
			return $this->_getSimpletextControl($value, $disabled, $style, $params, $searchMode, $record);
			break;
			case Data_Column_Grid::CURRENCY:
			return $this->_getCurrencyControl($value, $disabled, $style, $params, $searchMode, $record);
			break;
			case Data_Column_Grid::TIME:
			return $this->_getTimeControl($value, $disabled, $style, $params, $searchMode, $record);
			break;
			case Data_Column_Grid::FILE:
			return $this->_getFileControl();
			break;
			case Data_Column_Grid::LINE:
			return $this->_getLineControl($value);
			break;
			case Data_Column_Grid::REGEXP:
			default:
			return $this->_getTextControl($value, $disabled, $style, $params, $searchMode, $record);
			break;
		}
	}

	protected function _getLineControl($value) {
		$date = new DateTime($value);
		$date = $date->format('m-d-Y H:i:s');
		return "<span>{$date}</span>";
	}
	

	protected function _getSignatureControl($value, $disabled, $style, $params, $searchMode, $record) {
		// no image - nothing to render TODO: show edit image button anyways
		//if (!$value)
		//	return "";

		$id = $this->getId();
		$gridId = $this->_gridId;
		$req = $this->getRequired() ? 'true' : 'false';
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		
		$width = array_key_exists('width', $params) ? $params['width'] : '200px';
		$height = array_key_exists('height', $params) ? $params['height'] : '50px';

		// depending on whether the path is full or relative generate the right link
		if ($value)
		{

			if (substr($value, 0, 7) != "http://" && substr($value, 0, 8) != "https://")
				$value = "{$baseUrl}/content/pictures/{$value}";

				// $value = "{$baseUrl}/content/pictures?id={$value}";
			
			$html = '<img src="'.$value.'"id="'.$gridId.'_'.$id.'" width="'.$width.'" height="'.$height.'">';
		}else{
			$value = "{$baseUrl}/content/pictures/tran_012.png";
			// $value = "{$baseUrl}/content/pictures?id=tran_012.png";
			
			$html = '<img src="'.$value.'"id="'.$gridId.'_'.$id.'" width="'.$width.'" height="'.$height.'">';	
		}
		//$html = "<div style='{$style}' src='{$value}' previewSrc='{$value}' dojoType='ginger.form.Image' name={$id} required='{$req}'
		//id='{$gridId}_{$id}' field={$id} grid='cmp_{$gridId}', width='{$width}' height='{$height}'></div>";

		return $html;
	}

	protected function _getImageControl($value, $disabled, $style, $params, $searchMode, $record) {
		// no image - nothing to render TODO: show edit image button anyways
		if (!$value)
			return "";

		$id = $this->getId();
		$gridId = $this->_gridId;
		$req = $this->getRequired() ? 'true' : 'false';
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		
		$width = array_key_exists('width', $params) ? $params['width'] : '200px';
		$height = array_key_exists('height', $params) ? $params['height'] : '50px';

		// depending on whether the path is full or relative generate the right link
		if (substr($value, 0, 7) != "http://" && substr($value, 0, 8) != "https://")
			$value = "{$baseUrl}/content/pictures/{$value}";

			// $value = "{$baseUrl}/content/pictures?id={$value}";
		
		$html = "<div style='{$style}' src='{$value}' previewSrc='{$value}' dojoType='ginger.form.Image' name={$id} required='{$req}'
		id='{$gridId}_{$id}' field={$id} grid='cmp_{$gridId}', width='{$width}' height='{$height}'></div>";

		return $html;
	}

	protected function _getDateControl($value, $disabled, $style, $params, $searchMode, $record) {
		$gridId = $this->_gridId;
		$id = $this->getId();
		$req = $this->getRequired() ? 'true' : 'false';

		

		if ($searchMode) { // we show "from" and "till" values then
		return
		"<table>
		<tr>
		<td><label id='{$gridId}_{$id}_lbl_from'>From:</label></td>
		<td><input type='text' dojoType = 'ginger.form.DateTextBox' value='{$value}'
		name='{$id}' id='{$gridId}_{$id}' style='width:144px'/></td>
		</tr><tr>
		<td><label id='{$gridId}_{$id}_lbl_to'>To:</label></td><td>
		<input type='text' dojoType = 'ginger.form.DateTextBox' value='{$value}'
		name='{$id}_to' id='{$gridId}_{$id}_to' style='width:144px'/></td></td>
		</tr>
		</table>";
	} else {
			// remove the time from the value
		$tPos = strpos($value, 'T');
			if ($tPos !== false) { // the value contains the "T" after which a time value is provided. This is causing misrepresentation on the client date-only fields.
			$value = substr($value, 0, $tPos);
		}

		if(trim($value) == '1900-01-01' || trim($value) == '1/1/1900'){
			$value = '';
		}


			//Zend_Registry::get('logger')->info("Date value: " . $value);
		$dojoType = $this->_autosave ? 'ginger.InlineText' : 'ginger.form.DateTextBox';
		$type = $this->_autosave && !$searchMode ? "div" : "input type='text'";
		$end = $this->_autosave && !$searchMode ? "></div>" : "/>";
		return
		"<$type dojoType='{$dojoType}' $disabled
		name='{$id}' id='{$gridId}_{$id}' field='{$id}' style='{$style}'
		editor='ginger.form.DateTextBox'
		grid='cmp_{$gridId}' required='{$req}' value='{$value}'{$end}";
	}
}

protected function _getMemoControl($value, $disabled, $style, $params, $searchMode, $record) {
	$id = $this->getId();
	$req = $this->getRequired() ? 'true' : 'false';
	$rows = array_key_exists('rows', $params) ? $params['rows'] : '3';
	$cols = array_key_exists('cols', $params) ? $params['cols'] : '20';
	$dojoType = $params['autosave'] && !$searchMode ? 'ginger.InlineText' : 'ginger.form.TextArea';
	$type = $params['autosave'] && !$searchMode ? "div" : "textarea";
	return
	"<$type dojoType='{$dojoType}' $disabled
	name='{$id}' id='{$this->_gridId}_{$id}' field='{$id}' style='{$style}'
	editor='ginger.form.TextArea' placeHolder='{$params['emptyText']}'
	rows='{$rows}' cols='{$cols}' grid='cmp_{$this->_gridId}' required={$req} value='" . str_replace("'", "`", $value) . "'></$type>";
}

protected function _getDropdownControl($value, $disabled, $style, $params, $searchMode, $record) {
	$id = $this->getId();
	$gridId = $this->_gridId;
	$req = $this->getRequired() ? 'true' : 'false';
	$caption = $this->getDropdownCaption();

	$displayedValue = array_value($id, $record);
	if ($displayedValue == null && !$searchMode) {
			// fetch default displayedValue by value id
		$displayedValue = $this->_grid->getDropDown($this)->getDisplayedValue($value);
	}

		// retrieve baseUrl including controller
	$actionPath = Zend_Controller_Front::getInstance()->getBaseUrl() . '/' .
	Zend_Controller_Front::getInstance()->getRequest()->getControllerName();

		// put any columns this dropdown is set to be dependent on
	$parentColumns = $this->getParentColumns();
	if (count($parentColumns) > 0) {
		$parentColumns = implode(',', $parentColumns);
	} else
	$parentColumns = '';

	return "<input type='text' dojoType='ginger.form.DropDown' $disabled parents='{$parentColumns}'
	name='{$id}' id='{$gridId}_{$id}' field='{$id}' style='{$style}'
	emptyCaption='{$caption}' hiddenValue='{$value}' gridId='{$gridId}'
	actionPath='{$actionPath}' 
	value='{$value}' displayedValue='{$displayedValue}'
	grid='cmp_{$gridId}' required='{$req}'/>";
}

protected function _getRadioControl($value, $disabled, $style, $params, $searchMode, $record) {
	$id = $this->getId();
	$gridId = $this->_gridId;
	$req = $this->getRequired() ? 'true' : 'false';

		// fetch all possible values this column can take - similar behavior to dropdown
	$radioValues = $this->_grid->getDropDown($this, true)->fetchEntries(null, '*', null, 0);

	$cb = false;
		if ($radioValues->getTotalItemCount() == 1) { // then we show a checkbox with associated value
			$cb = true;
			$inputType = 'checkbox';
			$dojoType = 'dijit.form.CheckBox';
		} else {
			$inputType = 'radio';
			$dojoType = 'dijit.form.RadioButton';
		}

		// put all radio buttons in a group element
		$html = "<span id='{$gridId}_{$id}' dojoType='ginger.RadioGroup' oldValue='{$value}'>";
		//$html .= "<input type='hidden' id='{$gridId}_{$id}_old_value' value='{$value}'>";
		// radiobuttons should be organized in columns, $cols=number of columns
		$rows = array_value('rows', $params);
		$cols = array_value('cols', $params);
		if ($cols > 1)
			$html .= "<table cellpadding='0' cellspacing='0'>";

		// output the available options
		$cnt = $radioValues->getTotalItemCount();
		$omit = array_value('omit', $params);

		foreach ($radioValues as $entryId => $entry) {
			if ($entry['ID'] != $omit) {
				if ($cols > 1) {
					if ($entryId % $cols == 0) { // borderline
						if ($entryId != 0) // not first record
						$html .= '</td></tr>';
						if ($entryId == $cnt - 1) // last record
						$html .= '<tr><td colspan="' . $cols . '">';
						else
							$html .= '<tr><td height="5">';
					}
					else {
						if ($entryId != 0)
							$html .= '</td>';
						if ($entryId == $cnt - 1 && ($cols - $entryId % $cols > 1)) // last record
						$html .= '<tr><td colspan="' . ($cols - $entryId % $cols) . '">';
						else
							$html .= '<td height="5">';
					}

					// if the record is the last one, close tags
					if ($entryId == $radioValues->getTotalItemCount())
						$html .= '</td></tr>';
				}

				// add input tag
				$radioId = $gridId . '_' . $id . '_' . $entryId;
				$entryIdent = $entry['ID'];
				$checked = $entryIdent == $value ? 'checked' : '';
				$entryName = $entry['NAME'];

				$html .= "<input type='radio' dojoType='dijit.form.RadioButton' $disabled
				name='{$id}' id='{$radioId}' style='{$style}'
				{$checked} value='{$entry['ID']}' 
				/>";
				//onClick=\"dijit.byId('{$gridId}_{$id}').changeState('{$radioId}')\"
				// add label tag
				if ($entry['NAME'] == 'Other' && array_key_exists('other', $params) && ($searchMode == false)) { // show input for 'other'
				$html .= $this->_grid->getFieldControl($params['other'], $record, array('width' => '99'));
			}
			else
				$html .= "<label id='label_{$radioId}' for='{$radioId}'>$entryName</label>";

			if ($cols && !($cols > 1))
				$html .= '<br/>';
			elseif (!$cols)
				$html .= '&nbsp;&nbsp;';
		}
	}
	if ($cols > 1)
		$html .= '</table>';
	$html .= '</span>';
	return $html;
}

protected function _getCheckboxControl($value, $disabled, $style, $params, $searchMode, $record) {
	$id = $this->getId();
	$gridId = $this->_gridId;
	$req = $this->getRequired() ? 'true' : 'false';
	$checked = $value != '' ? 'checked' : '';
	return
	"<input type='text' dojoType='ginger.form.CheckBox' $disabled $checked
	name='{$id}' id='{$gridId}_{$id}' field='{$id}' style='{$style}'
	grid='cmp_{$gridId}' required='{$req}' value='{$value}'/>";
}

protected function _getSimpletextControl($value, $disabled, $style, $params, $searchMode, $record) {
	$id = $this->getId();
	$gridId = $this->_gridId;
	$req = $this->getRequired() ? 'true' : 'false';
	$dojoType = $this->_autosave && !$searchMode ? 'ginger.InlineText' : 'dijit.form.TextBox';
	$type = $this->_autosave && !$searchMode ? "div" : "input type='text'";
	$end = $this->_autosave && !$searchMode ? "></div>" : "/>";
	$maxlength = $this->getMaxlength() ? "maxlength='" . $this->getMaxlength() . "'" : '';
	return
	"<$type dojoType='{$dojoType}' $disabled required='{$req}'
	name='{$id}' id='{$gridId}_{$id}' field='{$id}' style='{$style}'
	editor='dijit.form.TextBox' {$maxlength}
	grid='cmp_{$gridId}' required='{$req}' value='" . str_replace("'", "`", $value) . "'{$end}";
}

protected function _getCurrencyControl($value, $disabled, $style, $params, $searchMode, $record) {
	$id = $this->getId();
	$gridId = $this->_gridId;
	$req = $this->getRequired() ? 'true' : 'false';
	$dojoType = $this->_autosave && !$searchMode ? 'ginger.InlineText' : 'dijit.form.CurrencyTextBox';
	$type = $this->_autosave && !$searchMode ? "div" : "input type='text'";
	$end = $this->_autosave && !$searchMode ? "></div>" : "/>";
	return
	"<$type dojoType='{$dojoType}' $disabled
	name='{$id}' id='{$gridId}_{$id}' field='{$id}' style='{$style}'
	grid='cmp_{$gridId}' required='{$req}' value='{$value}'
	editor='dijit.form.CurrencyTextBox'
	constraints={min:1,max:100,fractional:true} currency='USD'{$end}";
}

protected function _getTextControl($value, $disabled, $style, $params, $searchMode, $record) {
	$id = $this->getId();
	$gridId = $this->_gridId;
	$req = $this->getRequired() ? 'true' : 'false';
	$regExp = array_value('regExp', $params);
	$invalidMessage = array_value('invalidMessage', $params);
	$size = array_value('size', $params);
	$dojoType = $this->_autosave && !$searchMode ? 'ginger.InlineText' : 'ginger.form.TextBox';
	$type = $this->_autosave && !$searchMode ? "div" : "input type='text'";
	$end = $this->_autosave && !$searchMode ? "></div>" : "/>";
	$class = array_key_exists('class', $params) ? "class='" . $params['class'] . "'" : "";
	$maxlength = $this->getMaxlength() ? "maxlength='" . $this->getMaxlength() . "'" : '';

		//hidden values ave simpler html
	if ($this->getInputType() == 'hidden')
		return "<input type='hidden' name='{$id}' id='{$this->_gridId}_{$id}' 
	grid='cmp_{$this->_gridId}' required={$req} value='{$value}'/>";

	return
	"<$type dojoType='{$dojoType}' $disabled
	regExp='{$regExp}' invalidMessage='{$invalidMessage}' size='{$size}'
	name='{$id}' id='{$gridId}_{$id}' field='{$id}' style='{$style}'
	editor='ginger.form.TextBox' {$class} {$maxlength}
	grid='cmp_{$gridId}' required='{$req}' value='" . str_replace("'", "`", $value) . "'{$end}";
}

protected function _getTextareaControl($value, $disabled, $style, $params, $searchMode, $record) {
	$id = $this->getId();
	$req = $this->getRequired() ? 'true' : 'false';
	$rows = array_key_exists('rows', $params) ? $params['rows'] : '2';
	$cols = array_key_exists('cols', $params) ? $params['cols'] : '20';
	$dojoType = $params['autosave'] && !$searchMode ? 'ginger.InlineText' : 'ginger.form.TextArea';
	$type = $params['autosave'] && !$searchMode ? "div" : "textarea";
	$maxlength = $this->getMaxlength() ? "maxlength='" . $this->getMaxlength() . "'" : '';
	return
	"<$type dojoType='{$dojoType}' $disabled
	name='{$id}' id='{$this->_gridId}_{$id}' field='{$id}' style='{$style}'
	editor='ginger.form.TextArea' placeHolder='{$params['emptyText']}'
	rows='{$rows}' cols='{$cols}' {$maxlength} grid='cmp_{$this->_gridId}' required={$req} value='" . str_replace("'", "`", $value) . "'></$type>";
}


protected function _getTimeControl($value, $disabled, $style, $params, $searchMode, $record) {
	$id = $this->getId();
	$gridId = $this->_gridId;
	$req = $this->getRequired() ? 'true' : 'false';
	$dojoType = $this->_autosave && !$searchMode ? 'ginger.InlineText' : 'ginger.form.TimeTextBox';
	$type = $this->_autosave && !$searchMode ? "div" : "input type='text'";
	$end = $this->_autosave && !$searchMode ? "></div>" : "/>";
	return
	"<$type dojoType='{$dojoType}' $disabled
	regExp='{$regExp}' invalidMessage='{$invalidMessage}' size='{$size}'
	name='{$id}' id='{$gridId}_{$id}' field='{$id}' style='{$style}'
	editor='ginger.form.TimeTextBox' " . 'constraints="{timePattern: \'HH:mm\'}" ' . " 
	grid='cmp_{$gridId}' required='{$req}' value='{$value}'{$end}";
}

protected function _getFileControl() {
	$id = $this->getId();
	$gridId = $this->_gridId;
	return
	"<div style='position: relative;'>
	<input type='file' id='{$gridId}_{$id}_hidden' name='$id'
	style='position: relative; text-align: right; -moz-opacity: 0;
	filter:alpha(opacity: 0); opacity: 0;z-index: 2; width: 270px;'
	onchange  ='dojo.byId('{$gridId}_{$id}').value = dojo.byId('{$gridId}_{$id}_hidden').value'
	onmouseout='dojo.byId('{$gridId}_{$id}').value = dojo.byId('{$gridId}_{$id}_hidden').value'/>
	<table style='position: absolute; left: 0px; top: 0px; z-index: 1'><tr>
	<td><input id='logoFilePath' type='text' dojoType='dijit.form.ValidationTextBox' disabled/></td>
	<td><button dojoType='dijit.form.Button' label='BROWSE...' class='short'></button></td>
	</tr></table>
	</div>";
}

public function setParentColumns($columnIds = array()) {
	if (is_array($columnIds))
		$this->_parentColumns = $columnIds;
	else
		$this->_parentColumns = array($columnIds);
}

public function getParentColumns() {
	return $this->_parentColumns;
}
}