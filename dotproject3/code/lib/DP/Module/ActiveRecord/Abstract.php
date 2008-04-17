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
	public static function find($ids);
	
	/**
	 * Instantiate one object from a rowset record
	 * 
	 * @param Object $row Rowset
	 */
	public static function load($rows);
	
	/**
	 * Instantiate an object from a set of form values
	 * 
	 * @param Array $vars Form values
	 */
	public static function bind($vars);
	
	// Modification methods
	
	/**
	 * Insert this object.
	 */
	public function insert();
	
	/**
	 * Update this object.
	 */
	public function update();
	
	/**
	 * Delete this object.
	 */
	public function delete();
}
?>