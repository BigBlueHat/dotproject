<?php /* TASKS $Id$ */
$AppUI->savePlace();

// act on passed parameters
if (isset( $_POST['f'] )) {
	$AppUI->setState( 'TaskIdxFilter', $_POST['f'] );
}
$f = $AppUI->getState( 'TaskIdxFilter' ) ? $AppUI->getState( 'TaskIdxFilter' ) : 'my';

if (isset( $_GET['project_id'] )) {
	$AppUI->setState( 'TaskIdxProject', $_GET['project_id'] );
}
$project_id = $AppUI->getState( 'TaskIdxProject' ) ? $AppUI->getState( 'TaskIdxProject' ) : 0;
$AppUI->setState( 'ActiveProject', $project_id );


// setup the title block
$titleBlock = new CTitleBlock( 'Tasks', 'tasks.gif', $m, "$m.$a" );
$titleBlock->addCell( $AppUI->_('Filter') . ':' );
$titleBlock->addCell(
	arraySelect( $filters, 'f', 'size=1 class=text onChange="document.taskFilter.submit();"', $f, true ), '',
	'<form action="?m=tasks" method="post" name="taskFilter">', '</form>'
);
$titleBlock->addCell();
if ($canEdit && $project_id) {
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new task').'">', '',
		'<form action="?m=tasks&a=addedit&task_project=' . $project_id . '" method="post">', '</form>'
	);
}
$titleBlock->addCrumb( "?m=tasks&a=todo", "my todo" );
$titleBlock->show();

// include the re-usable sub view
	$min_view = false;
	include("{$AppUI->cfg['root_dir']}/modules/tasks/tasks.php");
?>