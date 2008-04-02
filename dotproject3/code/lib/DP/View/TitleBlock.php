<?php
class DP_View_TitleBlock extends DP_View {
	protected $title;
/** The name of the icon used to the left of the title */
	protected $icon;
/** The name of the module that this title block is displaying in */
	protected $module;
/** An array of the table 'cells' to the right of the title block and for bread-crumbs */
	protected $cells;
/** The reference for the context help system */
	protected $helpref;	
	
	public function __construct($id, $title = '', $icon = '', $module = '', $helpref = '') {
		parent::__construct($id);
		$this->title = $title;
		$this->icon = $icon;
		$this->module = $module;
		$this->helpref = $helpref;
		$this->cells1 = array();
		$this->cells2 = array();
		$this->crumbs = array();
	}
	
 /** Add a cell beside the title
	*
	* Cells are added from left to right.
	* @param $data HTML to add in this cell
	* @param $attribs Extra attributes to add to this cells TD element
	* @param $prefix HTML to add before the TD element
	* @param $suffix HTML to add after the TD element
	*/
	function addCell( $data='', $attribs='', $prefix='', $suffix='' )
	{
		$this->cells1[] = array( $attribs, $data, $prefix, $suffix );
	}
	
	/** Add a cell that contains a dropdown list of filter criteria
	 *
	 * The $filters_selection parameter contains an associative array of "filtername"=>(Array of filter options)
	 *
	 * @param $filters_selection Associative array containing filter list
	 * @return Associative array of filters applied, using the filter name as the key
	 * @deprecated Each view has its own associated filters
     */
	function addFiltersCell($filters_selection)
	{
		/*
		$AppUI = DP_AppUI::getInstance();

		foreach($filters_selection as $filter => $array)
		{
			if(isset($_REQUEST[$filter])){
				$AppUI->setState($filter, $_REQUEST[$filter]);
				$filters[$filter] = $_REQUEST[$filter];
			} else {
				$filters[$filter] = $AppUI->getState($filter);
				if (! isset($filter)) {
					$filters[$filter] = (strpos($filter, 'owner') > 0)?$AppUI->user_id:0;
					$AppUI->setState($filter, $filters[$filter]);
				}
			}
			
			if (isset($array[0])) {
				$list = $array;
			} else {
				$list = array(0 => $AppUI->_("All", UI_OUTPUT_RAW)) + (array)$array;
			}
			$filters_combos[str_replace('_', ' ', substr($filter, strpos($filter, '_') + 1))] = arraySelect($list, $filter, 'class="text" onchange="javascript:document.filtersform.submit()"', $filters[$filter], false);
		}
		
		$this->tpl->assign('filters', $filters_combos);
		$this->tpl->assign('post_url', str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
		$data = $this->tpl->fetch('filters.html');
		$this->cells1[] = array('', $data, '', '');
		
		return $filters;
		*/
	}
	
	
	/** Add a cell that contains a search input box
	 *
	 * The text beside the search box is always "Search"
	 * 
	 * @return The search string last posted
	 * @deprecated Each view has its own associated search box
	 */
	function addSearchCell()
	{
		/*
		$AppUI = DP_AppUI::getInstance();
		
		$search_string = dPgetParam( $_REQUEST, 'search_string', "" );
		if($search_string != ""){
			$search_string = $search_string == "-1" ? "" : $search_string;
			$AppUI->setState("search_string", $search_string);
		} else {
			$search_string = $AppUI->getState("search_string");
		}
		
		$this->tpl->assign('search_string', $search_string);
		$data = $this->tpl->fetch('search.html');
		$this->cells1[] = array('', $data, '', '');
		
		return addslashes($search_string);
		*/
	}

	/** Add a left aligned link to the title block 
	*
	* dotProject calls this a titleblock "crumb"
	* Cells are added from left to right.
	* @param $link URL to link to
	* @param $label Label to use for the link
	* @param $icon Defaults to none, URL of an icon to place beside the link
	*/
	function addCrumb( $link, $label, $icon='' )
	{
		$this->crumbs[$link] = array( $label, $icon );
	}
	
	/** Add a right aligned link to the title block 
	*
	* dotProject calls this a titleblock "crumb"
	* @param $data HTML to add in this cell
	* @param $attribs Extra attributes to add to this cells TD element
	* @param $prefix HTML to add before the TD element
	* @param $suffix HTML to add after the TD element
	*/
	function addCrumbRight( $data='', $attribs='', $prefix='', $suffix='' )
	{
		$this->cells2[] = array( $attribs, $data, $prefix, $suffix );
	}

	/** Create a standard delete link to delete the current record
	 *
	 * Automatically adds itself using the method CAppUI::addCrumbRight()
	 *
	 * @param $title Title of the button
	 * @param $canDelete Boolean, if false will display an icon indicating the user has no permission to delete
	 * @param $msg Displayed as the title attribute of the delete link
	 */
	function addCrumbDelete( $title, $canDelete='', $msg='' )
	{
		$this->tpl->assign('title', $title);
		$this->tpl->assign('canDelete', $canDelete);
		if ($canDelete)
			$this->tpl->assign('msg', ''.$msg);
		else
			$this->tpl->assign('msg', '');
			
		$this->addCrumbRight($this->tpl->fetch('crumbDelete.html'));
	}
	
	
	function render() {
		$output = '<table width="100%" border="0" cellpadding="1" cellspacing="1">';
		$output .= '<tr>';
		if ($this->icon != '') {
			$output .= '<td width="42"><img src="'.$this->icon.'" /></td>';
		}
		
		$output .= '<td align="left" width="100%" nowrap="nowrap">';
		if ($this->title) {
			// @todo Translate title
			$output .= '<h1>'.$this->title.'</h1>';
		}
		$output .= '</td>';
		
		foreach ($this->cells1 as $cell) {
			$output .= $cell[2];
			$output .= '<td align="right" nowrap="nowrap"'.$cell[0].'>';
			$output .= $cell[1];
			$output .= '</td>';
			$output .= $cell[3];
		}
		
		if ($this->helpref) {
			$output .= '<td nowrap="nowrap" width="20" align="right">
				<a href="#'.$this->helpref.'" 
					onclick="javascript:window.open(\'/help/view/hid/'.$this->helpref.'\', \'contexthelp\', \'width=400, height=400, left=50, top=50, scrollbars=yes, resizable=yes\')" 
					title="translate:Help">
					<img src="/images/icons/stock_help-16.png" width="16" height="16" alt="?" />
				</a>
			</td>';
		}
		
		$output .= '</tr></table>';
		
		if (count($this->cells2) > 0 || count($this->crumbs) > 0) {
			$output .= '<table border="0" cellpadding="4" cellspacing="0" width="100%">
						<tr>
						<td nowrap="nowrap">';
			
			foreach ($this->crumbs as $crumb) {
				$output .= '<strong>
						  	<a href="'.$crumb['link'].'">
							<img src='.$crumb['img'].' />'.$crumb['name'].'
							</a></strong>';		
			}
			
			$output .= '</td>';
			
			foreach ($this->cells2 as $cell) {
				$output .= $cell[2];
				$output .= '<td align="right" nowrap="nowrap"'.$cell[0].'>';
				$output .= $cell[1];
				$output .= '</td>';
				$output .= $cell[3];				
			}
			
			$output .= '</tr></table>';
		}
		
		return $output;
	}
	
}
?>