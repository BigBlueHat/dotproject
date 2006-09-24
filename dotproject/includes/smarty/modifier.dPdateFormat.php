<?php
function smarty_modifier_dPdateFormat($date, $format=null) {
    global $AppUI;

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
    else if ($format == 'db')
    	$format = FMT_TIMESTAMP_DATE;
    else if ($format == 'timestamp')
    	$format = FMT_TIMESTAMP;
    else if ($format == 'full')
    	return $cdate->format($AppUI->getPref('SHDATEFORMAT')) . ' ' . $cdate->format($AppUI->getPref('TIMEFORMAT'));
    else if (empty($format))
    	$format = $AppUI->getPref('SHDATEFORMAT');
	
    return $cdate->format($format);
}
?>