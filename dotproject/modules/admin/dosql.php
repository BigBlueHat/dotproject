<?php
$del = isset($_POST['del']) ? $_POST['del'] : 0;

$user = new CUser();

if (($msg = $user->bind( $_POST ))) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
	$AppUI->redirect();
}

if ($del) {
	if (($msg = $user->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( "User deleted", UI_MSG_ALERT );
		$AppUI->redirect( "m=admin" );
	}
} else {
	$isNotNew = @$_POST['user_id'];
	if (!$isNotNew) {
		$user->user_owner = $AppUI->user_id;
	}
	if (($msg = $user->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( "User ".($isNotNew ? 'updated' : 'inserted'), UI_MSG_OK );
	}
	$AppUI->redirect();
}
?>