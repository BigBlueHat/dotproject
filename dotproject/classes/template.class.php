<?php
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

require_once(DP_BASE_DIR . '/lib/smarty/Smarty.class.php');



/** HTML templating class
 *
 * dotProject extension to the Smarty class used for HTML templating.
 * Abstracts some of the seperate dotProject visual components into seperate methods. Also provides methods
 * which are purpose based, ie. display a template for editing, display a template for viewing etc..
 */
class CTemplate extends Smarty
{
	var $page;

	/** CTemplate constructor
	 *
	 * Calls the Smarty constructor and sets the template directories to the correct locations within the dotproject directory
	 */
	function CTemplate()
	{
		global $AppUI, $m, $a;
	
		parent::Smarty();
		// $this->template_dir = DP_BASE_DIR . '/style/' . $AppUI->getPref('template');
		$this->template_dir = DP_BASE_DIR . '/style/';
		$this->compile_dir	= DP_BASE_DIR . '/files/cache/smarty_templates';
		$this->cache_dir	= DP_BASE_DIR . '/files/cache/smarty';
		$this->plugins_dir[]= DP_BASE_DIR . '/includes/smarty';
	}
	
	/** Initialise the CTemplate class with variables from dotproject */
	function init()
	{
		global $m, $a, $dPconfig, $AppUI,
		$company_id, $project_id, $task_id, $file_id;
		
		
		$this->assign('template', $this->template_dir);
		$this->assign('config', $dPconfig);
		$this->assign('version', $AppUI->getVersion());
		$this->assign('user_id', $AppUI->user_id);
		$this->assign('user_name', $AppUI->user_first_name . ' ' . $AppUI->user_last_name);
		
		$this->assign('baseUrl', DP_BASE_URL);
		$this->assign('baseDir', DP_BASE_DIR);
		foreach(array('m', 'a', 'company_id', 'task_id', 'file_id') as $global)
			$this->assign($global, $$global);

		$this->page = isset($_REQUEST['page'])?$_REQUEST['page']:1;
		$this->assign('page', $this->page);
	}
	
	/** Display the header section of the dotProject interface
	 *
	 * Contains any information to be inserted into the HEAD element including javascript and style information.
	 * Includes permission checking for the current module and the CAppUI message.
	 */
	function displayHeader()
	{
		global $locale_char_set, $uistyle, $AppUI, $style_extras;
		
		$perms = & $AppUI->acl();
		
		$dialog = dPgetParam( $_GET, 'dialog', 0 );
		if (!$dialog)
		{
			$page_title = dPgetConfig('page_title');
			$page_title = ($page_title == 'dotProject') ? $page_title . '&nbsp;' . $AppUI->getVersion() : $page_title;
		}
//echo $page_title;
		$this->assign('page_title', $page_title);
		$this->assign('charset', isset( $locale_char_set ) ? $locale_char_set : 'UTF-8');
		$this->assign('version', $AppUI->getVersion());
		$this->assign('dialog', $dialog);
		
		$this->assign('access_calendar', $perms->checkModule('calendar', 'access'));
		$this->assign('access_links', $perms->checkModule('links', 'access'));

		$this->assign('msg', $AppUI->getMsg());
		$this->assign('now', new CDate());
	
		$this->assign('js', $AppUI->loadJS());
		$this->assign('uistyle', $uistyle);
		$this->assign('style_extras', $style_extras);
		
		// top navigation menu
		$nav = $AppUI->getMenuModules();
		$perms =& $AppUI->acl();
		$links = array();
		foreach ($nav as $module) {
			if ($perms->checkModule($module['mod_directory'], 'access')) {
				$links[] = $module; //'<a href="?m='.$module['mod_directory'].'">'.$AppUI->_($module['mod_ui_name']).'</a>';
			}
		}
		$this->assign('modules', $links);
		
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

		$this->displayFile('header', '.');
	}

	/**
	 * Display a list of records through a smarty template.
	 * 
	 * @param $module 		the module for which the list applies (used to determine the smarty template file).
	 * @param $rows			the actual data to be displayed
	 * @param $totalRows	the total rows available (if 0, it will be interpreted as count($rows)). This is necessary if $rows is a partial result, returned by an sql query with limits, but there are more results.
	 */	
	function displayList($module, $rows, $totalRows = 0, $show = null)
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
		$page_size = dPgetConfig('page_size', 25);
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
		$this->displayFile('list', $module);
		$this->displayPagination($this->page, $totalRows > 0?$totalRows:$total_rows, $module);
	}
	
	/** Display the view page for the current module using the data from the $item object parameter
	 * @param $item Object to display
	 */
	function displayView($item)
	{
		global $m;
		
		$this->assign('obj', $item);
		
		$this->displayFile('view');
	}

	/** Display the add/edit page for the current module using the data from the $item object parameter
	 * @param $item Object to edit, If null then blank fields will be used to add an item
	 */	
	function displayAddEdit($item)
	{
		global $m;
		
		$this->assign('obj', $item);
		
		$this->displayFile('addedit');
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
		$pagination['page_size'] = dPgetConfig('page_size');
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
		$this->displayFile('pagination', '.');
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
		$this->displayFile('calendar', '.');
	}
	
	/** Get the filename of the template to use
	 *
	 * Based on the style defined by the user preference, or system configuration, returns the filename
	 * of the template to use. Also contains a fallback mechanism to use the default template.
	 * @param $file Template file name eg. addedit
	 * @param $module Name of the module
	 * @return The relative path of the template to use
	 */
	function file($file, $module)
	{
		global $m, $uistyle;
		
		if ($module == null)
			$module = $m;
		
		if ($module == '.')
			$module = '';
		else
			$module .= '/';

		$style = $uistyle;
		if (is_file(DP_BASE_DIR . "/style/$style/$module$file.html"))
			return "$style/$module$file.html";
		// Allow modules to provide their own templates, if one doesn't exist in the current theme.
		elseif (is_file(DP_BASE_DIR . '/modules/'.$module.'style/'.$file.'.html'))
			return '../modules/'.$module.'style/'.$file.'.html';
		else // default fallback
			return "_smarty/$module$file.html";
	}
	
	/** Display a template
	 * @param $file Filename (without extension) to display
	 * @param $module Name of the current module
	 */
	function displayFile($file, $module = null)
	{
		$this->display($this->file($file, $module));
	}
	
	/** Fetch template output as a string
	 * @param $file Filename (without extension) to fetch
	 * @param $module Name of the current module
	 */	
	function fetchFile($file, $module = null)
	{
		return $this->fetch($this->file($file, $module));
	}
	
	/** Load style overrides
	*/
	function loadOverrides()
	{
		global $AppUI, $uistyle;
/*		global $file_id, $company_id, $task_id;
		global $currentTabId, $currentTabName;
		global $uistyle, $style_extras; */
		
		if (is_file(DP_BASE_DIR . '/style/' . $uistyle . '/overrides.php'))		
			include(DP_BASE_DIR . '/style/' . $uistyle . '/overrides.php');
	}
}
?>
