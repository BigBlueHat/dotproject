<?php /* COMPANIES $Id$ */
$del = isset($_POST['del']) ? $_POST['del'] : 0;
$company = new CCompany();

if (($msg = $company->bind( $_POST ))) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
	$AppUI->redirect();
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Company' );
if ($del) {
	if (($msg = $company->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( 'deleted', UI_MSG_ALERT, true );
		$AppUI->redirect( '', -1 );
	}
} else {
	if (($msg = $company->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$isNotNew = @$_POST['company_id'];
		$AppUI->setMsg( $isNotNew ? 'added' : 'inserted', UI_MSG_OK, true );
	}
	$AppUI->redirect();
}
?>