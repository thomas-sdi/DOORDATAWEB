<?
require_once APPLICATION_PATH . '/controllers/Component.php';

class HelpController extends Controller_Component {    

	public function init() {
		
		$this->_helper->layout->setLayout('html');
		$access = array();
		$this->view->access = $access;

		$db = Zend_Registry::getInstance()->dbAdapter;
		
		if(Zend_Auth::getInstance()->getIdentity() == 'admin')
			$sql = "select * from videohelp order by sorder ASC";
		else
			$sql = "select * from videohelp where status=1 order by sorder ASC";

		
		$this->view->videoList = $db->fetchAll($sql);

		parent::init();
	}

	public function videouploadAction()
	{
		$this->_helper->layout->setLayout('html');
		$access = array();

		$this->view->access = $access;
	}

	public function savehelpAction()
	{	
		$temparr = [];	
		$targetVideo= ROOT_PATH ."/public/help/video/";

		if($_FILES['video']){
			
			$originalName = basename($_FILES['video']['name']);

			$ext = pathinfo($originalName, PATHINFO_EXTENSION);

			$newName = $this->random_strings(10).'.'.$ext;

			array_push($temparr, $newName);

			$targetVideo=$targetVideo.$newName;
			
			chmod($targetVideo , 0777);

			

			$uplaod_success=move_uploaded_file($_FILES['video']['tmp_name'],$targetVideo);
		}
		
		// $targetImage="public/help/thumbnail/";
		// if($_FILES['thumbnail']){
		// 	array_push($temparr, basename($_FILES['thumbnail']['name']));
		// 	$targetImage=$targetImage.basename($_FILES['thumbnail']['name']);
		// 	$uplaod_success=move_uploaded_file($_FILES['thumbnail']['tmp_name'],$targetImage);
		// }

		array_push($temparr, 'nourl');
		array_push($temparr, $_POST['title']);
		array_push($temparr, $_POST['description']);
		
		$db = Zend_Registry::getInstance()->dbAdapter;
		
		$sql = "INSERT INTO videohelp (videoUrl,thumbnailUrl,title,description) VALUES (?,?,?,?)";
		$stmt = $db->prepare($sql);
		$stmt->execute($temparr);
		
		array_push($temparr, $db->lastInsertId());
		$this->view->result = $temparr;
		//insert data 
	}

	public function deletehelpAction()
	{	
		$db = Zend_Registry::getInstance()->dbAdapter;
		$id = $this->_getParam('id');
		$sql = "DELETE FROM videohelp WHERE ID=?";
		$stmt = $db->prepare($sql);
		$paramArray[] = $id;
		$stmt->execute($paramArray);
	}

	public function enablevideoAction()
	{	
		$db = Zend_Registry::getInstance()->dbAdapter;
		$id = $this->_getParam('id');
		$status = $this->_getParam('status');
		$sql =  "Update videohelp set status = ".$status." where ID = ".$id;
		$stmt = $db->prepare($sql);
		$stmt->execute();
	}

	public function sordervideoAction()
	{	
		$db = Zend_Registry::getInstance()->dbAdapter;
		$id = $this->_getParam('id');
		$order = $this->_getParam('order');
		$sql =  "Update videohelp set sorder = ".$order." where ID = ".$id;
		$stmt = $db->prepare($sql);
		$stmt->execute();
	}

	public function random_strings($length_of_string) 
	{ 
		$str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; 
		return substr(str_shuffle($str_result),  
			0, $length_of_string); 
	} 
}