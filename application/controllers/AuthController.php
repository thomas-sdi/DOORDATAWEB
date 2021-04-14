<?
require_once APPLICATION_PATH . '/models/User.php';
require_once APPLICATION_PATH . '/models/UserRole.php';
require_once APPLICATION_PATH . '/models/Employee.php';
require_once APPLICATION_PATH . '/controllers/Component.php';

class AuthController extends Controller_Component {

	public function loginAction() {
        $this->_helper->layout->setLayout('html');
        if ($this->getRequest()->isGet()) {
        	$this->view->logoUrl = nvl($this->getRequest()->getCookie('companyLogo'), $this->_baseUrl . '/public/images/logo.png');
              // /public/images/doordata_logo1.jpg


         if($this->view->logoUrl == $this->_baseUrl . '/public/images/stackedLogo.png'){
            $this->view->logoUrl = $this->_baseUrl . '/public/images/logo.png';
        }

        	// checks if there is any custom branding involved 
        $this->view->customBranding = nvl($this->getRequest()->getCookie('companyLogo'), $this->getRequest()->getCookie('companyTheme'));

        return;
    }
    $this->_helper->ViewRenderer->setNoRender(true);

        // check if all fields are filled
    if (!$this->getParam('login')) {
        $this->report400('Please specify your user name', 'login');
        return;
    } elseif (!$this->getParam('password')) {
        $this->report400('Please specify your password', 'password');
        return;
    }

        // perform authentication
    $auth = Zend_Registry::get('_authAdapter'); 
    $auth->setIdentity($this->getParam('login')); $auth->setCredential(md5($this->getParam('password')));
    $result = Zend_Auth::getInstance()->authenticate($auth);

    if ($result->getCode() == Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND) {
        $this->report400("Invalid ID or password. Please try again or contact your system administrator.", 'login');
    } else
    if ($result->getCode() == Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID) {
        $this->report400('The password does not match. Please try again', 'password');
    } else
    if ($result->getCode() == Zend_Auth_Result::SUCCESS) {
            // fill last login field in employee table with current time
        $user = Zend_Auth::getInstance()->getIdentity();
        $employee = Model_Employee::retrieve()->fetchEntry(null, array(
            'ID', new Data_Column('USER_ID', $user, Model_Employee::retrieve(), 'LOGIN'), 'COMPANY' => new Data_Column('COMPANY_ID', null, Model_Employee::retrieve(), 'NAME')));
        Model_Employee::retrieve()->save(array('ID' => $employee['ID'], 'LAST_LOGIN' => date('Ymd')));

        $session = new Zend_Session_Namespace('default');

            // remember the time of the latest request
        $session->latestRequestTime = time();

			// remember the user id
        $session->userRecord = $auth->getResultRowObject();
        $session->employeeId = $employee['ID'];
        $session->companyId = $employee['COMPANY_ID'];
        $session->companyName = $employee['COMPANY'];

        $this->_session->fromController = $this->_session->fromAction = null;
    }
    else {
        $this->report500('Unrecognized authentication problem: ' . $result->getCode);
    }

}

public function logoutAction() {
    $this->_helper->layout->setLayout('html');
    $this->_helper->ViewRenderer->setNoRender(true);
    Zend_Auth::getInstance()->clearIdentity();      
    Zend_Session::namespaceUnset('default');
	//Zend_Session::namespaceUnset('identity');
	//App::session('identity')->unsetAll();
    App::unsetSession('default');
    App::unsetSession('identity');

    $this->_redirect("http://www.doordatasolutions.com");

        //$this->_helper->redirector('login', 'auth');
}

public function loginjsonAction() {

  $this->_helper->layout->setLayout('json');
  $this->_helper->ViewRenderer->setNoRender(true);

  $auth 	= Zend_Auth::getInstance();
  if ($auth->hasIdentity()) 	
     $this->view->placeholder('data')->set(Zend_Json::encode(array('login' => 'true')));
 else
     $this->view->placeholder('data')->set(''); 
}

public function logindialogAction() {
  $this->_helper->layout->setLayout('html');

  $this->view->logoUrl = nvl($this->getRequest()->getCookie('companyLogo'), $this->_baseUrl . '/public/images/doordata_logo3.jpg');
}


}