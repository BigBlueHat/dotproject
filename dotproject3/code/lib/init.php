<?php

// Should be auto-loaded, but if not, make sure we aren't loaded twice
if (! defined('DP_AUTOLOAD_INIT')) {
	define('DP_AUTOLOAD_INIT', 1);
	// Set up the php include path
	set_include_path(dirname(__FILE__) . ':' . get_include_path());
	// Standard definitions.
	define('DP_BASE_CODE', dirname(dirname(__FILE__)));
	define('DP_BASE_DIR', dirname(DP_BASE_CODE));
	define('DP_BASE_WWW', DP_BASE_CODE . DIRECTORY_SEPARATOR . 'www');
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
	$config = DP_Config::getBaseConfig();
	// For those that have defined paths, use those instead of the inbuilt defaults.
	if (isset($config->paths)) {
		if (isset($config->paths->base)) {
			define('DP_BASE_DIR', dPmakePath($config->paths->base));
		}
		if (isset($config->paths->www)) {
			define('DP_BASE_WWW', dPmakePath($config->paths->www));
		}
	}

	$registry = Zend_Registry::getInstance();
	// Set the default logger
	$logger = new Zend_Log();
	$screen = new Zend_Log_Writer_Stream('php://output');
	$screen->setFormatter(new Zend_Log_Formatter_Simple('<div class="error">%message%</div>'));
	$logger->addWriter($screen);
	$error_level = Zend_Log::EMERG;
	if (isset($config->logging)) {
		if ($config->logging->log_file) {
			$writer = new Zend_Log_Writer_Stream(dPmakePath($config->logging->log_file));
			$logger->addWriter($writer);
			if (isset($config->logging->log_level)) {
				$writer->addFilter(new Zend_Log_Filter_Priority((int)$config->logging->log_level));
			}
		}
		if (isset($config->logging->error_level)) {
			$error_level = $config->logging->error_level;
		}
	}
	$screen->addFilter(new Zend_Log_Filter_Priority((int)$error_level));
	$registry['logger'] = $logger;
	// Now we need to set up Zend sessions.
	Zend_Session::setSaveHandler(new DP_Session_SaveHandler());
	Zend_Session::start();
	$session = new Zend_Session_Namespace('dPsession');
	// Put the session into the registry, makes life a little easier.
	$registry['session'] = $session;

	// Once the session has been created we also have the config defaults
	$dbconfig = DP_Config::getInstance();
	if (!empty($dbconfig->timezone)) {
		date_default_timezone_set($dbconfig->timezone);
	}
}
?>
