<?php
$del = isset($_POST['del']) ? $_POST['del'] : 0;

$company = new CCompany();

if (($msg = $company->bind( $_POST ))) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
	$AppUI->redirect();
}

if ($del) {
	if (($msg = $company->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( "Company deleted", UI_MSG_ALERT );
		$AppUI->redirect( "m=companies" );
	}
} else {
	if (($msg = $company->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$isNotNew = @$_POST['company_id'];
		$AppUI->setMsg( "Company ".($isNotNew ? 'updated' : 'inserted'), UI_MSG_OK );
	}
	$AppUI->redirect();
}
?>