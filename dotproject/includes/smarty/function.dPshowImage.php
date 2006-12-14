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
	
	$find = null; // set below.
	extract($params);
	if($find)
	{
		if (!empty($mod))
			$src = dPfindImage($find, $mod);
		else
			$src = dPfindImage($find);
	}
	
	$return = '';
	if ($src)
	{
		$result = '<img src="' . $src . '"'; // align="center"';
		if (!empty($wsize) && is_int($wsize))
	    $result .= ' width="' . $wsize . '"';
	  if (!empty($hsize) && is_int($hsize))
	    $result .= ' height="' . $hsize . '"';
	  else if ($wsize)
	    $result .= ' height="' . $wsize . '"';
	  if (empty($alt))
	    $alt = substr($src, strrpos($src, '/') + 1, -4);
	
	  $result .= ' alt="' . $AppUI->_($alt) . '"';
	  
	  if (!empty($title))
	    $result .= ' title="' . $AppUI->_($title) . '"';
	  $result .= ' border="0" />';
	}
	
	return $result;
}

/* vim: set expandtab: */
?>
