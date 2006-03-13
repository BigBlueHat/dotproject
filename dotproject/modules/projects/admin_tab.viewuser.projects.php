<?php /* PROJECTS $Id$ */
global $a, $AppUI, $buffer, $company_id, $department, $min_view, $m, $orderby, $orderdir, $priority, $projects, $pstatus, $tab, $tpl, $user_id;

$perms =& $AppUI->acl();
$df = $AppUI->getPref('SHDATEFORMAT');

$pstatus =  dPgetSysVal( 'ProjectStatus' );

if (isset(  $_POST['proFilter'] )) {
	$AppUI->setState( 'UsrProjectIdxFilter',  $_POST['proFilter'] );
}
$proFilter = $AppUI->getState( 'UsrProjectIdxFilter' ) !== NULL ? $AppUI->getState( 'UsrProjectIdxFilter' ) : '-1';

$projFilter = arrayMerge( array('-1' => 'All Projects'), $pstatus);
$projFilter = arrayMerge( array( '-2' => 'All w/o in progress'), $projFilter);
natsort($projFilter);

// load the companies class to retrieved denied companies
require_once( $AppUI->getModuleClass( 'companies' ) );

// retrieve any state parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'UsrProjIdxTab', $_GET['tab'] );
}

if (isset( $_GET['orderby'] )) {
    $orderdir = $AppUI->getState( 'UsrProjIdxOrderDir' ) ? ($AppUI->getState( 'UsrProjIdxOrderDir' )== 'asc' ? 'desc' : 'asc' ) : 'desc';    
    $AppUI->setState( 'UsrProjIdxOrderBy', $_GET['orderby'] );
    $AppUI->setState( 'UsrProjIdxOrderDir', $orderdir);
}
$orderby  = $AppUI->getState( 'UsrProjIdxOrderBy' ) ? $AppUI->getState( 'UsrProjIdxOrderBy' ) : 'project_end_date';
$orderdir = $AppUI->getState( 'UsrProjIdxOrderDir' ) ? $AppUI->getState( 'UsrProjIdxOrderDir' ) : 'asc';

$extraGet = '&user_id='.$user_id;

require("{$dPconfig['root_dir']}/functions/projects_func.php");
require_once( $AppUI->getModuleClass( 'projects' ) );

// collect the full projects list data via function in projects.class.php
projects_list_data($user_id);
//if ($proFilter == -1 || $row["project_status"] == $proFilter || ($proFilter == -2 && $row["project_status"] != 3) ) {
foreach ($projects as $k => $row) {
	if (! $perms->checkModuleItem('projects', 'view', $row['project_id']))
		continue;
		
	if ($proFilter == -1 || $row["project_status"] == $proFilter || ($proFilter == -2 && $row["project_status"] != 3) )
		$projects[$k]['project_status_name'] = $pstatus[$row['project_status']];
	else 

		unset($projects[$k]);
}

$show = array(
	'project_priority',
	'project_percent_complete', 
	'project_name', 
	'company_name', 
	'project_start_date', 
	'project_duration',
	'project_end_date', 
	'project_actual_end_date',
	'project_owner',
	'total_tasks',
	'my_tasks',
	'project_status');

$tpl->assign('buffer', $buffer);
$tpl->assign('cols', count($show));
$tpl->assign('proFilter', $proFilter);
$tpl->assign('projFilter', $projFilter);
$tpl->assign('pstatus', $pstatus);
$tpl->assign('showFilters', true);
$tpl->assign('tab', $tab);
$tpl->assign('user_id', $user_id);


$tpl->assign('current_url', 'index.php?m='.$m.'&a='.$a.'&user_id='.$user_id);

$tpl->displayList('projects', $projects, $show);
?>
