<?
require_once APPLICATION_PATH . '/components/CustomGrid.php';
require_once APPLICATION_PATH . '/models/UserFile.php';

class UserFileGrid extends Cmp_Custom_Grid {
	


	public function __construct($id, $parent=null, $parentLink=null) {
		
		$this->_usePaginator = true;

        $acl  = Zend_Registry::get('_acl');
        $user = Zend_Auth::getInstance()->getIdentity();

        // define columns
        $columns = array(
            'id'            => array('Field'=> 'ID','Visible'=> '000','Id'=> 'ID'),
            'USER_ID'       => array('Display' => 'LOGIN',
                'Filter'=> $user,
                'Visible' => '000'),
            'FILE_NAME'     => array('Title'   => 'File', 'Id' => 'FILE_NAME'),
            'FILE_SIZE'      => array('Title'   => 'Size',
                'Editable'=> false,
                'Width' => '150px'
            ),
            'ADDED_ON'      => array('Title'   => 'Added On',
                'Editable'=> false,
                'View'  => Data_Column_Grid::DATE,
                'Width' => '150px'),
            'DESCRIPTION'   => array('Title'   => 'Description', 'View' => Data_Column_Grid::MEMO, 'Id' => 'DESCRIPTION'),
            array('Title' => 'Actions', 'Calculated' => array($this, "calculateInspectionActions"), 'Visible' => '100')
        );

        parent::__construct($id, Model_UserFile::retrieve(), $parent, $parentLink, $columns);    
    }

    public function calculateInspectionActions($entry){
       
        $file = Model_UserFile::retrieve()->fetchEntry($entry["ID"]);

        $gridId = $this->getId();

        $actionHead = '<div class="dropdown action-dropdown">
        <a data-toggle="dropdown">
        -Select-
        <i class="glyph-icon icon-chevron-down"></i>
        </a><div class="dropdown-menu float-right"><div class="">';
        $actionFoot = '</div></div></div>';

        $actions = "<div class='pad5A button-pane button-pane-alt text-center'><a data-original-title='Edit' title='' data-placement='top' class='btn btn-sm hover-blue-alt tooltip-button' href='javascript: cmp_".$gridId.".showEditDialog(". $entry["ID"] .")'> <i class='fa fa-edit'></i>Edit</a></div>" .
        "<div class='pad5A button-pane button-pane-alt text-center'><a data-original-title='Download' title='Download' data-placement='top' class='btn btn-sm hover-blue-alt tooltip-button' href='" . Zend_Controller_Front::getInstance()->getBaseUrl() .  
        "/content/userdata?id=" . rawurlencode($file['FILE_NAME']) . "' target=_self><i class='glyph-icon icon-download'                ></i>Download</a></div> " .
        "<div class='pad5A button-pane button-pane-alt text-center'><a data-original-title='Delete' title='Delete' data-placement='top' class='btn btn-sm hover-red tooltip-button' href='javascript: cmp_".$gridId.".deleteItem(". $entry["ID"] .")'><i class='glyph-icon icon-remove'></i>Delete</a></div>";
        return $actionHead . $actions . $actionFoot;
        
    }


}