<?php /* $Id$ */
$del = isset($_REQUEST['del']) ? $_REQUEST['del'] : 0;

$user = new CUser();

if (($msg = $user->bind( $_REQUEST ))) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
	$AppUI->redirect();
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'User' );
if ($del) {
	if (($msg = $user->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
		$AppUI->redirect( '', -1 );
	}
} else {
	$isNotNew = @$_REQUEST['user_id'];
	if (!$isNotNew) {
		$user->user_owner = $AppUI->user_id;
	}
	if (($msg = $user->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( $isNotNew ? 'updated' : 'added', UI_MSG_OK, true );
	}
	$AppUI->redirect();
}
?>