<?php
require_once($baseDir . '/lib/smarty/Smarty.class.php');

class CTemplate extends Smarty
{
	function CTemplate()
	{
		global $AppUI, $baseDir;
		
		// $this->template_dir = $baseDir . '/style/' . $AppUI->getPref('template');
		$this->template_dir = $baseDir . '/style/smarty1';
		$this->compile_dir	= $baseDir . '/files/cache/smarty_templates';
		$this->cache_dir		= $baseDir . '/files/cache/smarty';
	}
	
	function displayList($module, $rows, $show = null)
	{
		if (!isset($show))
			$show = array_keys($rows);
			
		$this->assign('rows', $rows);
		$this->assign('show', $show);
		
		$this->display($module . '/list.html');
	}
	
	function displayView($item)
	{
		global $m;
		
		$this->assign('obj', $item);
		
		$this->display($m . '/view.html');
	}
	
	function displayAddEdit($item)
	{
		global $m;
		
		$this->assign('obj', $item);
		
		$this->display($m . '/addedit.html');
	}
}
?>