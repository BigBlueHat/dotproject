<?php
$del = isset($_GET['del']) ? $_GET['del'] : 0;

$user = new CUser();

if (($msg = $user->bind( $_REQUEST ))) {
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
	$isNotNew = @$_REQUEST['user_id'];
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
