<?php
$del = isset($_POST['del']) ? $_POST['del'] : 0;
$hassign = isset($_POST['hassign']) ? $_POST['hassign'] : 0;
$hdependencies = isset($_POST['hdependencies']) ? $_POST['hdependencies'] : 0;
$notify = isset($_POST['notify']) ? $_POST['notify'] : 0;
$dayhour = isset($_POST['dayhour']) ? $_POST['dayhour'] : 1;

$task = new CTask();

if (($msg = $task->bind( $_POST ))) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
	$AppUI->redirect();
}
// convert dates to SQL format first
$task->task_start_date = db_unix2DateTime( $task->task_start_date );
$task->task_end_date = db_unix2DateTime( $task->task_end_date );
$task->task_duration = $task->task_duration ? $task->task_duration * $dayhour : '0';

//echo '<pre>';print_r( $task );echo '</pre>';die;
if ($del) {
	if (($msg = $task->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( "Task deleted", UI_MSG_ALERT );
		$AppUI->redirect();
	}
} else {
	if (($msg = $task->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$isNotNew = @$_POST['task_id'];
		$AppUI->setMsg( "Task ".($isNotNew ? 'updated' : 'inserted'), UI_MSG_OK );
	}

	if ($hassign) {
		$task->updateAssigned( $hassign );
	}
	if ($hdependencies) {
		$task->updateDependencies( $hdependencies );
	}
	if ($notify) {
		if ($msg = $task->notify()) {
			$AppUI->setMsg( $msg, UI_MSG_ERROR );
		}
	}
	$AppUI->redirect();
}
?>