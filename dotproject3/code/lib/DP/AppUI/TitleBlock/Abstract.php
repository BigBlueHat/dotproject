<?php

require_once 'DP/AppUI.php';

/** Title block class, Generates module header
 *
 * The title block class generates the header which appears at the top of each module.
 * It includes the title of the module, the module icon, and links or buttons that can appear next to the module title
 */
class DP_AppUI_TitleBlock_Abstract {
/** The main title of the page */
	protected $title='';
/** The name of the icon used to the left of the title */
	protected $icon='';
/** The name of the module that this title block is displaying in */
	protected $module='';
/** An array of the table 'cells' to the right of the title block and for bread-crumbs */
	protected $cells=null;
/** The reference for the context help system */
	protected $helpref='';
	/** Template engine */
	protected $tpl = null;


	public function __construct($tpl)
	{
		$this->tpl = $tpl;
	}
 /** CTitleBlock_core constructor
	*
	* Assigns the title, icon, module and help reference.  If the user does not
	* have permission to view the help module, then the context help icon is
	* not displayed.
	* @param $title The large title displayed by the titleblock
	* @param $icon The icon displayed next to the title
	* @param $module The current module
	* @param $helpref The reference to this module in the help
	*/
	public function init( $title, $icon='', $module='', $helpref='' )
	{
		$this->title = $title;
		$this->icon = $icon;
		$this->module = $module;
		$this->helpref = $helpref;
		$this->cells1 = array();
		$this->cells2 = array();
		$this->crumbs = array();
		// $this->showhelp = !getDenyRead( 'help' );
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
     */
	function addFiltersCell($filters_selection)
	{
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
	}
	
	
	/** Add a cell that contains a search input box
	 *
	 * The text beside the search box is always "Search"
	 * 
	 * @return The search string last posted
	 */
	function addSearchCell()
	{
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

	/** Display the title block
	*/
	function show()
	{
		$AppUI = DP_AppUI::getInstance();
		if (empty($this->icon))
			$this->icon = 'stock_new.png';
		
		$this->tpl->assign('icon', $AppUI->findImage($this->icon, $this->module));
		$this->tpl->assign('title', $this->title);
		//$tpl->assign('module', $this->module);

		$this->tpl->assign('cells1', $this->cells1);
		$this->tpl->assign('cells2', $this->cells2);

		$this->tpl->assign('help', $this->helpref);
		
		if (count( $this->crumbs ) ) {
			$crumbs = array();
			foreach ($this->crumbs as $k => $v) {
				if ($v[1])
					$crumb['img'] = $AppUI->findImage( $v[1], $this->module );
				$crumb['name'] = $v[0];
				$crumb['link'] = $k;
				$crumbs[] = $crumb;
			}
			$this->tpl->assign('crumbs', $crumbs);
		} else {
			$this->tpl->assign('crumbs', array());
		}
		$this->tpl->assign('titleBlock', true);
	}

}

