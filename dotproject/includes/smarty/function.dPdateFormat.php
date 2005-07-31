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
    
    if (empty($date)) {
        $date = '';
    }
    
    if (empty($cdate))
    	$cdate = new CDate( $date );
    
    if ($format == 'db')
    	$df = FMT_TIMESTAMP_DATE;
    else if (!empty($format))
    	$df = $format;
    else
    	$df = $AppUI->getPref('SHDATEFORMAT');
    
    return $cdate->format($df);
}

/* vim: set expandtab: */
?>