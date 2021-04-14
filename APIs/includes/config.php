<?php
/*************************************************************************************************************
	 *  *  *  *  *  *  *  *  *  *  *   I D E A V A T E   S O L U T I O N S   *  *  *  *  *  *  *  *   *
**************************************************************************************************************
	 * Filename				 :config.php	
	 * Description 		  	 :configuration file
	 * External Files called :NA
	 * Global Variables	  	 :NA
	 * 
	 * Modification Log
	 * Date:20 SEP 2011  	   Author:IDEAVATE SOLUTIONS      		Description
	 * --------------------------------------------------------------------------------------------------------
	 * 
 **************************************************************************************************************/  

//SETTING THE ERROR REPORTING 
//error_reporting(E_ALL);
ini_set("display_errors",1); 

//DEFINING THE CONSTANTS for local
/*defined('DB_SERVER') 	? NULL : define('DB_SERVER','localhost');
defined('DB_USER') 		? NULL : define('DB_USER','root');
defined('DB_PASS') 		? NULL : define('DB_PASS','');
defined('DB_NAME') 		? NULL : define('DB_NAME','online_door_data');
defined('ERROR_CODE') 	? NULL : define('ERROR_CODE','200');
defined('SUCCESS_CODE') ? NULL : define('SUCCESS_CODE','100');
defined('DS') 			? NULL : define('DS', DIRECTORY_SEPARATOR);
//defined('DIR_ROOT') 	? NULL : define('DIR_ROOT','http://122.168.132.243/online_door_data/API');
//defined('IMAGE_DIR_ROOT') 	? NULL : define('IMAGE_DIR_ROOT','http://122.168.132.243/onlinedoordata/content/pictures/');
defined('DIR_ROOT') 	? NULL : define('DIR_ROOT','http://10.10.1.79/purshottam/kiln/OnlineDoorData/online_door_data/API');
defined('IMAGE_DIR_ROOT') 	? NULL : define('IMAGE_DIR_ROOT','http://10.10.1.79/purshottam/kiln/OnlineDoorData/onlinedoordata/content/pictures/');
*/

//DEFINING THE CONSTANTS for server
defined('DB_SERVER') 	? NULL : define('DB_SERVER','localhost');
defined('DB_USER') 		? NULL : define('DB_USER','doordata');
defined('DB_PASS') 		? NULL : define('DB_PASS','Doordata@123');
defined('DB_NAME') 		? NULL : define('DB_NAME','doordt');
defined('ERROR_CODE') 	? NULL : define('ERROR_CODE','200');
defined('SUCCESS_CODE') ? NULL : define('SUCCESS_CODE','100');
defined('DS') 			? NULL : define('DS', DIRECTORY_SEPARATOR);
    
defined('DIR_ROOT') 	? NULL : define('DIR_ROOT','http://23.253.20.117/APIs');
defined('IMAGE_DIR_ROOT') 	? NULL : define('IMAGE_DIR_ROOT','http://23.253.20.117/content/pictures/');
    
    //defined('DIR_ROOT')     ? NULL : define('DIR_ROOT','http://www.mydoordata.com/APIs');
    //defined('IMAGE_DIR_ROOT')     ? NULL : define('IMAGE_DIR_ROOT','http://www.mydoordata.com/content/pictures/');
        
    
//defined('IMAGE_FOLDER') 	? NULL : define('IMAGE_FOLDER','/var/www/mydoordata.com/content/pictures/');
?>

