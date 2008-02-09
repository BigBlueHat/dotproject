<?php

/**
 * Handle logouts
 * @author ajdonnison
 * @version 3.0
 * @package dotproject
 */

/**
 * The LogoutController logs a user our and kills the session.
 * @package dotproject
 * @subpackage default
 */
class LogoutController extends DP_Controller_Action
{
	/**
	 * Log a user out
	 * 
	 */
	public function indexAction()
	{
		Zend_Session::destroy();
		$this->_redirect('/');
	}

}
?>
