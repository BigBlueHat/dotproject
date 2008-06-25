<?php
/**
 * Interface used to define a data source for a DP_View
 * 
 * This interface must be implemented for any object to be used as a data source for
 * a view object rendering a collection of items.
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0 alpha
 * 
 */
interface DP_View_List_DataSource {

	/**
	 * Notify the datasource that the client is about to render its view.
	 * 
	 * This gives the datasource a chance to refresh its query before the items are iterated through.
	 */
	public function clientWillRender();
	
	/**
	 * Get an associative array containing keys of the column data to descriptive titles
	 * of that column.
	 * 
	 * @return Array associative array column_name=>column_title
	 */
	public function getColumns();
}
?>