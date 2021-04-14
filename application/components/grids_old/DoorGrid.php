<?

require_once APPLICATION_PATH . '/components/CustomGrid.php';
require_once APPLICATION_PATH . '/models/Door.php';
require_once APPLICATION_PATH . '/models/Inspectionother.php';
require_once APPLICATION_PATH . '/models/Inspection.php';
require_once APPLICATION_PATH . '/models/Hardware.php';

class DoorGrid extends Cmp_Custom_Grid {

	public $visibilityFloorPlan = false;
	public $inspectionChecklistReadonly = false;

	public function __construct($id, $parent = null, $parentLink = null, $inspectionId = -1) {
		$this->_numbered = true;
		
		$this->_usePaginator = true;

		// define columns
		$columns = array(
			'INSPECTION_ID' => array(
				'Visible' => '000',
				//'Display' => 'STATUS',
				//'Title'		=> 'Inspection Status',
				'Id' => 'INSPECTION_ID',
				'Filter' => $inspectionId
			),
			array(
				'Field' => 'INSPECTION_ID',
				'Visible' => '000',
				'Display' => 'STATUS',
				'Title'		=> 'Inspection Status',
				'Id' => 'INSPECTION_STATUS'
			),
			'NUMBER' => array(
				'Id' => 'NUMBER',
				'Maxlength' => '15',
				'Title' => 'Door Number',
				'NaturalSort' => true,
				'Width' => '90px'),
			'COMPLIANT' => array(
				'Id' => 'COMPLIANT',
				'Id' => 'compliant',
				'View' => Data_Column_Grid::RADIO, 
				'DropdownFilter' => new Data_Column('category', 'Logical', Model_Dictionary::retrieve()),
				'Visible' => '011',
				'Width' => '55'),
			array('Title' => 'Compliant',
				'Calculated' => array($this, "calculateCompliant"),
				'Visible' => '100',
				'Width' => '65px'),
			array('Title' => 'Door Type',
				'Calculated' => array($this, "calculateDoorType"),
				'Visible' => '100', 
				'Width' => '100%'),
			array('Title' => 'Fire Rating',
				'Calculated' => array($this, "calculateFireRating"),
				'Visible' => '130',
				'Width' => '250px'),
			'LOCATION' => array(
				'Id'		=> 'LOCATION',
				'Maxlength' => '42',
				'Title' 	=> 'Door Location',
				'Width' 	=> '288px'),
			'DOOR_BARCODE' => array(
				'Id'		=> 'DOOR_BARCODE',
				'Hidden' 	=> true, 
				'Visible' 	=> '000', 
				'Maxlength' => '50'),
			'STYLE' => array(
				'Id'				=> 'STYLE',
				'Hidden' 			=> true,
				'View' 				=> Data_Column_Grid::RADIO,
				'DropdownFilter' 	=> new Data_Column('category', 'Door Style', Model_Dictionary::retrieve()),
				'Default' 			=> Model_Door::DEFAULT_DOOR_STYLE,
				'Visible' 			=> '000'
			),
			'TYPE_OTHER' => array(
				'Id'		=> 'TYPE_OTHER',
				'Hidden' 	=> true, 
				'Visible' 	=> '000', 
				'Maxlength' => '40'),
			'MATERIAL' => array(
				'Id'				=> 'MATERIAL',
				'Hidden' 			=> true,
				'View' 				=> Data_Column_Grid::RADIO,
				'DropdownFilter'	=> new Data_Column('category', 'Door Material', Model_Dictionary::retrieve()),
				'Default'			=> Model_Door::DEFAULT_DOOR_MATERIAL,
				'Visible' 			=> '000'
			),
			'MATERIAL_OTHER' => array(
				'Id'				=> 'MATERIAL_OTHER',
				'Hidden' 			=> true, 
				'Visible' 			=> '000', 
				'Maxlength' 		=> '40'),
			'ELEVATION' => array(
				'Id'				=> 'ELEVATION',
				'Hidden' 			=> true,
				'View' 				=> Data_Column_Grid::RADIO,
				'DropdownFilter' 	=> new Data_Column('category', 'Door Elevation', Model_Dictionary::retrieve()),
				'Default' 			=> Model_Door::DEFAULT_DOOR_ELEVATION,
				'Visible' 			=> '000'
			),
			'ELEVATION_OTHER' => array(
				'Id'				=> 'ELEVATION_OTHER',
				'Hidden' 			=> true, 
				'Visible' 			=> '000', 
				'Maxlength' 		=> '40'),
			'FRAME_MATERIAL' => array(
				'Id'				=> 'FRAME_MATERIAL',
				'Hidden' 			=> true,
				'View' 				=> Data_Column_Grid::RADIO,
				'DropdownFilter' 	=> new Data_Column('category', 'Frame Material', Model_Dictionary::retrieve()),
				'Default' 			=> Model_Door::DEFAULT_FRAME_MATERIAL,
				'Visible' 			=> '000'
			),
			'FRAME_MATERIAL_OTHER' => array(
				'Id'				=> 'FRAME_MATERIAL_OTHER',
				'Visible' 			=> '000', 
				'Maxlength' 		=> '40'),
			'FRAME_ELEVATION' => array(
				'Id'				=> 'FRAME_ELEVATION',
				'Hidden' 			=> true,
				'View' 				=> Data_Column_Grid::RADIO,
				'DropdownFilter' 	=> new Data_Column('category', 'Frame Elevation', Model_Dictionary::retrieve()),
				'Default' 			=> Model_Door::DEFAULT_FRAME_ELEVATION,
				'Visible' 			=> '000'
			),
			'FRAME_ELEVATION_OTHER' => array(
				'Id'				=> 'FRAME_ELEVATION_OTHER',
				'Visible' 			=> '000', 
				'Maxlength' 		=> '40'),
			'FIRE_RATING_1' => array(
				'Id' => 'FIRE_RATING_1',
				'Hidden' => true, 
				'Visible' => '000',
				'View' => Data_Column_Grid::RADIO,
				'DropdownFilter' => new Data_Column('category', 'Fire-Rating 1', Model_Dictionary::retrieve()),
				'Title' => 'Fire Rating'
				),
			'FIRE_RATING_2' => array(
				'Id'				=> 'FIRE_RATING_2',
				'Hidden' 			=> true, 
				'Visible' 			=> '000',
				'View' 				=> Data_Column_Grid::RADIO,
				'DropdownFilter' 	=> new Data_Column('category', 'Fire-Rating 2', Model_Dictionary::retrieve()),
				'Title' 			=> ""
				),
			'FIRE_RATING_3' => array(
				'Id'				=> 'FIRE_RATING_3',
				'Hidden' 			=> true, 
				'Visible' 			=> '000', // 20
				'View' 				=> Data_Column_Grid::RADIO,
				'DropdownFilter' 	=> new Data_Column('category', 'Fire-Rating 3', Model_Dictionary::retrieve()),
				'Title' 			=> 'Positive Pressure'),
			'FIRE_RATING_4' => array(
				'Id'				=> 'FIRE_RATING_4',
				'Hidden' 			=> true, 
				'Visible' 			=> '000', // 21
				'View' 				=> Data_Column_Grid::RADIO,
				'DropdownFilter' 	=> new Data_Column('category', 'Fire-Rating 4', Model_Dictionary::retrieve()),
				'Title' 			=> ''),
			'TEMP_RISE' => array(
				'Id'				=> 'TEMP_RISE',
				'Hidden' 			=> true, // 22
				'View' 				=> Data_Column_Grid::RADIO,
				'DropdownFilter' 	=> new Data_Column('category', 'Door Temperature Rise', Model_Dictionary::retrieve()),
				'Visible' 			=> '010',
				'Title' 			=> 'Temp. Rise'),
			'MANUFACTURER' => array(
				'Id'				=> 'MANUFACTURER',
				'Maxlength' 		=> '15', 
				'Visible' 			=> '000'
			), // 23
			'BARCODE' => array(
				'Id'				=> 'BARCODE',
				'Visible' 			=> '000', 
				'Maxlength' 		=> '50'
			), // 24
			'REMARKS' => array(
				'Id'				=> 'REMARKS',
				'Hidden' 			=> true, // 25
				'Maxlength' 		=> '42',
				'Visible' 			=> '000'),
			'MODEL' => array(
				'Id'				=> 'MODEL',
				'Maxlength' 		=> '12', 
				'Visible' 			=> '000'
			), // 26
			'FRAME_MANUFACTURER' => array('Visible' => '000'), // 27
			'LISTING_AGENCY' => array(
				'Id'				=> 'LISTING_AGENCY',
				'Hidden' 			=> true, // 28
				'View' 				=> Data_Column_Grid::RADIO,
				'DropdownFilter' 	=> new Data_Column('category', 'Door Listing Agency', Model_Dictionary::retrieve()),
				'Visible' 			=> '010',
				'Title' 			=> 'Listing Agency'),
			'LISTING_AGENCY_OTHER' => array(
				'Id'				=> 'LISTING_AGENCY_OTHER',
				'Visible' 			=> '000', 
				'Maxlength' 		=> '40'), // 29
			'GAUGE' => array(
				'Id'				=> 'GAUGE',
				'Maxlength' 		=> '2', 
				'Visible' 		=> '000'
			),
			'HANDING' => array(
				'Id'				=> 'HANDING',
				'DropdownCaption' 	=> 'Select Hand',
				'DropdownFilter' 	=> new Data_Column('category', 'Handing', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'),
			//'Default' => '1384'),
			'HINGE_HEIGHT' => array(
				'Id'				=> 'HINGE_HEIGHT',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Hinge Height', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'),
			'HINGE_THICKNESS' => array(
				'Id'				=> 'HINGE_THICKNESS',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Hinge Thickness', Model_Dictionary::retrieve()),
				'Visible' => '000'),
			'HINGE_HEIGHT1' => array(
				'Id'			=> 'HINGE_HEIGHT1',
				'Maxlength' 	=> '1', 
				'Visible' 		=> '000'
			),
			'HINGE_HEIGHT2' => array(
				'Id'		=>'HINGE_HEIGHT2',
				'Maxlength' => '2', 
				'Visible' 	=> '000'
			),
			'HINGE_HEIGHT3' => array(
				'Id'		=> 'HINGE_HEIGHT3',
				'Maxlength' => '2', 
				'Visible' 	=> '000'
			),
			'HINGE_HEIGHT4' => array(
				'Id'		=> 'HINGE_HEIGHT4',
				'Maxlength' => '3', 
				'Visible' 	=> '000'
			),
			'HINGE_FRACTION1' => array(
				'Id'				=> 'HINGE_FRACTION1',
				'DropdownFilter' => new Data_Column('category', 'Hinge Fraction1', Model_Dictionary::retrieve()),
				'Visible' => '000'
			),
			'HINGE_FRACTION2' => array(
				'Id'				=> 'HINGE_FRACTION2',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Hinge Fraction2', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'HINGE_FRACTION3' => array(
				'Id'				=> 'HINGE_FRACTION3',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Hinge Fraction3', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'),
			'HINGE_FRACTION4' => array(
				'Id'				=> 'HINGE_FRACTION4',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Hinge Fraction4', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'HINGE_BACKSET' => array(
				'Id'				=> 'HINGE_BACKSET',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Hinge Backset', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'HINGE_MANUFACTURER' => array(
				'Id'		=> 'HINGE_MANUFACTURER',
				'Maxlength' => '10', 
				'Visible' 	=> '000'
			),
			'HINGE_MANUFACTURER_NO' => array(
				'Id'		=> 'HINGE_MANUFACTURER_NO',
				'Maxlength' => '8', 
				'Visible' 	=> '000'
			),
			'TOP_TO_CENTERLINE' => array(
				'Id'		=> 'TOP_TO_CENTERLINE',
				'Maxlength' => '2', 
				'Visible' 	=> '000'
			),
			'TOP_TO_CENTERLINE_FRACTION' => array(
				'Id'				=> 'TOP_TO_CENTERLINE_FRACTION',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Top To Centerline Fraction', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'LOCK_BACKSET' => array(
				'Id'				=> 'LOCK_BACKSET',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Lock Backset', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'),
			'FRAME_BOTTOM_TO_CENTER' => array(
				'Id'		=> 'FRAME_BOTTOM_TO_CENTER',
				'Maxlength' => '2', 
				'Visible' 	=> '000'
			),
			'STRIKE_HEIGHT' => array(
				'Id'				=> 'STRIKE_HEIGHT',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Strike Height', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'PREFIT_DOOR_SIZE_X' => array(
				'Id'		=> 'PREFIT_DOOR_SIZE_X',
				'Maxlength' => '2', 
				'Visible' 	=> '000'
			),
			'PREFIT_FRACTION_X' => array(
				'Id'				=> 'PREFIT_FRACTION_X',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Prefit Fraction X', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'PREFIT_DOOR_SIZE_Y' => array(
				'Id'				=> 'PREFIT_DOOR_SIZE_Y',
				'Maxlength' 		=> '3', 
				'Visible' 			=> '000'
			),
			'PREFIT_FRACTION_Y' => array(
				'Id'				=> 'PREFIT_FRACTION_Y',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Prefit Fraction Y', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'FRAME_OPENING_SIZE_X' => array(
				'Id'		=> 'FRAME_OPENING_SIZE_X',
				'Maxlength' => '2', 
				'Visible' 	=> '000'
			),
			'FRAME_OPENING_FRACTION_X' => array(
				'Id'				=> 'FRAME_OPENING_FRACTION_X',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Frame Opening Fraction X', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'FRAME_OPENING_SIZE_Y' => array(
				'Id'				=> 'FRAME_OPENING_SIZE_Y',
				'Maxlength' 		=> '3', 
				'Visible' 			=> '000'
			),
			'FRAME_OPENING_FRACTION_Y' => array(
				'Id'				=> 'FRAME_OPENING_FRACTION_Y',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Frame Opening Fraction Y', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'LITE_CUTOUT_SIZE_X' => array(
				'Id'				=> 'LITE_CUTOUT_SIZE_X',
				'Maxlength' 		=> '2', 
				'Visible' 			=> '000'
			),
			'LITE_CUTOUT_FRACTION_X' => array(
				'Id'				=> 'LITE_CUTOUT_FRACTION_X',
				'Hidden' 			=> true,
				'DropdownFilter' => new Data_Column('category', 'Lite Cutout Fraction X', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'LITE_CUTOUT_SIZE_Y' => array(
				'Id'				=> 'LITE_CUTOUT_SIZE_Y',
				'Maxlength' 		=> '3', 
				'Visible' => '000'
			),
			'LITE_CUTOUT_FRACTION_Y' => array(
				'Id'				=> 'LITE_CUTOUT_FRACTION_Y',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Lite Cutout Fraction Y', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'LOCKSTILE_SIZE' => array(
				'Id'				=> 'LOCKSTILE_SIZE',
				'Maxlength' 		=> '2', 
				'Visible' 			=> '000'
			),
			'LOCKSTILE_FRACTION' => array(
				'Id'				=> 'LOCKSTILE_FRACTION',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Lockstile Fraction', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'TOPRAIL_SIZE' => array(
				'Id'				=> 'TOPRAIL_SIZE',
				'Maxlength' 		=> '2', 
				'Visible' 			=> '000'
			),
			'TOPRAIL_FRACTION' => array(
				'Id'				=> 'TOPRAIL_FRACTION',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Top Rail Fraction', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'A' => array(
				'Id'				=> 'A',
				'Maxlength' 		=> '3', 
				'Visible' 			=> '000'
			),
			'A_FRACTION' => array(
				'Id'				=> 'A_FRACTION',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Frame Fraction', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'B' => array(
				'Id'				=> 'B',
				'Maxlength' 		=> '3', 
				'Visible' 			=> '000'
			),
			'B_FRACTION' => array(
				'Id'				=> 'B_FRACTION',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Frame Fraction', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'C' => array(
				'Id'				=> 'C',
				'Maxlength' 		=> '3', 
				'Visible' 			=> '000'
			),
			'C_FRACTION' => array(
				'Id'				=> 'C_FRACTION',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Frame Fraction', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'D' => array(
				'Id'				=> 'D',
				'Maxlength' 		=> '3', 
				'Visible' 			=> '000'
			),
			'D_FRACTION' => array(
				'Id'				=> 'D_FRACTION',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Frame Fraction', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'E' => array(
				'Id'				=> 'E',
				'Maxlength' 		=> '3', 
				'Visible' 			=> '000'
			),
			'E_FRACTION' => array(
				'Id'				=> 'E_FRACTION',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Frame Fraction', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'F' => array(
				'Id'				=> 'F',
				'Maxlength' 		=> '3', 
				'Visible' 			=> '000'
			),
			'F_FRACTION' => array(
				'Id'				=> 'F_FRACTION',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Frame Fraction', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'G' => array(
				'Id'				=> 'G',
				'Maxlength' 		=> '3', 
				'Visible' 			=> '000'
			),
			'G_FRACTION' => array(
				'Id'				=> 'G_FRACTION',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Frame Fraction', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'H' => array(
				'Id'				=> 'H',
				'Maxlength' 		=> '3', 
				'Visible' 			=> '000'
			),
			'H_FRACTION' => array(
				'Id'				=> 'H_FRACTION',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Frame Fraction', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'I' => array(
				'Id'				=> 'I',
				'Maxlength' 		=> '3', 
				'Visible' 			=> '000'
			),
			'I_FRACTION' => array(
				'Id'				=> 'I_FRACTION',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Frame Fraction', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'J' => array(
				'Id'				=> 'J',
				'Maxlength' 		=> '3', 
				'Visible' 			=> '000'
			),
			'J_FRACTION' => array(
				'Id'				=> 'J_FRACTION',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Frame Fraction', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'K' => array(
				'Id'				=> 'K',
				'Maxlength' 		=> '3', 
				'Visible' 			=> '000'
			),
			'K_FRACTION' => array(
				'Id'				=> 'K_FRACTION',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Frame Fraction', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'),
			'L' => array(
				'Id'				=> 'L',
				'Maxlength' 		=> '3', 
				'Visible' 			=> '000'
			),
			'L_FRACTION' => array(
				'Id'				=> 'L_FRACTION',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Frame Fraction', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'M' => array(
				'Id'				=> 'M',
				'Maxlength' 		=> '3', 
				'Visible' 			=> '000'
			),
			'M_FRACTION' => array(
				'Id'				=> 'M_FRACTION',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Frame Fraction', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'N' => array(
				'Id'				=> 'N',
				'Maxlength' 		=> '3', 
				'Visible' 			=> '000'
			),
			'N_FRACTION' => array(
				'Id'				=> 'N_FRACTION',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Frame Fraction', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'O' => array(
				'Id'				=> 'O',
				'Maxlength' 		=> '3', 
				'Visible' 			=> '000'
			),
			'O_FRACTION' => array(
				'Id'				=> 'O_FRACTION',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Frame Fraction', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'P' => array(
				'Id'				=> 'P',
				'Maxlength' 		=> '3', 
				'Visible' 			=> '000'
			),
			'P_FRACTION' => array(
				'Id'				=> 'P_FRACTION',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Frame Fraction', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'Q' => array(
				'Id'				=> 'Q',
				'Maxlength' 		=> '3', 
				'Visible' 			=> '000'
			),
			'Q_FRACTION' => array(
				'Id'				=> 'Q_FRACTION',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Frame Fraction', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'R' => array(
				'Id'				=> 'R',
				'Maxlength' 		=> '3', 
				'Visible' 			=> '000'
			),
			'R_FRACTION' => array(
				'Id'				=> 'R_FRACTION',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Frame Fraction', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'S' => array(
				'Id'				=> 'S',
				'Maxlength' 		=> '3', 
				'Visible' 			=> '000'
			),
			'S_FRACTION' => array(
				'Id'				=> 'S_FRACTION',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Frame Fraction', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'T' => array(
				'Id'				=> 'T',
				'Maxlength' 		=> '3', 
				'Visible' 			=> '000'
			),
			'T_FRACTION' => array(
				'Id'				=> 'T_FRACTION',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Frame Fraction', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'U' => array(
				'Id'				=> 'U',
				'Maxlength' 		=> '3', 
				'Visible' 			=> '000'
			),
			'U_FRACTION' => array(
				'Id'				=> 'U_FRACTION',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Frame Fraction', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'V' => array(
				'Id'				=> 'V',
				'Maxlength' 		=> '3', 
				'Visible' 			=> '000'
			),
			'V_FRACTION' => array(
				'Id'				=> 'V_FRACTION',
				'Hidden' 			=> true,
				'DropdownFilter' 	=> new Data_Column('category', 'Frame Fraction', Model_Dictionary::retrieve()),
				'Visible' 			=> '000'
			),
			'HARDWARE_GROUP' => array(
				'Id'				=> 'HARDWARE_GROUP',
				'Maxlength' 		=> 28/* '12' */, 
				'Visible' 			=> '001', 
				'Visible' 			=> '000', 
				'View' 				=> Data_Column_Grid::SIMPLETEXT),
			'HARDWARE_SET' => array(
				'Id'				=> 'HARDWARE_SET',
				'Maxlength' 		=> 42/* '8' */, 
				'Visible' 			=> '000', 
				'View' 				=> Data_Column_Grid::SIMPLETEXT
			),
			array('Title' => 'Actions', 
				'Calculated' => array($this, "calculateDoorActions"), 
				'Visible' => '100', 
				'Width' => '110px'),
			'ID' => array('Id' => 'ID', 'Visible' => '000')
		);

		parent::__construct($id, Model_Door::retrieve(), $parent, $parentLink, $columns);
		$this->setDefaultSorting('NUMBER', true);
	}

	public function calculateCompliant($entry) {
		$comp = $entry['compliant'];
		
		if ($comp) {
    		if ($comp == 'No') {
    			$comp = str_repeat("&nbsp", 12) . " " . $comp;
    		}
    	} 
		
		
		return $comp;
	}

	public function calculateDoorType($entry) {
		$field = "";
		//let's get door ID
		$id = $entry['ID'];
		if ($id) {
			//based on the door id let's retrieve from the database door type
			$doorType = Model_DoorType::retrieve()->fetchEntries(
					array(new Data_Column('DOOR_ID', $id),
				'TYPE_ID' => new Data_Column('TYPE_ID', null, Model_DoorType::retrieve(), 'ITEM')
					), null, true//, 'VALUE_ORDER'
			);

			foreach ($doorType as $type) {
				$t = $type['TYPE_ID'];
				if (trim($t) == "Other") {
					//add explanations for the other value
					$t_other = current($this->getColumnsByField('TYPE_OTHER'));
					$t_other = $entry[$t_other->getId()];
					//if($t_other) $t .= ' (' . $t_other . ')';
					if (strlen($t_other) > 0)
						$t = $t_other;
				}
				if ($field == "")
					$field = $t;
				else
					$field = $field . ", " . $t;
			}
		}
		return $field;
	}

	public function calculateFireRating($entry) {


		$fr1 = $entry['FIRE_RATING_1']  ? $entry['FIRE_RATING_1'] . ', ' : '';
		$fr2 = $entry['FIRE_RATING_2']  ? $entry['FIRE_RATING_2'] . ', ' : '';
		$fr3 = $entry['FIRE_RATING_3']  ? $entry['FIRE_RATING_3'] . ', ' : '';
		$fr4 = $entry['FIRE_RATING_4']  ? $entry['FIRE_RATING_4'] . ', ' : '';

		$field = substr($fr1 . $fr2 . $fr3 . $fr4, 0, -2); //remove the last two characters


		$la = $entry['LISTING_AGENCY'];
		if (trim($la) == "Other") { // add other value if available
			$la_other = $entry['LISTING_AGENCY_OTHER'];
			if (strlen($la_other) > 0)
				$la = $la_other;
		}
		if ($field != "" && $la != "")
			$field .= ", " . $la;

		$tr = $entry['TEMP_RISE'];
		if ($field != "" && $tr != "")
			$field .= ", " . $tr;

		return $field;
	}

	public function calculateDoorActions($entry) {
		$gridId = $this->getID();
		
		$inspectionStatus = $entry['INSPECTION_STATUS'];
		
		
		if (!$inspectionStatus) return '';

		//in case inspection is locked users should not be able to make changes in the door
		//$edit = "<a href='javascript: gridDialogDoor.showEdit(\"ordinary\")'>Edit</a>";
		$edit = '<a data-original-title="Edit" title="Edit" data-placement="top" class="btn btn-sm hover-blue-alt tooltip-button" 
						href="javascript: cmp_' . $gridId . '.showDoorEditDialog('. $entry['ID'] .', \'ordinary\');"><i class="fa fa-edit">edit3</i>edit3</a>';
		
		$editDisabled 	= '<a data-original-title="Edit" title="Edit" data-placement="top" class="btn btn-sm tooltip-button" style="color: gray;"
						href="#"><i class="glyph-icon icon-view"></i></a>';	
		
		//$inspect = "<a href='javascript: gridDialogDoor.showEdit(\"checklist\")'>Inspect</a>";
		$inspect = '<a data-original-title="Inspect" title="Inspect" data-placement="top" class="btn btn-sm hover-blue-alt tooltip-button" 
						href="javascript: cmp_' . $gridId . '.showDoorEditDialog('. $entry['ID'] .', \'checklist\');"><i class="glyph-icon icon-check"></i></a>';
		
		
		$inspectDisabled = '<a data-original-title="Inspect" title="Inspect" data-placement="top" class="btn btn-sm tooltip-button" style="color: gray;"
						href="#"><i class="glyph-icon icon-check"></i></a>';	
		
		$delete = '<a data-original-title="Delete" title="Delete" data-placement="top" class="btn btn-sm hover-red tooltip-button" 
						href="javascript: cmp_' . $gridId . '.deleteItem('. $entry['ID'] .')"><i class="glyph-icon icon-remove"></i></a>';
		$deleteDisabled = '<a data-original-title="Delete" title="Delete" data-placement="top" class="btn btn-sm tooltip-button" style="color: gray;"
						href="#"><i class="glyph-icon icon-remove"></i></a>';
						
		$view = '<a data-original-title="View" title="View" data-placement="top" class="btn btn-sm hover-blue-alt tooltip-button" 
						href="javascript: cmp_' . $gridId . '.showEditDialog('. $entry['ID'] .', \'ordinary\');"><i class="glyph-icon icon-eye"></i></a>';
		
		$actions = '';
		if ($inspectionStatus == Model_Inspection::PENDING || $inspectionStatus == Model_Inspection::INCOMPLETED){
			$actions = $edit . $inspect. $delete;
		}
		if ($inspectionStatus == Model_Inspection::SUBMITTING || $inspectionStatus == Model_Inspection::SUBMITTED || $inspectionStatus == Model_Inspection::COMPLETED){
			$actions = $view;
		}
		return $actions;
	}

	public function getHardwareGrid($id) {
		/* $columns = array(
		  'QTY'    => array('Maxlength'=>3),
		  'ITEM'	 => array('Maxlength'=>12),
		  'PRODUCT'=> array('Maxlength'=>16),
		  'MFG'    => array('Maxlength'=>3),
		  'FINISH' => array('Maxlength'=>3)
		  ); */
		$columns = array(
			'ITEM_ID' => array('Maxlength' => 42, 'Width' => 280, 'Visible' => '000'),
			'QTY' => array('Maxlength' => 10, 'Width' => 70),
			'ITEM' => array('Maxlength' => 42, 'Width' => 280),
			'PRODUCT' => array('Maxlength' => 63, 'Width' => '100%'),
			'MFG' => array('Maxlength' => 10, 'Width' => 70),
			'FINISH' => array('Maxlength' => 10, 'Width' => 70)
		);
		return new Cmp_Custom_Grid($id, Model_Hardware::retrieve(), $this, 'DOOR_ID', $columns, 'VERIFY', array('MaxRows' => '15'));
	}

	public function setSecurityOptions($inspectionId) {
		$acl = Zend_Registry::get('_acl');
		$user = Zend_Auth::getInstance()->getIdentity();

		if ($acl->inheritsRole($user, 'Building Owner Employees'))
			$this->visibilityFloorPlan = false;
		else
			$this->visibilityFloorPlan = false; // asked to be hidden for everyone now

		$inspection = Model_Inspection::retrieve()->fetchEntry($inspectionId, array('STATUS'));

		if ($acl->inheritsRole($user, 'Building Owner Employees')) {
			$this->inspectionChecklistReadonly = true;
			if ($inspection['STATUS'] != Model_Inspection::PENDING) {
				$this->setReadonly();
			}
		} elseif ($acl->inheritsRole($user, 'Web Users')) {
			$this->inspectionChecklistReadonly = true;
		}
		else
			$this->inspectionChecklistReadonly = false;

		if ($inspection['STATUS'] == Model_Inspection::SUBMITTING ||
				$inspection['STATUS'] == Model_Inspection::SUBMITTED ||
				$inspection['STATUS'] == Model_Inspection::COMPLETED) {
			$this->setReadonly();
			$this->inspectionChecklistReadonly = true;
		}
	}
	
	/* override the function to add the door checklist details */
	public function fetchExcel($queryParams=null, $start=0, $sortBy=null, $sortDirection=true ) {
		$entries = $this->fetchEntries($queryParams, $start, false, $sortBy, $sortDirection);
		
		// put column headers
		$excel_table = '<table><tr>';
		foreach ($entries as $entry) {
			
			// retrieve the custom codes
			$otherCodes = Model_Door::retrieve()->getOthers(null, $entry['_parent_ID']);
			
			// pull all the door codes within this inspection
			$allDoorCodes = array();
			foreach (Model_DoorCode::retrieve()->fetchEntries(array(
					'DOOR_ID',
					new Data_Column('DOOR_ID', $entry['_parent_ID'], Model_DoorCode::retrieve(), 'INSPECTION_ID'),
					'code' => new Data_Column('CODE_ID', null, Model_DoorCode::retrieve(), 'DESCRIPTION')),
					null, true) as $code) {
				
				// retrieve custom code values
				$codeValue = $code['code'] == 'Other' ? $otherCodes[$code['code_ID']] : $code['code'];
						
				if (array_key_exists($code['DOOR_ID'], $allDoorCodes)) {
					$allDoorCodes[$code['DOOR_ID']] .= ', ' . $codeValue;
				}
				else {
					$allDoorCodes[$code['DOOR_ID']] = $codeValue;
				}
			}
			
			foreach ($entry as $columnId => $value) {
				// eliminate _parent columns
				if ($columnId == '_parent' || $columnId == '_parent_ID')
					continue;
	
				// get grid column object
				$column = $this->getColumnById($columnId);
				if (!$column ) continue;
	
				//check excel property
				if (!$column->getExcel())
					continue;
	
				// eliminate ID columns
				if ($column->getField() == 'ID')
					continue;
				elseif ($column->getLinkPath())
				unset ($entry[$columnId . '_ID']);
	
				// add column header
				$excel_table .= "<th>" . $column->getTitle() . "</th>";
			}
			// add a special column for the door codes
			$excel_table .= "<th>Door Codes</th>";
			break;
		}
		$excel_table .= "</tr>";
		 
		// add data rows, but no more than a defined maximum
		$max_records = (int)Zend_Registry::getInstance()->configuration->excel->max_records;
		if ($max_records < 10) $max_records = 10; // min allowed value
		if ($max_records > 10000) $max_records = 10000; // max allowed value
		$total = min($entries->getTotalItemCount(), $max_records); $perPage = $entries->getItemCountPerPage();
		$max_page = (int)($total / $perPage) + (fmod($total, $perPage) == 0 ? 0 : 1);
		for ($page = 1; $page <= $max_page; $page++) {
			$entries->setCurrentPageNumber($page);
			foreach ($entries as $entry) {
				$excel_table .= '<tr>';
				foreach ($entry as $columnId => $value) {
					// show the door exception codes
					if ($columnId == 'ID') {
						
					}
					
					// eliminate _parent columns
					if ($columnId == '_parent' || $columnId == '_parent_ID')
						continue;
	
					// get grid column object
					$column = $this->getColumnById($columnId);
					if (!$column ) continue;
	
					//check excel property
					if (!$column->getExcel())
						continue;
	
					// eliminate ID columns
					if ($column->getField() == 'ID')
						continue;
					elseif ($column->getLinkPath())
					unset ($entry[$columnId . '_ID']);
	
					//images
					if ($column->getView() == Data_Column_Grid::IMAGE && strlen($value) > 0) {
						 
						//if this is relative path
						if ($value[0] == '/') $value = ROOT_PATH . $value;
						 
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
				// add the door codes
				$excel_table .= '<td>' . $allDoorCodes[$entry['ID']] . '</td>';
				$excel_table .= '</tr>';
			}
		}
		$excel_table .= '</table>';
		 
		return $excel_table;
	}

}
