<?php
//connect to db
$q=mysql_connect('localhost','doordata','Doordata@123');
//select db
mysql_select_db('doordt',$q);

//if sql in not empty
if(isset($_REQUEST['sql']) && !empty($_REQUEST['sql'])){
	//get the sql
	$sql=$_REQUEST['sql'];
	$result=mysql_query($sql);
	$records=array();
	if(mysql_num_rows($result)>0){
		while($fetch=mysql_fetch_assoc($result)){
			$records[]=$fetch;		
		}	
	}
	
	echo "<pre>";
	print_r($records);
	
}
else{
	echo "Sql is empty";	
}
?>

database.params.host     = localhost
database.params.username = doordata
database.params.password = Doordata@123
database.params.dbname   = doordt
