<?
// phpinfo();

//ini_set('display_errors',1);
//error_reporting(E_ALL);
error_reporting(E_ALL & ~E_NOTICE);

// constants
define( 'APPLICATION_PATH', realpath( dirname(__FILE__) . '/application/' ));
define( 'ROOT_PATH', realpath( dirname(__FILE__) ));
// define( 'APPLICATION_ENVIRONMENT', 'development' );
define( 'APPLICATION_ENVIRONMENT', 'production' );
//date_default_timezone_set("America/New_York");

set_include_path(PATH_SEPARATOR . './library'
    .PATH_SEPARATOR.get_include_path());


require_once "Zend/Version.php";
if (Zend_Version::compareVersion("1.8.0") > 0) {
	require_once "Zend/Loader.php";
	Zend_Loader::registerAutoload();
} else {
	require_once "Zend/Loader/Autoloader.php";
	Zend_Loader_Autoloader::getInstance();
}

require_once ROOT_PATH . "/functions.php";
require_once "application/controllers/plugins/Security.php";
require_once APPLICATION_PATH . "/components/App.php";

// layouts
Zend_Layout::startMvc( APPLICATION_PATH . '/layouts/scripts' );
$view = Zend_Layout::getMvcInstance()->getView();
$view->doctype( 'XHTML1_STRICT' );

$view->setEncoding('UTF-8');

// configuration
$configuration = new Zend_Config_Ini(
    APPLICATION_PATH . '/../config/app.ini',
    APPLICATION_ENVIRONMENT
);

// database
$dbAdapter = Zend_Db::factory( $configuration->database );
Zend_Db_Table_Abstract::setDefaultAdapter( $dbAdapter );

// logger
$logger = new Zend_Log();
$writer = new Zend_Log_Writer_Stream( APPLICATION_PATH . '/../logs/error.log' );
$logger->addWriter( $writer );

// registry
$registry = Zend_Registry::getInstance();
$registry->configuration = $configuration;
$registry->dbAdapter     = $dbAdapter;
$registry->logger 		 = $logger;
$registry->models		 = array();



// caching of database tables metadata (table.describe)
$cache = Zend_Cache::factory('Core',
   'File',
   array('automatic_serialization' => true),
   array('cache_dir' => APPLICATION_PATH . '/../cache'));
Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);

set_time_limit(600);

// registering view helper
Zend_Controller_Action_HelperBroker::addHelper(new Zend_Controller_Action_Helper_ViewRenderer());

// create authentication adapter
Zend_Registry::set('_authAdapter', new Zend_Auth_Adapter_DbTable(
	Zend_Registry::getInstance()->dbAdapter, 'user', 'LOGIN', 'PASSWORD'));

require_once ROOT_PATH . '/application/controllers/plugins/AccessControl.php';		

//acl
$acl = new AccessControl();
$acl->createAcl();
Zend_Registry::set('_accessControl', $acl);



// setup & launch front controller
$frontController = Zend_Controller_Front::getInstance();
$frontController->setControllerDirectory(APPLICATION_PATH . '/controllers/');
// if($_SERVER['REMOTE_ADDR']=="162.216.141.4"){
//   echo APPLICATION_ENVIRONMENT." -- ";
//   die("Vengi testing");
// }
$frontController->setParam('env', APPLICATION_ENVIRONMENT )
->registerPlugin(new Plugin_Security())
->dispatch();
