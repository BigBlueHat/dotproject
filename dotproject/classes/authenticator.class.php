<?php

	/*
	 *	Authenticator Class
	 *
	 */


	function getAuth($auth_mode)
	{
		switch($auth_mode)
		{
			case "sql":
				$auth = new SQLAuthenticator();
				return $auth;
				break;
			case "ldap":
				$auth = new LDAPAuthenticator();
				return $auth;
				break;
		}
	}

	class SQLAuthenticator
	{
		var $user_id;

		function authenticate($username, $password)
		{
			GLOBAL $db;

			$sql = "
			SELECT user_id, user_password
			FROM users
			WHERE user_username = '$username'
			";

			if (!$rs = $db->Execute($sql)) return -1;
			if (!$row = $rs->FetchRow()) return -1;

			$this->user_id = $row["user_id"];
			if (MD5($password) == $row["user_password"]) return 1;
			return 0;
		}

		function userId()
		{
			return $this->user_id;
		}
	}	

	class LDAPAuthenticator
	{
		var $ldap_host;
		var $ldap_port;
		var $ldap_version;
		var $base_dn;
		var $filter;

		var $user_id;

		function LDAPAuthenticator()
		{
			GLOBAL $dPconfig;

			$this->ldap_host = $dPconfig["ldap_host"];
			$this->ldap_port = $dPconfig["ldap_port"];
			$this->ldap_version = $dPconfig["ldap_version"];
			$this->base_dn = $dPconfig["ldap_base_dn"];
			$this->filter = $dPconfig["ldap_user_filter"];
		}

		function authenticate($username, $password)
		{
			GLOBAL $dPconfig;

			if (!$rs = @ldap_connect($this->ldap_host, $this->ldap_port)) return false;
			@ldap_set_option($rs, LDAP_OPT_PROTOCOL_VERSION, $this->ldap_version);

			if (!$bindok = @ldap_bind($rs, "cn=$username,".$this->base_dn, $password))
			{
				return false;
			}
			else
			{
				if ($this->userExists($username))
				{
					return true;
				}
				else
				{
					$filter_r = str_replace("%USERNAME%", $username, $this->filter);
					$result = @ldap_search($rs, $this->base_dn, $filter_r);				
					$result_arr = ldap_get_entries($rs, $result);
	
					$this->createsqluser($username, $password, $result_arr[0]);	
					return true;
				}
			}
		}

		function userExists($username)
		{
			GLOBAL $db;
			$sql = "SELECT * FROM users WHERE user_username = '".$username."'";
			$rs = $db->Execute($sql);
			if ($rs->RecordCount() > 0) return true;
			return false;
		}

		function userId($username)
		{
			GLOBAL $db;
			$sql = "SELECT * FROM users WHERE user_username = '".$username."'";
			$rs = $db->Execute($sql);
			$row = $rs->FetchRow();
			return $row["user_id"];	
		}

		function createsqluser($username, $password, $ldap_attribs = Array())
		{
			GLOBAL $db, $AppUI;
			$hash_pass = MD5($password);

			require_once($AppUI->getModuleClass("contacts"));
	
			if (!count($ldap_attribs) == 0)
			{
				// Contact information based on the inetOrgPerson class schema
				$c = New CContact();
				$c->contact_first_name = $ldap_attribs["givenname"][0];
				$c->contact_last_name = $ldap_attribs["sn"][0];
				$c->contact_email = $ldap_attribs["mail"][0];
				$c->contact_phone = $ldap_attribs["telephonenumber"][0];
				$c->contact_mobile = $ldap_attribs["mobile"][0];
				$c->contact_city = $ldap_attribs["city"][0];
				$c->contact_country = $ldap_attribs["country"][0];

				//print_r($c); die();
				db_insertObject('contacts', $c, 'contact_id');
			}
			$contact_id = ($c->contact_id == NULL) ? "NULL" : $c->contact_id;

			$sql = "
			INSERT INTO users 
			(
				user_username, 
				user_password, 
				user_type, 
				user_contact
			) 
			VALUES 
			(
				'".$username."', 
				'".$hash_pass."', 	
				1,
				".$c->contact_id."
			)
			";
			$db->Execute($sql);
			$user_id = $db->Insert_ID();
			$this->user_id = $user_id;

			$acl =& $AppUI->acl();
			$acl->insertUserRole(11, $this->user_id);
		}

	}


?>
