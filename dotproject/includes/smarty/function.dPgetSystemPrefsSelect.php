<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/*
 * Smarty {dPgetSystemPrefsSelect var=pid} function plugin
 *
 * Type:     function<br>
 * Name:     system values<br>
 * Purpose:  get prefs list select list options<br>
 *
 * @param array Format: array('var' => variable name)
 * @param Smarty
 */
function smarty_function_dPgetSystemPrefsSelect($params, &$smarty)
{
	global $AppUI, $tpl;
    extract($params);

    if (empty($pid)) {
        $smarty->trigger_error("dPgetSystemPrefsSelect: missing 'pref_id' parameter");
        return;
    } 
	$q = new DBQuery;
	$q->addTable('user_prefs_list');
	$q->addQuery('pref_list_id, pref_list_name');
	$q->addOrder('pref_list_name');
	$q->addWhere('pref_name = "'. $pid .'"');
	$cli = $q->loadHashList();
	
	$clist = array();
	foreach($cli as $cl => $c) {
		$clist[$c] = $AppUI->_('prefs_'.$c.'_item_title');
	}
	
	$tpl->assign('plist', $clist);

	$pln = 'pref_name['.$pname.']';
	$tpl->assign('pln', $pln);
	return;
}
/* vim: set expandtab: */
?>
