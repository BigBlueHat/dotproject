<?php
/**
 * The pager view provides an interface to page a list of records.
 * 
 * @todo In future it could provide a field to set the number of records per page.
 * @package dotproject
 * @subpackage system
 */
class DP_View_Pager extends DP_View_Stateful {
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
	
	/**
	 * @var PAGER_MODE_RANGES Display page links as ranges of record IDs
	 */
	const PAGER_MODE_RANGES = 1;
	/**
	 * @var PAGER_MODE_PAGES Display page links as page numbers
	 */
	const PAGER_MODE_PAGES = 2;
	
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
		$this->page = $this->loadState(1);
		$this->totalitems = 100;
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
	
	/**
	 * Get the current selected page
	 * 
	 * @return integer The current page
	 */
	public function page() {
		return $this->page;
	}
	
	/**
	 * Render the pager view
	 * @return HTML Output
	 */
	public function render() {
		$numpages = $this->totalitems / $this->pageitems + 1;
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
			$this->page = $request->page;
		}
		
		$this->saveState($this->page);
	}
	
}
?>