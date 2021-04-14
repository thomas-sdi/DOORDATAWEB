<?

class Controller_Abstract extends Zend_Controller_Action {
	
	protected $_params = array();
	protected $_session;
	protected $_loggedSession;
	protected $_baseUrl;
	protected $_fullUrl;
	protected $_controllerUrl;
	
    public function init() {
    	// base url (without host info)
        $this->_baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
    	$this->view->baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
        $this->_controllerUrl = $this->_baseUrl . '/' . Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
		$this->_session 	  = $this->view->session 		= new Zend_Session_Namespace('default');
        $this->_loggedSession = $this->view->loggedSession  = new Zend_Session_Namespace('identity');
        
        // full base  url (including host info)
        $this->view->fullBaseUrl =
            ((!empty($_SERVER['HTTPS'])) ? 'https://' : 'http://') . // http or https
            $_SERVER['HTTP_HOST'] . // host
            $this->view->baseUrl; // remaining part

        // security objects
        $this->view->acl  = $this->getAcl();
        $this->view->user = $this->getUser();
        $this->view->accessControl = $this->getAccessControl();
        
        // Zend backward compatibility
		if (Zend_Version::compareVersion("1.7.5") <= 0)
			$this->view->setLfiProtection(false);
        
        // request parameters
        $this->_params = $this->getRequest()->getParams();
    }
    
    public function getSecurityPlugin() {
    	return Zend_Controller_Front::getInstance()->getPlugin('Plugin_Security'); 
    }
    
    /**
     * @return Zend_Acl
     */
    public function getAcl() {
    	return $this->getSecurityPlugin()->getAcl();
    }
    
    public function getUser() {
    	return Zend_Auth::getInstance()->getIdentity();
    }
    
    public function getAccessControl() {
    	return Zend_Registry::get('_accessControl');
    }
    
    public function log($message) {
        Zend_Registry::getInstance()->logger->info($message);
    }
    
    public function getParam($param, $default = null) {
        if (!$this->_params) {
            $this->_params = $this->getRequest()->getParams();
        }
        if (!array_key_exists($param, $this->_params))
            return null;
        $param = $this->_params[$param];
        if (is_string($param) && trim($param) == '')
            return null;
        return $param;
    }
    
    public function report400($message, $field=null, $echo = false){
        if (!$echo) {
            $this->_helper->layout->setLayout('http400');
            $this->_helper->ViewRenderer->setNoRender(true);
            // output error message in a valid json format {field: message}
            $this->view->placeholder('error')->set(
                $field ? '{"' . $field . '": "' . $message . '"}' : $message);
        }
        else echo $field ? '{"' . $field . '": "' . $message . '"}' : $message;
    }
    
    public function report500($message, $echo=false){
        Zend_Registry::getInstance()->logger->err($message);    
        if (!$echo) {
            $this->_helper->layout->setLayout('http500');
            $this->_helper->ViewRenderer->setNoRender(true);
            $this->view->placeholder('error')->set('{"general" : ' . '"Server is unavailable, please try again later"}');
        }
        else echo '{"general" : ' . '"Server is unavailable, please try again later"}';        
    }
    
    public function redirectAjax($controller, $action) {
    	echo '{href: "/' . $controller . '/' . $action . '"}';
        
    }
}