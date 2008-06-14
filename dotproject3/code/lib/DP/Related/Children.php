<?php
/**
 * An object representing a collection of objects related to one parent object.
 * 
 * The class can instantiate and provide views or lists of a requested child.
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0 alpha
 *
 */
class DP_Related_Children implements Countable {
	
	/**
	 * @var Zend_Db_Table_Rowset $child_list List of child relationships.
	 */
	protected $child_list;
	
	/**
	 * @var Zend_Db_Table_Row $parent Instance of parent row.
	 */
	protected $parent;
	
	public function __construct($child_list, $parent_object) {
		$this->child_list = $child_list;
		$this->parent = $parent_object;
	}
	
	// From Countable
	
	public function count() {
		return count($this->children);
	}
	
	public function getChildRowset() {
		return $this->child_list;
	}
	
	/**
	 * Instantiate and return a SubView with the given relationship ID.
	 * 
	 * The SubView handler specified by the relationship with $rel_id will be instantiated
	 * and passed a reference to the parent object, along with the relevant key value of the parent.
	 * 
	 * The child subview handler is responsible for generating the appropriate DP_View instance and returning it
	 * to this method. This method returns the view to be added to another view. 
	 * 
	 * @param mixed $rel_id ID of relationship.
	 */
	public function viewFactory($rel_id) {
		
	}
	
	
	
}
?>