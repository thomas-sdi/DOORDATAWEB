<?
include_once APPLICATION_PATH . '/controllers/Abstract.php';

/**
 * Base class supporting RESTful services realized by specific descendent controllers.
 * 4 operations are supported:
 *   - GET
 *   - POST
 *   - PUT
 *   - DELETE
 */
abstract class Controller_RESTService extends Controller_Abstract {
	
	public function indexAction() {
		// no view will be defined for this action
		$this->_helper->ViewRenderer->setNoRender(true);
		
		// redirect to an appropriate method
		if ($this->getRequest()->isGet())
		    return $this->get();
		elseif ($this->getRequest()->isPost())
		    return $this->post();
		elseif ($this->getRequest()->isPut())
            return $this->put();
        elseif ($this->getRequest()->isDelete())
            return $this->delete();
        else throw new Exception('HTTP Method ' . $this->getRequest()->getMethod() . ' is not yet supported');
	}

	protected abstract function get();
	
	protected abstract function post();
	
	protected abstract function put();
		
	protected abstract function delete();
}