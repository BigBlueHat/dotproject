<?php
/**
 * Zend_Table factory.
 *
 * Uses the requested module name to determine the relevant table class to return.
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0 alpha
 * @author ebrosnan
 */

class DP_Table
{
	static public function factory($module)
	{		
		switch($module) {
			case $module == 'projects':
				return new Db_Table_Projects();
				break;
			case $module == 'contacts':
				return new Db_Table_Contacts();
				break;
			case $module == 'companies':
				return new Db_Table_Companies();
				break;
			default:
				return null;	
		}
	}
}
?>