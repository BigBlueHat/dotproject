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
class DP_Query_Sort implements Iterator {
	/**
	 * Array of sorting rules.
	 *
	 * @var array $sorting_rules
	 */
	public $sorting_rules;
	protected $sorting_priority;
	protected $iter_index;
	
	const SORT_ASCENDING = 0;
	const SORT_DESCENDING = 1;
	
	public function __construct() {
		$this->sorting_rules = Array();
		$this->sorting_priority = Array();
		$this->iter_index = 0;
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
		// Take this field out of the sorting order if it already exists
		for ($i = 0; $i < count($this->sorting_priority); $i++) {
			if ($this->sorting_priority[$i] == $field_name) {
				$this->sorting_priority[$i] = null;
			}
		}

		// Add the field to the top of the order list
		if (count($this->sorting_priority > 0 && is_array($this->sorting_priority))) {
			$old_values = array_values($this->sorting_priority);
			$this->sorting_priority = array_merge(Array($field_name));
		} else {
			$this->sorting_priority[] = $field_name;
		}
		
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
	
	// From Zend_Engine Iterator
	public function current() {
		return $this->sorting_rules[$this->sorting_priority[$this->iter_index]];
	}
	
	public function key() {
		return $this->sorting_priority[$this->iter_index];
	}
	
	public function next() {
		$this->iter_index++;
	}
	
	public function rewind() {
		$this->iter_index = 0;
	}
	
	public function valid() {
		if ($this->iter_index < count($this->sorting_priority)) {
			return true;
		} else {
			return false;
		}
	}
}
?>