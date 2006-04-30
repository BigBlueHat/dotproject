<?php /* CALENDAR $Id$ */

require_once( $AppUI->getSystemClass( 'webdav_client' ) );

// load the event types
$types = dPgetSysVal( 'EventType' );

// format dates
$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');

$recurs =  array (
	'Never',
	'Hourly',
	'Daily',
	'Weekly',
	'Bi-Weekly',
	'Every Month',
	'Quarterly',
	'Every 6 months',
	'Every Year'
);


/**
* Sub-function to collect events within a period
* @param Date the starting date of the period
* @param Date the ending date of the period
* @param array by-ref an array of links to append new items to
* @param int the length to truncate entries by
* @author Andrew Eddie <eddieajau@users.sourceforge.net>
*/
function getEventLinks( $startPeriod, $endPeriod, &$links, $strMaxLen ) {
	global $event_filter, $AppUI, $event_id, $df, $tf;
		
	// Check permissions.
	$perms = & $AppUI->acl();
	$canView = $perms->checkModule( 'calendar', 'view', $event_id );
	if (!$canView)
		return array();
	
	$events = CEvent::getEventsForPeriod( $startPeriod, $endPeriod, $event_filter );

	// assemble the links for the events
	foreach ($events as $row) {
		$start = new CDate( $row['event_start_date'] );
		$end = new CDate( $row['event_end_date'] );
		$date = $start;
		$cwd = explode(",", $GLOBALS["dPconfig"]['cal_working_days']);

		for($i=0; $i <= $start->dateDiff($end); $i++) {
		// the link
			// optionally do not show events on non-working days 
			if ( ( $row['event_cwd'] && in_array($date->getDayOfWeek(), $cwd ) ) || !$row['event_cwd'] ) {
				$url = '?m=calendar&a=view&event_id=' . $row['event_id'];
				$link['href'] = $url;
				$link['alt'] = $row['event_description'];
				$link['start_date'] = $row['event_start_date'];
				$link['end_date'] = $row['event_end_date'];
				$link['title'] = $row['event_title'];
				$link['description'] = $row['event_description'];
				$link['type'] = 'event';
				$link['img'] = 'event' . $row['event_type'] . '.png';
				$start_date = new CDate($row['event_start_date']);
				$end_date = new CDate($row['event_end_date']);
				// tooltip is in Javascript - it needs slashes before new lines.
				$link['tooltip'] = '\
<table>\
<tr>\
	<td>'.$AppUI->_('Start Date').':</td>\
	<td>' . $start_date->format($df.' '.$tf) . '</td>\
</tr>\
<tr>\
	<td>'.$AppUI->_('End Date').':</td>\
	<td>' . $end_date->format($df.' '.$tf) . '</td>\
</tr>\
<tr>\
	<td>'.$AppUI->_('Description').':</td>\
	<td>'.$row['event_description'].'</td>\
</tr>\
</table>';
				$link['text'] = $row['event_title'];
				$links[$date->format( FMT_TIMESTAMP_DATE )][] = $link;
			 }
				$date = $date->getNextDay();
		}
	}
}


function getExternalWebcalEventLinks( $startPeriod, $endPeriod, &$links, $strMaxLen ) {
	global $df, $event_filter, $AppUI, $recurs, $types, $event_id;
		
	// Check permissions.
	$perms = & $AppUI->acl();
	$canView = $perms->checkModule( 'calendar', 'view', $event_id );
	if (!$canView)
		return array();
	
	$calendars = array();
	$calendars = CWebCalresource::getExternalWebcalendars();
	

	foreach ($calendars as $cal) {

		$cal['webcal_path'] = 'http://'.$cal['webcal_path'];

		// establish webDAV client connection
		$wdc = new WebDAVclient();
		$target_path = $wdc->pathInfo( $cal['webcal_path'] );
		$wdc->setPath( $target_path );
		$wdc->setServer( $wdc->hostInfo( $cal['webcal_path'] ) );
		$wdc->setPort($cal['webcal_port']);
		$wdc->setUser($cal['webcal_user']);
		$wdc->setPass($cal['webcal_pass']);
		
		if (!$wdc->openConnection()) {
		//	$AppUI->setMsg( 'WebDAVClient: Could not open server connection', UI_MSG_ERROR );
		}

		// check if server supports webdav rfc 2518
		if (!$wdc->checkConnection()) {
		//	$AppUI->setMsg( 'WebDAVClient: Server does not support WebDAV or user/password may be wrong', UI_MSG_ERROR );
		}

		$ics = null;
		$http_status = $wdc->get($target_path, $ics);
		
		require_once( $AppUI->getLibraryClass( 'PEAR/File/IMC/Parse' ) );

		$parse = new File_IMC_Parse();
		$v = new vCalendar;
		// parse a iCal file and store the data
		// in $calinfo
		$calinfo = $parse->fromText($ics);
		
		$events = $v->icsParsedArrayToList($calinfo);

		// assemble the links for the events
		foreach ($events as $row) {
			$start = new CDate( $row['event_start_date'] );
			$end = new CDate( $row['event_end_date'] );
			$date = $start;
			$cwd = explode(",", $GLOBALS["dPconfig"]['cal_working_days']);
			// set Event_type to auto/external event
			$row['event_type'] = -1;

			for($i=0; $i <= $start->dateDiff($end); $i++) {
			// the link
				// optionally do not show events on non-working days 
				if ( ( $row['event_cwd'] && in_array($date->getDayOfWeek(), $cwd ) ) || !$row['event_cwd'] ) {
					$url = '?m=calendar&a=view&event_id=' . $row['event_id'];
					$ot = '\''.$AppUI->_('Type:').' '.$types[$row['event_type']].'<br />';
				//	$ot .=$AppUI->_('Project:').' '.$row['event_type'].'<br />';
					/*if (is_array($assigned)) {
						$start = false;
						foreach ($assigned as $user) {
							if ($start)
								echo ", ";
							else
								$start = true;
							echo $user;
						}
					}*/
					
					$ot .=$AppUI->_('Start:').' '.$start->format($df).'<br />';
					$ot .=$AppUI->_('End:').' '.$end->format($df).'<br />';
					$ot .=$AppUI->_('Recurs:').' '.$AppUI->_($recurs[$row['event_recurs']]).'<br />';
					$ot .=$AppUI->_('Description:').' '.$row['event_description'].'\'';
					$oc = 'onclick="return overlib('.$ot.', STICKY, CAPTION, \''.$row['event_title'].'\', CENTER);" onmouseout="nd();"';
					$link['href'] = '';
					$link['alt'] = $row['event_description'];
					$link['text'] = '
<table cellspacing="0" cellpadding="0" border="0">
<tr>
	<td>
		<a href="javascript:void(0);" '.$oc.'>
			' . dPshowImage( dPfindImage( 'event'.$row['event_type'].'.png', 'calendar' ), 16, 16, '' )	. '</a>
	</td>
	<td>
		<a href="javascript:void(0);" '.$oc.'>
			<span class="event">'.$row['event_title'].'</span>
		</a>
	</td>
</tr>
</table>';
					$links[$date->format( FMT_TIMESTAMP_DATE )][] = $link;
				 }
					$date = $date->getNextDay();
			}
		}
	}
}
?>
