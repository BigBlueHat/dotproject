<?php
$del = isset($HTTP_POST_VARS['del']) ? $HTTP_POST_VARS['del'] : 0;
$isNotNew = @$HTTP_POST_VARS['company_id'];

$company = new CCompany();

if (($msg = $company->bind( $HTTP_POST_VARS ))) {
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
		$AppUI->setMsg( "Company ".($isNotNew ? 'updated' : 'inserted'), UI_MSG_OK );
	}
	$AppUI->redirect();
}
?>
