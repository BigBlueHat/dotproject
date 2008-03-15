<?php
/**
 * Iterator class for DP_Filter objects.
 * 
 * The iterator is constructed by the DP_Filter class using its getIterator() method.
 * @see DP_Filter::getIterator()
 *
 */
class DP_Filter_Iterator {
	
	protected $filter;
	private $index;
	
	public function __construct(DP_Filter $filter) {
		$this->filter = $filter;
	}
	
	/**
	 * Set the iterator's cursor back to the first item.
	 */
	public function first() {
		$this->index = -1;
	}
	
	/**
	 * Get the next item.
	 * 
	 * @return mixed The next item.
	 */
	public function nextItem() {
		$this->index++;
		if ($this->index < $this->filter->count()) {
			return $this->filter[$this->index];
		} else {
			return null;
		}
	}
	
	/**
	 * Check if the iterator has gone through all of the items.
	 * 
	 * @return boolean TRUE if the iterator has gone past the last item, FALSE if not.
	 */
	public function isDone() {
		if ($this->index >= $this->filter->count()) {
			return true;
		} else {
			return false;
		}
	}
	
}
?>