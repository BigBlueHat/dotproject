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
class DP_Query_Sort implements Iterator, SplSubject {
	protected $_observers;
	/**
	 * Array of sorting rules.
	 *
	 * @var array $sorting_rules
	 */
	public $sorting_rules;
	/**
	 * @var array $sorting_priority Array of field names in the order they should be sorted.
	 */
	protected $sorting_priority;
	/**
	 * @var integer $iter_index Index of the iterator
	 */
	protected $iter_index;
	
	/**
	 * @var SORT_ASCENDING Sort a given field in ascending order.
	 */
	const SORT_ASCENDING = 0;
	/**
	 * @var SORT_DESCENDING Sort a given field in descending order.
	 */
	const SORT_DESCENDING = 1;
	
	public function __construct() {
		$this->_observers = Array();
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
		$this->sort($field_name, this::SORT_ASCENDING);
	}
	
	/**
	 * Sort the specified field in descending order.
	 * 
	 * @param $field_name Name of the field to sort.
	 */
	function sortDescending($field_name) {
		$this->sort($field_name, this::SORT_DESCENDING);
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
				$existing_rule = Array($field_name => $this->sorting_rules[$field_name]);
				unset($this->sorting_priority[$i]);
			}
		}
		
		// Add the field to the top of the order list
		if (count($this->sorting_priority > 0 && is_array($this->sorting_priority))) {
			$old_values = array_values($this->sorting_priority);
			
			$this->sorting_priority = array_merge(Array($field_name), $old_values);
		} else {
			$this->sorting_priority[] = $new_rule;
		}
		
		// Sort the field descending unless it already exists, then reverse its sorting order.
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
	
	// From Iterator
	
	public function current() {
		$sort_field = $this->sorting_priority[$this->iter_index];
		return $this->sorting_rules[$sort_field];
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

	// From SplSubject
	
	/**
	 * Attach an observer
	 * 
	 * @param SplObserver $observer The observer to attach
	 * @return null
	 */
	public function attach(SplObserver $observer) {
		if (!in_array($observer, $this->_observers)) {
			$this->_observers[] = $observer;
			$observer->update($this);
		}		
	}
	
	/**
	 * Detach an observer
	 * 
	 * @param SplObserver $observer The observer to detach
	 * @return null
	 */
 	public function detach (SplObserver $observer) {
 		if (in_array($observer, $this->_observers)) {
			$observer_key = array_search($this->_observers, $observer);
			$this->_observers[$observer_key] = null;
			
			$reordered_observers = array_values($this->_observers);
			$this->_observers = $reordered_observers;
		}		
 	}
 	
 	/**
 	 * Notify all observers
 	 * 
 	 */
 	public function notify() {
 		foreach($this->_observers as $ob) {
 			$ob->update($this);
 		}
 	}

}
?>