<?php
/**
 * Represents a certain presentation of a datasource.
 * 
 * For instance a datasource may be presented as a json notation object, an array, or as an xml document.
 * The presentation object provides a layer of translation from a datasource into the target format.
 * 
 * The presentation object also determines what parts of the result set are shown.
 * 
 * @package dotproject
 * @version 3.0 alpha
 * @author ebrosnan
 */
class DP_Presentation
{
	public function __construct() 
	{
		
	}
	
	public function getDefaultFields()
	{
		// TODO - extract table metadata to display all fields
		return $this->default_columns;
	}
	
	/**
	 * Translate a result set into the desired presentation format.
	 * 
	 * @param Zend_Db_Rowset $result_set Array of rows containing query result.
	 * @return Zend_Db_Rowset Array of rows containing query result.
	 */
	public function translate($result_set)
	{
		return $result_set;
	}
}
?>