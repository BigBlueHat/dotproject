<?php /* TASKS $Id$ */

$del = dPgetParam( $_POST, 'del', 0 );
$task_hours_worked = dPgetParam( $_POST, 'task_hours_worked', 0 );
$task_percent_complete =

$log = new CTaskLog();

if (($msg = $log->bind( $_POST ))) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
	$AppUI->redirect();
}

$log->task_log_date = db_unix2DateTime( $log->task_log_date );

if ($del) {
	if (($msg = $log->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( "Task log deleted", UI_MSG_ALERT );
	}
} else {
	if (($msg = $log->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$isNotNew = @$_POST['company_id'];
		$AppUI->setMsg( "Task log ".($isNotNew ? 'updated' : 'inserted'), UI_MSG_OK );
	}
}

$task = new CTask();
$task->load( $log->task_log_task );
$task->check();

if ($task->task_percent_complete < 100) {
	$task->task_end_date = $log->task_log_date;
}
$task->task_percent_complete = dPgetParam( $_POST, 'task_percent_complete', null );
$task->task_hours_worked += $log->task_log_hours;

if (($msg = $task->store())) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR, true );
}

$AppUI->redirect();
?>