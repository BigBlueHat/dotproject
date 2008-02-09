<?php

/**
 * Base class for pluggable authentication, basically a stub with a single static method.
 */
class DP_Auth
{
	public static function getAuthenticator()
	{
		$auth_mode = ucwords(strtolower(str_replace('_', ' ', DP_Config::getConfig('auth_method'))));
		require_once 'DP/Auth/' . str_replace(' ', '/', $auth_mode) . '.php';
		$class = 'DP_Auth_' . str_replace(' ', '_', $auth_mode);
		$obj = new $class;
		if ($obj instanceof DP_Auth_Interface) {
			return $obj;
		} else {
			return null;
		}
	}

}
?>
