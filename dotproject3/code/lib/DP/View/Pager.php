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
		$this->page = $this->loadState(1);
		//$this->totalitems = 100;
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
	
	public function pageCount() {
		// TODO - this is wrong in some circumstances, check with DP stable code
		$numpages = $this->totalitems / $this->pageitems;
		if ($numpages - round($numpages) > 0) {
			return round($numpages)+ 1;		
		} else {
			return round($numpages);
		}
	}
	
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
	
	public function renderStyleNextPrev() {
		$numpages = $this->pageCount();
		$output = '<div align='.$this->align().' class="View_Pager">';
		$output .= '<form method="GET">';
		$output .= '<input type="hidden" name="view_id" value="'.$this->id().'" />';

		// Only display pager if there are more than 1 page worth of items
		if ($this->totalitems > $this->pageitems) {
			$output .= '&nbsp; <b>Page</b> ';
			
			if ($this->page() > 1) {
				
				$output .= '<input type="button"
								   onClick="document.getElementById(\''.$this->id().'-page\').value = \''.($this->page()-1).'\';this.form.submit();"	
								   value="&lt;" />';	
			}
			
			$output .='<input type="text" id="'.$this->id().'-page" name="page" size="5" style="text-align: center" value="'.$this->page().'" />';
			
			if ($this->page() != $numpages) {

				$output .= '<input type="button"
								   onClick="document.getElementById(\''.$this->id().'-page\').value = \''.($this->page()+1).'\';this.form.submit();" 
								   value="&gt;" />';
			}
			
			$output .= ' of '.$numpages.' &nbsp;';
		}
		$output .= '</form>';
		$output .= '</div>';
		return $output;
	}
	
	/**
	 * Render the pager view
	 * @return HTML Output
	 */
	public function renderStylePages() {
		$numpages = $this->pageCount();
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