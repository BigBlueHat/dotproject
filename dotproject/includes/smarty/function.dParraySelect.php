<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/*
 * Smarty {arraySelect array= name= extras= value= translation=} function plugin
 *
 * Type:     function<br>
 * Name:     arraySelect<br>
 * Purpose:  create a select form through dp<br>
 *
 * @param array Format: array(<br>
 * 'var' => variable name, 
 * 'value' => value to assign,
 * )
 * @param Smarty
 */
function smarty_function_dParraySelect($params, &$smarty)
{
	extract($params);
	
	if (!isset($array)) {
	    $smarty->trigger_error('dParraySelect: missing parameter "array"');
	    return;
	} elseif (!isset($name)) {
	    $smarty->trigger_error('dParraySelect: missing parameter "name"');
	    return;
	}
	
	if (!isset($extras))
		$extras = '';
		
	if (!isset($value))
		$value = null;
	
	if (!isset($translation))
		$translation = false;
	
	return arraySelect($array, $name, $extras, $value, $translation);
}

/* vim: set expandtab: */

?>
