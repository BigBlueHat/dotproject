<?php
$del = isset($_POST['del']) ? $_POST['del'] : 0;

$permission = new CPermission();

if (($msg = $permission->bind( $_POST ))) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
	$AppUI->redirect();
}

if ($del) {
	if (($msg = $permission->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( "Permission deleted", UI_MSG_ALERT );
		$AppUI->redirect();
	}
} else {
	$isNotNew = @$_POST['permission_id'];
	if (($msg = $permission->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( "Permission ".($isNotNew ? 'updated' : 'inserted'), UI_MSG_OK );
	}
	$AppUI->redirect();
}
?>