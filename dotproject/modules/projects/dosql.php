<?php
$del = isset($_POST['del']) ? $_POST['del'] : 0;
$isNotNew = @$_POST['project_id'];

$project = new CProject();

if (($msg = $project->bind( $_POST ))) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
	$AppUI->redirect();
}
// convert dates to SQL format first
$project->project_start_date = db_unix2DateTime( $project->project_start_date );
$project->project_end_date = db_unix2DateTime( $project->project_end_date );
$project->project_actual_end_date = db_unix2DateTime( $project->project_actual_end_date );

if ($del) {
	if (($msg = $project->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( "Project deleted", UI_MSG_ALERT );
		$AppUI->redirect( "m=projects" );
	}
} else {
	if (($msg = $project->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( "Project ".($isNotNew ? 'updated' : 'inserted'), UI_MSG_OK );
	}
	$AppUI->redirect();
}
?>