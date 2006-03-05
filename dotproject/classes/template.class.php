<?php
require_once($baseDir . '/lib/smarty/Smarty.class.php');

class CTemplate extends Smarty
{
//	var $plugins_dir = array('/var/www/html/dotproject/includes/smarty');

	function CTemplate()
	{
		global $AppUI, $baseDir;
	
		parent::Smarty();
		// $this->template_dir = $baseDir . '/style/' . $AppUI->getPref('template');
		$this->template_dir = $baseDir . '/style/smarty1';
		$this->compile_dir	= $baseDir . '/files/cache/smarty_templates';
		$this->cache_dir		= $baseDir . '/files/cache/smarty';
		$this->plugins_dir[]= $baseDir . '/includes/smarty';
		
		$this->assign('template', $this->template_dir);
	}
	
	function displayList($module, $rows, $show = null)
	{
		$page = dPgetParam($_GET, 'page', 1);

		if (!isset($show))
		{
			$keys = array_keys($rows);
			$show = array_keys($rows); //[$keys[0]]);
		}
		
		if (!$this->get_template_vars('current_url'))
			$this->assign('current_url', '?m=' . $module);			
			
		$this->assign('rows', $rows);
		$this->assign('show', $show);
		
		$this->displayPagination($page, count($rows), $module);
		$this->displayFile('list', $module);
		$this->displayPagination($page, count($rows), $module);
	}
	
	function displayView($item)
	{
		global $m;
		
		$this->assign('obj', $item);
		
		$this->displayFile('view');
	}
	
	function displayAddEdit($item)
	{
		global $m;
		
		$this->assign('obj', $item);
		
		$this->displayFile('addedit');
	}
	
	function displayPagination($currentPage, $totalRecords, $module = null)
	{
		$pagination['page'] = $currentPage;
		$pagination['total_records'] = $totalRecords;
		$pagination['page_size'] = 30;
		$pagination['pages_size'] = 30;
		$pagination['total_pages'] = ceil($pagination['total_records'] / $pagination['page_size']);
		$pagination['pages'] = range(($pagination['page'] >= ($pagination['pages_size'] / 2))?$pagination['page'] : 1, $pagination['total_pages']);
		$this->assign('pagination', $pagination);
		$this->display('pagination.html', $module);
	}
	
	function displayFile($file, $module = null)
	{
		global $m, $a, $dPconfig;
		
		if ($module == null)
			$module = $m;
			
		$this->assign('m', $m);
		$this->assign('a', $a);
		$this->assign('config', $dPconfig);
			
		$this->display($module . '/' . $file . '.html');
	}
	
	function fetchFile($file, $module = null)
	{
		global $m, $a;
		
		if ($module == null)
			$module = $m;
			
		$this->assign('m', $m);
		$this->assign('a', $a);
			
		return $this->fetch($module . '/' . $file . '.html');
	}
	
	function displayStyle($file)
	{
		global $baseDir, $dPconfig, $AppUI;
		global $file_id, $company_id, $task_id;
		global $currentTabId, $currentTabName;
		global $uistyle;
				
		include($baseDir . '/style/' . $uistyle . '/' . $file . '.php');
	}
}
?>
