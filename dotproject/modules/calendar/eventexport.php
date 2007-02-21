<?php
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

// get GETPARAMETER for event_id
$event_id = intval( $_GET['event_id']);
$types = dPgetSysVal('EventType');

$canRead = !getDenyRead( 'events' );
if (!$canRead) {
	$AppUI->redirect('m=public&a=access_denied');
}

if ( isset($_GET['event_id']) && !($_GET['event_id']=='') ) {
	$events = NULL;
	//pull data for this event
	$q  = new DBQuery;
	$q->addTable('events');
	$q->addQuery('*');
	$q->addWhere("event_id = $event_id");
	$q->loadObject($event);	
	$q->clear();

	$start_date =& new CDate($event->event_start_date);
	$end_date =& new CDate($event->event_end_date);	
	
	// create vEvent String	
	$v = new vCalendar;
	$v->addEventsByFilter('event_id ='.$event_id, true);
	$text = $v->genString();

	//send http-output with this iCalendar

	// BEGIN extra headers to resolve IE caching bug (JRP 9 Feb 2003)
	// [http://bugs.php.net/bug.php?id=16173]
	header('Pragma: ');
	header('Cache-Control: ');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header('Cache-Control: no-store, no-cache, must-revalidate');  //HTTP/1.1
	header('Cache-Control: post-check=0, pre-check=0', false);
	// END extra headers to resolve IE caching bug

	header('MIME-Version: 1.0');
	header('Content-Type: text/icalendar');
	header("Content-Disposition: attachment; filename={$event->event_title}".$start_date->format('%Y%m%d').$start_date->format('%H%M%S').'.ics');
	print_r($text);
} else {
	$AppUI->setMsg( "eventIdError", UI_MSG_ERROR );
	$AppUI->redirect();
}
?>