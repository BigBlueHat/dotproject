<?php
/**
 * Abstract factory for Zend_Auth_Adapter_Interface classes.
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0 alpha
 * @author ebrosnan
 */
class DP_Auth
{	
	/**
	 * Abstract factory method for retrieving a Zend_Auth_Adapter class.
	 * 
	 * The default is to return a Zend_Auth_Adapter_DbTable configured with the default dotProject schema options.
	 * This method is responsible for setting up the configuration details required for each adapter eg. the LDAP host
	 * or the database table etc.
	 * 
	 * @param string $adapter_name Name of the auth adapter class to return.
	 * @return Zend_Auth_Adapter_Interface Instance implementing Zend_Auth_Adapter_Interface
	 */
	public static function factory($adapter_name = null)
	{
		switch($adapter_name) {
			case 'DbTable':
			default:
				$tbl = 'users';
				$user_col = 'user_username';
				$pass_col = 'user_password';
				
				return new Zend_Auth_Adapter_DbTable(DP_Config::getDB(), $tbl, $user_col, $pass_col, 'MD5(?)');
				break;				
		}
	}
}
?>
