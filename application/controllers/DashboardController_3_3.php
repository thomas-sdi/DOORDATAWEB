<?
require_once APPLICATION_PATH . '/controllers/Component.php';

class DashboardController extends Controller_Component {

	public function init() {
		parent::init();
	}
	
	public function indexAction() {
		$this->_helper->layout->setLayout('html');

		$this->view->pfilter = $this->getFilterList(App::companyId());

	}


	public function top20noncompliantAction(){
		$this->_helper->layout->setLayout('html');
		
		$companyId = App::companyId();
		$this->view->top20noncompliant = $this->top20noncompliant($companyId);
		$this->view->levelLabel = $this->getCompanyName($companyId);

	}
	
	public function top20othersAction(){
		$this->_helper->layout->setLayout('html');
		
		$companyId = App::companyId();

		// $this->view->top20others = $companyId;
		$this->view->top20others = $this->top20others($companyId);


	}
	
	public function reinspectionsAction(){
		$this->_helper->layout->setLayout('html');
		
		$companyId = App::companyId();
		$this->view->reinspections = $this->reinspections($companyId);
	}
	
	public function percentageAction(){
		$this->_helper->layout->setLayout('html');
		
		$companyId = App::companyId();
		$level = $this->_getParam('level');
		$selectedBuildingOwner = $this->_getParam('owner');
		$selectedInspection = $this->_getParam('inspection');
		$selectedIbuilding = $this->_getParam('building');
		$selectedInspector = $this->_getParam('inspector');

		$levelLabel = '';
		
		$params = array('companyId' => $companyId);
		
		$params['buildingOwnerId'] = $selectedBuildingOwner;
		$params['buildingId'] = $selectedIbuilding;
		$params['inspectionId'] = $selectedInspection;
		$params['inspectorId'] = $selectedInspector;

		$this->view->multi = false;
		switch($level){
			case 'northamerica': $levelLabel = 'North America'; break;
			case 'companywide': $levelLabel = $this->getCompanyName($companyId); break;
			
			case 'inspector':
			$inspectors = $this->getInspectorList($companyId);

			// if (count($inspectors) > 0){
			// 	if(!$selectedInspector) $selectedInspector = $inspectors[0]['id'];
			// 	$params['inspectorId'] = $selectedInspector;
			// }
			$this->view->filterParams = $this->getInspectorFilterList($companyId,$selectedInspector);
			$this->view->selectedOption = $selectedInspector;
			$this->view->options = $inspectors;
			$this->view->multi = true;
			break;

			case 'buildingowner':
			$buildingOwners = $this->getBuildingOwnerList($companyId,$selectedInspector);
			
			// if (count($buildingOwners) > 0){
			// 	if(!$selectedBuildingOwner) $selectedBuildingOwner = $buildingOwners[0]['id'];
			// 	$params['buildingOwnerId'] = $selectedBuildingOwner;
			// }
			$this->view->filterParams = $this->getOwnerFilterList($companyId,$selectedInspector,$selectedBuildingOwner);

			$this->view->selectedOption = $selectedBuildingOwner;
			$this->view->options = $buildingOwners;
			$this->view->multi = true;
			break;
			
			case 'building':
			$buildings = $this->getBuildingList($selectedBuildingOwner);
			// if (count($buildings) > 0){
				// if(!$selectedIbuilding) $selectedIbuilding = $buildings[0]['id'];
			// $params['buildingId'] = $selectedIbuilding;
			// }
			$this->view->filterParams = $this->getBuildingFilterList($companyId,$selectedInspector,$selectedBuildingOwner,$selectedIbuilding);


			$this->view->selectedOption = $selectedIbuilding;
			$this->view->options = $buildings;
			$this->view->multi = true;
			break;
			
			case 'inspection':
			$inspections = $this->getInspectionsList($selectedIbuilding);
			// if (count($inspections) > 0){
				// if(!$selectedInspection) $selectedInspection = $inspections[0]['id'];
			// $params['inspectionId'] = $selectedInspection;
			// }
			$this->view->selectedOption = $selectedInspection;
			$this->view->options = $inspections;
			$this->view->multi = true;
			break;
			
			default: break;

		}
		
		$this->view->percentage = $this->percentage($level, $params);
		
		$this->view->levelLabel = $levelLabel;
		
		$this->view->level = $level;
	}
	
	protected function getCompanyName($companyId)
	{
		$db = Zend_Registry::getInstance()->dbAdapter;
		$sql = "select * from company where id = ?";
		$result = $db->fetchRow($sql, $companyId);
		return $result['NAME'];
	}

	protected function getFilterList($companyId){
		// start constructing db query
		$db = Zend_Registry::getInstance()->dbAdapter;


		$sql5 = "select distinct d.id as id, concat(d.first_name, ' ', d.last_name) as name
		from inspection i
		inner join employee	as d on i.inspector_id=d.id	
		where i.company_id = ?
		and i.status = 1077
		order by d.first_name, i.inspection_date";

		$inspector = $db->fetchRow($sql5, $companyId);

		
		$sql = "select distinct c.id as id, c.name as name
		from company c
		inner join building b on b.customer_id = c.id
		inner join inspection i on i.building_id = b.id
		where c.type = 1001
		and c.inspection_company =".$companyId."
		and i.status = 1077
		and i.inspector_id = ?"
		;
		
		$buildingOwner = $db->fetchRow($sql, $inspector['id']);

		if(count($buildingOwner)){
			
			$sql1 = "select distinct b.id as id from building b inner join inspection i on i.building_id = b.id and i.status = 1077 and b.customer_id=?";

			$building = $db->fetchRow($sql1, $buildingOwner['id']);
			if(count($building)){

				$sql2 = "select distinct i.id as id from inspection i where i.building_id = ? and i.status = 1077";
				
				$inspection = $db->fetchRow($sql2, $building['id']);

				return ['inspectorId'=>$inspector['id'],'ownerId'=>$buildingOwner['id'],'buildingId'=>$building['id'],'inspectionId'=>$inspection['id']];
			}
			return ['inspectorId'=>$inspector['id'],'ownerId'=>$buildingOwner['id'],'buildingId'=>0,'inspectionId'=>0];
		}

		return ['ownerId'=>0,'buildingId'=>0,'inspectionId'=>0,'inspectorId'=>$inspector['id']];

	}


	protected function getInspectorFilterList($companyId,$inspectorId){
		// start constructing db query
		$db = Zend_Registry::getInstance()->dbAdapter;		
		$sql = "select distinct c.id as id, c.name as name
		from company c
		inner join building b on b.customer_id = c.id
		inner join inspection i on i.building_id = b.id
		where c.type = 1001
		and c.inspection_company = ?
		and i.status = 1077"
		;
		$buildingOwner = $db->fetchRow($sql, $companyId);
		if(count($buildingOwner)){
			
			$sql1 = "select distinct b.id as id from building b inner join inspection i on i.building_id = b.id and i.status = 1077 and b.customer_id=?";

			$building = $db->fetchRow($sql1, $buildingOwner['id']);
			if(count($building)){
				$sql2 = "select distinct i.id as id from inspection i where i.building_id = ? and i.status = 1077";
				$inspection = $db->fetchRow($sql2, $building['id']);
				return ['inspectorId'=>$inspectorId,'ownerId'=>$buildingOwner['id'],'buildingId'=>$building['id'],'inspectionId'=>$inspection['id']];
			}
			return ['inspectorId'=>$inspectorId,'ownerId'=>$buildingOwner['id'],'buildingId'=>0,'inspectionId'=>0];
		}
		return ['ownerId'=>0,'buildingId'=>0,'inspectionId'=>0,'inspectorId'=>$inspectorId];
	}

	protected function getOwnerFilterList($companyId,$inspectorId,$ownerId){
		// start constructing db query
		$db = Zend_Registry::getInstance()->dbAdapter;		
		
		$sql1 = "select distinct b.id as id from building b inner join inspection i on i.building_id = b.id and i.status = 1077 and b.customer_id=?";
		$building = $db->fetchRow($sql1, $ownerId);
		if(count($building)){
			$sql2 = "select distinct i.id as id from inspection i where i.building_id = ? and i.status = 1077";
			$inspection = $db->fetchRow($sql2, $building['id']);
			return ['inspectorId'=>$inspectorId,'ownerId'=>$ownerId,'buildingId'=>$building['id'],'inspectionId'=>$inspection['id']];
		}
		return ['inspectorId'=>$inspectorId,'ownerId'=>$ownerId,'buildingId'=>0,'inspectionId'=>0];
	}

	protected function getBuildingFilterList($companyId,$inspectorId,$ownerId,$buildingId){
		// start constructing db query
		$db = Zend_Registry::getInstance()->dbAdapter;		
		
		$sql2 = "select distinct i.id as id from inspection i where i.building_id = ? and i.status = 1077";
		
		$inspection = $db->fetchRow($sql2, $buildingId);
		
		return ['inspectorId'=>$inspectorId,'ownerId'=>$ownerId,'buildingId'=>$buildingId,'inspectionId'=>$inspection['id']];
	}


	//returns the list of building owners with at least of one completed inspections
	protected function getBuildingOwnerList($companyId,$inspectorId){
		// start constructing db query
		$db = Zend_Registry::getInstance()->dbAdapter;
		
		$sql = "select distinct c.id as id, c.name as name
		from company c
		inner join building b on b.customer_id = c.id
		inner join inspection i on i.building_id = b.id
		where c.type = 1001
		and c.inspection_company = ?
		and i.status = 1077";
		
		$result = $db->fetchAll($sql, $companyId);
		return $result;
	}
	

	//returns the list of company building
	protected function getBuildingList($ownerId){
		// start constructing db query
		$db = Zend_Registry::getInstance()->dbAdapter;
		// $sql = "select distinct b.id as id, b.name as name
		// from company c
		// inner join building b on b.customer_id = c.id
		// inner join inspection i on i.building_id = b.id
		// where c.type = 1001
		// and c.inspection_company = ?
		// and b.customer_id=?
		// and i.status = 1077";

		$sql = "select distinct b.id, b.name from building b inner join inspection i on i.building_id = b.id and i.status = 1077 and b.customer_id=?";
		
		$result = $db->fetchAll($sql, $ownerId);
		return $result;
	}

		//returns the list of completed inspections
	protected function getInspectionsList($buildingId){
		// start constructing db query
		$db = Zend_Registry::getInstance()->dbAdapter;
		
		$sql = "select i.id as id, concat(b.name, ', on ', date_format(i.inspection_date, '%m/%d/%Y')) as name
		from inspection i
		inner join building b on b.id = i.building_id
		where i.building_id = ?
		and i.status = 1077
		order by b.name, i.inspection_date";

		$result = $db->fetchAll($sql, $buildingId);
		return $result;
	}
	



	//returns the list of company building
	protected function getInspectorList($companyId){
		// start constructing db query
		$db = Zend_Registry::getInstance()->dbAdapter;
		
		$sql = "select distinct d.id as id, concat(d.first_name, ' ', d.last_name) as name
		from inspection i
		inner join employee	as d on i.inspector_id=d.id	
		where i.company_id = ?
		and i.status = 1077
		order by d.first_name, i.inspection_date";
		
		$result = $db->fetchAll($sql, $companyId);
		return $result;
	}



	protected function percentage($level, $params){
		// start constructing db query
		$db = Zend_Registry::getInstance()->dbAdapter;	
		
		if ($level=='northamerica'){
			$sql = "select d.compliant as compliant, count(d.id) as door_count
			from door d
			inner join inspection i on i.id = d.inspection_id
			where i.status = 1077
			group by d.compliant";
			$result = $db->fetchAll($sql, $params['companyId']);
			
			return $result;
		}
		
		if ($level == 'companywide'){
			if (!$params['companyId']) return null; //for this level the company id has to be specified
			
			$sql = "select d.compliant as compliant, count(d.id) as door_count
			from door d
			inner join inspection i on i.id = d.inspection_id
			where i.status = 1077
			and i.company_id = ?
			group by d.compliant";
			$result = $db->fetchAll($sql, $params['companyId']);
			
			return $result;
		}
		
		if ($level == 'buildingowner'){
			if (!$params['companyId']) return null;
			if (!$params['buildingOwnerId']) return null;

			
			$sql = "select d.compliant as compliant, count(d.id) as door_count
			from door d
			inner join inspection i on i.id = d.inspection_id
			inner join building b on b.id = i.building_id
			where i.status = 1077
			and i.company_id = ?
			and b.customer_id = ?
			group by d.compliant";

			$result = $db->fetchAll($sql, array($params['companyId'], $params['buildingOwnerId']));
			
			return $result;
		}

		if ($level == 'building'){
			if (!$params['companyId']) return null;
			if (!$params['buildingId']) return null;

			
			$sql = "select d.compliant as compliant, count(d.id) as door_count
			from door d
			inner join inspection i on i.id = d.inspection_id
			inner join building b on b.id = i.building_id
			where i.status = 1077
			and i.company_id = ?
			and b.id = ?
			group by d.compliant";

			$result = $db->fetchAll($sql, array($params['companyId'], $params['buildingId']));
			
			return $result;
		}

		if ($level == 'inspector'){

			if (!$params['companyId']) return null;
			if (!$params['inspectorId']) return null;
			$sql = "select d.compliant as compliant, count(d.id) as door_count
			from door d
			inner join inspection i on i.id = d.inspection_id

			inner join employee	as e on i.inspector_id = e.id	

			where i.status = 1077
			and i.company_id = ?
			and e.id = ?
			group by d.compliant";

			$result = $db->fetchAll($sql, array($params['companyId'], $params['inspectorId']));
			
			return $result;
		}


		if ($level == 'inspection'){
			if (!$params['companyId']) return null;
			if (!$params['inspectionId']) return null;
			
			$sql = "select d.compliant as compliant, count(d.id) as door_count
			from door d
			inner join inspection i on i.id = d.inspection_id
			where i.status = 1077
			and i.company_id = ?
			and d.inspection_id = ?
			group by d.compliant";

			$result = $db->fetchAll($sql, array($params['companyId'], $params['inspectionId']));
			
			return $result;
		}
	}
	
	protected function reinspections($companyId){
		// start constructing db query
		$db = Zend_Registry::getInstance()->dbAdapter;
		
		$sql = "select * from (
		select b.id as building_id, b.name as building_name, c.id as owner_id, c.name as owner_name, max(i.inspection_date) as latest_inspection_date
		from building b
		left join company c on b.customer_id = c.id
		left outer join inspection i on i.building_id = b.id
		where c.inspection_company = ?
		and i.status = 1077
		group by b.id, b.name, c.id, c.name
		having latest_inspection_date < sysdate() - interval 1 year
		) a
		order by latest_inspection_date asc";
		
		$result = $db->fetchAll($sql, $companyId);
		return $result;
	}
	
	protected function top20noncompliant($companyId){			
		// start constructing db query
		$db = Zend_Registry::getInstance()->dbAdapter;
		
		$sql = "select count(dc.code_id) as code_frequency, d.item as item, d.description as description
		from door_code dc left join dictionary d on dc.code_id = d.id
		left join door dd on dd.id = dc.door_id
		left join inspection i on i.id = dd.inspection_id
		where d.description <> 'Other'
		and i.company_id = ?
		group by d.item, d.description
		order by code_frequency desc limit 0,20";
		
		$result = $db->fetchAll($sql, $companyId);
		return $result;

	}
	
	protected function top20others($companyId){
		// start constructing db query
		$db = Zend_Registry::getInstance()->dbAdapter;
		
		//inner join inspection_other io on io.inspection_id = i.id and io.other_id = dc.code_id
		
		$sql = "select count(other_value) as cnt, other_value from(
		select distinct dc.door_id, dc.code_id, i.id, oi.other_value
		from door_code dc
		inner join dictionary dic on dic.id = dc.code_id
		inner join door d on d.id = dc.door_id
		inner join inspection i on i.id = d.inspection_id
		
		inner join inspection_other oi on oi.inspection_id = i.id
		
		where dic.description = 'Other'
		and i.company_id = ?) others
		group by other_value
		order by cnt desc limit 0,20";
		
		$result = $db->fetchAll($sql, $companyId);
		return $result;
	}
}