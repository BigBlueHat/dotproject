<?php /* TASKS $Id$ */

$del = isset($_POST['del']) ? $_POST['del'] : 0;
$hassign = @$_POST['hassign'];
$hdependencies = @$_POST['hdependencies'];
$notify = isset($_POST['task_notify']) ? $_POST['task_notify'] : 0;

$obj = new CTask();

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

// convert dates to SQL format first
if ($obj->task_start_date) {
	$date = new CDate( $obj->task_start_date );
	$obj->task_start_date = $date->format( FMT_DATETIME_MYSQL );
}
if ($obj->task_end_date) {
	$date = new CDate( $obj->task_end_date );
	$obj->task_end_date = $date->format( FMT_DATETIME_MYSQL );
}

//echo '<pre>';print_r( $hassign );echo '</pre>';die;
// prepare (and translate) the module name ready for the suffix
if ($del) {
	if (($msg = $obj->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( 'Task deleted' );
		$AppUI->redirect( '', -1 );
	}
} else {
	if (($msg = $obj->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( @$_POST['task_id'] ? 'Task updated' : 'Task added', UI_MSG_OK);
	}

	if (isset($hassign)) {
		$obj->updateAssigned( $hassign );
	}
	if (isset($hdependencies)) {
		$obj->updateDependencies( $hdependencies );
	}
	if ($notify) {
		if ($msg = $obj->notify()) {
			$AppUI->setMsg( $msg, UI_MSG_ERROR );
		}
	}
	$AppUI->redirect();
}
?>
