<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/*
 * Smarty {dateFormat date=} function plugin
 *
 * Type:     function<br>
 * Name:     dateFormat<br>
 * Purpose:  format a date as per dp user preferences<br>
 *
 * @param array Format: array('date' => the date to be formatted)
 * @param Smarty
 */
function smarty_function_dPdateFormat($params, &$smarty)
{
    global $AppUI;
	
    extract($params);
    
    if (empty($date)) {
        $date = '';
    }
    
    $cdate = new CDate( $date );
    $df = $AppUI->getPref('SHDATEFORMAT');
    return $cdate->format($df);
}

/* vim: set expandtab: */
?>