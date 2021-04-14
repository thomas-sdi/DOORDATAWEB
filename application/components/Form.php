<?

require_once APPLICATION_PATH . '/components/validation/Exception.php';

class Ginger_Form {

	const SUBMIT_BUTTON = 'submit_button', HEADER = 'HEADER';

	protected $_elements;
	protected $_id;
	protected $_validationErrors;
	protected $_redirect;
	protected $_values = array();
	protected $_action;  // Action
	protected $_submitText = "Submit"; // SubmitText
	protected $_model;  // Model
	protected $_isCustomView = false; // IsCustomView: Boolean, shows if the form has custom view
	protected $_title = ''; // Title
	protected $_iframe = false;   // IFrame: Boolean, shows if the form must be sent with iframe
	protected $_dojoType = "";   // DojoType: replaces ginger.form.ValidationForm with custom form
	protected $_dojoParams = array();   // Custom parameters to be passed to the form dojo object 
	protected $_style = ""; // Style: css style
	protected $_ignoreReturn = false; // ignore return array in execute

	public function __construct($id, $elements = array(), $params = array()) {
		$this->_elements = array();
		$this->_flushValidationErrors();
		$this->_id = $id;
		if (count($elements) > 0)
			$this->setElements($elements);
		if (count($params) > 0) {
			$this->setParams($params);
		}

		// on back/forward try to get previous values
		$prevForm = $this->getSession()->forms[$this->getId()];
		if ($prevForm && $_GET['history'] == '1') {
			$this->setValues($prevForm->getValues());
		} else {
			unset($this->getSession()->forms[$this->getId()]);
		}
	}

	public function setParams($params) {

		foreach ($params as $param => $value) {
			switch ($param) {
				case 'Action' : $this->_action = $value;
					break;
				case 'SubmitText' : $this->_submitText = $value;
					break;
				case 'Model' : $this->_model = $value;
					break;
				case 'IsCustomView' : $this->_isCustomView = $value;
					break;
				case 'Title' : $this->_title = $value;
					break;
				case 'IFrame' : $this->_iframe = $value;
					break;
				case 'DojoType' : $this->_dojoType = $value;
					break;
				case 'DojoParams' : $this->_dojoParams = $value;
					break;
				case 'Style' : $this->setStyle($value);
					break;
			}
		}
	}

	public function getElements() {
		return $this->_elements;
	}

	public function getElementIds() {
		$ids = array();
		foreach ($this->_elements as $element)
			$ids[] = $element->getId();

		return $ids;
	}

	/**
	 *
	 * @param String $id
	 * @return Data_Column_Grid 
	 */
	public function getElement($id) {
		return $this->_elements[$id];
	}

	public function addElement($columnGrid) {
		$this->_elements[$columnGrid->getId()] = $columnGrid;
	}

	public function setElements($columns = array()) {
		foreach ($columns as $columnName => $columnAttrs) {
			if (is_array($columnAttrs)) {
				if (!array_value('Id', $columnAttrs))
					$columnAttrs['Id'] = $columnName;
				$column = new Data_Column_Grid($this->_id, $columnAttrs);
			}
			$this->addElement($column);
		}
	}

	public function getId() {
		return $this->_id;
	}

	public function getValues() {
		return $this->_values;
	}

	public function getValue($id) {
		$element = $this->getElement($id);
//		if ($element && $element->getView() == Data_Column_Grid::ADDRESS) {
//			return array(
//				$id => $this->_values[$id],
//				$id . '_lat' => $this->_values[$id . '_lat'],
//				$id . '_lng' => $this->_values[$id . '_lng']
//			);
//		}
		return $this->_values[$id];
	}

	public function setValues($data = array()) {
		$this->_values = array();
		if (is_empty($data))
			return;
		foreach ($data as $key => $value) {
			$value = $value == '' ? null : $value;
			$this->_values[strtolower($key)] = $value;
		}
	}

	public function getTitle() {
		return t($this->_title);
	}

	public function setTitle($title) {
		$this->_title = $title;
	}

	public function getAction() {
		return $this->_action;
	}

	public function setAction($action) {
		$this->_action = $action;
	}

	public function setSubmitText($text) {
		return $this->_submitText = $text;
	}

	public function getSubmitText() {
		return t($this->_submitText);
	}

	public function isCustomView() {
		return $this->_isCustomView;
	}

	public function isIFrame() {
		return $this->_iframe;
	}

	public function getDojoType() {
		return $this->_dojoType;
	}

	public function setDojoType($dojoType) {
		$this->_dojoType = $dojoType;
	}

	public function getDojoParams() {
		return nvl($this->_dojoParams, array());
	}

	/**
	 * Eexcutes the form and returns array of params to validation form
	 * @param $data
	 * @return array($paramKey => $paramValue), where $paramKey is one of following:
	 * 		'href' 		=> href	- redirect to following href,
	 * 		'global'	=> true - refresh whole page
	 */
	public function execute($data) {
		$this->setValues($data);
		$this->convert($data);

		// remember to session submitted values
		$prevForm = $this;
		$prevForm->setValues($this->getValues());
		$this->getSession()->forms[$this->getId()] = $prevForm;

		$this->_validate();

		return $this->getValues();
	}

	protected function _flushValidationErrors() {
		$this->_validationErrors = array();
	}

	protected function _validate() {
		$data = $this->getValues();
		$this->_flushValidationErrors();


		$this->_validateRequiredFields($data);

		$this->_validateFormattedFields($data);

		if (count($this->_validationErrors)) {
			throw new Validation_Exception($this->_validationErrors);
		}
	}

	protected function _validateRequiredFields($data) {
		foreach ($this->getElements() as $element) {
			if ($element->getRequired()) {
				$value = $this->getValue($element->getId());

				if (is_empty($value)) {
					if ($element->getView() == Data_Column_Grid::DROPDOWN) {
						if (!$element->getAllowNewValues() || is_empty($data[$element->getId() . '_name']))
							$this->_addValidationError('This field is required', $element->getId());
					} else
						$this->_addValidationError('This field is required', $element->getId());
				}
			}
		}
	}

	/**
	 * Validate elements using regExp
	 */
	protected function _validateFormattedFields($data) {
		foreach ($this->getElements() as $element) {
			$value = $this->getValue($element->getId());
			if ($element->getRegExp() && !is_empty($value)) {
				$regExp = trim($element->getRegExp(), ' /');
				$regExp = '/' . $regExp . '/';

				if (preg_match($regExp, $value) <= 0)
					$this->_addValidationError('Wrong format', $element->getId());
			}
		}
	}

	protected function _addValidationError($rule, $field) {
		if (!array_key_exists($rule, $this->_validationErrors))
			$this->_validationErrors[$rule] = array();

		$this->_validationErrors[$rule][] = $field;
	}

	/**
	 * Modifies values before saving to DB
	 * @param $data array
	 * @return $data array(key=>value)
	 */
	public function convert($data) {
		foreach ($this->getElements() as $id => $element) {
			$value = $data[$element->getId()];
			$view = $element->getView();
			if (is_empty($value))
				continue;
			if ($view == Data_Column_Grid::TIME) {
				//remove leading T from time
				$value = str_replace('T', '', $value);
			} else if ($view == Data_Column_Grid::CHECKBOX) {
				if ($value == 'true' || $value === true)
					$value = '1';
			} else if ($view == Data_Column_Grid::ADDRESS) {
				$address = Address::fromString($value);
				$data[$element->getId() . '_lat'] = $address['lat'];
				$data[$element->getId() . '_lng'] = $address['lng'];
				$value = $address['name'];
			} /* else if ($view == Data_Column_Grid::DROPDOWN) {
			  $pos = strpos($value, '#'); if ($pos === false) $pos = strlen($value);
			  $displayedValue = substr($value, $pos+1);
			  $value = substr($value, 0, $pos);
			  $data[$element->getId().'_name'] = $displayedValue;
			  } */
			$data[$element->getId()] = $value;
		}
		$this->setValues($data);
		return $data;
	}

	/**
	 * Renders form element
	 * @param $id id of element
	 * @param $params array of parameters
	 * @return string html of element
	 */
	public function render($id, $params = array()) {

		if ($id == self::SUBMIT_BUTTON)
			return "<div id='{$this->_id}Form_submit' dojoType='ginger.form.SubmitButton' text='{$this->getSubmitText()}'></div>";
		if ($id == self::HEADER)
			return $this->renderHeader();

		$element = $this->getElement($id);
		$value = $this->getValue($id);
		if (!$element) {
			throw new Exception('Cannot find form element ' . $id);
		}
		if ($element->getView() == Data_Column_Grid::RADIO) {
			return self::renderRadio($this->getId(), $id, $element->getRadioOptions(), nvl($value, $element->getDefault()));
		}

		return $element->getFieldControl($this->getValue($id), $params);
	}

	/**
	 * Returns the head of form
	 */
	public function renderHeader() {
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$formId = $this->getId();
		$dojoType = $this->getDojoType() ? $this->getDojoType() : 'ginger.form.ValidationForm';
		$action = $baseUrl . $this->getAction();
		$encType = $this->isIFrame() ? 'enctype="multipart/form-data"' : '';
		$style = $this->getStyle();

		// set custom form dojo params, if any
		$formParams = '';
		foreach ($this->getDojoParams() as $param => $value) {
			$formParams .= "$param='$value'";
		}

		return
				"dojoType='{$dojoType}' 
			id='{$formId}Form' 
			jsid='{$formId}Form'  
			action='{$action}' 
			class='form-{$formId}'
			style='{$style}'
			$formParams 
			{$encType}";
	}

	public static function renderRadio($id, $name, $options = array(), $default = null) {
		$html = "";
		foreach ($options as $value => $title) {
			$html .= "<span class='formInput' style='float: left; margin: 10px 10px 6px 0'>
				<input type='radio' dojoType='dijit.form.RadioButton' name='{$name}' value='{$value}' "
					. ($default == $value ? "checked" : "") .
					"/>
				</span>
				<span class='formFieldFloatLabel'>{$title}</span>";
		}
		return $html;
	}

	public static function renderDate($id, $name, $value, $params = array()) {
		$column = new Data_Column_Grid($id, array(
					'View' => Data_Column_Grid::DATE,
					'Id' => $name
				));
		return $column->getFieldControl($value, $params);
	}

	public function log($text) {
		Zend_Registry::getInstance()->logger->info($text);
	}

	public function getConfig() {
		return Zend_Registry::getInstance()->configuration;
	}

	public function getSession() {
		return new Zend_Session_Namespace('default');
	}

	public function getUser() {
		return Zend_Auth::getInstance()->getIdentity();
	}

	public function getUserId() {
		if (!$this->getUser())
			return null;
		$user = Model_User::getInstance()->fetchEntry(null, array('id', 'login' => new Data_Column('login', $this->getUser())));
		if (!$user)
			return null;
		else
			return $user['ID'];
	}

	public function getModel() {
		return null;
	}

	/**
	 * Do some actions when form is loaded
	 * @param array $params 
	 */
	public function load($params = array()) {
		
	}

	public function getStyle() {
		return $this->_style;
	}

	public function setStyle($style = "") {
		$this->_style = $style;
	}

	public function getIgnoreReturn() {
		return $this->_ignoreReturn;
	}

	public function setIgnoreReturn($ignore) {
		$this->_ignoreReturn = $ignore;
	}

}