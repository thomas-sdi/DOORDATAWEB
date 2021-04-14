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
		
		if($this->_getParam('inspector') != null){
			$sq = 'e.id ='.$this->_getParam('inspector');
			$levelLabel= $this->getInspectorName($companyId,$this->_getParam('inspector'));
		}
		else if($this->_getParam('owner') !=null ){
			$sq = 'b.customer_id ='.$this->_getParam('owner');
			$levelLabel= $this->getOwnerName($companyId,$this->_getParam('owner'));
		}
		else if($this->_getParam('building') !=null ){
			$sq = 'b.id ='.$this->_getParam('building');
			$levelLabel= $this->getBuildingName($companyId,$this->_getParam('building'));
		}
		else if($this->_getParam('inspection') !=null ){
			$sq = 'i.id ='.$this->_getParam('inspection');
			$levelLabel= $this->getInspectionName($companyId,$this->_getParam('inspection'));
		}
		else
		{
			$sq = 'i.company_id='.$companyId;
			$levelLabel= $this->getCompanyName($companyId);
		}

		$this->view->top20noncompliant = $this->top20noncompliant($sq);
		$this->view->levelLabel = $levelLabel;
	}

	public function getInspectorName($companyId,$inspector){
		
		$db = Zend_Registry::getInstance()->dbAdapter;
		$sql = "select distinct d.id as id, concat(d.first_name, ' ', d.last_name) as name from inspection i inner join employee as d on i.inspector_id=d.id where i.company_id = ".$companyId." and d.id=".$inspector." order by d.first_name, i.inspection_date";
		$result = $db->fetchRow($sql, $companyId);
		return $result['name'];
	}

	public function getOwnerName($companyId,$owner_id){
		$db = Zend_Registry::getInstance()->dbAdapter;
		$sql = "select distinct c.id as id, c.name as name from company c inner join building b on b.customer_id = c.id inner join inspection i on i.building_id = b.id where c.type = 1001
		and c.inspection_company = ".$companyId." and b.customer_id = ".$owner_id;
		$result = $db->fetchRow($sql, $companyId);
		return $result['name'];
	}

	public function getBuildingName($companyId,$id){
		$db = Zend_Registry::getInstance()->dbAdapter;
		$sql = "select distinct b.id, b.name from building b inner join inspection i on i.building_id = b.id and b.id=".$id;
		$result = $db->fetchRow($sql);
		return $result['name'];
	}

	public function getInspectionName($companyId,$id){
		$db = Zend_Registry::getInstance()->dbAdapter;
		$sql = "select i.id as id, b.name as alterName, concat(b.name, ', on ', date_format(i.inspection_date, '%m/%d/%Y')) as name
		from inspection i
		inner join building b on b.id = i.building_id
		where i.id = ".$id."
		order by b.name, i.inspection_date";
		$result = $db->fetchRow($sql);
		return ($result['name'] != null)? $result['name']:$result['alterName'];
	}

	
	public function northamericaAction(){
		$this->_helper->layout->setLayout('html');
		$companyId = App::companyId();
		$this->view->top20noncompliant = $this->top20noncompliantAmerica($companyId);
		$this->view->levelLabel = "North America";

	}


	public function top20othersAction(){
		$this->_helper->layout->setLayout('html');

		$companyId = App::companyId();

		// $this->view->top20others = $companyId;
		$this->view->top20others = $this->top20others($companyId);


	}

	public function reinspectionsAction(){
		$this->_helper->layout->setLayout('html');
		$page = $this->_getParam('page');
		$perPage = $this->_getParam('perPage');
		$companyId = App::companyId();
		$this->view->reinspections = $this->reinspections($companyId,$perPage,$page);
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
			$this->view->filterParams = $this->getInspectionsFilterList($companyId,$selectedInspector,$selectedBuildingOwner,$selectedIbuilding,$selectedInspection);

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
		order by d.first_name, i.inspection_date";
		// and i.status = 1077

		$inspector = $db->fetchRow($sql5, $companyId);


		$sql = "select distinct c.id as id, c.name as name
		from company c
		inner join building b on b.customer_id = c.id
		inner join inspection i on i.building_id = b.id
		where c.type = 1001
		and c.inspection_company =".$companyId."
		order by c.name"
		;
		// and i.status = 1077

		$buildingOwner = $db->fetchRow($sql);

		if(count($buildingOwner)){

			$sql1 = "select distinct b.id as id,b.name from building b inner join inspection i on i.building_id = b.id  and b.customer_id=? order by b.name";
// and i.status = 1077
			$building = $db->fetchRow($sql1, $buildingOwner['id']);
			if(count($building)){

				$sql2 = "select distinct i.id as id from inspection i where i.building_id = ? ";
				//and i.status = 1077
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
		order by c.name"
		;

		// and i.status = 1077

		$buildingOwner = $db->fetchRow($sql, $companyId);
		if(count($buildingOwner)){

			$sql1 = "select distinct b.id as id, b.name as name from building b inner join inspection i on i.building_id = b.id and b.customer_id=? order by b.name";

			// and i.status = 1077

			$building = $db->fetchRow($sql1, $buildingOwner['id']);
			if(count($building)){
				$sql2 = "select distinct i.id as id from inspection i where i.building_id = ? ";
				// and i.status = 1077
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

		$sql1 = "select distinct b.id as id, b.name as name  from building b inner join inspection i on i.building_id = b.id and b.customer_id=? order by b.name";
		//and i.status = 1077
		$building = $db->fetchRow($sql1, $ownerId);
		if(count($building)){
			$sql2 = "select distinct i.id as id from inspection i where i.building_id = ? ";
			//and i.status = 1077
			$inspection = $db->fetchRow($sql2, $building['id']);
			return ['inspectorId'=>$inspectorId,'ownerId'=>$ownerId,'buildingId'=>$building['id'],'inspectionId'=>$inspection['id']];
		}
		return ['inspectorId'=>$inspectorId,'ownerId'=>$ownerId,'buildingId'=>0,'inspectionId'=>0];
	}

	protected function getBuildingFilterList($companyId,$inspectorId,$ownerId,$buildingId){
		// start constructing db query
		$db = Zend_Registry::getInstance()->dbAdapter;		

		$sql2 = "select distinct i.id as id from inspection i where i.building_id = ?";
		//and i.status = 1077

		$inspection = $db->fetchRow($sql2, $buildingId);

		return ['inspectorId'=>$inspectorId,'ownerId'=>$ownerId,'buildingId'=>$buildingId,'inspectionId'=>$inspection['id']];
	}

	protected function getInspectionsFilterList($companyId,$inspectorId,$ownerId,$buildingId,$inspectionId){
		// start constructing db query
		return ['inspectorId'=>$inspectorId,'ownerId'=>$ownerId,'buildingId'=>$buildingId,'inspectionId'=>$inspectionId];
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
		order by c.name";

		//and i.status = 1077 
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

		$sql = "select distinct b.id, b.name from building b inner join inspection i on i.building_id = b.id and b.customer_id=? order by b.name";

		$result = $db->fetchAll($sql, $ownerId);
		return $result;
	}

		//returns the list of completed inspections
	protected function getInspectionsList($buildingId){
		// start constructing db query
		$db = Zend_Registry::getInstance()->dbAdapter;

		$sql = "select i.id as id,b.name as alterName, concat(b.name, ', on ', date_format(i.inspection_date, '%m/%d/%Y')) as name
		from inspection i
		inner join building b on b.id = i.building_id
		where i.building_id = ?
		order by b.name, i.inspection_date";
		
		// and i.status = 1077

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
		order by d.first_name, i.inspection_date";
		// and i.status = 1077

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
			group by d.compliant";
			$result = $db->fetchAll($sql, $params['companyId']);

			// where i.status = 1077

			return $result;
		}

		if ($level == 'companywide'){
			if (!$params['companyId']) return null; //for this level the company id has to be specified
			
			$sql = "select d.compliant as compliant, count(d.id) as door_count
			from door d
			inner join inspection i on i.id = d.inspection_id
			where i.company_id = ?
			group by d.compliant";
			$result = $db->fetchAll($sql, $params['companyId']);
			//i.status = 1077
			 // and 
			return $result;
		}
		
		if ($level == 'buildingowner'){
			if (!$params['companyId']) return null;
			if (!$params['buildingOwnerId']) return null;

			
			$sql = "select d.compliant as compliant, count(d.id) as door_count
			from door d
			inner join inspection i on i.id = d.inspection_id
			inner join building b on b.id = i.building_id
			where i.company_id = ?
			and b.customer_id = ?
			group by d.compliant order by b.name";

			// i.status = 1077
			// and 
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
			where i.company_id = ?
			and b.id = ?
			group by d.compliant";

			// i.status = 1077 and
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

			where i.company_id = ?
			and e.id = ?
			group by d.compliant";

			// i.status = 1077
			// and 
			$result = $db->fetchAll($sql, array($params['companyId'], $params['inspectorId']));
			
			return $result;
		}


		if ($level == 'inspection'){
			if (!$params['companyId']) return null;
			if (!$params['inspectionId']) return null;
			
			$sql = "select d.compliant as compliant, count(d.id) as door_count
			from door d
			inner join inspection i on i.id = d.inspection_id
			where i.company_id = ?
			and d.inspection_id = ?
			group by d.compliant";		
			//i.status = 1077 and 
			$result = $db->fetchAll($sql, array($params['companyId'], $params['inspectionId']));
			
			return $result;
		}
	}
	
	protected function reinspections($companyId,$perPage,$page){
		// start constructing db query
		$st = $perPage * ($page - 1);
		
		$start = ($st <= 0 ) ? 0 : $st;
		// $end = $page * $perPage;


		$db = Zend_Registry::getInstance()->dbAdapter;
		
		$sql = "select * from (select b.id as building_id, b.name as building_name, b.ADDRESS_1 as address,b.city,di.ITEM as state, c.id as owner_id, c.name as owner_name, i.inspection_date as latest_inspection_date, max(i.reinspect_date) as reinspect
		from building b
		
		left join company c on b.customer_id = c.id
		left outer join inspection i on i.building_id = b.id
		left outer join dictionary di on b.STATE = di.id
		
		where c.inspection_company = ?
		group by b.id, b.name, c.id, c.name
		having latest_inspection_date < sysdate() 
		) a  
		ORDER BY reinspect Desc,latest_inspection_date DESC
		LIMIT ".$start.",".$perPage;
		// and i.status = 1077
		
		$result = $db->fetchAll($sql, $companyId);

		$totalRecord = $this->reinspectCount($companyId);

		$this->view->totalRecord = $totalRecord;
		
		$totalPages_pre = floor($totalRecord/$perPage);
		$totalPages = ($totalRecord % $perPage) == 0 ? $totalPages_pre : $totalPages_pre + 1;
		
		$startNo = ($page-2 <= 1 )? 1 : $page-2 ;
		
		$this->view->startNo = $startNo;

		$d = $startNo + 4 - $page;

		$this->view->endNo = ($page+$d >= $totalPages )? $totalPages : $page+$d;

		$this->view->currentPage = $page;
		$this->view->perPage = $perPage;
		$this->view->totalPages = $totalPages;
		
		return $result;
	}
	
	protected function reinspectCount($companyId){

		$db = Zend_Registry::getInstance()->dbAdapter;
		
		$sql = "select COUNT(*) as count from (select b.id as building_id, b.name as building_name, b.ADDRESS_1 as address,b.city,di.ITEM as state, c.id as owner_id, c.name as owner_name, i.inspection_date as latest_inspection_date, max(i.reinspect_date) as reinspect
		from building b
		
		left join company c on b.customer_id = c.id
		left outer join inspection i on i.building_id = b.id
		left outer join dictionary di on b.STATE = di.id
		
		where c.inspection_company = ?
		group by b.id, b.name, c.id, c.name
		having latest_inspection_date < sysdate() 
		) a  
		ORDER BY `a`.`reinspect` DESC";
		// and i.status = 1077
		
		$result = $db->fetchAll($sql, $companyId);
		return $result[0]['count'];
	}



	protected function top20noncompliantAmerica($companyId){			
		// start constructing db query
		$db = Zend_Registry::getInstance()->dbAdapter;
		
		$sql = "select count(dc.code_id) as code_frequency, d.item as item, d.description as description
		from door_code dc left join dictionary d on dc.code_id = d.id
		left join door dd on dd.id = dc.door_id
		left join inspection i on i.id = dd.inspection_id
		where d.description <> 'Other'
		group by d.item, d.description
		order by code_frequency desc limit 0,20";
		
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

		left join building b on b.id = i.building_id
		left join employee	as e on i.inspector_id = e.id	

		where ".$companyId." 
		group by d.item, d.description
		order by code_frequency desc limit 0,20";

		// print_r($sql);
		// die;

		$result = $db->fetchAll($sql);
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