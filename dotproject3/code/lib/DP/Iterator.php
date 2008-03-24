<?php
/**
 * Simple array iterator class
 * 
 * Provides a simple array iterator as a base class for more complex iterators.
 *
 * @package dotproject
 * @subpackage system
 * @version not.even.alpha
 */
class DP_Iterator {
	private $data;
	private $index;
	
	public function __construct($data) {
		$this->data = $data;
		$this->index = 0;
	}
	
	/**
	 * Rewind the iterator to the first item.
	 */
	public function first() {
		$this->index = 0;	
	}
	
	/**
	 * Move the iterator forward one item.
	 */
	public function next() {
		$this->index++;
	}
	
	/**
	 * Check if the iterator has gone through all of the items.
	 * 
	 * @return bool Status of iterator.
	 */
	public function isDone() {
		if ($this->index >= count($this->data)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Get the current item.
	 * 
	 * @return mixed The current item.
	 */
	public function currentItem() {
		if (isset($this->data[$this->index])) {
			return $this->data[$this->index];
		} else {
			return null;
		}
	}
	
	/**
	 * Get the number of items
	 */
	public function count() {
		return count($this->data);
	}
}
?>