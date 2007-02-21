<?php // $Id$
if (!defined('DP_BASE_DIR')){
  die('You should not access this file directly');
}

/**
 * @package Authenticator Class
 * 
 * @abstract
 * Description:
 * The ui->login() method first checks the result of the supported() method to determine if the server supports all of the
 * features and extensions required to authenticate via that method. If the server does support the features then it uses the configured 
 * authenticator class. 
 *
 * If the authentication fails because the authentication server is unreachable (not because the server does not
 * support the feature), the authenticator class will fall back to the SQLAuthenticator method (if fall back to sql is enabled in the
 * dP config). 
 *
 * Usually an authenticator will inherit methods from the SQLAuthenticator class, so the fall back authentication can be
 * performed via the parent::authenticate() method.
 * 
 */


	function &getAuth($auth_mode)
	{
		$auth_base = dirname(__FILE__) . '/auth/';
		// Sanitize auth_mode
		$auth_mode = strtolower(strtr($auth_mode, '\\;:./ ', '_____'));
		if (file_exists($auth_base . $auth_mode . '.class.php')) {
			require_once $auth_base . $auth_mode . '.class.php';
			$auth_class = DP_AUTH_SUBCLASS;  // Must be defined in the included class file.
			$auth = new $auth_class;
		} else {
			$auth = new SQLAuthenticator;
		}

		return $auth;
	}

	class SQLAuthenticator
	{
		var $user_id;
		var $username;

		/**
		 * @param string $username
		 * @param string $password
		 * 
		 * @return boolean Returns true if the user's password is correct
		 */
		function authenticate($username, $password)
		{
			global $db, $AppUI;

			$this->username = $username;

			$q  = new DBQuery();
			$q->addTable('users');
			$q->addQuery('user_id, user_password');
			$q->addWhere("user_username = '$username'");
			if (!$rs = $q->exec()) {
				$q->clear();
				return false;
			}
			if (!$row = $q->fetchRow()) {
				$q->clear();
				return false;
			}

			$this->user_id = $row["user_id"];
			$q->clear();

			if (MD5($password) == $row["user_password"]) return true;
			return false;
		}
		
		function displayName()
		{
			return "SQL Database";
		}
		
		function supported()
		{
			// every authenticator should support this method
			return true;
		}

		function userId()
		{
			return $this->user_id;
		}
	}	

?>
