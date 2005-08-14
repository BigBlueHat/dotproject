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
	}
	
	function displayList($module, $rows, $show = null)
	{
		if (!isset($show))
		{
			$keys = array_keys($rows);
			$show = array_keys($rows); //[$keys[0]]);
		}
		
		if (!$this->get_template_vars('current_url'))
			$this->assign('current_url', '?m=' . $module);			
			
		$this->assign('rows', $rows);
		$this->assign('show', $show);
		
		$this->displayFile('list', $module);
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
	
	function displayFile($file, $module = null)
	{
		global $m, $a;
		
		if ($module == null)
			$module = $m;
			
		$this->assign('m', $m);
		$this->assign('a', $a);
			
		$this->display($module . '/' . $file . '.html');
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