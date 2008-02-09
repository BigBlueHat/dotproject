<?php
/**
 * SQL Based authentication (default)
 *
 * @author Adam Donnison <ajdonnison@dotproject.net>
 */

class DP_Auth_Sql implements DP_Auth_Interface
{
	protected $user_id = null;
	protected $username = null;

	function authenticate($username, $password)
	{
		$this->username = $username;
		$result = false;
		try {
			$db = DP_Config::getDB();
			$sql = $db->select()
				->from('users', array('user_id', 'user_password'))
				->where('user_username = ?', $username);
			$row = $db->fetchRow($sql);
			if (($this->user_id = $row['user_id']) && $row['user_password'] == md5($password)) {
				$result = true;
			}

		}
		catch (Exception $e) {
			$result = false;
		}
		return $result;
	}

	public function displayName()
	{
		return 'SQL Database';
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
