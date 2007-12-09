<?php
require_once 'DP/Module/Interface.php';
require_once 'DP/Module/Abstract.php';

class DP_Module
{
	protected static $_modlist = null;

	/**
	 * Don't allow instantiation, all methods are static
	 */
	private function __construct()
	{
	}

	public static function &register($module)
	{
		if (null === self::$_modlist) {
			self::$_modlist = array();
		}
		if (array_key_exists($module, self::$_modlist)) {
			return self::$_modlist[$module];
		}
		// Find the module, and register it.
		$modfile = DP_BASE_CODE.'/modules/'.$module.'/models/module.php';
		self::$_modlist[$module] = NULL;
		if (is_file($modfile)) {
			require_once $modfile;
			$className = 'DP_Module_' . ucfirst($module);
			if (class_exists($className)) {
				self::$_modlist[$module] = new $className();
				if ( !self::$_modlist[$module] instanceof DP_Module_Interface) {
					self::$_modlist[$module] = null;
				}
			}
		} 
		return self::$_modlist[$module];
	}

	public static function isRegistered($module)
	{
		if (isset(self::$_modlist[$module])) {
			return true;
		}
		return false;
	}
}
?>
