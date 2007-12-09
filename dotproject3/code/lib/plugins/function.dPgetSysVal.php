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
function smarty_function_dPgetSysVal($params, &$smarty)
{
    extract($params);

    if (empty($var) && empty($sentence)) {
        $smarty->trigger_error("dPgetSysVal: missing 'var' parameter");
        return;
    }

    return dPgetSysVal($var);
}

/* vim: set expandtab: */

?>
