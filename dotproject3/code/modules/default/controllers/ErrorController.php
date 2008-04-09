<?php
require_once 'DP/Controller/Action.php';

/**
 * This is the file containing the definition of the error controller for the default module
 * @author ajdonnison
 * @version 3.0
 * @package dotproject
 * @subpackage default
 */

/**
 * Handles generated error messages.
 * @package dotproject
 * @subpackage default
 */
class ErrorController extends DP_Controller_Action
{
	public function errorAction()
	{
		$this->_helper->layout()->disableLayout();
		
		$errors = $this->_getParam('error_handler');
		$this->_helper->viewRenderer->view->error = $errors->exception->getMessage();
		$this->_helper->viewRenderer->view->dialog = 1;
	}
}
?>
