<?php /* TASKS $Id$ */

$del = dPgetParam( $_POST, 'del', 0 );
$task_hours_worked = dPgetParam( $_POST, 'task_hours_worked', 0 );

$obj = new CTaskLog();

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

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
} else {
	if (($msg = $obj->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
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
$task->task_hours_worked += $obj->task_log_hours;

if (($msg = $task->store())) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR, true );
}

$AppUI->redirect();
?>