<?php /* TASKS $Id$ */
$AppUI->savePlace();

$log_all_projects = true; // show tasks for all projects
$df = $AppUI->getPref('SHDATEFORMAT'); // get the prefered date format

// setup the title block
$titleBlock = new CTitleBlock( 'Tasks per User', 'applet-48.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=tasks", "tasks list" );
$titleBlock->addCrumb( "?m=tasks&a=todo&user_id=$user_id", "my todo" );
$titleBlock->show();

// include the re-usable sub view
	$min_view = false;
	include("{$AppUI->cfg['root_dir']}/modules/tasks/tasksperuser_sub.php");

?>
