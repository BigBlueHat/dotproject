<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/*
 * Smarty {dPgetSysVal var=word} function plugin
 *
 * Type:     function<br>
 * Name:     system values<br>
 * Purpose:  get dp system values<br>
 *
 * @param array Format: array('var' => variable name)
 * @param Smarty
 */
function smarty_function_dPgetContact($params, &$smarty)
{
    extract($params);

    if (empty($user_id)) {
        $smarty->trigger_error("dPgetSysVal: missing 'user_id' parameter");
        return;
    }

		return dPgetUsernameFromID($user_id);
}
/* vim: set expandtab: */
?>