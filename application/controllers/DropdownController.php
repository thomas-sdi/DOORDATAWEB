<?
require_once APPLICATION_PATH . '/components/DropDown.php';
require_once APPLICATION_PATH . '/controllers/Component.php';

class DropDownController extends Controller_Component {
	
    public function fetchAction() {
    	// layout must conform to json format
        $this->_helper->layout->setLayout('json');
        $this->_helper->ViewRenderer->setNoRender(true);
        
        // get column from model to display values from (fail if not defined)
        if (!array_key_exists('_column', $this->_params))
            throw new Exception('Cannot fetch data for the dropdown: column is not defined');
        $column = $this->_params['_column'];
        
        // whether the value can be null or not
        $required = array_key_exists('_required', $this->_params) ? $this->_params['_required'] : false;
        
        // get filter (if presented)
        $nameFilter = array_key_exists('name', $this->_params) ? $this->_params['name'] : '*';
        
        $dropDown = new Cmp_Drop_Down($this->_model, new Data_Column($column, null, $this->_model),
            null, $required);

        // insert dropdown values into the view
        $this->view->placeholder('data')->set($dropDown->fetchJson(
            $nameFilter, null, $this->_fetchStart));
    }
}