<?php
$del = isset($_POST['del']) ? $_POST['del'] : 0;

$contact = new CContact();

if (($msg = $contact->bind( $_POST ))) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
	$AppUI->redirect();
}

if ($del) {
	if (($msg = $contact->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( "Contact deleted", UI_MSG_ALERT );
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
		$AppUI->setMsg( "Contact ".($isNotNew ? 'updated' : 'inserted'), UI_MSG_OK );
	}
	$AppUI->redirect();
}
?>