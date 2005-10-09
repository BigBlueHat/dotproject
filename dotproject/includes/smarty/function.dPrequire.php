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
 * Purpose:  get dp file include<br>
 *
 * @param array Format: array('file' => variable name)
 * @param Smarty
 */
function smarty_function_dPrequire($params, &$smarty)
{
	global $AppUI;
    extract($params);

    if (empty($file) && empty($sentence)) {
        $smarty->trigger_error("dPrequire: missing 'file' parameter");
        return;
    }

    require $file;
}

/* vim: set expandtab: */

?>
