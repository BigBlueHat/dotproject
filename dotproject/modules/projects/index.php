<?php  /* PROJECTS $Id$ */
$AppUI->savePlace();

// load the companies class to retrieved denied companies
require_once( $AppUI->getModuleClass( 'companies' ) );

// retrieve any state parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'ProjIdxTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'ProjIdxTab' ) !== NULL ? $AppUI->getState( 'ProjIdxTab' ) : 0;
$active = intval( !$AppUI->getState( 'ProjIdxTab' ) );

if (isset( $_POST['company_id'] )) {
	$AppUI->setState( 'ProjIdxCompany', $_POST['company_id'] );
}
$company_id = $AppUI->getState( 'ProjIdxCompany' ) !== NULL ? $AppUI->getState( 'ProjIdxCompany' ) : $AppUI->user_company;

if (isset( $_GET['orderby'] )) {
	$AppUI->setState( 'ProjIdxOrderBy', $_GET['orderby'] );
}
$orderby = $AppUI->getState( 'ProjIdxOrderBy' ) ? $AppUI->getState( 'ProjIdxOrderBy' ) : 'project_end_date';

// get any records denied from viewing
$obj = new CProject();
$deny = $obj->getDeniedRecords( $AppUI->user_id );

// retrieve list of records
$sql = "
SELECT
	project_id, project_active, project_status, project_color_identifier, project_name,
	project_start_date, project_end_date, project_actual_end_date,
	project_color_identifier,
	project_company, company_name,
	COUNT(distinct t1.task_id) AS total_tasks,
	COUNT(distinct t2.task_id) AS my_tasks,
	user_username,
	SUM(t1.task_duration*t1.task_duration_type*t1.task_percent_complete)/sum(t1.task_duration*t1.task_duration_type) as project_percent_complete
FROM permissions,projects
LEFT JOIN companies ON company_id = projects.project_company
LEFT JOIN users ON projects.project_owner = users.user_id
LEFT JOIN tasks t1 ON projects.project_id = t1.task_project
LEFT JOIN tasks t2 ON projects.project_id = t2.task_project
	AND t2.task_owner = $AppUI->user_id
WHERE permission_user = $AppUI->user_id
	AND permission_value <> 0
	AND (
		(permission_grant_on = 'all')
		OR (permission_grant_on = 'projects' AND permission_item = -1)
		OR (permission_grant_on = 'projects' AND permission_item = project_id)
		)"
.(count($deny) > 0 ? "\nAND project_id NOT IN (" . implode( ',', $deny ) . ')' : '')
.($company_id ? "\nAND project_company = $company_id" : '')
."
GROUP BY project_id
ORDER BY $orderby
";

$projects = db_loadList( $sql );

// get the list of permitted companies
$obj = new CCompany();
$companies = $obj->getAllowedRecords( $AppUI->user_id, 'company_id,company_name', 'company_name' );
$companies = arrayMerge( array( '0'=>$AppUI->_('All') ), $companies );

// setup the title block
$titleBlock = new CTitleBlock( 'Projects', 'applet3-48.png', $m, "$m.$a" );
$titleBlock->addCell( $AppUI->_('Company') . ':' );
$titleBlock->addCell(
	arraySelect( $companies, 'company_id', 'onChange="document.pickCompany.submit()" class="text"', $company_id ), '',
	'<form action="?m=projects" method="post" name="pickCompany">', '</form>'
);
$titleBlock->addCell();
if ($canEdit) {
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new project').'">', '',
		'<form action="?m=projects&a=addedit" method="post">', '</form>'
	);
}
$titleBlock->show();

// tabbed information boxes
$tabBox = new CTabBox( "?m=projects&orderby=$orderby", "{$AppUI->cfg['root_dir']}/modules/projects/", $tab );
$tabBox->add( 'vw_idx_active', 'Active Projects' );
$tabBox->add( 'vw_idx_complete', 'Completed Projects' );
$tabBox->add( 'vw_idx_archived', 'Archived Projects' );
$tabBox->show();
?>