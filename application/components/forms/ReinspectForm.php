<?
require_once APPLICATION_PATH . '/components/Form.php';

class ReinspectForm extends Ginger_Form {

	public function __construct($id) {

		parent::__construct($id);

		$this->setElements(array(
			'id'   => array('InputType' => 'hidden'),
			'option' => array('View' => Data_Column_Grid::RADIO, 'RadioOptions' => array(
				'1' => 'Everything',
				'2' => 'Non-compliant only',
				'3' => 'Non-compliant and non-inspected'), 'Default' => '1')
		));

		$this->setParams(array(
			'Action' => '/inspection/submit',
			'IsCustomView' => true
		));
	}

	public function execute($data) {
		
		$existingInspectionId = $data['id'];
			
		// create a new inspection record
		$newInspectionId = Model_Inspection::retrieve()->saveAsNew($existingInspectionId);
		
		// get the copy mode
		$copyMode = $data['option'];
		
		// if the mode is "everything", clear the checklist
		$clearChecklist = $data['option'] == 1;
		
		// get all the doors from the existing inspection
		$doors = Model_Door::retrieve()->fetchEntries(array(new Data_Column('INSPECTION_ID', $existingInspectionId), 'ID', 'COMPLIANT'), null, true);
		foreach ($doors as $door) {
			// if non-compliant doors only
			if ($copyMode == 2 && $door['COMPLIANT'] != 136)
				continue;
			
			// if non-compliant and non-inspected
			if ($copyMode == 3 && $door['COMPLIANT'] == 135)
				continue;
				
			// copy the door
			Model_Door::retrieve()->copyToInspection($door['ID'], $newInspectionId, $clearChecklist);
		}
	}
}
