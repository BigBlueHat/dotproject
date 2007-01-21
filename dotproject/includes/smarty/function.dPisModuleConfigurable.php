<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/*
 * Smarty {require file=word} function plugin
 *
 * Type:     function<br>
 * Name:     configuration<br>
 * Purpose:  Check configurability of module via existence of config_group entry in config table
 *
 * @param array Format: array('file' => variable name)
 * @param Smarty
 */
function smarty_function_dPisModuleConfigurable($params, &$smarty)
{
	global $AppUI, $tpl;
    extract($params);

    if (empty($modDir)) {
        $smarty->trigger_error("dPrequire: missing 'modDir' parameter");
        return;
    }
		$q = new DBQuery;
		$q->addTable('config');
		$q->addWhere('config_group = "'.$modDir.'"');
		$res = $q->exec();
		$tpl->assign('modConfigurable', $res);
}

/* vim: set expandtab: */

?>
