<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/*
 * Smarty {dPgetSystemConfigSelect var=cid} function plugin
 *
 * Type:     function<br>
 * Name:     system values<br>
 * Purpose:  get config list select list options<br>
 *
 * @param array Format: array('var' => variable name)
 * @param Smarty
 */
function smarty_function_dPgetSystemConfigSelect($params, &$smarty)
{
	global $AppUI, $tpl;
    extract($params);

    if (empty($cid)) {
        $smarty->trigger_error("dPgetSysVal: missing 'config_id' parameter");
        return;
    } 
	$q = new DBQuery;
	$q->addTable('config_list');
	$q->addQuery('config_list_id, config_list_name');
	$q->addOrder('config_list_id');
	$q->addWhere('config_id ='. $cid);
	$cli = $q->loadHashList();
	
	$clist = array();
	foreach($cli as $cl => $c) {
		$clist[$cl] = $AppUI->_($c.'_item_title');
	}

	$tpl->assign('clist', $clist);

	$cln = 'dPcfg['.$cname.']';
	$tpl->assign('cln', $cln);
	return;
}
/* vim: set expandtab: */
?>
