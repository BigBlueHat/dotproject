<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_dPgetMsg($params, &$smarty)
{
  return DP_AppUI::getInstance()->getMsg();
}

/* vim: set expandtab: */
?>
