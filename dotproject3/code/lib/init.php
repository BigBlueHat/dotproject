<?php

// Should be auto-loaded, but if not, make sure we aren't loaded twice
if (! defined('DP_AUTOLOAD_INIT')) {
	define('DP_AUTOLOAD_INIT', 1);
	// Set up the php include path
	set_include_path(dirname(__FILE__) . ':' . get_include_path());
	/**
	 * Autoload objects if they are Zend or our own.
	 * @author Adam Donnison <ajdonnison@dotproject.net>
	 * @param string classname
	 */
	function __autoload($classname)
	{
		if (substr($classname, 0, 5) == 'Zend_'
		|| substr($classname, 0, 3) == 'DP_')
		{
			$classname = str_replace('_', '/', $classname);
			require_once $classname . '.php';
		}
	}

	require_once 'functions.php';

	// Now we need to set up Zend sessions.
	Zend_Session::setSaveHandler(new DP_Session_SaveHandler);
	Zend_Session::start();
	$session = new Zend_Session_Namespace('dPsession');
}
?>
