<?php
/**
 * Model class for record pager.
 * 
 * This class keeps track of the current page and total number of items.
 * It is encapsulated within a DP_View_Pager to show a pager control.
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0 alpha
 *
 */
class DP_Pager implements SplSubject, DP_Originator_Interface {
	protected $id;
	
	protected $_observers;
	/**
	 * @var $pageitems The number of items appearing on a single page
	 */
	private $pageitems;
	/**
	 * @var $totalitems The total number of items
	 */
	private $totalitems;
	/**
	 * @var $page The current page.
	 */
	private $page;
	
	public function __construct($id = -1) {
		$this->id = $id;
		$this->_observers = Array();
		$this->page = 1;
	}
	
	/**
	 * Set the number of items to display per page
	 * 
	 * @param integer $pageitems The number of items per page.
	 */
	public function setItemsPerPage($pageitems) {
		$this->pageitems = $pageitems;
	}
	
	/**
	 * Get the number of items per page
	 * 
	 * @return integer Number of items per page
	 */
	public function itemsPerPage() {
		return $this->pageitems;
	}
	
	/**
	 * Set the total number of items available
	 * 
	 * @param integer $totalitems The total number of items available.
	 */
	public function setTotalItems($totalitems) {
		$this->totalitems = $totalitems;
	}
	
	public function totalItems() {
		return $this->totalitems;
	}
	
	/**
	 * Get the current selected page
	 * 
	 * @return integer The current page
	 */
	public function page() {
		return $this->page;
	}
	
	/**
	 * Set the current selected page.
	 * 
	 * @param integer $page The page number to set.
	 */
	public function setPage($page) {
		$this->page = $page;
		$this->notify();
	}
	
	/**
	 * Get the current pagecount.
	 * 
	 * @return integer number of pages.
	 */
	public function pageCount() {
		$numpages = $this->totalitems / $this->pageitems;
		if ($numpages - round($numpages) > 0) {
			return round($numpages)+ 1;		
		} else {
			return round($numpages);
		}
	}
	
	// From SplSubject Interface.
	
	/**
	 * Attach an observer
	 * 
	 * @param SplObserver $observer The observer to attach
	 * @return null
	 */
	public function attach (SplObserver $observer) {
		if (!in_array($observer, $this->_observers)) {
			$this->_observers[] = $observer;
			//$observer->update($this);
			// Observer only updated if something is changed
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
 	
	// From DP_Originator_Interface
 	
 	/**
	 * Restore internal state from a memento.
	 * 
	 * @param DP_Memento $m State memento.
	 */
	public function setMemento(DP_Memento $m) {
		$state = $m->getState();
		
		$this->page = $state['page'];
		$this->pageitems = $state['pageitems'];
		// Not sure if we really need to store totalitems, as it will be populated by a dynamic list every time.
		$this->totalitems = $state['totalitems'];
	}
	
	/**
	 * Create a memento containing a snapshot of the current internal state.
	 * 
	 * @return DP_Memento current state memento.
	 */
	public function createMemento() {
		$state = Array('page'=>$this->page,
					'pageitems'=>$this->pageitems,
					'totalitems'=>$this->totalitems);
		
		return new DP_Memento($state);
	}
}
?>