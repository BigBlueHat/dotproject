<?php
require_once 'DP/Auth/Interface.php';
require_once 'DP/AppUI.php';
require_once 'DP/Config.php';
require_once 'DP/Query.php';

/**
 * The HTTP Basic authentication relies on the web server providing
 * the basic authentication.  We only then need to take the username
 * and look it up in the database.
 */

class DP_Auth_Http_Basic implements DP_Auth_Interface
{
	protected $user_id = null;
	protected $username = null;

	/**
	 * We actually ignore the username and password provided by
	 * the base authentication as it will in fact be from the
	 * server REMOTE_USER variable.
	 */
	public function authenticate($username, $password)
	{
		$this->user_id = 0;
		try {
			$db = DP_Config::getDB();
			$sql = $db->select()
				->from('users')
				->where('user_username = ?', $_SERVER['REMOTE_USER']);
			$row = $db->fetchRow($sql);
			$this->user_id = $row['user_id'];
		}
		catch (Exception $e) {
			$this->user_id = 0;
		}
		return $this->user_id > 0;
	}

	public function displayName()
	{
		return 'HTTP Basic';
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
