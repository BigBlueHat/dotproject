<?php
require_once 'smarty/Smarty.class.php';
require_once 'DP/AppUI.php';
require_once 'DP/Base.php';

/**
 * Smarty template glue class to allow Zend_View to use Smarty in
 * a sane way.
 *
 * @license	http://www.fsf.org/licensing/licenses/gpl.html GNU General Public License v3
 *
 */
class DP_Template extends Smarty implements Zend_View_Interface
{
	/**
	 * Smarty instance to provide output rendering.
	 * @var Smarty
	 */
	protected $_smarty = null;
	/**
	 * Page number for paginated output.
	 * @var integer
	 */
	protected $page;
	/**
	 * Flag to suppress output of header and footer.
	 * @var boolean
	 */
	protected $_suppress_headers = false;
	/**
	 * Current module being processed.
	 * @var string
	 */
	protected $mod = null;

	/**
	 * Constructor
	 *
	 * Instantiates Smarty instance and sets default behaviour.
	 *
	 * @return void
	 */
	public function __construct($path = null, $params = array())
	{
		$ui = DP_AppUI::getInstance();
		$this->mod = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
		$this->_smarty = new Smarty();
		$this->_smarty->template_dir = array(
			DP_BASE_CODE.'/modules/'.$this->mod.'/views/style/'.(isset($ui->style) ? $ui->style : 'default'),
			DP_BASE_CODE.'/modules/'.$this->mod.'/views/style',
			DP_BASE_CODE.'/style/'.(isset($ui->style) ? $ui->style : 'default'),
			DP_BASE_CODE.'/style/_smarty/'.$this->mod,
			DP_BASE_CODE.'/style/_smarty'
		);
		$this->_smarty->compile_dir = DP_BASE_DIR . '/files/cache/smarty_templates';
		$this->_smarty->cache_dir = DP_BASE_DIR . '/files/cache/smarty';
		$this->_smarty->plugins_dir[] = DP_BASE_CODE . '/lib/plugins';
		// Load the basics that are required for plugin functions
		$this->init();
	}

	/**
	 * Interface method to get underlying template engine
	 *
	 * @return Smarty
	 */
	public function getEngine()
	{
		return $this->_smarty;
	}

	/**
	 * This defines extra paths as used by the view controller.
	 * We simply adjust the smarty template path to include this.
	 * This uses the little documented feature of smarty that template_dir can be an array.
	 *
	 * @param string $path
	 * @return void
	 */
	public function setScriptPath($path)
	{
		error_log('DP_Template::setScriptPath(' . $path . ')');
		$paths = $this->getScriptPaths();
		if (is_array($paths) && in_array($path, $paths)) {
			return;
		} else {
			// Search the new path before the default.
			array_unshift($this->_smarty->template_dir, $path);
		}
	}

	/**
	 * Get currently set script path.
	 *
	 * Required interface method.
	 *
	 * @return string|array
	 */
	public function getScriptPaths()
	{
		return $this->_smarty->template_dir;
	}

	/**
	 *  Set Base script search path.
	 *
	 * Required interface method, currently calls setScriptPath
	 *
	 * @param string $path
	 * @param string $prefix Optional parameter to set search prefix
	 * @return void
	 */
	public function setBasePath($path, $prefix = 'Zend_View')
	{
		$this->setScriptPath($path);
	}

	/**
	 * Add A search path.
	 *
	 * Required interface method, currently calls setScriptPath
	 *
	 * @param string $path
	 * @param string $prefix
	 * @return void
	 */
	public function addBasePath($path, $prefix = 'Zend_View')
	{
		$this->setScriptPath($path);
	}

	/**
	 * Magic function to allow direct assignment of variables.
	 *
	 * @param string $key
	 * @param mixed $val
	 * @return void
	 */
	public function __set($key, $val)
	{
		$this->_smarty->assign($key, $val);
	}

	/**
	 * Magic function to allow direct referencing of variables.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		$this->_smarty->get_template_vars($key);
	}

	/**
	 * Magic function to allow testing of variables
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function __isset($key)
	{
		return (null !== $this->_smarty->get_template_vars($key));
	}

	/**
	 * Magic function to clear a variable
	 *
	 * @param string key
	 * @return void
	 */
	public function __unset($key)
	{
		$this->_smarty->clear_assign($key);
	}

	/**
	 * Required interface function to assign values to template variables.
	 *
	 * @param string|array $spec
	 * @param mixed $value
	 * @return void
	 */
	public function assign($spec, $value = null)
	{
		if (is_array($spec)) {
			$this->_smarty->assign($spec);
		} else {
			$this->_smarty->assign($spec, $value);
		}
	}

	/**
	 * Clean all variables associated with this template.
	 *
	 * @return void
	 */
	public function clearVars()
	{
		$this->_smarty->clear_all_assign();
	}

	/**
	 * Render the current template.
	 * Note that Zend can use cascading to allow rendering partial output and combine it
	 * in the final display, so suppressHeaders needs to be called to make sure output
	 * doesn't include header and footer pages.
	 *
	 * @param string $name
	 * @return string
	 */
	public function render($name)
	{
		if (! $this->_smarty->template_exists($name)) {
			// Try using just the base name (Zend adds the controller name as a path component)
			if ( $this->_smarty->template_exists(($base = basename($name)))) {
				$name = $base;
			} else {
				$this->error = 'Could not find template ' . $name;
				error_log($this->error);
				$name = 'error/error.html';
			}
		}
		if (Zend_Controller_Front::getInstance()->getRequest()->getParam('fromTab')) {
			$this->assign('includeFile', $name);
			return $this->_smarty->fetch('jsTab.html');
		}
		return ($this->_suppress_headers ? '' : $this->_smarty->fetch('header.html')) . $this->_smarty->fetch($name) . ($this->_suppress_headers ? '' : $this->_smarty->fetch('footer.html'));
	}

	public function fetch($name) {
		return $this->_smarty->fetch($name);
	}
	/**
	 * Turn off headers.
	 *
	 * @return void
	 */
	public function suppressHeaders()
	{
		$this->_suppress_headers = true;
	}

	/** 
	 * Initialise the DP_Template class with variables from dotproject
	 *
	 * @return void
	 */
	public function init()
	{
		$AppUI = DP_AppUI::getInstance();
		$this->assign('template', $this->template_dir);
		// TODO
		$this->assign('config', DP_Config::getConfig());
		if (isset($AppUI)) {
			$this->assign('version', $AppUI->getVersion());
			$this->assign('user_id', $AppUI->user_id);
			$this->assign('user_name', $AppUI->user_first_name . ' ' . $AppUI->user_last_name);
		}
		
		$this->assign('baseUrl', Zend_Controller_Front::getInstance()->getBaseUrl());
		$this->assign('baseDir', DP_BASE_DIR);
		$this->page = isset($_REQUEST['page'])?$_REQUEST['page']:1;
		$this->assign('page', $this->page);
		
		/*
		$dialog = ( $_GET, 'dialog', 0 );
		if (!$dialog)
		{
			$page_title = ('page_title');
			$page_title = ($page_title == 'dotProject') ? $page_title . '&nbsp;' . $AppUI->getVersion() : $page_title;
		}
		*/
//echo $page_title;
		$perms = $AppUI->acl();
		$this->assign('page_title', $page_title);
		$this->assign('charset', isset( $locale_char_set ) ? $locale_char_set : 'UTF-8');
		$this->assign('version', $AppUI->getVersion());
		
		$this->assign('access_calendar', $perms->checkModule('calendar', 'access'));
		$this->assign('access_links', $perms->checkModule('links', 'access'));

		$this->assign('msg', $AppUI->getMsg());
	
		$this->assign('js', $AppUI->loadJS());
		$this->assign('uistyle', $AppUI->getPref('UISTYLE'));
		$this->assign('style_extras', $style_extras);
		
		// top navigation menu
		if ($AppUI->user_id > 0) {
			$nav = $AppUI->getMenuModules();
			$perms =& $AppUI->acl();
			$links = array();
			
			foreach ($nav as $module) {
				if ($perms->checkModule($module['mod_directory'], 'access')) {
					$links[] = $module; //'<a href="?m='.$module['mod_directory'].'">'.$AppUI->_($module['mod_ui_name']).'</a>';
				}
			}
			$this->assign('modules', $links);
		}
		
		$newItem = array( '' => '- New Item -' );
		if ($perms->checkModule( 'companies', 'add' )) 
			$newItem['companies'] = 'Company';
		if ($perms->checkModule( 'contacts', 'add' )) 
			$newItem['contacts'] = 'Contact';
		if ($perms->checkModule( 'calendar', 'add' )) 
			$newItem['calendar'] = 'Event';
		if ($perms->checkModule( 'files', 'add' )) 
			$newItem['files'] = 'File';
		if ($perms->checkModule( 'projects', 'add' )) 
			$newItem['projects'] = 'Project';
		$this->assign('new_item', $newItem);
	}
	
	/**
	 * Display a list of records through a smarty template.
	 * 
	 * @param string $module 	the module for which the list applies (used to determine the smarty template file).
	 * @param array $rows		the actual data to be displayed
	 * @param integer $totalRows	the total rows available (if 0, it will be interpreted as count($rows)). This is necessary if $rows is a partial result, returned by an sql query with limits, but there are more results.
	 * @return void
	 */	
	public function displayList($module, $rows, $totalRows = 0, $show = null)
	{
		if (!isset($show) && is_array($rows))
		{
			$keys = array_keys($rows);
			$show = array_keys($rows);
			//print_r($show);
		}
		
		if (!$this->get_template_vars('current_url'))
			$this->assign('current_url', '?m=' . $module);			

		$total_rows = count($rows);		
		$page_size = DP_Config::getConfig('page_size', 25);
		$i = 0;

		// Make sure there are any results to display
		if (is_array($rows))
		{
			foreach ($rows as $k => $row)
			{
				if ($i >= $this->page*$page_size - $page_size 
				 && $i < $this->page * $page_size)
					$paginated_rows[$k] = $row;

				++$i;
			}
		}

		$rows = $paginated_rows;
		$this->assign('rows', $rows);
		$this->assign('show', $show);
		$this->displayPagination($this->page, $totalRows > 0?$totalRows:$total_rows, $module);
	}
	
	/** Display a set of paged records
	 * @param $currentPage The number of the current page
	 * @param $totalRecords The total number of records
	 * @param $module Defaults to null, the name of the module (not used)
	 */
	function displayPagination($currentPage, $totalRecords, $module = null)
	{
		// remove orderby's to prevent resorting
		$pagination['url'] = ereg_replace('&orderby=[^&]+', '', $_SERVER['QUERY_STRING']);
		$pagination['url'] = 'index.php?' . ereg_replace('&page=[^&]+', '', $pagination['url']);
		$pagination['url'] = str_replace('&', '&amp;', $pagination['url']);
		// The current page
		$pagination['page'] = $currentPage;
		// how many items in total there are in the list
		$pagination['total_records'] = $totalRecords;
		// how many records there will be per page
		$pagination['page_size'] = DP_Config::getConfig('page_size');
		// how many direct page links to display in the pagination bar
		$pagination['pages_size'] = 30;
		// how many pages there are in total
		$pagination['total_pages'] = ceil($pagination['total_records'] / $pagination['page_size']);
		$start_page = ($pagination['page'] >= ($pagination['pages_size'] / 2))?$pagination['page'] : 1;
		$end_page = ($pagination['total_pages'] <= $pagination['page'] + $pagination['pages_size'] / 2)?$pagination['total_pages']:($pagination['page'] + ($pagination['pages_size'] / 2));
		if ($start_page >= $end_page) // no pagination necessary - only one page!
			return;
		// an array with the pages numbers to be displayed
		$pagination['pages'] = range($start_page, $end_page);

		$this->assign('pagination', $pagination);
	}
	
	/** Display a calendar
	 * @param $field Field to populate with calendar date
	 * @param $module Name of the module where the calendar is being displayed
	 */
	function displayCalendar($field, $module)
	{
		global $AppUI;

		$this->assign('ampm_time_format', stristr($AppUI->getPref('TIMEFORMAT'), '%p'));
		$this->assign('df', $AppUI->getPref('SHDATEFORMAT'));
		$this->assign('tf', $AppUI->getPref('TIMEFORMAT'));
		$this->assign('field', $field);
		$this->assign('module', $module);
	}
	
}
?>
