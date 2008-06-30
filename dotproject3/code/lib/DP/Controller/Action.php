<?php
/**
 * Extend the Zend_Controller_Action to provide default paths for view helpers, etc.
 * 
 * @deprecated.  Should not be used.
 */
class DP_Controller_Action extends Zend_Controller_Action
{
	/**
	 * Log the fact that we were used, but do sod all else.
	 */
	public function init()
	{
		Zend_Registry::get('logger')->emerg('DP_Controller_Action used instead of Zend_Controller_Action');
	}

}

?>
