<?php
$del = isset($_POST['del']) ? $_POST['del'] : 0;

$dept = new CDepartment();

if (($msg = $dept->bind( $_POST ))) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
	$AppUI->redirect();
}

if ($del) {
	if (($msg = $dept->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( "Department deleted", UI_MSG_ALERT );
	}
} else {
	if (($msg = $dept->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$isNotNew = @$_POST['department_id'];
		$AppUI->setMsg( "Department ".($isNotNew ? 'updated' : 'inserted'), UI_MSG_OK );
	}
}
	$AppUI->redirect();
?>