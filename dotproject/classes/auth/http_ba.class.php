<?php
if (!defined('DP_BASE_DIR')){
  die('You should not access this file directly');
}

	define('DP_AUTH_SUBCLASS', 'HTTPBasicAuthenticator');

	class HTTPBasicAuthenticator extends SQLAuthenticator
	{
		var $user_id;
		var $username;
	
		function HTTPBasicAuthAuthenticator() {}
	
		//  Assume that the web server has already taken care of authenticating the user and 
		//  simply get the userid from the users table or fail. In some environments it is probably
		//  appropriate to create an account for the user if they were successfully authenticated,
		//  but it isn't in mine so I'm going to leave it at this. (Perhaps this should be a configurable
		//  option .... 'create_account_on_successful_external_authentication' or something. It would also
		//  be nice for the administrator to have the option of getting an e-mail when such an account
		//  is created.
		/**
		 * @param string $username
		 * 
		 * @return boolean Return true if $username exists; die if not
		 */
		function authenticate($username)
		{
			global $AppUI;
	
			$this->user_id = $this->userId($username);
			if (isset($this->user_id) && $this->user_id > 0) {
				return true;
			} else {
				die($AppUI->_("noAccount"));
			}
		}
		
		function displayName()
		{
			return "HTTP Basic";
		}

		/**
		 * @param string $username
		 */
		function userId($username)
		{
			global $db;
	
			$q  = new DBQuery();
			$q->addTable('users');
			$q->addWhere("user_username = '$username'");
			$rs = $q->exec();
			$row = $rs->FetchRow();
			$q->clear();
	
			return $row["user_id"];
		}
	}
?>