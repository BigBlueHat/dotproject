<?php /* $Id$ */

$del = isset($_POST['del']) ? $_POST['del'] : 0;

$event = new CEvent();

if (($msg = $event->bind( $_POST ))) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
	$AppUI->redirect();
}
// add the seconds (=minutes * 60) to the date
$event->event_start_date += @$_POST['start_time'] * 60;
$event->event_end_date += @$_POST['end_time'] * 60;

if ($del) {
	if (($msg = $event->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( "Event deleted", UI_MSG_ALERT );
	}
} else {
	$isNotNew = @$_POST['event_id'];
	if (!$isNotNew) {
		$event->event_owner = $AppUI->user_id;
	}
	if (($msg = $event->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( "Event ".($isNotNew ? 'updated' : 'inserted'), UI_MSG_OK );
	}
}
$AppUI->redirect();
?>