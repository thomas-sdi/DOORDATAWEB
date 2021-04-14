<?
require_once APPLICATION_PATH . '/components/CustomGrid.php';
require_once APPLICATION_PATH . '/components/CustomTree.php';
require_once APPLICATION_PATH . '/components/DropDown.php';
require_once APPLICATION_PATH . '/controllers/Abstract.php';

abstract class Controller_Component extends Controller_Abstract {

  protected $_components = array();
  protected $_filter     = array();
  protected $_component;
  protected $_fetchStart;
  protected $_model;
    protected $_sortBy; // grid column to sort by
    protected $_sortDirection; // true == 'asc', false == 'desc'
    protected $_forms = array();
    
    public function init() {
    	parent::init();
    	$this->view->components = $this->_components;
    	
    	if (!array_key_exists('_model', $this->_params)) {  
			// Zend_Registry::get('logger')->info('compinit completed');
    		return;
      }

    	// remember the component and the model
      $modelName = $this->_params['_model'];
      $this->_component = $this->getComponent($modelName);
      if ($this->_component)
        $this->_model = $this->_component->getModel();
      else {
        $modelName = ucwords($modelName);
        require_once APPLICATION_PATH . '/models/' . $modelName . '.php';
        $this->_model = call_user_func(array('Model_' . $modelName, 'retrieve'));
      } 

    	// get number of record from where to start fetching (needed for paging)
      if (array_key_exists('start', $this->_params)) {
        $this->_fetchStart = $this->_params['start'];
      } else $this->_fetchStart = null;

        // get sorting column (and direction)
      if (array_key_exists('_sort', $this->_params)) {
        $this->_sortBy = $this->_params['_sort'];
        if (array_key_exists('_sortDir', $this->_params))
         $this->_sortDirection = $this->_params['_sortDir'] == 'asc';
       else $this->_sortDirection = true;
     }

        // get user filter
     foreach ($this->_params as $param => $value) {
      $this->_filter[$param] = $value;
    }

    Zend_Registry::get('logger')->info('Params: ' . var_export($this->_filter, true));
  }

    /**
     * Creates a new grid
     *
     * @param string $id
     * @param Model_Abstract $model
     * @param string $parentId
     * @return Cmp_Custom_Grid
     */
    public function addGrid($id, $model=null, $parentId=null, $parentLink=null, $columns=null) {
    	if (is_object($id)) { // $id is a grid itself
    		$this->_components[$id->getId()] = $id;
       return $id;
     }

     $parent = $this->getComponent($parentId);
        //Zend_Registry::get('logger')->info('Parent before: ' . $parentId . ', after: ' . $parent . ', id: ' . $id);

     if (!$parent) $parent = $parentId;

     $newGrid = new Cmp_Custom_Grid($id, $model, $parent, $parentLink, $columns);
     $this->_components[$id] = $newGrid;

     return $newGrid;
   }

   public function addTree($id, $model, $relModel, $relParent, $relChild='ID') {
    $newTree = new Cmp_Custom_Tree($id, $model, $relModel, $relParent, $relChild);
    $this->_components[$id] = $newTree;
    return $newTree;
  }

  public function addForm($form) {
   $this->_forms[$form->getId()] = $form;
		//$this->_components[$form->getId()] = $form;

   return $form;
 }

 public function getForm($formId) {
   return $this->_forms[$formId];
 }

    /**
     * Returns grid by its id
     *
     * @param string $id
     * @return component
     */
    public function getComponent($id) {
    	if(!array_key_exists($id, $this->_components))
    		return null;
    	else 
       return $this->_components[$id];
   }

   public function excelAction($id) {
    $this->_helper->layout->setLayout('excel');
    $this->_helper->ViewRenderer->setNoRender(true);

    $this->_filter['_super'] = $this->getParam('_super');
    $this->view->placeholder('data')->set($this->getComponent($id)->fetchExcel($this->_filter, $this->_sortBy, $this->_sortDirection));
  }


    /**
     * Used by grids to display inline dropdowns for reference columns
     */
    public function dropdownAction() {
    	// set output format to json and disable view rendering
      $this->_helper->layout->setLayout('json');
      $this->_helper->ViewRenderer->setNoRender(true);

        // get filter
      $byId = true; $filter = $this->getParam('id');
      if (!$filter) {$byId = false; $filter =  $this->getParam('name');}
      if (!$filter) $filter = '*';
        //$parent = $this->getParam('_parent');
        // extra filter (in case of column dependencies)
      $parent = $this->_filter;

        // insert dropdown values into the layout
      $requiredParam = $this->getParam('_required');
      $this->view->placeholder('data')->set(
        $this->_component->fetchReferenceValuesJson(
          $this->getParam('_column'), $filter, $parent, $this->_fetchStart, $requiredParam, $byId));
    }
    
    public function fetchAction() {
		// set layout to conform to json format
      $this->_helper->layout->setLayout('json');
      $this->_helper->ViewRenderer->setNoRender(true);

      $this->view->placeholder('data')->set($this->_component->fetchJson(
        $this->_filter, $this->_fetchStart, $this->_sortBy, $this->_sortDirection));
    }

    public function saveAction($customValues=false) {
      $this->_helper->layout->setLayout('json');
      $json = $this->getRequest()->json;
      if (!$json) { 
        // basic saving mode, where all post parameters represent the actual form values
        $recordId = array_value('id', $this->_params);
        $token = $recordId ? ($this->getRequest()->isDelete() ? 'deletedItems' : 'changedItems') : 'newItems';
        $postParams = $this->getRequest()->getPost();
        if ($recordId) {
          $postParams['_ident'] = $postParams['id'] = $postParams['_ID'] = $recordId;
        }

        if(isset($postParams['NAME'])){
          $word = str_replace('’', "'", $postParams['NAME']);
          $word = str_replace('”', '"', $word);
          $postParams['NAME'] = $word;
        }

        $json = Zend_Json::encode(array($token => array($postParams)));
      }


        // check if there are any errors during the save operation
      $resultJson = $this->_component->saveJson($json, $customValues);
      $result = Zend_Json::decode($resultJson);

      if ($result && array_key_exists('errors', $result)) {
       Zend_Registry::get('logger')->info('Errors during save: ' . var_export($result['errors'], true));
       $errorByField = array();
       foreach ($result['errors'] as $ruleGroup) {
        foreach ($ruleGroup as $rule) {
         if (is_array($rule['Fields'])){
          foreach($rule['Fields'] as $field) {
           if (!array_key_exists($field, $errorByField))
            $errorByField[$field] = array();
          $errorByField[$field][] = $rule['Rule'];
          Zend_Registry::get('logger')->info('Rule: ' . $rule['Rule']); 
        }
      }
    }
  }
  $this->report400(Zend_Json::encode(array('problems' => $errorByField)));
  return;
}

		// no errors, proceed
$this->view->placeholder('data')->set($resultJson);

        // for this action we don't need any special view
        // all presentation is done on a template level
$this->_helper->ViewRenderer->setNoRender(true);
}

public function indexAction() {
  $this->_helper->layout->disableLayout();
  $this->view->components = $this->_components;
}

	/**
	 * For detailed dialog rendering
	 */
	public function detailedAction() {
		$this->_helper->layout->setLayout('detailed');
		$this->_helper->ViewRenderer->setNoRender(true);
		
		// set view grid 
		$this->view->placeholder('content')->grid = $this->_component;
		
		// fetch selected record
		$row = $this->getParam('_parent');
		$record = ($row && $row > 0) ? $this->_component->fetchEntry($row) : null;
    $this->view->placeholder('content')->record = $record;

		// set mode
    $this->view->placeholder('content')->search = false;
  }

	/**
	 * For search dialog rendering
	 */
	public function searchAction() {
		$this->_helper->layout->setLayout('detailed');
		$this->_helper->ViewRenderer->setNoRender(true);
		
		// set view grid 
		$this->view->placeholder('content')->grid = $this->_component;
		
		// set mode
		$this->view->placeholder('content')->search = true;
	}

  protected function _detailedView($grid) {
    $grid = $this->_components[$grid];
    $this->view->grid = $grid;
    $row = $this->getParam('_parent');
    if ($row && $row > 0) $this->view->record = $grid->fetchEntry($row);
  }

	/*
	 * Submit a form
	 */
	public function submitAction() {
		$this->_helper->ViewRenderer->setNoRender(true);

		$form = $this->getForm($this->_params['formId']);
		if ($form->isIFrame()) {
			$this->_helper->layout->setLayout('iframe');
			$placeholder = $this->view->placeholder('content');
		}
		else {
			$this->_helper->layout->setLayout('json');
			$placeholder = $this->view->placeholder('data');
		}
		
		$result = null;
		
		// execute the form within a single database transaction
		$db = Zend_Registry::get('dbAdapter'); 
		$db->beginTransaction();
		try {
     $result = $form->execute($this->_params);
     $db->commit();
     $placeholder->set(Zend_Json::encode($result));
     Zend_Registry::get('logger')->info('Result: ' . var_export($result));
   } catch(Validation_Exception $v) {
        	// client error message, return JSON with the list of errors
     $this->report400('{problems: ' . $v->getMessageJson() . '}');
     $db->rollback();
   } catch (Exception $e) {
     $this->report500('Server error during processing of form ' . $form->getId() . ': ' . $e->getMessage());
     $db->rollback();
   }
 }

 protected function _initializeBratiliusGrid($params) {
  $this->view->grid = $this->getComponent(array_value('gridId', $params));

  $this->view->gridParams = array(
   'page' 	 		=> nvl($this->getParam('page'), 0),            
   'sortBy' 		=> nvl($this->getParam('sort_by'), array_value('sortBy', $params)),
   'sortDirection' => nvl($this->getParam('sort_direction'), array_value('sortDirection', $params)),
			// 'rowsPerPage'	=> nvl($this->getParam('rows_per_screen'), Zend_Registry::getInstance()->configuration->paginator->page),
   'rowsPerPage'   => nvl($this->getParam('rowsPerPage'),20),

   'selectAll'		=> nvl($this->getParam('select_all'), false)
 );

  $filter = $this->_params;
  unset($filter['page']); unset($filter['sort_by']); unset($filter['sort_direction']); unset($filter['_model']);
  $this->view->gridParams['filter'] = $filter;
}
}
