<?php
/*require_once("../config.php");
require_once("../database.php");
*/
class M_dictionary
{
	public static function get($CATEGORY)
	{
		global $DB;
		$sql="
			 	SELECT * 
				FROM  `dictionary` 
				WHERE  `CATEGORY` =  '".$CATEGORY."'
			 ";	
		$result=$DB->query($sql);
		if($DB->num_rows($result)>0)
		{
			$records=array();
			while($fetch=$DB->fetch_assoc($result))
			{
				$records[]=$fetch;			
			}
			return $records;

		}
		else
		{
			return false;	
		}
	}
	public static function getState($ID)
	{
		global $DB;
		$sql="
			 	SELECT * 
				FROM  `dictionary` 
				WHERE  `ID` =  '".$ID."'
			 ";	
		$result=$DB->query($sql);
		if($DB->num_rows($result)>0)
		{
			$records=array();
			$fetch=$DB->fetch_assoc($result);
			
				$records=$fetch;			
			
			return $records;

		}
		else
		{
			return false;	
		}
	}
	public static function is_this_id_exists($ID)
	{
		global $DB;
		$sql="
			 	SELECT * 
				FROM dictionary
				WHERE ID='".$ID."'
				LIMIT 1
			 ";
		$result=$DB->query($sql);
		if($DB->num_rows($result)==1)
		{
			return true;	
		}
		else
		{
			return false;	
		}
		
	}
	
	public static function is_this_state_exists($ID)
	{
		global $DB;
		$sql="
			 	SELECT * 
				FROM dictionary
				WHERE ID='".$ID."' AND CATEGORY='State'
				LIMIT 1
			 ";
		$result=$DB->query($sql);
		if($DB->num_rows($result)==1)
		{
			return true;	
		}
		else
		{
			return false;	
		}
		
	}
	
	public static function is_this_country_exists($ID)
	{
		global $DB;
		$sql="
			 	SELECT * 
				FROM dictionary
				WHERE ID='".$ID."' AND CATEGORY='Country'
				LIMIT 1
			 ";
		$result=$DB->query($sql);
		if($DB->num_rows($result)==1)
		{
			return true;	
		}
		else
		{
			return false;	
		}
		
	}		
	
	public static function get_inspection_status($ID)
	{
		global $DB;
		$sql="
				SELECT ITEM
				FROM dictionary
				WHERE ID='".$ID."'	
				LIMIT 1
			 ";	
		$result=$DB->query($sql);
		$record=NULL;
		if($DB->num_rows($result)==1)
		{
			$fetch=$DB->fetch_assoc($result);
			$record=$fetch['ITEM'];
		}
		return $record;	
		
	}
	public static function get_all_dictionary_data()
	{
		global $DB;
		$sql="
				SELECT *
				FROM dictionary
				
			 ";	
		$result=$DB->query($sql);
		$record=NULL;
		if($DB->num_rows($result)>0)
		{
			while($fetch=$DB->fetch_assoc($result)){
				$record[]=$fetch;
			}
		}
		return $record;	
		
	}
}
?>