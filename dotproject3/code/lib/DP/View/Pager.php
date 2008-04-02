<?php
/**
 * The pager view provides an interface to page a list of records.
 * 
 * @todo In future it could provide a field to set the number of records per page.
 * @package dotproject
 * @subpackage system
 */
class DP_View_Pager extends DP_View {
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
	
	const PAGER_DISPLAY_RANGE = 1;
	
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
	}
	
	public function setItemsPerPage($pageitems) {
		$this->pageitems = $pageitems;
	}
	
	public function setTotalItems($totalitems) {
		$this->totalitems = $totalitems;
	}
	
	/*
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
			$output .= '<a href="javascript:setPage(x)" style="margin-right:5px;">'.$p.'</a>';
		}
		
		return $output;
	}
	
	public function updateWithServer($request) {
		if ($request->view_id == $this->id && isset($request->page)) {
			$this->page = $request->page;
		}
	}
	
}
?>