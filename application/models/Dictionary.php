<?
require_once APPLICATION_PATH . '/models/DBTable/Dictionary.php';
require_once APPLICATION_PATH . '/models/Abstract.php';

class Model_Dictionary extends Model_Abstract{
	
	const COUNTRY_USA = 999, COUNTRY_CANADA = 1000;
    
    protected function _init(){
        $this->_table = new DBTable_Dictionary();
        $this->_name = 'ITEM';
        parent::_init();
    }
    
    public static function retrieve($class=null) {
        return parent::retrieve(__CLASS__);
    }
    
	/**
     * Inserts or updates a row in the table after performing validation checks
     * @param array $data    The row to be saved
     * @param array $ignored List of warnings asked to be ignored
     * @return string New id (if this was an insert) or existing (if update)   
	 */
    public function save($data, $ignored=null){
    	$rows = $this->fetchEntries(array('ID', new Data_Column('CATEGORY', $data['CATEGORY']), new Data_Column('ITEM', $data['ITEM'])));
    	if ($rows->count() == 0) {
    		return parent::save($data, $ignored);
    	} else {
    		$row = $rows->getItem(1);
    		return $row['ID']; 
    	}
    }

	/**
     * Returns id of item from dictionary
     *
     * @param string 	$item		item to search in dictionary
     * @param string 	$category	optional, search only within this category
     * @param boolean	$create		optional, if item does not exist, create it or return null 
     * @return string	id of item from dictionary or null
     */
    public static function getIdByItem($item, $category = null, $create = false) {
    	if (strlen(trim($item)) == 0) {
    		return NULL;
    	}
    	
    	if (strlen(trim($category)) == 0) {
    		$codes = Model_Dictionary::retrieve()->fetchEntries(array('ID', new Data_Column('ITEM',trim($item))));
    	} else {
    		$codes = Model_Dictionary::retrieve()->fetchEntries(array('ID', new Data_Column('ITEM', trim($item)), new Data_Column('CATEGORY', trim($category))));
    	}
    	
    	if ($codes->getTotalItemCount() > 0) {
    		$code = $codes->getItem(1);
    		return $code['ID'];
    	} else {
    		if ($create) {
    			//if no such item in dictionary, then just add it
    			Zend_Registry::getInstance()->logger->warn('Item ' . $item . ' with category '. $category . ' was added to dictionary');
    		
    			return Model_Dictionary::retrieve()->save(array('ITEM' => $item, 'CATEGORY' => $category));
    		} else {
    			//$this->_helper->layout->setLayout("http400clienterror");
    			//throw new  Exception('No item ' . $item . ' for category ' . $category . ' in dictionary');
    			Zend_Registry::getInstance()->logger->warn('Item ' . $item . ' with category '. $category . ' does not exist in dictionary');
    			return null;
    		}
    	}
    	
    }
    
    /**
     * Returns item from dictionary by id
     *
     * @param string 	$id		id to search in dictionary
     * @return string	item of id from dictionary
     */
    public static function getItemById($id) {
    	if ($id == null) {
    		return null;
    	}
    	
   		$item = Model_Dictionary::retrieve()->fetchEntry($id, array('ITEM'));
    	
    	if ($item) {
    		return $item['ITEM'];
    	} else {
    		return null;
    	}
    }
	
	public function getDescriptionById($id) {
		$desc = Model_Dictionary::retrieve()->fetchEntry($id, array('DESCRIPTION'));
		if ($desc) return $desc['DESCRIPTION']; else return null;
	}
    
}