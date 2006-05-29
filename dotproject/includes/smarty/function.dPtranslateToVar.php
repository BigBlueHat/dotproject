<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/*
 * Smarty {dPtranslateToVar var=word var=append var=ass} function plugin
 *
 * Type:     function<br>
 * Name:     system values<br>
 * Purpose:  translate string with appended substring and assign it to a smarty variable<br>
 * Gain:     the assigned var can be passed to a plugin function<br>
 *
 * @param array Format: array('var' => variable name)
 * @param Smarty
 */
function smarty_function_dPtranslateToVar($params, &$smarty)
{
	global $AppUI, $tpl;
    extract($params);

    if ((empty($word) && empty($sentence)) || empty($ass)) {
        $smarty->trigger_error("dPtranslate: missing parameter");
        return;
    }

    if (!empty($append))
		$word .= $append; 
	
	if ($type == 'js')
    	$i18n = $AppUI->_($word . $sentence, UI_OUTPUT_JS);

    $i18n = $AppUI->_($word . $sentence);
	//$tpl->assign('tt', $i18n);
	$tpl->assign($ass, $i18n);
	return;

}
/* vim: set expandtab: */
?>
