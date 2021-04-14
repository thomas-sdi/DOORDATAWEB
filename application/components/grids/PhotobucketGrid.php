<?
require_once APPLICATION_PATH . '/components/CustomGrid.php';
require_once APPLICATION_PATH . '/models/Photobucket.php';

class PhotobucketGrid extends Cmp_Custom_Grid {
	
	public function __construct($id, $parent=null, $parentLink=null) {
		
		// define columns
		$columns = array(
        	'NAME'			=> array('Title'		=> 'Name',
        					   		 'Maxlength'	=> '50'),
			'URL'			=> array('Title'		=> 'Url',
									 'Visible'		=> '011', 
									 'View'			=> Data_Column_Grid::MEMO,
									 'Excel'		=> false),
			'DESCRIPTION'	=> array('Title'		=> 'Description',
									 'View'     	=> Data_Column_Grid::MEMO),
		 					   array('Field'		=> 'INSPECTION_ID',
		 					   		 'Visible' 		=> '000',
            						 'Display' 		=> 'STATUS',
            						 'Id'			=> 'INSPECTION_STATUS'), 
			'IMAGE'			=> array('Visible'		=> '000',
									 'Title'		=> 'Image',
									 'View'			=> Data_Column_Grid::IMAGE),
							   array('Title'	    => 'Actions',
							   	     'Calculated' 	=> array($this, "calculatePhotobucketActions"),
							   	     'Visible' 		=> '100',
							   		 'Width'	    => '100px'
							   )
    	);
				
		parent::__construct($id, Model_Photobucket::retrieve(), $parent, $parentLink, $columns);
	}

	public function calculatePhotobucketActions($entry){
    	$gridId = $this->getId();
    	$inspectionStatus = $entry['INSPECTION_STATUS'];
    	if ($inspectionStatus){
    		$inspectionStatus = strstr($inspectionStatus, 35); //35 is ascii for #
    		$inspectionStatus = str_replace('#', '', $inspectionStatus); 
    	}
    	else return '';
    	
    	//in case inspection is locked users should not be able to make changes in the door
    	switch($inspectionStatus){
    		case ($inspectionStatus == Model_Inspection::PENDING || $inspectionStatus == Model_Inspection::INCOMPLETED):
    			$actions = "<a href='javascript: cmp_" . $gridId . ".showDetailed()'>Edit</a> | " .
    			   		   "<a href='javascript: cmp_" . $gridId . ".deleteItems()'>Delete</a>";
    			$entry['editable'] = true;
    			break;
    		case ($inspectionStatus == Model_Inspection::SUBMITTING || 
    			  $inspectionStatus == Model_Inspection::SUBMITTED ||
    			  $inspectionStatus == Model_Inspection::COMPLETED):
    			//$actions = "Edit | " .
    			$actions = "<a href='javascript: cmp_" . $gridId . ".showDetailed()'>View</a> | " .
    					   "Delete";
    			$entry['editable'] = false;
    			break;
    	}
    	return $actions;
	}
	
	public function isReadonly($inspId = null) {
		if ($inspId) {
			$inspectionStatus = Model_Inspection::retrieve()->fetchEntry($inspId);
			$inspectionStatus = $inspectionStatus['STATUS'];
	    	return !($inspectionStatus == Model_Inspection::PENDING || 
	    			$inspectionStatus == Model_Inspection::INCOMPLETED);
		}
		else return parent::isReadonly();
	}
	
	/**
     * Returns results in html table format
     * which is recognizable by Excel 
     */
    public function fetchExcel($queryParams=null, $start=0, $sortBy=null, $sortDirection=true) {
        $entries = $this->fetchEntries($queryParams, $start, false, $sortBy, $sortDirection);
        
        /*$excel = new COM("Excel.Application") or die("Unable to instanciate excel");
        $excel->Visible = false;
        $excel->DisplayAlerts = false;
        $excel->Application->Visible = 0;
        $excel->DisplayAlerts = 0;
        $excel->Workbooks->Open(ROOT_PATH . "/public/scripts/doordata/Photobucket.xls");
        $sheet = $excel->Worksheets(1);
        $sheet->Activate;
        $cell = $sheet->Cells(1,1);
        $cell->Activate;
        $cell->Value = 'test';
        $filename = ROOT_PATH . "/public/excel/".date('YmdHis').".xls";
        $excel->Workbooks[1]->SaveAs($filename);
        $excel->Workbooks->Close();
        $excel->Quit();
        unset($sheet);
        unset($excel);
        //$sheet = NULL;
        //$excel = NULL;
        
        return $filename;*/
        
        // get building name
        $inspectionId = $queryParams['_parent'];
        $building = Model_Inspection::retrieve()->fetchEntry($inspectionId, array(
        	'NAME' => new Data_Column('BUILDING_ID', null, Model_Inspection::retrieve(), 'NAME')
        ));
        
	
        $excel_table = '<table width="780">';
       	// add data rows, but no more than a defined maximum
       	$max_records = (int)Zend_Registry::getInstance()->configuration->excel->max_records;
       	if ($max_records < 10) $max_records = 10; // min allowed value
       	if ($max_records > 10000) $max_records = 10000; // max allowed value
       	$total = min($entries->getTotalItemCount(), $max_records); 
       	$perPage = 2;
       	$counter = 1;
       	$max_page = (int)($total / $perPage) + (fmod($total, $perPage) == 0 ? 0 : 1);
       	for ($page = 1; $page <= $max_page; $page++) {
       		$entries->setCurrentPageNumber($page);
       		
       		$excel_table .= '<tr><td height="50" style="vertical-align: top" colspan=3 align="center">' . $building['NAME'] . ' ' .date('m-d-Y').'</td></tr>';
       		for ($i = 0; $i < 1; $i++) {
       			if ($counter + 1 <= $total) {
       				$entry1 = $entries->getItem($counter++);
       				$entry2 = $entries->getItem($counter++);
       				
       				$excel_table .= '<tr><td>Door '.$entry1['column_0'].'</td><td width="20">&nbsp;</td><td width="435">Door '.$entry2['column_0'].'</td></tr>';
       				$excel_table .= '<tr><td height="40">Notes: '.$entry1['column_2'].'</td><td>&nbsp;</td><td>Notes: '.$entry2['column_2'].'</td></tr>';
       				$excel_table .= '<tr>'.$this->getTDWithImage($entry1['column_4']);
                    $excel_table .= '<td>&nbsp;</td>'.$this->getTDWithImage($entry2['column_4']).'</tr>';
       			}
       			elseif ($counter == $total) {
       				$entry = $entries->getItem($counter++);
       				
       				$excel_table .= '<tr><td>Door '.$entry['column_0'].'</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
       				$excel_table .= '<tr><td height="40">Notes: '.$entry['column_2'].'</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
       				$excel_table .= '<tr>'.$this->getTDWithImage($entry['column_4']).'<td>&nbsp;</td><td>&nbsp;</td></tr>';
       			}
       		}
       		$excel_table .= '<tr><td height="40" colspan=3 style="text-align: center">'.$page.' of '.$max_page.'</td></tr>';
       		$excel_table .= '<tr><td colspan=3>&nbsp;</td></tr>';
       	}

		$excel_table .= '</table>';
       
        return $excel_table;
    }
    
    public function getTDWithImage($image) {
    	if ($image == '/') $image = ROOT_PATH . $image;
        $height = 380;	$width = 380;
       	/*$size = getimagesize($image);
       	if (is_array($size)) {
       		$width = $size[0];
       		$height = $size[1];
        }*/
        
        //$width1 = round($width / max($width, $height) * 435);
        //$height1 = round($height / max($width, $height) * 435);
        
        $image = '<img height=' . $height . ' width=' . $width .' src="' . $image . '"><br>';
    	//return '<td height=' . $height1 . ' width=' . $width1 .' style="text-align: center">' . $image . '</td>';
    	return '<td width="380" height="450">' . $image . '</td>';
    }
    
}