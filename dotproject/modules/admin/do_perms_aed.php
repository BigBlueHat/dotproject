<?php /* ADMIN $Id$ */
$del = isset($_POST['del']) ? $_POST['del'] : 0;

$permission = new CPermission();

if (($msg = $permission->bind( $_POST ))) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
	$AppUI->redirect();
}

$AppUI->setMsg( 'Permission' );
if ($del) {
	if (($msg = $permission->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
		$AppUI->redirect();
	}
} else {
	$isNotNew = @$_POST['permission_id'];
	if (($msg = $permission->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( $isNotNew ? 'updated' : 'added', UI_MSG_OK, true );
	}
	$AppUI->redirect();
}
?>