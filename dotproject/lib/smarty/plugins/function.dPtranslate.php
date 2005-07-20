<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/*
 * Smarty {translate word=word} function plugin
 *
 * Type:     function<br>
 * Name:     translate<br>
 * Purpose:  translate words through dp<br>
 *
 * @param array Format: array('var' => variable name, 'value' => value to assign)
 * @param Smarty
 */
function smarty_function_dPtranslate($params, &$smarty)
{
	global $AppUI;
	
    extract($params);

    if (empty($word)) {
        $smarty->trigger_error("assign: missing 'word' parameter");
        return;
    }

    return $AppUI->_($word);
}

/* vim: set expandtab: */

?>
