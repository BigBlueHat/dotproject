<?php
/**
 * A factory class for dotproject objects
 * 
 * The DP_Object_Creator class is a base Factory class for DP_Object(s).
 * It retrieves instances of DP_Object or subclasses of DP_Object initialised with attributes from
 * a database or from a html <form> POST method.
 * It stores and updates DP_Object based classes in the database.
 * 
 * @package dotproject
 * @subpackage system
 */
class DP_Object_Creator {
	
	/** string Name of the table in the db schema relating to child class */
	protected static $_tbl = '';
	/** string Name of the primary key field in the table */
	protected static $_tbl_key = '';	
	
	/**
	 * Retrieve an instance of DP_Module_Abstract by its ID number.
	 * 
	 * @param integer $id ID number of DP_Module_Abstract instance to create.
	 * @return DP_Module_Abstract Instance of DP_Module_Abstract propagated with values according to id $id.
	 */
	public static function getById($id) {
		// fetch object via dbquery
	}
	
	/**
	 * Retrieve one or more instances of DP_Module_Abstract by a specified database field value.
	 * 
	 * @param string $field_name Name of the database field
	 * @param mixed $field_value Value that the field must match
	 * @return array
	 */
	public static function getByFieldValue($field_name, $field_value) {
		// fetch object(s) via dbquery
	}
	
	/**
	 * Retrieve one or more instances of the object by a set of specified field and value hashes.
	 * 
	 * @param array $fields_hash Associative array of field name to field value.
	 * @return array 
	 */
	public static function getByFieldValues($fields_hash) {
		
	}
	
	/**
	 * Retrieve a list object using a DP_Query object.
	 * The list object will implement the listview source interface.
	 * 
	 * @param DP_Query $dpquery The query to use
	 * @return DP_List_Base Instance of DP_List_Base
	 */
	public static function getListFromQuery($dpquery) {
		$list = $dpquery->loadList();
		return new DP_List_Base($list);
	}
	
	/**
	 * Retrieve a list object using a constructed DP_Query and a set of filter objects.
	 * 
	 * @param DP_Query $dpquery query object being used to fetch the row list.
	 * @param array $filters Set of filters (new DP_Filter(), new DP_Query_Sort(), page_number)
	 * @todo Cleaner implementation of filter param.
	 * @return DP_List Instance of DP_List containing items produced by combining the query with the filter objects.
	 */
	public static function getListFromFilteredQuery($dpquery, $filters = Array()) {
		$query_filter = $filters[0]; // TODO - Define consts for these indexes.
		$query_sort = $filters[1];
		$page_number = $filters[2];
				
		foreach($query_filter->filters as $filter) {
			switch($filter['filter_type']) {
				case DP_Filter::VALUE_EQUAL:
					$dpquery->addWhere($filter['filter_field']." = ".$filter['field_value']);
					break;
				case DP_Filter::VALUE_SUBSTR:
					$dpquery->addWhere($filter['filter_field']." LIKE '%".$filter['field_value']."%'");
					break;
			}
		}
		
		foreach($query_sort->sorting_rules as $field => $sort_rule) {
			switch($sort_rule) {
				case DP_Query_Sort::SORT_DESCENDING:
					$dpquery->addOrder($field.' DESC');
					break;
				case DP_Query_Sort::SORT_ASCENDING:
					$dpquery->addOrder($field.' ASC');
					break;
			}
		}
		
		$list = $dpquery->loadList();
		return new DP_List($list);
	}
	
	/**
	 * Insert an object into the database.
	 * 
	 * @param object $obj The object to insert
	 * @return integer The ID number of the object inserted.
	 * @todo flesh out stub method
	 */
	public static function insert($obj) {

	}
	
	/**
	 * Store an object into the database.
	 * 
	 * This method determines whether it should call the internal method insert() or update().
	 * 
	 * @param object $obj The object to store
	 * @return integer The ID number of the object inserted.
	 * @todo flesh out stub method
	 */
	public static function store($obj) {
	}
	
	/**
	 * Delete an object from the database.
	 * 
	 * @param object $obj The object to delete.
	 * @todo flesh out stub method.
	 */
	public static function delete($obj) {
		// delete object
	}
}
?>