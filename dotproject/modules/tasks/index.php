<?php /* TASKS $Id$ */
$AppUI->savePlace();

// retrieve any state parameters
if (isset( $_POST['f'] )) {
	$AppUI->setState( 'TaskIdxFilter', $_POST['f'] );
}
$f = $AppUI->getState( 'TaskIdxFilter' ) ? $AppUI->getState( 'TaskIdxFilter' ) : 'my';

if (isset( $_POST['f2'] )) {
	$AppUI->setState( 'CompanyIdxFilter', $_POST['f2'] );
}
$f2 = $AppUI->getState( 'CompanyIdxFilter' ) ? $AppUI->getState( 'CompanyIdxFilter' ) : 'all';

if (isset( $_GET['project_id'] )) {
	$AppUI->setState( 'TaskIdxProject', $_GET['project_id'] );
}
$project_id = $AppUI->getState( 'TaskIdxProject' ) ? $AppUI->getState( 'TaskIdxProject' ) : 0;

// get CCompany() to filter tasks by company
require_once( $AppUI->getModuleClass( 'companies' ) );
$obj = new CCompany();
$companies = $obj->getAllowedRecords( $AppUI->user_id, 'company_id,company_name', 'company_name' );
$filters2 = arrayMerge(  array( 'all' => $AppUI->_('All Companies') ), $companies );

// setup the title block
$titleBlock = new CTitleBlock( 'Tasks', 'applet-48.png', $m, "$m.$a" );

$titleBlock->addCell( $AppUI->_('Company Filter') . ':' );
$titleBlock->addCell(
	arraySelect( $filters2, 'f2', 'size=1 class=text onChange="document.companyFilter.submit();"', $f2, false ), '',
	'<form action="?m=tasks" method="post" name="companyFilter">', '</form>'
);

$titleBlock->addCell( '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $AppUI->_('Task Filter') . ':' );
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

if ( dPgetParam( $_GET, 'inactive', '' ) == 'toggle' )
	$AppUI->setState( 'inactive', $AppUI->getState( 'inactive' ) == -1 ? 0 : -1 );
$in = $AppUI->getState( 'inactive' ) == -1 ? '' : 'in';

$titleBlock->addCrumb( "?m=tasks&a=todo", "my todo" );
$titleBlock->addCrumb( "?m=tasks&inactive=toggle", "show ".$in."active tasks" );
$titleBlock->show();

// include the re-usable sub view
	$min_view = false;
	include("{$AppUI->cfg['root_dir']}/modules/tasks/tasks.php");
?>
