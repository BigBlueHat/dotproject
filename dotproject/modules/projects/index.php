<?php  /* PROJECTS $Id$ */
$AppUI->savePlace();

// load the companies class to retrieved denied companies
require_once( $AppUI->getModuleClass( 'companies' ) );

// Let's update project status!
if(isset($_GET["update_project_status"]) && isset($_GET["project_status"]) && isset($_GET["project_id"]) ){
	$projects_id = $_GET["project_id"]; // This must be an array

	foreach($projects_id as $project_id){
		$sql = "UPDATE projects
		        SET project_status = '{$_GET['project_status']}'
				WHERE project_id   = '$project_id'";
		db_exec( $sql );
		echo db_error();
	}
}
// End of project status update

// retrieve any state parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'ProjIdxTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'ProjIdxTab' ) !== NULL ? $AppUI->getState( 'ProjIdxTab' ) : 0;
$active = intval( !$AppUI->getState( 'ProjIdxTab' ) );

if (isset( $_POST['company_id'] )) {
	$AppUI->setState( 'ProjIdxCompany', intval( $_POST['company_id'] ) );
}
$company_id = $AppUI->getState( 'ProjIdxCompany' ) !== NULL ? $AppUI->getState( 'ProjIdxCompany' ) : $AppUI->user_company;

if (isset( $_GET['orderby'] )) {
	$AppUI->setState( 'ProjIdxOrderBy', $_GET['orderby'] );
}
$orderby = $AppUI->getState( 'ProjIdxOrderBy' ) ? $AppUI->getState( 'ProjIdxOrderBy' ) : 'project_end_date';

// get any records denied from viewing
$obj = new CProject();
$deny = $obj->getDeniedRecords( $AppUI->user_id );

// Task sum table
// by Pablo Roca (pabloroca@mvps.org)
// 16 August 2003

$sql = "
CREATE TEMPORARY TABLE tasks_sum
 SELECT task_project,
 COUNT(distinct task_id) AS total_tasks,
 SUM(task_duration*task_duration_type*task_percent_complete)/sum(task_duration*task_duration_type) as project_percent_complete
 FROM tasks GROUP BY task_project
";

$tasks_sum = db_exec($sql);

// temporary My Tasks
// by Pablo Roca (pabloroca@mvps.org)
// 16 August 2003
$sql = "
CREATE TEMPORARY TABLE tasks_summy
 SELECT task_project, COUNT(distinct task_id) AS my_tasks
 FROM tasks 
 WHERE task_owner = $AppUI->user_id GROUP BY task_project
";

$tasks_summy = db_exec($sql);

// retrieve list of records
// modified for speed
// by Pablo Roca (pabloroca@mvps.org)
// 16 August 2003
$sql = "
SELECT
	project_id, project_active, project_status, project_color_identifier, project_name, project_description,
	project_start_date, project_end_date, project_actual_end_date,
	project_color_identifier,
	project_company, company_name, project_status,
	tasks_sum.total_tasks,
	tasks_summy.my_tasks,
	tasks_sum.project_percent_complete,
	user_username
FROM permissions,projects
LEFT JOIN companies ON company_id = projects.project_company
LEFT JOIN users ON projects.project_owner = users.user_id
LEFT JOIN tasks_sum ON projects.project_id = tasks_sum.task_project
LEFT JOIN tasks_summy ON projects.project_id = tasks_summy.task_project
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
LIMIT 0,50
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
$tabBox->add( 'vw_idx_active'  , 'Active Projects' );
$tabBox->add( 'vw_idx_proposed', 'Proposed Projects' );
$tabBox->add( 'vw_idx_complete', 'Completed Projects' );
$tabBox->add( 'vw_idx_archived', 'Archived Projects' );
$tabBox->show();
?>
