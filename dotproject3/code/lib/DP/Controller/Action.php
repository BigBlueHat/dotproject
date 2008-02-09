<?php
/**
 * Extend the Zend_Controller_Action to provide entry point for all dP
 * pages.
 */
class DP_Controller_Action extends Zend_Controller_Action
{
	protected $_forwarded = false;

	/**
	 * Override the default View object.
	 */
	public function init()
	{
		if ($this->_helper->hasHelper('viewRenderer')) {
			$this->_helper->viewRenderer->setView(new DP_Template());
			$this->_helper->viewRenderer->setViewSuffix('html');
		}
	}

	public function getView()
	{
		return $this->_helper->viewRenderer->view;
	}

	/**
	 * Perform pre dispatch checks, primarily to ensure we are logged in.
	 */
	public function preDispatch()
	{
		$controller = $this->getRequest()->getControllerName();
		if ($controller != 'login' && $controller != 'error' && DP_AppUI::getInstance()->doLogin()) {
			$this->getResponse()->setRedirect($this->getRequest()->getBaseUrl() . '/login/?from=' . urlencode($this->getRequest()->getRequestUri()));
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

	public function &moduleClass($modname = null)
	{
		if (null === $modname) {
			$modname = $this->getRequest()->getModuleName();
		}
		return DP_Module::register($modname);
	}

	public function &tabBox()
	{
		return DP_AppUI::tabBoxFactory($this->getRequest()->getModuleName(), $this);
	}

	public function &titleBlock()
	{
		$view = $this->getView();
		return DP_AppUI::titleBlockFactory($this->getRequest()->getModuleName(), $view);
	}

	public function defVal($key, $default)
	{
		return isset($key) ? $key : $default;
	}

	public function appendRequest($request, $extras = array())
	{
		$args = isset($request['args']) ? $request['args'] : array();
		$params = array_merge($this->_getAllParams(), $args, $extras);
		$params['_forwarded'] = 1;
		$this->_forward($request['action'], $request['controller'], $request['module'], $params);
	}

}

?>
