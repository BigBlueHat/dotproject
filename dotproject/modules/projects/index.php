<?php  /* PROJECTS $Id$ */
$AppUI->savePlace();

// Set up 'filters'

if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'ProjIdxTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'ProjIdxTab' ) !== NULL ? $AppUI->getState( 'ProjIdxTab' ) : 0;

if (isset( $_POST['company_id'] )) {
	$AppUI->setState( 'ProjIdxCompany', $_POST['company_id'] );
}
$company_id = $AppUI->getState( 'ProjIdxCompany' ) !== NULL ? $AppUI->getState( 'ProjIdxCompany' ) : $AppUI->user_company;

if (isset( $_GET['orderby'] )) {
	$AppUI->setState( 'ProjIdxOrderBy', $_GET['orderby'] );
}
$orderby = $AppUI->getState( 'ProjIdxOrderBy' ) ? $AppUI->getState( 'ProjIdxOrderBy' ) : 'project_end_date';

$active = intval( !$AppUI->getState( 'ProjIdxTab' ) );

// get read denied projects
$deny = array();
$sql = "
SELECT project_id, project_id
FROM projects, permissions
WHERE permission_user = $AppUI->user_id
	AND permission_grant_on = 'projects'
	AND permission_item = project_id
	AND permission_value = 0
";
$deny = db_loadHashList( $sql );

// pull projects
$sql = "
SELECT
	project_id, project_active, project_status, project_color_identifier, project_name,
	project_start_date, project_end_date, project_actual_end_date,
	project_color_identifier,
	COUNT(distinct t1.task_id) AS total_tasks,
	COUNT(distinct t2.task_id) AS my_tasks,
	user_username,
	SUM(t1.task_duration*t1.task_precent_complete)/sum(t1.task_duration) as project_precent_complete
FROM permissions,projects
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

$sql = "SELECT company_id,company_name FROM companies ORDER BY company_name";
$companies = arrayMerge( array( '0'=>'All' ), db_loadHashList( $sql ) );


$titleBlock = new CTitleBlock( 'Projects', 'projects.gif', $m, 'ID_HELP_PROJ_IDX' );

$titleBlock->addCell(
	'align="right" width="100%" nowrap="nowrap"',
	$AppUI->_('Company') . ':',
	'<form action="?m=projects" method="post" name="pickCompany">'
);
$titleBlock->addCell(
	'',
	arraySelect( $companies, 'company_id', 'onChange="document.pickCompany.submit()" class="text"', $company_id ),
	'',
	'</form>'
);

$titleBlock->addCell();

if ($canEdit) {
	$titleBlock->addCell(
		'nowrap="nowrap"',
		'<input type="submit" class="button" value="'.$AppUI->_('new project').'">',
		'<form action="?m=projects&a=addedit" method="post">',
		'</form>'
	);
}
$titleBlock->show();

// tabbed information boxes
$tabBox = new CTabBox( "?m=projects&orderby=$orderby", "{$AppUI->cfg['root_dir']}/modules/projects/", $tab );
$tabBox->add( 'vw_idx_active', 'Active Projects' );
$tabBox->add( 'vw_idx_archived', 'Archived Projects' );
$tabBox->show();
?>