<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/*
 * Smarty {config var=word} function plugin
 *
 * Type:     function<br>
 * Name:     configuration<br>
 * Purpose:  get dp config variables<br>
 *
 * @param array Format: array('var' => variable name)
 * @param Smarty
 */
function smarty_function_dPconfig($params, &$smarty)
{
    extract($params);

    if (empty($var) && empty($sentence)) {
        $smarty->trigger_error("assign: missing 'var' parameter");
        return;
    }

    return dPgetConfig($var);
}

/* vim: set expandtab: */

?>
