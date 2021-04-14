<?php

$dataJson='';
if($_POST)
	$dataJson = $_POST['requestJSON'];
if(trim($dataJson) == '') {
	$dataJson = file_get_contents("php://input");
}

//INCLUDING THE CONFIGURATION FILE
require_once 'includes/config.php';
//INCLUDEING THE DATABASE FILE
require_once 'includes/database.php';

require_once 'includes/apiFunctions.php';

if(trim($dataJson)){
	
	//print_r($dataJson);	

	//TODO: when debugging, write the whole json to the log
	//loggerTxt($dataJson);
		
	//CONVERT THE JSON INTO AN ARRAY
	$arrayPostData = json_decode($dataJson,true);
	$arrayPostData = json_decode($arrayPostData,true);
	print_r($arrayPostData);die;

	//CHECK FOR CORRECT JSON FORMAT STRING
	if(trim(!$arrayPostData)){
		$DB->json_response_basic(ERROR_CODE,'Json string you sent is not valid!');
	}
		
	//SET THE METHOD IDENTIFIER INTO A LOCAL VARIABLE
	$methodIdentifier = $arrayPostData['methodIdentifier'];
}

?>