<?php

require_once 'DP/AppUI.php';

/** Tabbed interface base class
 *
 * Provides a programmatical interface to generate a tabbed style interface
 */
class DP_AppUI_TabBox_Abstract
{
	/** Array of tabs */
	protected $tabs = null;
	/** The active tab */
	protected $active = null;
	/** The base URL query string to prefix tab links */
	protected $baseHRef = null;
	/** The base path to prefix the include file */
	protected $baseInc = null;
	/** A javascript function that accepts two arguments,
	the active tab, and the selected tab **/
	protected $javascript = null;
	/** The view class template engine */
	protected $tpl = null;
	protected $action_controller = null;
	protected $key = null;

	public function __construct(&$action_controller)
	{
		$this->tpl = $action_controller->getView();
		$this->action_controller = $action_controller;
		// Find tabs for each module.
		$this->tabs = array();
		$base_module = $this->action_controller->getRequest()->getModuleName();
		$base_controller = $this->action_controller->getRequest()->getControllerName();
		$base_action = $this->action_controller->getRequest()->getActionName();
		$modlist = DP_AppUI::getInstance()->getActiveModules();
		$this->baseHRef = '/'.$base_module.'/'.$base_controller.'/'.$base_action.'/?';
		// Make sure our base module comes first.
		$my_name = $modlist[$base_module];
		unset($modlist[$base_module]);
		$modlist = array($base_module => $my_name) + $modlist;
		foreach ($modlist as $mod => $ui_name) {
			if (($modclass = DP_Module::register($mod)) && $modclass->hasTabs()){
				$this->tabs += $modclass->getTabs($base_module, $base_controller, $base_action);
			}
		}
		$this->key = ucfirst($base_module) . ucfirst($base_controller) . 'IdxTab';
		// Check what tab is currently set to active
		$AppUI = DP_AppUI::getInstance();
		if (isset($this->action_controller->getRequest()->tab)) {
			$AppUI->setState($this->key, $this->action_controller->getRequest()->tab);
		}
		$this->active = $AppUI->getState($this->key, 0);
	}


	/**
	* Get the name of a tab
	* @return String containing the tabs name
	*/
	function getTabName( $idx )
	{
		return $this->tabs[$idx]['name'];
	}

	function getActiveTabName()
	{
		return $this->tabs[$this->active]['name'];
	}
 
	/** Find out if the tabbox is in tabbed mode (not flat mode)
	 * @return True if the tabbox is in tabbed mode
	 */
	function isTabbed() {
		if ($this->active < -1 || @DP_AppUI::getInstance()->getPref( 'TABVIEW' ) == 2 )
			return false;
		return true;
	}

	/** Set the actve tab index
	* @param integer $tab
	* @return void
	*/
	public function setActive($tab)
	{
		$this->active = $tab;
	}

	public function getActive()
	{
		return $this->active;
	}

	public function setTabKey($key)
	{
		$this->key = $key;
	}

	public function getActiveParam($key, $default = null)
	{
		if (isset($this->tabs[$this->active]['args']) && isset($this->tabs[$this->active]['args'][$key])) {
			return $this->tabs[$this->active]['args'][$key];
		}
		return $default;
	}


	 /** Display the tabbed box
	*
	* This function may be overridden
	* @param $extra Parameter deprecated, template does not contain {extra} variable
	* @param $js_tabs Defaults to false. Use javascript to show tabs
	*/
	function show($js_tabs = false)
	{
		reset( $this->tabs );
		$this->tpl->assign('current_tab', $this->baseHRef);
		$this->tpl->assign('totaltabs', count($this->tabs));
		$this->tpl->assign('tabs', $this->tabs);
		$this->tpl->assign('javascript', $this->javascript);
		$this->tpl->assign('js_tabs', $js_tabs);
		$this->tpl->assign('active', $this->active);
		$this->tpl->assign('tabview', DP_AppUI::getInstance()->getPref( 'TABVIEW' ));
		$this->tpl->assign('tabBox', true);
		// Now for each tab, if we are in JS mode we need to run the controller for all tabs
		// otherwise we just run it for the currently active tab
		if ($js_tabs) {
			foreach ($this->tabs as $tab) {
				$this->action_controller->appendRequest($tab, array('fromTab' => true));
			}
		} else if (isset($this->tabs[$this->active])) {
			$this->action_controller->appendRequest($this->tabs[$this->active]);
		} 
	}

}
