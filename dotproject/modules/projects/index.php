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

// BUG FIX: Selecting all companies didn't work
$company_id = $AppUI->getState( 'ProjIdxCompany' ) !== NULL ? $AppUI->getState( 'ProjIdxCompany' ) : $AppUI->user_company;
//$company_id = $AppUI->getState( 'ProjIdxCompany' );

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
LEFT JOIN companies ON projects.project_company = company_id
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
.($company_id ? "\nAND projects.project_company = '$company_id'" : '')
."
GROUP BY project_id
ORDER BY $orderby
";
//echo "<pre>$sql</pre>";

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

$project_types = dPgetSysVal("ProjectStatus");

$fixed_project_type_file = array("In Progress" => "vw_idx_active",
                                 "Complete"    => "vw_idx_complete",
                                 "Archived"    => "vw_idx_archived");
// we need to manually add Archived project type because this status is defined by 
// other field (Active) in the project table, not project_status
$project_types[] = "Archived";

// Only display the All option in tabbed view, in plain mode it would just repeat everything else
// already in the page
if ( $tab != -1 ) {
	$project_types[0] = "All Projects";
	$project_types[] = "Not Defined";
}

/**
* Now, we will figure out which vw_idx file are available
* for each project type using the $fixed_project_type_file array 
*/
$project_type_file = array();

foreach($project_types as $project_type){
	$project_type = trim($project_type);
	if(isset($fixed_project_type_file[$project_type])){
		$project_file_type[$project_type] = $fixed_project_type_file[$project_type];
	} else { // if there is no fixed vw_idx file, we will use vw_idx_proposed
		$project_file_type[$project_type] = "vw_idx_proposed";
	}
}

$show_all_projects = false;
if($tab == 0) $show_all_projects = true;

// tabbed information boxes
$tabBox = new CTabBox( "?m=projects&orderby=$orderby", "{$AppUI->cfg['root_dir']}/modules/projects/", $tab );
foreach($project_types as $project_type)
	$tabBox->add($project_file_type[$project_type], $project_type);

$tabBox->show();
?>
