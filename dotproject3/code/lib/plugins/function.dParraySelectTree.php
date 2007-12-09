<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/*
 * Smarty {arraySelectTree array= name= extras= value= translation=} function plugin
 *
 * Type:     function<br>
 * Name:     arraySelectTree<br>
 * Purpose:  create a select form through dp - display hierarchy in select list<br>
 *
 * @param array Format: array(<br>
 * 'var' => variable name, 
 * 'value' => value to assign,
 * )
 * @param Smarty
 */
function smarty_function_dParraySelectTree($params, &$smarty)
{
    extract($params);
    
    if (empty($array) || empty($name)) {
        $smarty->trigger_error("dParraySelectTree: missing parameter");
        return;
    }
    
    if (!isset($extras))
    	$extras = '';
    	
    if (!isset($value))
    	$value = null;
    
    if (!isset($translation))
    	$translation = false;

    return arraySelectTree($array, $name, $extras, $value, $translation);
}

/* vim: set expandtab: */

?>
