<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_dPgetMsg($params, &$smarty)
{
	global $AppUI;
	
  return $AppUI->getMsg();
}

/* vim: set expandtab: */
?>
