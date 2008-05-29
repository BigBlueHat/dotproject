<?php
/**
 * Static class to retrieve relationship definitions from the relationship table.
 * 
 * The DP_Related class checks the relationships table for anything containing the parent object's
 * originating table. The row(s) returned indicate which child module handlers should be loaded (instances of DP_Related_Handler).
 * These handlers are instantiated and asked to create DP_View objects based on their parent which get returned to the controller.
 *
 */

class DP_Related
{
	/**
	 * Get a related children object for a given parent row.
	 * 
	 * @param Zend_Db_Table_Row $parent Parent row.
	 * @return DP_Related_Children Instance of DP_Related_Children, populated with the child list.
	 */
	public static function findChildren($parent) {
		$related_tbl = new DP_Db_Related_Table();
		
		$parent_table = $parent->getTable();
		$parent_name = $parent_table->info(Zend_Db_Table_Abstract::NAME);
		$parent_key = $parent_table->info(Zend_Db_Table_Abstract::PRIMARY);
		
		$relationships = $related_tbl->fetchAll($related_tbl->select()->where('parent_key = ?', $parent_key)
																	  ->where('parent_tbl = ?', $parent_name)
		);
		
		//$children = new DP_Related_Children($relationships, $parent);
		
		return $relationships;
	}
	
	/**
	 * Instantiate a child view given a relationship row.
	 * 
	 * @param Zend_Db_Table_Row $relrow relationship row.
	 */
	public static function factory($parent, $relrow) {
		
		$fc = Zend_Controller_Front::getInstance();
		$cdir = $fc->getControllerDirectory($relrow->child_module);
		$handler_dir = dirname($cdir).'/models/Related';
		Zend_Loader::loadClass($relrow->child_viewhandler, $handler_dir);
		
		$subview_handler = new $relrow->child_viewhandler($relrow);
		
		if ($subview_handler instanceof DP_Related_Handler) {
			$subview = $subview_handler->makeRelatedView($parent, $relrow);
			return $subview;
		}
	}
}
?>