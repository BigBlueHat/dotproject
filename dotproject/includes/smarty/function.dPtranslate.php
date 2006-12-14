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
	
	$type = ''; // Set below.
	extract($params);
	
	if (empty($word) && empty($sentence)) {
		$smarty->trigger_error("dPtranslate: missing parameter");
		return;
	}
	
	if (!empty($prepend)) {
	  $word = $prepend.$word;
	} 
	
	if (!empty($append)) {
	  $word .= $append;
	}
	
	if ($type == 'js') {
		return $AppUI->_($word . $sentence, UI_OUTPUT_JS);
	}
	
	if ($type == 'cl') {
		return $AppUI->_($word . $sentence, UI_CASE_LOWER);
	}
	
	return $AppUI->_($word . $sentence);
}

/* vim: set expandtab: */

?>