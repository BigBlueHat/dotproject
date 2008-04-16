<?php
/**
 * The pager view provides an interface to page a list of records.
 * 
 * @todo In future it could provide a field to set the number of records per page.
 * @package dotproject
 * @subpackage system
 */
class DP_View_Pager extends DP_View_Stateful implements SplSubject {
	/**
	 * @var array $_observers Array of observer objects implementing SplObserver
	 */
	protected $_observers;
	/**
	 * @var DP_Pager $pager Instance of pager
	 */
	protected $pager;
	/**
	 * @var PAGER_MODE_RANGES Display page links as ranges of record IDs
	 */
	const PAGER_MODE_RANGES = 1;
	/**
	 * @var PAGER_MODE_PAGES Display page links as page numbers
	 */
	const PAGER_MODE_PAGES = 2;
	/**
	 * @var PAGER_MODE_NEXTPREV Display next and prev buttons as well as a text input containing the page number.
	 */
	const PAGER_MODE_NEXTPREV = 3;
	
	/**
	 * Pager view constructor.
	 * 
	 * @param mixed $id The view's identifier.
	 * @param integer $pageitems The number of items per page.
	 * @param integer $totalitems The total number of items available.
	 * @return DP_View_Pager Instance of DP_View_Pager
	 */
	public function __construct($id) {
		parent::__construct($id);
		$this->_observers = Array();

		// Manage DP_Pager state
		$AppUI = DP_AppUI::getInstance();
		$this->pager = new DP_Pager($this->id().'-pager');
		$pager_state = $AppUI->getState($this->id().'-pager');
		
		if ($pager_state != null) {
			$this->pager->setMemento($pager_state);
		}		
	}
	
	// Pager view access methods
	
	/**
	 * Get the current instance of DP_Pager
	 * 
	 * @return DP_Pager Instance.
	 */
	public function getPager() {
		return $this->pager;
	}
	
	// Pager proxy methods
	
	/**
	 * Set the number of items to display per page
	 * 
	 * @param integer $pageitems The number of items per page.
	 */
	public function setItemsPerPage($pageitems) {
		$this->pager->setItemsPerPage($pageitems);
	}
	
	/**
	 * Get the number of items per page
	 * 
	 * @return integer Number of items per page
	 */
	public function itemsPerPage() {
		return $this->pager->itemsPerPage();
	}
	
	/**
	 * Set the total number of items available
	 * 
	 * @param integer $totalitems The total number of items available.
	 */
	public function setTotalItems($totalitems) {
		$this->pager->setTotalItems($totalitems);
	}
	
	/**
	 * Get the current selected page
	 * 
	 * @return integer The current page
	 */
	public function page() {
		return $this->pager->page();
	}
	
	/**
	 * Set the current selected page.
	 * 
	 * @param integer $page The page number to set.
	 */
	public function setPage($page) {
		$this->pager->setPage($page);
	}
	
	/**
	 * Get the current pagecount.
	 * 
	 * @return integer number of pages.
	 */
	public function pageCount() {
		return $this->pager->pageCount();
	}
	
	// View methods
	
	/**
	 * Render pager with specified style
	 * 
	 * Style is one of PAGER_MODE_RANGES, PAGER_MODE_PAGES or PAGER_MODE_NEXTPREV
	 */
	public function renderWithStyle($style) {
		switch ($style) {
			case DP_View_Pager::PAGER_MODE_PAGES:
				$output = $this->renderStylePages();
				break;
			case DP_View_Pager::PAGER_MODE_NEXTPREV:
				$output = $this->renderStyleNextPrev();
				break;
		}
		return $output;
	}
	
	public function render() {
		return $this->renderWithStyle(DP_View_Pager::PAGER_MODE_NEXTPREV);
	}
	
	/**
	 * Render a pager with next and previous buttons, and a page number box.
	 * 
	 * @return HTML output
	 */
	public function renderStyleNextPrev() {
		$numpages = $this->pageCount();
		$output = '<div align='.$this->align().' class="View_Pager">';
		$output .= '<form method="GET" name="form-pager">';
		$output .= '<input type="hidden" name="view_id" value="'.$this->id().'" />';
		
		// Only display pager if there are more than 1 page worth of items
		if ($this->pager->totalItems() > $this->pager->itemsPerPage()) {
			$output .= '&nbsp; <b>Page</b> ';
			
			if ($this->pager->page() > 1) {
				
				$output .= '<input type="button"
								   onClick="document.getElementById(\''.$this->id().'-page\').value = \''.($this->pager->page()-1).'\';this.form.submit();"	
								   value="&lt;" />';	
			}
			
			$output .='<input type="text" id="'.$this->id().'-page" name="page" size="5" style="text-align: center" value="'.$this->pager->page().'" />';
			
			if ($this->pager->page() != $numpages) {

				$output .= '<input type="button"
								   onClick="document.getElementById(\''.$this->id().'-page\').value = \''.($this->pager->page()+1).'\';this.form.submit();" 
								   value="&gt;" />';
			}
			
			$output .= ' of '.$numpages.' &nbsp;';
		}
		$output .= '</form>';
		$output .= '</div>';
		return $output;
	}
	
	/**
	 * Render a pager with a list of pages as individual links.
	 * 
	 * @return HTML Output
	 */
	public function renderStylePages() {
		$numpages = $this->pager->pageCount();
		$output = 'Page: ';
		
		for ($p = 1; $p <= $numpages; $p++) {
			//$pager_title = ($p*$this->pageitems-($this->pageitems-1)).'-'.($p*$this->pageitems)
			if ($p != $this->page) {
				$output .= '<a href="'.$this->url_prefix.'/view_id/'.$this->id().'/page/'.$p.'" style="margin-right:5px;">'.$p.'</a>';
			} else {
				$output .= ' &lt; '.$p.' &gt; ';
			}
		}
		
		return $output;
	}
	
	public function updateStateFromServer($request) {
		if ($request->view_id == $this->id && isset($request->page)) {
			$previous_page_state = $this->pager->page();
			$this->pager->setPage($request->page);
			
			$AppUI = DP_AppUI::getInstance();
			$AppUI->setState($this->id().'-pager', $this->pager->createMemento());
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