<?php /* SYSKEYS $Id$ */
$del = isset($_POST['del']) ? $_POST['del'] : 0;

$sysval = new CSysVal();

if (($msg = $sysval->bind( $_POST ))) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
	$AppUI->redirect();
}

if ($del) {
	if (($msg = $sysval->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( "System lookup value deleted", UI_MSG_ALERT );
	}
} else {
	if (($msg = $sysval->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$isNotNew = @$_POST['sysval_id'];
		$AppUI->setMsg( "System lookup value ".($isNotNew ? 'updated' : 'inserted'), UI_MSG_OK );
	}
}
$AppUI->redirect( "m=system&u=syskeys" );
?>