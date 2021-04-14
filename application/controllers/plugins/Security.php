<?
require_once APPLICATION_PATH . '/models/Role.php';
require_once APPLICATION_PATH . '/models/UserRole.php';
require_once APPLICATION_PATH . '/models/Employee.php';
require_once APPLICATION_PATH . '/components/data/Column.php';

class Plugin_Security extends Zend_Controller_Plugin_Abstract
{
	
    public function getAcl() {
    	return Zend_Registry::get('_acl');
    }
    
    /**
     * Manage authentication and authorization
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {   
        $auth 	= Zend_Auth::getInstance();
        $ctrl 	= $request->getControllerName();
        $act  	= $request->getActionName();
        //$acl  = Zend_Registry::get('_acl');        

        $session = new Zend_Session_Namespace('default');
        $registry = Zend_Registry::getInstance();
		
    	// if latest request was made more than a number of seconds ago
    	// then we make user logout 
    	if (!isset($session->latestRequestTime)) $auth->clearIdentity();
    	elseif (time() - $session->latestRequestTime > 1*$registry->configuration->user_maximum_inactivity_period) $auth->clearIdentity();
    	// remember the time of the latest request
    	$session->latestRequestTime = time();
    	
		/* Pass through allowed requests */
        //if ($acl->isAllowed('Guest', $ctrl)) return;
        if ($ctrl == 'inspectionservice' || $ctrl == 'micopdf' || $ctrl == 'error' || $ctrl == 'download'
            || ($ctrl == 'index' && $act == 'index')
            || ($ctrl == 'auth' && $act == 'login')
            || ($ctrl == 'auth' && $act == 'loginjson')
            || ($ctrl == 'auth' && $act == 'logindialog')
            ) return;

    	// remember current controller & action in session
        $session->fromController = $ctrl; $session->fromAction = $act;
            
    	// check if the user is logged in
    	
    	if (!$auth->hasIdentity()) {
    	    // redirect to login page
            if ($ctrl == 'index' && $act == 'home') {
    	    	$request->setControllerName('auth');
            	$request->setActionName('login');
            }
            else {
            	$request->setControllerName('auth');
            	$request->setActionName('loginjson');
            }
    	}
    }
}