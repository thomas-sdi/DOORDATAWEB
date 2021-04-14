<?php

function arrayValue($array, $key){
		if(array_key_exists($key, $array)) return $array[$key];
		else return null;
}
	
function arrayValueZero($array, $key){	
	$value = arrayValue($array, $key);
	return (strlen($value) > 0) ? ($value !== '0' ? $value : '') : '';
		
}
	
	
?>
