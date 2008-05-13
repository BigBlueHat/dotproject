<?php
/**
 * An object representing one contact record.
 * 
 * @package dotproject
 * @subpackage contacts
 * @version 3.0 alpha
 * @deprecated Zend_Db_Table and Zend_Db_Rowset already provides this functionality. May be subclassed later if need arises.
 *
 */
class Contact extends DP_Module_ActiveRecord_Abstract {
	
	public $contact_id;
	public $contact_first_name;
	public $contact_last_name;
	public $contact_order_by;
	public $contact_title;
	public $contact_birthday;
	public $contact_job;
	public $contact_company;
	public $contact_department;
	public $contact_type;
	public $contact_email;
	public $contact_email2;
	public $contact_url;
	public $contact_phone;
	public $contact_phone2;
	public $contact_fax;
	public $contact_mobile;
	public $contact_address1;
	public $contact_address2;
	public $contact_city;
	public $contact_state;
	public $contact_zip;
	public $contact_country;
	public $contact_jabber;
	public $contact_icq;
	public $contact_msn;
	public $contact_yahoo;
	public $contact_aol;
	public $contact_notes;
	public $contact_project;
	public $contact_icon;
	public $contact_owner;
	public $contact_private;
	
	public function __construct() {
		$this->contact_owner = 0;
		$this->contact_type = 0;
	}
	
	/**
	 * Find and instantiate a collection of objects by one or more primary keys
	 * 
	 * @param Array $ids object ids
	 */
	public static function find($ids) {
		$tbl = new Db_Table_Contacts();
		$objs = $tbl->find($ids);
		return $objs;		
	}
	
	/**
	 * Instantiate one object from a rowset record
	 * 
	 * @param Object $row Rowset
	 */
	public static function load($rows) {
		$obj = new Contact();
		$row = $rows->current();
		$rowarr = $row->toArray();
		foreach ($rowarr as $k => $v) {
			$obj->$k = $v;
		}
		return $obj;
	}
	
	/**
	 * Instantiate an object from a set of form values
	 * 
	 * @param Array $vars Form values
	 */
	public static function bind($vars) {
		$obj = new Contact();

		$valid_vars = array_keys(get_class_vars('Contact'));

		foreach ($vars as $k => $var) {
			if (in_array($k, $valid_vars)) {
				$obj->$k = $var;
			}
		}
		
		return $obj;
	}
	
	// Modification methods
	
	/**
	 * Insert this object.
	 */
	public function insert() {
		$tbl = new Db_Table_Contacts();
		$tbl->insert(get_object_vars($this));		
	}
	
	/**
	 * Update this object.
	 */
	public function update() {
		$tbl = new Db_Table_Contacts();
		$where = $tbl->getAdapter()->quoteInto('contact_id = ?', $this->contact_id);
		$tbl->update(get_object_vars($this), $where);		
	}
	
	/**
	 * Delete this object.
	 */
	public function delete() {
		$tbl = new Db_Table_Contacts();
		$where = $tbl->getAdapter()->quoteInto('contact_id = ?', $this->contact_id);
		$tbl->delete($where);				
	}
}
?>