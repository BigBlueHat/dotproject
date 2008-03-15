<?php
/**
 * Object representing a set of sorting rules for a list containing one or more fields.
 * 
 * @package dotproject
 * @subpackage system
 * @version not.even.alpha
 * 
 * @todo Possible rename to DP_Sort to fit in with naming of DP_Filter
 * @todo Use rule access methods instead of referring directly to internal sort rules variable.
 */
class DP_Query_Sort {
	/**
	 * Array of sorting rules.
	 *
	 * @var array $sorting_rules
	 */
	public $sorting_rules;
	
	const SORT_ASCENDING = 0;
	const SORT_DESCENDING = 1;
	
	public function __construct() {
		$this->sorting_rules = Array();
	}
	
	/**
	 * Sort the specified field in ascending order.
	 * 
	 * @param $field_name Name of the field to sort.
	 */
	function sortAscending($field_name) {
		$this->sorting_rules[$field_name] = this::SORT_ASCENDING;
	}
	
	/**
	 * Sort the specified field in descending order.
	 * 
	 * @param $field_name Name of the field to sort.
	 */
	function sortDescending($field_name) {
		$this->sorting_rules[$field_name] = this::SORT_DESCENDING;
	}
	
	/**
	 * Sort the specified field
	 * 
	 * If no direction is specified then the sort method assumes the ascending order.
	 * If sort is called again on the same field then the sort order will be reversed.
	 * 
	 * @param string $field_name Name of the field to sort.
	 * @param integer $direction Direction to sort, can be DP_Query_Sort::SORT_ASCENDING or DP_Query_Sort::SORT_DESCENDING
	 * @todo API to define the default sort order when no direction specified.
	 */
	function sort($field_name, $direction = null) {
		if ($direction == null) {
			if (isset($this->sorting_rules[$field_name])) {
				// TODO - could probably do this with a logical xor.
				$current_direction = $this->sorting_rules[$field_name];
				if ($current_direction == DP_Query_Sort::SORT_ASCENDING) {
					$this->sorting_rules[$field_name] = DP_Query_Sort::SORT_DESCENDING;
				} else {
					$this->sorting_rules[$field_name] = DP_Query_Sort::SORT_ASCENDING;
				}
			}
			else {
				$this->sorting_rules[$field_name] = DP_Query_Sort::SORT_ASCENDING;
			}
		} else {
			$this->sorting_rules[$field_name] = $direction;
		}
	}
}
?>