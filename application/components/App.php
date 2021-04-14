<?

require_once APPLICATION_PATH . '/models/Employee.php';
require_once APPLICATION_PATH . '/models/Company.php';

/**
 * Static class with different helper functions
 * @author Igor
 *
 */
class App {
	public static function log($message, $severity=Zend_Log::INFO) {
		if (is_array($message))
			$message = var_export($message, true);
		$trace = debug_backtrace();
		
		Zend_Registry::getInstance()->logger->info("{$trace[1]['class']}::{$trace[1]['function']}({$trace[1]['line']}): " . $message, $severity);
	}
	
	public static function session($session="default") {
		return new Zend_Session_Namespace($session);
	}

	public static function unsetSession($session="default") {
		$namespace = new Zend_Session_Namespace($session);
		$namespace->unsetAll();
	}

	public static function identSession() {
		return new Zend_Session_Namespace('identity');
	}
	
	public static function acl() {
		return Zend_Registry::get('_acl');
	}

	public static function user() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			Zend_Session::namespaceUnset('identity');
			return null;
		}
    	if (App::session('identity')->user) {
    		return App::session('identity')->user;
    	}

		$user = Model_User::retrieve()->currentUser();
		$currentUser = array();
		foreach ($user as $id => $value) {
			$currentUser[strtolower($id)] = $value;
		}
		$currentUser['identity'] = Zend_Auth::getInstance()->getIdentity();

		$currentUser = (Object)$currentUser;
		App::session('identity')->user = $currentUser;
		return $currentUser;
	}

	public static function userIdentity() {
		return Zend_Auth::getInstance()->getIdentity();
	}
	
	public static function companyId(){
		$user = self::user();
		
		// get user's company information
		$employee = Model_Employee::retrieve()->fetchEntry(false, array(
			'COMPANY_ID', new Data_Column('USER_ID', $user->login, Model_Employee::retrieve(), 'LOGIN')));
		
		$companyId = $employee['COMPANY_ID'];
		
		return $companyId;
	}
	
	public static function inspectionCompanyId(){
		$user = self::user();
		
		// get user's company information
		$employee = Model_Employee::retrieve()->fetchEntry(false, array(
			'COMPANY_ID', new Data_Column('USER_ID', $user->login, Model_Employee::retrieve(), 'LOGIN')));
		$company = Model_Company::retrieve()->fetchEntry($employee['COMPANY_ID']);
		
		if($company['TYPE'] == Model_Company::INSPECTION_COMPANY){
			$inspectionCompanyId = $company['ID'];
		}
		
		if($company['TYPE'] == Model_Company::BUILDING_OWNER){
			$inspectionCompanyId = isset($company['INSPECTION_COMPANY']) && !empty($company['INSPECTION_COMPANY'])? $company['INSPECTION_COMPANY'] : $company['ID'];
		}
		
		return $inspectionCompanyId;
	}
	
	public static function config() {
		return Zend_Registry::getInstance()->configuration;	
	}

	public static function baseUrl() {
    	// base url (without host info)
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();

    	// full base  url (including host info)
		$fullBaseUrl =
            ((!empty($_SERVER['HTTPS'])) ? 'https://' : 'http://') . // http or https
            $_SERVER['HTTP_HOST'] . // host
            $baseUrl; // remaining part

            return $baseUrl;
        }

        public static function siteUrl() {
    	// base url (without host info)
        	$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();

    	// full base  url (including host info)
        	$fullBaseUrl =
            ((!empty($_SERVER['HTTPS'])) ? 'https://' : 'http://') . // http or https
            $_SERVER['HTTP_HOST'] . // host
            $baseUrl; // remaining part

            return $fullBaseUrl;
        }

        public static function isAllowed($resource, $action=null) {
        	return self::acl()->isallowed(App::user()->identity, $resource, $action);
        }

        public static function dateFormat() {
        	return App::config()->date->displayformat;
        }
    }
