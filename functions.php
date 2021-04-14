<?
function array_value($key, $array) {
	if (is_array($array) && array_key_exists($key, $array))
		return $array[$key];
	else return null;
}

function is_empty($value) {
	if (is_array($value)){
		if (empty($value)) 
			return true;
		else 
			return false;
	}
	else {
		if ($value == '' || (gettype($value) == 'string' && strlen($value) == 0) || $value == null)
			return true;
		else
			return false;
	}
}

function removeCodesHashes($str, $needle, $startPos){
	$needlePos = strpos($str, $needle, $startPos);
	
	if ($needlePos === false) return $str;
	
	$hashPos = strpos($str, '#', $needlePos);
		
	$noHashStr = $str;
		
	if ($hashPos > 0)
		$noHashStr = substr($str, 0, $needlePos) .$needle . substr($str, $hashPos+1, strlen($str));
		
	if (strlen($noHashStr) == strlen($str))	{
		return $noHashStr;
	}
	else {
		//probably there were more occurrences of the needle, so let's check the rest of the string
		return removeCodesHashes($noHashStr, $needle, $needlePos + strlen($needle));
	}
}

function nvl($value, $result) {
	return !is_empty($value) ? $value : $result;
}

// Case insensitive version of array_key_exists() using preg_match() 
function array_ikey_exists($key,$arr) 
{ 
    if(preg_match("/".$key."/i", join(",", array_keys($arr))))                
        return true; 
    else 
        return false; 
} 

// returns translated version of text
function t($text) {
	return $text;
	//return Model_Content::getInstance()->translate($text);
}