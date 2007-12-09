<?php
require_once 'DP/Controller/Action.php';

/**
 * The LoginController handles the login page and its acceptance
 */
class LoginController extends DP_Controller_Action
{
	/**
	 * Default is to display the login page.
	 */
	public function indexAction()
	{
		$tpl = $this->_helper->viewRenderer->view;
		$tpl->dialog = 1;
		if ($redirect = $this->getRequest()->getParam('from')) {
			$tpl->redirect = $redirect;
		} else {
			$tpl->redirect = $this->getRequest()->getParam('redirect');
		}
	}

	/**
	 * On save we need to validate the login and either kick back
	 * or redirect to the new URL
	 */
	public function saveAction()
	{
		if (DP_AppUI::getInstance()->login($this->getRequest()->getParam('username'), $this->getRequest()->getParam('password'))) {
			$redirect = urldecode($this->getRequest()->getParam('redirect'));
			$this->getResponse()->setRedirect($redirect);
			$this->getResponse()->sendHeaders();
			error_log('Redirecting to '.$redirect);
			exit;
		} else {
			DP_AppUI::getInstance()->setMsg('Login Failed');
			// Display the login page again.
			$this->_forward('index');
		}
	}
}
?>
