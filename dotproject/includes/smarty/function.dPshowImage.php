<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/*
 * Smarty {showImage src=url wsize=width hsize=height alt=alt title=title} function plugin
 *
 * Type:     function<br>
 * Name:     translate<br>
 * Purpose:  show an image<br>
 *
 * @param array Format: array('var' => variable name, 'value' => value to assign)
 * @param Smarty
 */
function smarty_function_dPshowImage($params, &$smarty)
{
	global $AppUI;
	
    extract($params);

		$result = '<img src="' . $src . '" align="center"';
		if ($wsize)
		  $result .= ' width="' . $wsize . '"';
		if ($hsize)
		  $result .= ' height="' . $hsize . '"';
		if ($alt)
		  $result .= ' alt="' . $AppUI->_($alt) . '"';
		if ($title)
		  $result .= ' title="' . $AppUI->_($title) . '"';
		$result .= ' border="0">';
    
    return $result;
}

/* vim: set expandtab: */

?>
