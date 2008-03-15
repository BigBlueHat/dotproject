<?php
/**
 * A generic filter object for lists containing one or more columns/fields.
 * 
 * This object can contain multiple filter rules and multiple rules of the same operation.
 * The filter is usually applied when the data set is generated but could also be used with
 * already instantiated lists to iterate through matching items.
 * 
 * @package dotproject
 * @subpackage system
 * @todo Better retrieval method for filters, Better method of associating filters with views.
 * @todo Better method for converting filter rules to SQL
 */
class DP_Filter {
	/**
	 * Array of filter rules.
	 *
	 * @var array
	 */
	public $filters;
	
	const VALUE_EQUAL = 0;
	const VALUE_LT = 1;
	const VALUE_GT = 2;
	const VALUE_SUBSTR = 10;
	
	function __construct($id = -1) {
		$this->filters = Array();
	}
	
	/**
	 * Add a rule which filters the list by a field's value.
	 * 
	 * @param string $field_name Name of the field/column to filter.
	 * @param string $field_value Value the field must be equal to in order to pass the filter.
	 */
	function fieldEquals($field_name, $field_value) {
		$this->filters[] = Array("filter_type"=>DP_Filter::VALUE_EQUAL, "filter_field"=>$field_name, "field_value"=>$field_value);
	}
	
	/**
	 * Add a rule which filters the list by a field which contains the specified substring.
	 * 
	 * @param string $field_name Name of the field/column to filter.
	 * @param string $value_like The substring to use when comparing with the field's value.
	 */
	function fieldSubstring($field_name, $value_like) {
		$this->filters[] = Array("filter_type"=>DP_Filter::VALUE_SUBSTR, "filter_field"=>$field_name, "field_value"=>$value_like);
	}
	
	/**
	 * Retrieve the first filter acting upon a given field
	 * 
	 * @param string $field_name Name of the field being filtered.
	 * @return array Array containing the first rule which is acting on the specified field. 
	 */
	function getFilter($field_name) {
		foreach ($this->filters as $filter) {
			if ($filter['filter_field'] == $field_name) {
				return $filter;
			}
		}
	}
	
	/**
	 * Delete the first filter acting upon a given field
	 * 
	 * @param string $field_name Name of the field being filtered.
	 * @todo Implement method or use better criteria for retrieving filters.
	 */
	function deleteFilter($field_name) {
		
	}
	
	/**
	 * Delete all of the filter rules.
	 */
	function deleteAllRules() {
		$this->filters = null;
		$this->filters = Array();
	}
	
	/**
	 * Get the number of rules in this filter.
	 * 
	 * @return integer Number of rules
	 */
	function count() {
		return count($this->filters);
	}
	
	/**
	 * Get an iterator for this class.
	 * 
	 * @return DP_Filter_Iterator Filter iterator.
	 */
	function getIterator() {
		return new DP_Filter_Iterator($this);
	}
}
?>