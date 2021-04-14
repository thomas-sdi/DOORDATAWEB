<?php 
class Model_user{
	
	public static function VerifyLogIn($LOGIN,$PASSWORD)
	{
		global $DB;
		$sql="
			 	SELECT 	ID
				FROM user 
				WHERE LOGIN='".$LOGIN."' AND PASSWORD='".$PASSWORD."'
				LIMIT 1
			 ";
		$result=$DB->query($sql);
		if($DB->num_rows($result)==1)
		{
			$records=$DB->fetch_assoc($result);
			return $records['ID'];
		}
		return false;
	}		

}
?>