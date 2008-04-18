<?php
/**
 * Abstract ActiveRecord class, implements ActiveRecord pattern.
 *
 */
abstract class DP_Module_ActiveRecord_Abstract {

	// Load methods
	
	/**
	 * Find and instantiate a collection of objects by one or more primary keys
	 * 
	 * @param Array $ids object ids
	 */
	abstract public static function find($ids);
	
	/**
	 * Instantiate one object from a rowset record
	 * 
	 * @param Object $row Rowset
	 */
	abstract public static function load($rows);
	
	/**
	 * Instantiate an object from a set of form values
	 * 
	 * @param Array $vars Form values
	 */
	abstract public static function bind($vars);
	
	// Modification methods
	
	/**
	 * Insert this object.
	 */
	abstract public function insert();
	
	/**
	 * Update this object.
	 */
	abstract public function update();
	
	/**
	 * Delete this object.
	 */
	abstract public function delete();
}
?>