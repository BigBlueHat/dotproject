<?php /* SYSKEYS $Id$ */
$del = isset($_POST['del']) ? $_POST['del'] : 0;

$syskey = new CSysKey();

if (($msg = $syskey->bind( $_POST ))) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
	$AppUI->redirect();
}

if ($del) {
	if (($msg = $syskey->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( "System lookup key deleted", UI_MSG_ALERT );
	}
} else {
	if (($msg = $syskey->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$isNotNew = @$_POST['syskey_id'];
		$AppUI->setMsg( "System lookup key ".($isNotNew ? 'updated' : 'inserted'), UI_MSG_OK );
	}
}
$AppUI->redirect( "m=system&u=syskeys&a=keys" );
?>