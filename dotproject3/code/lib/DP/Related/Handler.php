<?php
/**
 * Interface for modules which can display a list of their objects which reference a specified parent object.
 */
interface DP_Related_Handler {
	
	/**
	 * Instantiate and return an instance of DP_View containing the related items.
	 * 
	 * @param Zend_Db_Table_Row $parent Parent object row.
	 * @param Zend_Db_Table_Row $relationship Row describing relevant parent relationship.
	 * @return DP_View Instance of DP_View.
	 */
	public function makeRelatedView($parent, $relationship);
}
?>