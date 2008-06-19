<?php
/**
 * Helper which checks if the user is logged in. And then redirects them if they are not.
 * 
 * LoginRedirector will only allow the original page request through if the user is logged in OR
 * the page is an error page, or the login page itself.
 *
 * @package dotproject
 * @subpackage system
 * @version 3.0 alpha
 * 
 */
class DP_Controller_Action_Helper_LoginRedirector extends Zend_Controller_Action_Helper_Abstract {

	/**
	 * Predispatch redirect if user is not logged in.
	 * 
	 * User is redirected to the login action of the default controller. The original requested URL is encoded in the
	 * 'from' GET parameter.
	 */
	public function preDispatch() {
		$fc = $this->getFrontController();
		$ac = $this->getActionController();
		
		$controller = $fc->getRequest()->getControllerName();
		
		$auth = Zend_Auth::getInstance();
		$identity = $auth->getIdentity();
		
		if ($controller != 'login' && $controller != 'error' && $auth->hasIdentity() == false) {
			$redir_login = $fc->getRequest()->getBaseUrl() . '/login/?from=' . urlencode($this->getRequest()->getRequestUri());
			$this->getResponse()->setRedirect($redir_login);
			$this->getResponse()->sendHeaders();
			exit;
		}
		
		if ($this->getRequest()->getParam('_forwarded')) {
			$this->getView()->suppressHeaders();
		}
	}
	

	public function postDispatch()
	{
		if ($this->getRequest()->getParam('_forwarded')) {
			$this->render();
		}
	}
}
?>