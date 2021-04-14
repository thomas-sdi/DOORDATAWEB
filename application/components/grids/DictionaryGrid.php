<?
require_once APPLICATION_PATH . '/components/CustomGrid.php';

class DictionaryGrid extends Cmp_Custom_Grid {
	
	public function __construct($id, $parent=null, $parentLink=null) {
		
		$this->_usePaginator = true;

		// define columns
		$columns = array (
			'id'		    => array('Field'=> 'id','Visible'=> '000','Id'=> 'ID'),
            'PARENT_ID'     => array('Id'=> 'PARENT_ID','Title' => 'Parent Id'),
            'CATEGORY'      => array('Id'=> 'CATEGORY','Title'  => 'Category'),
            'ITEM'          => array('Id'=> 'ITEM','Title'      => 'Item' ),
            'DESCRIPTION'   => array('Id'=> 'DESCRIPTION','Title'=> 'Description'),
            'VALUE_ORDER'   => array('Id'=> 'VALUE_ORDER','Title'=> 'Value Order'),
            'RECOMENDATION' => array('Visible'=> '000','Id'=> 'RECOMENDATION'),
        );

        $acl  = Zend_Registry::get('_acl');
        $user = Zend_Auth::getInstance()->getIdentity();

        parent::__construct($id, Model_Dictionary::retrieve(), $parent, $parentLink, $columns);    
    }

}