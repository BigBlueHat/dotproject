<?php
/**
 * An object representing one company
 * 
 * Implements the Active Record pattern.
 * 
 * @package companies
 * @subpackage models
 * @todo DP_Object_Base may be renamed to DP_Object (should be).
 * @deprecated Zend_Db_Table and Zend_Db_Rowset already offer this functionality. Can subclass later if need arises.
 */

class Company extends DP_Module_ActiveRecord_Abstract {
	
	public $company_id;
	public $company_name;
	public $company_phone1;
	public $company_phone2;
	public $company_fax;
	public $company_address1;
	public $company_address2;
	public $company_city;
	public $company_state;
	public $company_zip;
	public $company_primary_url;
	public $company_owner;
	public $company_description;
	public $company_type;
	public $company_email;
	// company_custom
	// company_module
	
	// Load methods
	
	/**
	 * Find and instantiate a collection of companies by one or more primary keys
	 * 
	 * @param Array $company_ids Company ids
	 */
	public static function find($company_ids) {
		$tbl = new Db_Table_Companies();
		$objs = $tbl->find($company_ids);
		return $objs;		
	}
	
	/**
	 * Instantiate one company from a rowset record
	 * 
	 * @param Object $row Rowset
	 */
	public static function load($rows) {
		$obj = new Company();
		$row = $rows->current();
		$rowarr = $row->toArray();
		foreach ($rowarr as $k => $v) {
			$obj->$k = $v;
		}
		return $obj;
	}
	
	/**
	 * Instantiate a company from a set of form values
	 * 
	 * @param Array $vars Form values
	 */
	public static function bind($vars) {
		$obj = new Company();

		$valid_vars = array_keys(get_class_vars('Company'));

		foreach ($vars as $k => $var) {
			if (in_array($k, $valid_vars)) {
				$obj->$k = $var;
			}
		}
		
		return $obj;
	}
	
	public function __construct() {
		$this->company_owner = 0;
		$this->company_type = 0;
	}
	
	// Modification methods
	
	public function insert() {
		$tbl = new Db_Table_Companies();
		$tbl->insert(get_object_vars($this));
	}
	
	public function update() {
		$tbl = new Db_Table_Companies();
		$where = $tbl->getAdapter()->quoteInto('company_id = ?', $this->company_id);
		$tbl->update(get_object_vars($this), $where);
	}
	
	public function delete() {
		$tbl = new Db_Table_Companies();
		$where = $tbl->getAdapter()->quoteInto('company_id = ?', $this->company_id);
		$tbl->delete($where);		
	}
 }
?>