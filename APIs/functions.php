<?php

function loggerTxt($message){
	$my_file = 'logger.txt';
	$handle = fopen($my_file, 'a') or die('Cannot open file:  '.$my_file);
	fwrite($handle, "\n". "\n" . ">>>>>>>>>>>>>>>>>" . "\n" . $message);
}

?>