<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/*
 * Smarty {formSafe var=} function plugin
 *
 * Type:     function<br>
 * Name:     formSafe<br>
 * Purpose:  check a variable to be put in a form through dp<br>
 *
 * @param array Format: array('var' => variable name)
 * @param Smarty
 */
function smarty_function_dPformSafe($params, &$smarty)
{
    extract($params);
    
    if (empty($var)) {
        $var = '';
    }
    
    return dPformSafe($var);
}

/* vim: set expandtab: */

?>
