<?php /* TASKS $Id$ */
$notify_owner =  isset($_POST['task_log_notify_owner']) ? $_POST['task_log_notify_owner'] : 0;

// dylan_cuthbert: auto-transation system in-progress, leave this line commented out for now
//include( '/usr/local/translator/translate.php' );

$del = dPgetParam( $_POST, 'del', 0 );

$obj = new CTaskLog();

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

// dylan_cuthbert: auto-transation system in-progress, leave these lines commented out for now
//if ( $obj->task_log_description ) {
//	$obj->task_log_description .= "\n\n[translation]\n".translator_make_translation( $obj->task_log_description );
//}

if ($obj->task_log_date) {
	$date = new CDate( $obj->task_log_date );
	$obj->task_log_date = $date->format( FMT_DATETIME_MYSQL );
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Task Log' );
if ($del) {
	if (($msg = $obj->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT );
	}
	$AppUI->redirect();
} else {
	if (($msg = $obj->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( @$_POST['task_log_id'] ? 'updated' : 'inserted', UI_MSG_OK, true );
	}
}

$task = new CTask();
$task->load( $obj->task_log_task );
$task->check();

if ($task->task_percent_complete >= 100) {
	$task->task_end_date = $obj->task_log_date;
}
$task->task_percent_complete = dPgetParam( $_POST, 'task_percent_complete', null );

if (($msg = $task->store())) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR, true );
}

if ($notify_owner) {
	if ($msg = $task->notifyOwner()) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	}
}
$AppUI->redirect();
?>