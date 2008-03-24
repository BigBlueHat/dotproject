<?php
/**
 * Interface used to define a data source for a DP_View_List
 * 
 * This interface must be implemented for any object to be used as a data source for
 * the DP_View_List object (list view).
 * 
 * @package dotproject
 * @subpackage system
 * @version not.even.alpha
 * @todo Possible rename to DP_View_List_Source (we already know its an interface :)
 * 
 */
interface DP_View_List_Source_Interface {

	/**
	 * Get an array of column names
	 */
	public function getColumns();
	
	/**
	 * Get an iterator for the data source
	 */
	public function getIterator();
}
?>