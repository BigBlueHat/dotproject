<?php
require_once 'DP/Auth/Sql.php';
require_once 'DP/AppUI.php';
require_once 'DP/Base.php';
require_once 'DP/Config.php';

/**
 * LDAP Base authentication.
 * Note that there is scope for a better implementation that can
 * integrate with LDAP better, providing ACL support.  This would
 * require extending the DP_Auth_Interface but probably makes sense.
 *
 * @todo Fix create user code
 */
class DP_Auth_Ldap extends DP_Auth_Sql
{
	private $ldap_host;
	private $ldap_port;
	private $ldap_version;
	private $base_dn;
	private $ldap_search_user;
	private $ldap_search_pass;	
	private $filter;

	public function __construct()
	{
		$AppUI = DP_AppUI::getInstance();
		$this->fallback = $AppUI->getConfig('ldap_allow_login', false);

		$this->ldap_host = explode(';', $AppUI->getConfig('ldap_host'));
		$this->ldap_port = $AppUI->getConfig('ldap_port');
		$this->ldap_version = $AppUI->getConfig('ldap_version');
		$this->base_dn = $AppUI->getConfig('ldap_base_dn');
		$this->ldap_search_user = $AppUI->getConfig('ldap_search_user');
		$this->ldap_search_pass = $AppUI->getConfig('ldap_search_pass');
		$this->filter = $AppUI->getConfig('ldap_user_filter');
	}
	
	function supported()
	{
		if (!function_exists("ldap_connect")) {
			return false;
		} else {
			return true;
		}
	}
	
	function displayName()
	{
		return "LDAP";
	}

	/**
	 * @param string $username
	 * @param string $password
	 * 
	 * @return boolean Returns true if user's password is correct
	 */
	function authenticate($username, $password)
	{
		$this->username = $username;

		if (strlen($password) == 0) return false; // LDAP will succeed binding with no password on AD (defaults to anon bind)
		if ($this->fallback == true) {
			if (parent::authenticate($username, $password)) return true;	
		}

		// Based in part on the LDAP interface class from Babel Com and
		// the LDAP authentication in Moodle.
		$bound = false;
		foreach ($this->ldap_host as $host) {
			dprint(__FILE__, __LINE__, 9, 'connecting to ' . $host . ' on port ' . $this->ldap_port);
			if (!$rs = @ldap_connect($host, $this->ldap_port)) {
				continue;
			}
			@ldap_set_option($rs, LDAP_OPT_PROTOCOL_VERSION, $this->ldap_version);
			@ldap_set_option($rs, LDAP_OPT_REFERRALS, 0);
			if (! empty($this->ldap_search_user)) {
				dprint(__FILE__, __LINE__, 9, 'Binding as user ' . $this->ldap_search_user);
				$bound = @ldap_bind($rs, $this->ldap_search_user, $this->ldap_search_pass);
			} else {
				dprint(__FILE__, __LINE__, 9, 'Anon Bind');
				$bound = @ldap_bind($rs);
				dprint(__FILE__, __LINE__, 9, 'bound=' . print_r($bound, true));
			}
			if ($bound) {
				dprint(__FILE__, __LINE__, 9, 'Bind successful');
				break;
			}
		}
		if (! $bound) {
			dprint(__FILE__, __LINE__, 9, 'No host will bind');
			return false;
		}

		$filter_r = html_entity_decode(str_replace("%USERNAME%", $username, $this->filter), ENT_COMPAT, 'UTF-8');
		$result = @ldap_search($rs, $this->base_dn, $filter_r);
		if (!$result) {
			dprint(__FILE__, __LINE__, 9, 'Failed to find user based on filter');
			return false; // ldap search returned nothing or error
		}
		
		$result_user = ldap_get_entries($rs, $result);
		if ($result_user["count"] == 0) {
			dprint(__FILE__, __LINE__, 9, 'No user matches filter');
			return false; // No users match the filter
		}

		$first_user = $result_user[0];
		$ldap_user_dn = $first_user["dn"];

		// Bind with the dn of the user that matched our filter (only one user should match sAMAccountName or uid etc..)

		if (!$bind_user = @ldap_bind($rs, $ldap_user_dn, $password)) {
			/*
			$error_msg = ldap_error($rs);
			die("Couldnt Bind Using ".$ldap_user_dn."@".$this->ldap_host.":".$this->ldap_port." Because:".$error_msg);
			*/
			dprint(__FILE__, __LINE__, 9, 'Failed to bind as user');
			return false;
		} else {
			if ($this->userExists($username)) {
				return true;
			} else {
				$this->createsqluser($username, $password, $first_user); 
			}
			return true;
		} 
	}

	/**
	 * @param string $username
	 */
	function userExists($username)
	{
		$result = false;
		try {
			$db = DP_Config::getDB();
			$q  = $db->select()
				->from('users')
				->where('user_username = ?' , $username);
			$row = $db->fetchRow($q);
			if ($row && !empty($row['user_id'])) {
				$result = true;
				$this->username = $row['user_username'];
				$this->user_id = $row['user_id'];
			}
		}
		catch (Exception $e) {
			$result = false;
		}
		return $result;
	}

	/**
	 * @param string $username
	 * @param string $password
	 * @param array $ldap_attribs
	 */
	function createsqluser($username, $password, $ldap_attribs = array())
	{
		$AppUI = DP_AppUI::getInstance();
		$db = DP_Config::getDB();

		$hash_pass = MD5($password);

		require_once $AppUI->getModuleClass("contacts");

		if (!count($ldap_attribs) == 0) {
			// Contact information based on the inetOrgPerson class schema
			$c = new CContact();
			$c->contact_first_name = $ldap_attribs["givenname"][0];
			$c->contact_last_name = $ldap_attribs["sn"][0];
			$c->contact_email = $ldap_attribs["mail"][0];
			$c->contact_phone = $ldap_attribs["telephonenumber"][0];
			$c->contact_mobile = $ldap_attribs["mobile"][0];
			$c->contact_city = $ldap_attribs["l"][0];
			$c->contact_country = $ldap_attribs["country"][0];
			$c->contact_state = $ldap_attribs["st"][0];
			$c->contact_zip = $ldap_attribs["postalcode"][0];
			$c->contact_job = $ldap_attribs["title"][0];

			//print_r($c); die();
			db_insertObject('contacts', $c, 'contact_id');
		}
		$contact_id = ($c->contact_id == NULL) ? "NULL" : $c->contact_id;

		$q  = new DP_Query();
		$q->addTable('users');
		$q->addInsert('user_username',$username );
		$q->addInsert('user_password', $hash_pass);
		$q->addInsert('user_type', '1');
		$q->addInsert('user_contact', $c->contact_id);
		$q->exec();
		$user_id = $db->Insert_ID();
		$this->user_id = $user_id;
		$this->username = $username;
		$q->clear();

		$acl =& $AppUI->acl();
		$acl->insertUserRole($acl->get_group_id('anon'), $this->user_id);
	}

}
?>
