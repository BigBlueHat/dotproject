<?php
/**
 * Dynamic list generates its data on demand using a
 * Query and a set of filter objects.
 * 
 * @package dotproject
 * @subpackage system
 * @version not.even.alpha
 */
class DP_List_Dynamic implements DP_View_List_Source_Interface, SplSubject, Countable {
	protected $query;
	protected $filter;
	protected $sort;
	protected $page;
	protected $list;
	
	public function __construct(DP_Query $query) {
		$this->query = $query;
		$this->filter = new DP_Filter();
		$this->page = 0;
		$this->sort = new DP_Query_Sort();
		$this->list = null;
	}
	
	/**
	 * Add a filter containing filter rules
	 * 
	 * The filter object will be used in generating the data for this dynamic list.
	 * 
	 * @param DP_Filter $filter The filter to use.
	 */
	public function addFilter($filter) {
		$this->filter = $filter;
	}
	
	/**
	 * Add a sort object containing sort rules
	 * 
	 * The sort object will be used in generating the data for this dynamic list.
	 * 
	 * @param DP_Query_Sort $sort The sort object to use.
	 */
	public function addSort($sort) {
		$this->sort = $sort;
	}
	
	/**
	 * Access the internal filter.
	 * 
	 * This method can be used to add or remove filter rules from the dynamic list by accessing the underlying filter.
	 * 
	 * @return DP_Filter The filter used to generate the data.
	 */
	public function filter() {
		return $this->filter;
	}
	
	/**
	 * Initialise the data by running the query with the filter to produce the rows.
	 */
	public function initData() {
		$this->list = DP_Object_Factory::getListFromFilteredQuery($this->query, Array($this->filter, $this->sort, $this->page));
	}
	
	/**
	 * Get the number of rows.
	 * 
	 * @return integer Number of rows.
	 */
	public function rowCount() {
		if ($this->list == null) {
			$this->initData();
		}
		
		return $this->list->rowCount();
	}
	
	/**
	 * Fetch a single row by its index (zero based)
	 * 
	 * @param integer $index The index of the row to fetch.
	 * @return array Hash containing the row data
	 */
	public function fetchRow($index) {
		if ($this->list == null) {
			$this->initData();
		}
		
		return $this->list->fetchRow($index);	
	}

		// From Countable
	
	public function count() {
		$full_list = $this->count_query->loadList();
		return count($full_list);
	}
	
	// From DP_View_List_Source_Interface
	
	public function getIterator() {
		return new DP_Iterator($this->object_list);
	}
	
	public function getColumns() {
		return $this->column_names;
	}
	
	// From SplSubject
	
	/**
	 * Attach an observer
	 * 
	 * @param SplObserver $observer The observer to attach
	 * @return null
	 */
	public function attach (SplObserver $observer) {
		if (!in_array($this->spl_observers, $observer)) {
			$this->spl_observers[] = $observer;
		}		
	}
	
	/**
	 * Detach an observer
	 * 
	 * @param SplObserver $observer The observer to detach
	 * @return null
	 */
 	public function detach (SplObserver $observer) {
 		if (in_array($this->spl_observers, $observer)) {
			$observer_key = array_search($this->spl_observers, $observer);
			$this->spl_observers[$observer_key] = null;
			
			$reordered_observers = array_values($this->spl_observers);
			$this->spl_observers = $reordered_observers;
		}		
 	}
 	
 	/**
 	 * Notify all observers
 	 * 
 	 */
 	public function notify () {
 		
 	}
}
?>