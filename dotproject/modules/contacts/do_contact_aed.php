<?php /* CONTACTS $Id$ */
$del = isset($_POST['del']) ? $_POST['del'] : 0;

$contact = new CContact();

if (($msg = $contact->bind( $_POST ))) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
	$AppUI->redirect();
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Contact' );
if ($del) {
	if (($msg = $contact->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
		$AppUI->redirect( "m=contacts" );
	}
} else {
	$isNotNew = @$_POST['contact_id'];
	if (!$isNotNew) {
		$contact->contact_owner = $AppUI->user_id;
	}
	if (($msg = $contact->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( $isNotNew ? 'updated' : 'added', UI_MSG_OK, true );
	}
	$AppUI->redirect();
}
?>