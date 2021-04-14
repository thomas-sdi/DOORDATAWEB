<?php
require_once APPLICATION_PATH . '/controllers/Abstract.php';

class ErrorController extends Controller_Abstract{ 

    public function errorAction() { 
        $this->_helper->layout()->disableLayout();
    	// Grab the error object from the request
        $errors = $this->getParam('error_handler'); 

        // pass the actual exception object to the view
        $this->view->exception = $errors->exception; 
        
        // pass the request to the view
        $this->view->request   = $errors->request; 
    }
    
    public function loginAction() {
    	$this->_helper->layout->setLayout('error');
    }

}