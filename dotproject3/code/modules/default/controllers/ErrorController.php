<?php
require_once 'DP/Controller/Action.php';
class ErrorController extends DP_Controller_Action
{
	public function errorAction()
	{
		$errors = $this->_getParam('error_handler');
		$this->_helper->viewRenderer->view->error = $errors->exception->getMessage();
		$this->_helper->viewRenderer->view->dialog = 1;
	}
}
?>
