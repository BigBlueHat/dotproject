<?php
require_once 'DP/Auth/Interface.php';

/**
 * SQL Based authentication (default)
 *
 * @author Adam Donnison <ajdonnison@dotproject.net>
 */

class DP_Auth_Null implements DP_Auth_Interface
{
	protected $user_id = null;
	protected $username = null;

	function authenticate($username, $password)
	{
		$this->username = 'anonymous@rejournal.com';
		$this->user_id = 0;

		return true;
	}

	public function displayName()
	{
		return 'Null';
	}

	public function supported()
	{
		return true;
	}

	public function __get($var)
	{
		if ($var == 'user_id' || $var == 'username') {
			return $this->$var;
		} else {
			return null;
		}
	}

}

?>