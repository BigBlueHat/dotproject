<?php /* COMPANIES $Id$ */
$del = dPgetParam( $_POST, 'del', 0 );
$obj = new CCompany();
$msg = '';

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

//Assign custom fields to task_custom for them to be saved
$custom_fields = dPgetSysVal("CompanyCustomFields");
$custom_field_data = array();
if ( count($custom_fields) > 0 ){
	foreach ( $custom_fields as $key => $array ) {
		$custom_field_data[$key] = $_POST["custom_$key"];
	}
	$obj->company_custom = serialize($custom_field_data);
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Company' );
if ($del) {
	if (!$obj->canDelete( $msg )) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	}
	if (($msg = $obj->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( 'deleted', UI_MSG_ALERT, true );
		$AppUI->redirect( '', -1 );
	}
} else {
	if (($msg = $obj->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( @$_POST['company_id'] ? 'updated' : 'added', UI_MSG_OK, true );
	}
	$AppUI->redirect();
}
?>