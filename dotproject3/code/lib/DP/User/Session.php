<?php
/**
 * Storage class to handle user session related data.
 *
 */
class DP_User_Session
{
	public $identity;
	// From AppUI
	/** current user's ID */
	public $user_id=null;
	/** current user's first name */
	public $user_first_name=null;
	/** current user's last name */
	public $user_last_name=null;
	/** current user's company */
	public $user_company=null;
	/** current user's department */
	public $user_department=null;
	/** current user's email */
	public $user_email=null;
	/** current user's type */
	public $user_type=null;
	/** current user's username */
	public $user_username=null;
	/** current user's preferences */
	public $user_prefs=null;
	
	public function __construct($identity)
	{
		$this->identity = $identity;
	}
	
	public function load()
	{
		$db = DP_Config::getDB();
		Zend_Db_Table_Abstract::setDefaultAdapter($db);
		
		$tbl = new DP_Db_Table_Users();
		$select = $tbl->select()->where('user_username = ?', $this->identity);
		$row = $tbl->fetchRow($select);
		
		foreach ($row as $col => $v) {
			$this->{$col} = $v;
		}
	}
	
	/**
	 * Retain compatibility with default Zend_Auth behaviour
	 */
	public function __toString()
	{
		return $this->identity;
	}
}
?>