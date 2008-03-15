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
	 * Get the number of columns.
	 * 
	 * @return integer Number of columns required.
	 */
	
	//public function columnCount();
	//public function columnHeaders(); 
	
	/**
	 * Get the number of rows.
	 * 
	 * @return integer Number of rows.
	 */
	public function rowCount();
	
	/**
	 * Fetch a single row by its index (zero based)
	 * 
	 * @param integer $index The index of the row to fetch.
	 * @return array Hash containing the row data
	 */
	public function fetchRow($index);
}
?>