<?php /* PROJECTS $Id$ */
$project = new CProject();

if (($msg = $project->bind( $_POST ))) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
	$AppUI->redirect();
}
// convert dates to SQL format first
$project->project_start_date = db_unix2DateTime( $project->project_start_date );
$project->project_end_date = db_unix2DateTime( $project->project_end_date );
$project->project_actual_end_date = db_unix2DateTime( $project->project_actual_end_date );

$del = isset($_POST['del']) ? $_POST['del'] : 0;

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Company' );
if ($del) {
	if (($msg = $project->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
		$AppUI->redirect( "", -1 );
	}
} else {
	if (($msg = $project->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$isNotNew = @$_POST['project_id'];
		$AppUI->setMsg( $isNotNew ? 'updated' : 'inserted', UI_MSG_OK, true );
	}
	$AppUI->redirect();
}
?>