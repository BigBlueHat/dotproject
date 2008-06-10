<?php
/**
 * Tab box view.
 * 
 * Presents the user with multiple tabs. When a tab is selected, the tabBox invokes
 * the render() method of the child associated with that tab. 
 * 
 * @package dotproject
 * @subpackage system
 * @version not.even.alpha
 * @todo Tab box state handler
 * @todo Render only selected child unless TabBox is JS based
 */
class DP_View_TabBox extends DP_View_Stateful {
	/**
	 * @var array $tabs Array of tabs
	 */
	protected $tabs;
	/**
	 * @var integer $active_tab_index The index of the active tab.
	 */
	private $active_tab_index;
	
	public function __construct($id) {
		parent::__construct($id);
		
		$AppUI = DP_AppUI::getInstance();
		$this->tabs = Array();
		$this->active_tab_index = $this->loadState(0);
	}
	
	/**
	 * Add a view to the tab box.
	 * 
	 * A new tab will be created with the specified label.
	 * This method overrides DP_View::add() in order to provide
	 * the tab name.
	 * The tabBox will call render() on the selected child only.
	 * @todo Add a human name attribute to each DP_View. On adding the DP_View, the name becomes the default tab label.
	 * @todo This could also possibly be used in future to disable or enable specific views on their human name rather than ID.
	 */
	public function add(DP_View $view, $label = null) {
		if ($label == null) {
			// Create a default label for the view.
			$label = $view->Id();
		}
		
		$this->tabs[] = Array('view'=>$view, 'label'=>$label);
	}
	
	/**
	 * Check if the given index is the index of the selected tab.
	 * 
	 * @param integer $idx A tab index.
	 * @return bool True if the supplied index matches the selected tab, otherwise false.
	 */
	public function isSelected($idx) {
		if ($this->active_tab_index == $idx) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Get the index of the selected tab
	 * 
	 * @return integer Index of selected tab
	 */
	public function selectedTab() {
		return $this->active_tab_index;
	}
	
	/**
	 * Set selected tab
	 * 
	 * @param integer $index The index of the selected tab
	 */
	public function setSelectedTab($index) {
		$this->active_tab_index = 0;
	}
	
	/**
	 * Render the tabBox view
	 * 
	 * Only the selected child is rendered
	 * 
	 * @return string HTML output
	 */
	public function render() {
		$output = '<div id='.$this->id().'>';
		// Output tabs
		$output .= '
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<tr><td>
				<table border="0" cellpadding="0" cellspacing="0">
				<tr height="28">';
		
		foreach ($this->tabs as $idx => $tab) {
			$tab_decoration_left = ($this->isSelected($idx)) ? 'tabSelectedLeft.png' : 'tabLeft.png';
			$tab_decoration_right = ($this->isSelected($idx)) ? 'tabSelectedRight.png' : 'tabRight.png';
			$tab_class = ($this->isSelected($idx)) ? 'tabon' : 'taboff';
			
			$output .= '
				<td valign="middle" width="3">
					<img src="/img/default/'.$tab_decoration_left.'" width="3" height="28" border="0" alt="" />
				</td>
				<td id="toptab_'.$idx.'" valign="middle" nowrap="nowrap" class="'.$tab_class.'">
					<a href="?view_id='.$this->id().'&tab='.$idx.'">'.$tab['label'].'</a>
				</td>
				<td valign="middle" width="3">
					<img src="/img/default/'.$tab_decoration_right.'" width="3" height="28" border="0" alt="" />
				</td>
				<td width="3" class="tabsp">
					<img src="/img/shim.gif" alt="shim" height="1" width="3" />
				</td>
			';
		}

		$output .= '
				</tr>
				</table>
			</td></tr><tr><td colspan="'.$this->tabCount().'" class="tabox">';
		
		// Output selected child
		$output .= $this->renderTab($this->active_tab_index);
		$output .= '</td></tr></table>';	
		$output .= '</div>';
				
		return $output;
	}
	
	/**
	 * Render the view associated with a specified index.
	 * 
	 * @param integer $idx The tab index
	 * @return string HTML Output of the associated DP_View
	 */
	protected function renderTab($idx) {
		if ($idx < $this->tabCount() && $idx >= 0) {
			return $this->tabs[$idx]['view']->render();
		}
	}
	
	/**
	 * Set the prefix to use with tab links
	 * 
	 * @param string $url_prefix tab link prefix
	 */
	public function setUrlPrefix($url_prefix) {
		$this->url_prefix = $url_prefix;
	}
	
	/**
	 * Get the number of tabs in this tabBox
	 * 
	 * @return integer Number of tabs.
	 */
	public function tabCount() {
		return count($this->tabs);
	}
	
	protected function notifyChildrenWillRender($view) {
		if (array_key_exists($this->active_tab_index, $this->tabs)) {
			$this->tabs[$this->active_tab_index]['view']->viewWillRender($view);
		}
	}
	
	/**
	 * Update child tab views with request object.
	 */
	public function updateChildrenFromServer($request) {
		$tab = $this->tabs[$this->active_tab_index];
		if ($tab['view'] instanceof DP_View_Stateful) {
			$tab['view']->updateStateFromServer($request);
		}
	}
	
	/**
	 * Update the tab box from the request object.
	 * 
	 * @see DP_View_Stateful::updateStateFromServer()
	 */
	public function updateStateFromServer($request) {
		if ($request->view_id == $this->id()) {
			if (isset($request->tab)) {
				$this->active_tab_index = $request->tab;
			}
		}
		$this->saveState($this->active_tab_index);
		$this->updateChildrenFromServer($request);
	}
}
?>