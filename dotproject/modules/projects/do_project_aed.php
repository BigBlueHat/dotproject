<?php /* PROJECTS $Id$ */
$obj = new CProject();
$msg = '';

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}
// convert dates to SQL format first
$date = new Date( $obj->project_start_date, DATE_FORMAT_TIMESTAMP_DATE );
$obj->project_start_date = $date->format( DATE_FORMAT_ISO );

if ($obj->project_end_date) {
	$date = new Date( $obj->project_end_date, DATE_FORMAT_TIMESTAMP_DATE );
	$obj->project_end_date = $date->format( DATE_FORMAT_ISO );
}
if ($obj->project_actual_end_date) {
	$date = new Date( $obj->project_actual_end_date, DATE_FORMAT_TIMESTAMP_DATE );
	$obj->project_actual_end_date = $date->format( DATE_FORMAT_ISO );
}

$del = dPgetParam( $_POST, 'del', 0 );

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Project' );
if ($del) {
	if (!$obj->canDelete( $msg )) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	}
	if (($msg = $obj->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
		$AppUI->redirect( "", -1 );
	}
} else {
	if (($msg = $obj->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$isNotNew = @$_POST['project_id'];
		$AppUI->setMsg( $isNotNew ? 'updated' : 'inserted', UI_MSG_OK, true );
	}
	$AppUI->redirect();
}
?>