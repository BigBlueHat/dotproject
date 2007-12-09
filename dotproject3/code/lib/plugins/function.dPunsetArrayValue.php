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
 * Purpose:  Unset a value from a given array $a by key $k
 *
 * @param array Format: array('file' => variable name)
 * @param Smarty
 */
function smarty_function_dPunsetArrayValue($params, &$smarty)
{
	global $AppUI, $tpl;
    extract($params);

    if (empty($a)) {
        $smarty->trigger_error("dPrequire: missing 'a' parameter");
        return;
    } elseif (empty($k)) {
        $smarty->trigger_error("dPrequire: missing 'k' parameter");
        return;
    }

    global $$a; // $a is the array name string, $$a the array itself
    $b =& $$a;  // redefine by reference
    unset($b[$k]); // unset the wished value
		$tpl->assign($a, $$a); // reassign the reduced array to smarty
}

/* vim: set expandtab: */

?>
