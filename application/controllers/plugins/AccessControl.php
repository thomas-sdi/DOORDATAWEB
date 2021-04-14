<?
require_once APPLICATION_PATH . '/models/Role.php';
require_once APPLICATION_PATH . '/models/UserRole.php';
require_once APPLICATION_PATH . '/models/User.php';

class AccessControl
{
	public function __construct() {
		return $this;
	}
	
    private function getAcl() {
    	return Zend_Registry::get('_acl');
    }
    
    private function getUser() {
    	return Zend_Auth::getInstance()->getIdentity();
    }
    
	/**
	 * creates acl, registers it in Zend_Registry::get('_acl'),
	 * registers all roles and resources, and sets permissions
	 * 
	 */
	public function createAcl() {
		
		// create authorization adapter and load with roles from database
		Zend_Registry::set('_acl', new Zend_Acl());
		
		$this->setRoles();
		
		$this->setResources();
		
		$this->setPermissions();
	}
	
	private function setRoles() {
		
		// get roles with relationship   
		$all_roles = array(); $role = Model_Role::retrieve(); 
		foreach ($role->fetchEntries(array(
    		'NAME', 'PARENT' => new Data_Column('PARENT_ROLE_ID', null, $role, 'NAME'))) as $rel)
		{	
    		$all_roles[$rel['NAME']] = array($rel['PARENT']);
		}
		
		// get users with role reslationship  
		$userRole = Model_User_Role::retrieve(); 
		foreach ($userRole->fetchEntries(array(
    		'NAME'   => new Data_Column('USER_ID', null, $userRole, 'LOGIN'),
    		'PARENT' => new Data_Column('ROLE_ID', null, $userRole, 'NAME')), null, true) as $rel)
		{			
		    // one user can belong to more than one role
			if (array_key_exists($rel['NAME'], $all_roles))
		        array_push($all_roles[$rel['NAME']], $rel['PARENT']);
		    else
		        $all_roles[$rel['NAME']] = array($rel['PARENT']);
			
			
		}

		// now register all roles and users from database as application roles
		foreach ($all_roles as $name => $roles) {
		    $this->_registerRole($name, $all_roles);
		}
		
		//$this->_registerRole('Guest', array());
	}
	
	private function setResources() {
		
		// add all controllers as resources
		/*
		$dir = new DirectoryIterator(APPLICATION_PATH . '/controllers');
		foreach ($dir as $file) {
			if ($file->isDir() || $file->isDot()) continue;
			$file = $file->getFileName();
			$pos = strpos($file, 'Controller');
			if ($pos === false) continue;
			$this->getAcl()->add(new Zend_Acl_Resource(strtolower(substr($file, 0, $pos))));
		}
		*/
		
		//add tabs as resources
		$this->getAcl()->add(new Zend_Acl_Resource('Dashboard'));
		$this->getAcl()->add(new Zend_Acl_Resource('Welcome'));
		$this->getAcl()->add(new Zend_Acl_Resource('Inspections'));
		$this->getAcl()->add(new Zend_Acl_Resource('Building Owners'));
		$this->getAcl()->add(new Zend_Acl_Resource('My Company'));	
		$this->getAcl()->add(new Zend_Acl_Resource('Buildings'));
		$this->getAcl()->add(new Zend_Acl_Resource('Inspection Companies'));
		$this->getAcl()->add(new Zend_Acl_Resource('<i>Online</i> <b>DOOR</b>DATA'));
		$this->getAcl()->add(new Zend_Acl_Resource('System Settings'));
		$this->getAcl()->add(new Zend_Acl_Resource('Log Out'));
		$this->getAcl()->add(new Zend_Acl_Resource('User Files'));
		$this->getAcl()->add(new Zend_Acl_Resource('Help'));
		
		//add grids as resources
		
		/*Building Owners tab */
		$this->getAcl()->add(new Zend_Acl_Resource('company_grid'));
		$this->getAcl()->add(new Zend_Acl_Resource('company_buildings_grid'));
		$this->getAcl()->add(new Zend_Acl_Resource('company_employees_grid'));
		$this->getAcl()->add(new Zend_Acl_Resource('company_inspections_grid'));
		
		
		/*Inspection tab */
		$this->getAcl()->add(new Zend_Acl_Resource('inspection_grid'));
		$this->getAcl()->add(new Zend_Acl_Resource('inspection_inspects_grid'));
		$this->getAcl()->add(new Zend_Acl_Resource('inspection_door_grid'));
		$this->getAcl()->add(new Zend_Acl_Resource('hardware_grid', 'inspection_door_grid')); //###

		/*Buildings tab */
		$this->getAcl()->add(new Zend_Acl_Resource('building_grid'));
		$this->getAcl()->add(new Zend_Acl_Resource('building_inspection_grid'));
		
		/*Systems Settings tab */
		$this->getAcl()->add(new Zend_Acl_Resource('dictionary_grid'));
		$this->getAcl()->add(new Zend_Acl_Resource('users_grid'));
		$this->getAcl()->add(new Zend_Acl_Resource('user_roles_grid'));
		
		/*Inspection Company grid*/
		$this->getAcl()->add(new Zend_Acl_Resource('inspection_company_grid'));
		$this->getAcl()->add(new Zend_Acl_Resource('emp_grid'));
		
		/*User File grid*/
		$this->getAcl()->add(new Zend_Acl_Resource('user_file_grid'));

	}
	
	private function setPermissions() {
        //tabs
        
		/* Administrators */
		$this->getAcl()->allow('Administrators');
		$this->getAcl()->deny('Administrators', array('My Company', '<i>Online</i> <b>DOOR</b>DATA', 'Welcome'));
		
		/* Field Inspectors */
        $this->getAcl()->allow('Field Inspectors', array('Welcome'));
        	
		/* Inspection Company Employees */
        $this->getAcl()->allow('Inspection Company Employees', array('Dashboard', 'Building Owners', 'Inspections', 'Buildings', '<i>Online</i> <b>DOOR</b>DATA', 'Log Out', 'User Files','Help'));
        
        /* Building Owner Employees */
        $this->getAcl()->allow('Building Owner Employees', array('My Company', 'Log Out', 'User Files'));

       	//grids
       	
        
        /* Building Owner Employees */
        $this->getAcl()->allow('Building Owner Employees', 'company_grid');
        $this->getAcl()->allow('Building Owner Employees', 'inspection_grid');
        $this->getAcl()->allow('Building Owner Employees', 'company_buildings_grid');
        $this->getAcl()->allow('Building Owner Employees', 'company_inspections_grid');
        $this->getAcl()->allow('Building Owner Employees', 'company_employees_grid');
        
        	/* Building Owners */
        	$this->getAcl()->allow('Building Owners', 'company_buildings_grid');  
        	$this->getAcl()->allow('Building Owners', 'company_employees_grid');  
        
        /* Inspection Company Employees */
        $this->getAcl()->allow('Inspection Company Employees', 'company_grid');
        $this->getAcl()->allow('Inspection Company Employees', 'company_buildings_grid');
        $this->getAcl()->allow('Inspection Company Employees', 'inspection_grid');
        $this->getAcl()->allow('Inspection Company Employees', 'inspection_inspects_grid');
        $this->getAcl()->allow('Inspection Company Employees', 'inspection_door_grid');
        $this->getAcl()->allow('Inspection Company Employees', 'hardware_grid'); //###
        	
        	/* Inspectors */
        
			/* Web Users */
        
        	/* Inspection Company Admins */
        	$this->getAcl()->allow('Inspection Company Admins', 'company_employees_grid');
        	$this->getAcl()->allow('Inspection Company Admins', 'emp_grid'); 
        	
        	
    }
	
	// security related function
	private function _registerRole($roleName, $allparents) {
    	if (Zend_Registry::get('_acl')->hasRole($roleName))
       		return;

		// first make sure all parents are registered
		if (array_key_exists($roleName, $allparents)) {
			foreach ($allparents[$roleName] as $parentName )
				$this->_registerRole($parentName, $allparents);
			Zend_Registry::get('_acl')->addRole(new Zend_Acl_Role($roleName), $allparents[$roleName]);
		} else Zend_Registry::get('_acl')->addRole(new Zend_Acl_Role($roleName));
	}
	
	/**
	 * returns array of permissions on grid for current user
	 *
	 * @param 	string 	$grid
	 * @return 	array of permissions
	 */
	public function getGridPermissions($grid) {
    	$perm = array();
    	
    	if (!strpos($grid, '_grid')) {
    		$grid = $grid . '_grid';
    	}
		$this->getAcl()->isAllowed($this->getUser(), $grid, 'create') ? $perm['create'] = true : $perm['create'] = false; 
    	$this->getAcl()->isAllowed($this->getUser(), $grid, 'update') ? $perm['update'] = true : $perm['update'] = false;  
    	$this->getAcl()->isAllowed($this->getUser(), $grid, 'delete') ? $perm['delete'] = true : $perm['delete'] = false;  
    	
    	return $perm;
	}
}

