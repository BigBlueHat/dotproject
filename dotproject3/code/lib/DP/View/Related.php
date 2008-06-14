<?php
/**
 * Related items view.
 * 
 * Uses the relationships table to determine child lists to generate.
 * Provides child add/remove functionality.
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0 alpha
 *
 */
class DP_View_Related extends DP_View_Stateful {
		/**
		 * @var DP_Module_ActiveRecord_Abstract $parent_obj The parent object to fetch a child list for.
		 */
		protected $parent_obj;
		/**
		 * @var DP_List_Dynamic $child_list List of children in the current view.
		 */
		protected $child_list;
		
		public function __construct($id) {
			parent::__construct($id);
		}
		
		private function getChildren() {
			
		}
		
		
		private function buildView() {
			// Add child button
			// Remove selected child(ren) button
			// Search children
			// Tab box of child types.
		}
		
		
		public function setParent(DP_Module_ActiveRecord_Abstract $pobj) {
			$this->parent_obj = $pobj;
		}
		
		
		
		
		public function render() {
			
		}
}
?>