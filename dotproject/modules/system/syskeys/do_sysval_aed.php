<?php /* SYSKEYS $Id$ */
$del = isset($_POST['del']) ? $_POST['del'] : 0;

$sysval = new CSysVal();

if (($msg = $sysval->bind( $_POST ))) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
	$AppUI->redirect();
}

$AppUI->setMsg( "System Lookup Values", UI_MSG_ALERT );
if ($del) {
	if (($msg = $sysval->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
	}
} else {
	if (($msg = $sysval->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( @$_POST['sysval_id'] ? 'updated' : 'inserted', UI_MSG_OK, true );
	}
}
$AppUI->redirect( "m=system&u=syskeys" );
?>