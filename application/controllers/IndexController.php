<?
require_once APPLICATION_PATH . '/controllers/Abstract.php';
require_once APPLICATION_PATH . '/models/Employee.php';
require_once APPLICATION_PATH . '/models/User.php';
require_once APPLICATION_PATH . '/models/UserRole.php';
require_once APPLICATION_PATH . '/models/Role.php';
require_once APPLICATION_PATH . '/models/Inspection.php';

class IndexController extends Controller_Abstract 
{
    public function init() {
        parent::init();
    }
    
    public function indexAction(){
    	$this->view->placeholder('data')->companyTheme = Model_Inspection::retrieve()->getThemeById($this->getRequest()->getCookie('theme'));
    }
    
    public function homeAction() {
        $this->_helper->layout->setLayout('html');
        
        // get user's first/last name
    	$employee = Model_Employee::retrieve()->fetchEntry(null, array(
            'FIRST_NAME' => 'FIRST_NAME',
            'LAST_NAME'  => 'LAST_NAME',
    		'COMPANY_ID' => 'COMPANY_ID',
    		'COMPANY_NAME' => new Data_Column('COMPANY_ID', null, Model_Employee::retrieve(), 'NAME'),
            new Data_Column('USER_ID', $this->getUser(), Model_Employee::retrieve(), 'LOGIN')
		));
        
        if (!$employee) {
            $employee = array();
        	$employee['FIRST_NAME'] = "Guest";
        	$employee['LAST_NAME']  = "";
        	$employee['COMPANY_ID'] ="";
			$employee['COMPANY_NAME'] = "Guest LTD";
        }
        $this->view->userName = $employee['FIRST_NAME'];
        if (array_key_exists('LAST_NAME', $employee) && $employee['LAST_NAME'] != '')
            $this->view->userName .= ' ' . $employee['LAST_NAME'];
        
        $alltabs = array(
            'Dashboard'			   => '/dashboard',
            'Inspections'          => '/inspection',
            'Building Owners'      => '/company',
 			'My Company'  	   	   => '/company/owner?_row=' . $employee["COMPANY_ID"],
        	'Buildings'		       => '/building',
        	'Inspection Companies' => '/inspectioncompany',
        	'<i>Online</i> <b>DOOR</b>DATA'	=> '/inspectioncompany/admin',
            'System Settings'      => '/admin',
        	'User Files'      	   => '/userfile',
            'Help'                 => '/help',
        	'Welcome'			   => '/welcome/index'   
        );
		
		$this->view->allIcons = array(
			'Dashboard'			   => '<i class="glyph-icon icon-dashboard"></i>',
            'Inspections'          => '<i class="fa fa-edit"></i>',
            'Building Owners'      => '<i class="glyph-icon icon-group"></i>',
 			'My Company'  	   	   => '<i class="glyph-icon icon-bars"></i>',
        	'Buildings'		       => '<i class="glyph-icon icon-building"></i>',
        	'Inspection Companies' => '<i class="glyph-icon icon-user-md"></i>',
        	'<i>Online</i> <b>DOOR</b>DATA'	=> '<i class="glyph-icon icon-bars"></i>',
            'System Settings'      => '<i class="glyph-icon icon-gear"></i>',
        	'User Files'      	   => '<i class="glyph-icon icon-files-o"></i>',
            'Help'             => '<i class="glyph-icon icon-info"></i>',
        	'Welcome'			   => '<i class="glyph-icon icon-bars"></i>'   
        );   
            
       	//show only those tabs which are permitted by security settings
        $this->view->tabs = array();
		$this->view->icons = array();
 		$this->view->firstTab = $this->_session->lastTab;//this is the tab that is opened by default when user logs in
        	foreach ($alltabs as $title=>$href){
	        	if ( $this->getAcl()->isAllowed($this->getUser(), $title )){
	        		$this->view->tabs[$title] = $href;
	        		if ($this->view->firstTab == null)
	        		    $this->view->firstTab = $href;
	        	}
        }
			
		// start with a standard branding
		$this->view->companyLogoUrl = $this->_baseUrl . '/public/images/logo.png';
		$this->view->companyThemeId = 0;
		$this->view->companyTheme = Model_Inspection::retrieve()->getThemeById($this->view->companyThemeId);
			
		// load the company-specific branding
		if (array_value('COMPANY_ID', $employee)) {
			$company = Model_Company::retrieve()->fetchEntry($employee['COMPANY_ID'], array('INSPECTION_COMPANY', 'LOGO_FILE', 'COLOR_THEME', 'BRANDING'));
			
			// if this is a building owner company, fetch the branding from the respective inspection company
			if (array_value('INSPECTION_COMPANY', $company)) {
				$company = Model_Company::retrieve()->fetchEntry($company['INSPECTION_COMPANY'], array('LOGO_FILE', 'COLOR_THEME', 'BRANDING'));
			}
			
			$branding = $company['BRANDING'];
			
			// apply a custom logo, if allowed
			if (Model_Company::brandingAllowsLogoChange($branding))
				$this->view->companyLogoUrl = nvl(array_value('LOGO_FILE', $company), $this->view->companyLogoUrl);
			
			// apply a custom theme, if allowed
			if (Model_Company::brandingAllowsThemeChange($branding)) {
				$this->view->companyThemeId = nvl($company['COLOR_THEME'], 0) * 1;
				$this->view->companyTheme = Model_Inspection::retrieve()->getThemeById($this->view->companyThemeId);
			}
			
			$this->view->customBranding = $branding > 0;
		}

		$this->view->employee = $employee;
    }
    
    public function changebodyAction() {
    	$this->_session->lastTab = $this->_params['url'];
    	$this->_redirect($this->_params['url']);
    }
}