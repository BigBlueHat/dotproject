<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/*
 * Smarty {dateFormat date= cdate= format=} function plugin
 *
 * Type:     function<br>
 * Name:     dateFormat<br>
 * Purpose:  format a date as per dp user preferences<br>
 *
 * @param array Format: array(
 *		'date' => the date to be formatted
 * 		'cdate' => the date to be formatted in CDate class
 *		'format' => optional format (if not specified - using user format))
 * @param Smarty
 */
function smarty_function_dPdateFormat($params, &$smarty)
{
    global $AppUI;
	
    extract($params);
    
    if (empty($date) || $date == '0000-00-00 00:00:00') {
    	if ($format == 'db')
    		return '';
    	else
    		return '-';
    }
    
    if (empty($cdate))
    	$cdate = new CDate( $date );
    	
	if ($format == 'time')
		$format = '%H%M%S';
	else if ($format == 'timestamp')
		$format = FMT_TIMESTAMP;
    else if ($format == 'db')
    	$format = FMT_TIMESTAMP_DATE;
    else if ($format == 'full')
    	return $cdate->format($AppUI->getPref('SHDATEFORMAT')) . ' ' . $cdate->format($AppUI->getPref('TIMEFORMAT'));
    else if (empty($format))
    	$format = $AppUI->getPref('SHDATEFORMAT');
	
    return $cdate->format($format);
}

/* vim: set expandtab: */
?>