<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/*
 * Smarty {bestColor bg=colour lt=light background dk=dark background} function plugin
 *
 * Type:     function<br>
 * Name:     translate<br>
 * Purpose:  choose a background colour<br>
 *
 * @param array Format: array('bg' => color, ...)
 * @param Smarty
 */
function smarty_function_dPbestColor($params, &$smarty)
{
	extract($params);
	
	if (!isset($bg))
		$smarty->trigger_error("dPbestColor: missing 'bg' parameter");
	
	if (!isset($dk))
		$dk = '#000000';
	if (!isset($lt))
		$lt = '#ffffff';
	
	$x = 128;

	$r = hexdec( substr( $bg, 0, 2 ) );
	$g = hexdec( substr( $bg, 2, 2 ) );
	$b = hexdec( substr( $bg, 4, 2 ) );

	if ($r < $x && $g < $x || $r < $x && $b < $x || $b < $x && $g < $x) {
		return $lt;
	} else {
		return $dk;
	}
}

/* vim: set expandtab: */
?>