<?php
require_once 'DP/Controller/Action.php';

/**
 * This is the file containing the definition of the login controller
 * @author ajdonnison
 * @version 3.0
 * @package dotproject
 */

/**
 * The LoginController handles the login page and its acceptance
 * @package dotproject
 * @subpackage default
 */
class LoginController extends DP_Controller_Action
{
	/**
	 * Displays the login page.
	 * 
	 * Stores the URL requested before the login page was displayed, so that the user can be
	 * redirected to their originally requested location after they log in.
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
	 * Save and process the submitted login details.
	 * 
	 * If the user authenticates successfully then redirect to their originally requested URL,
	 * otherwise display 'failed' message.
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
