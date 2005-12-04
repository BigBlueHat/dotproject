<?php /* PROJECTS $Id$ */
global $projects;
global $AppUI, $company_id, $priority, $tpl, $pstatus, $currentTabId, $currentTabName;

$check = $AppUI->_('All Projects', UI_OUTPUT_RAW);
$show_all_projects = false;
if ( stristr($currentTabName, $check) !== false)
	$show_all_projects = true;

//Tabbed view
$project_status_filter = $currentTabId;
//Project not defined
if ($currentTabId == count($project_types)-1)
	$project_status_filter = 0;
	
$perms =& $AppUI->acl();
$df = $AppUI->getPref('SHDATEFORMAT');
foreach ($projects as $k => $row) {
	if (! $perms->checkModuleItem('projects', 'view', $row['project_id']))
		continue;
	if ($perms->checkModuleItem('projects', 'edit', $row['project_id']))
		$projects[$k]['edit'] = true;
		
	if (!$show_all_projects &&
	    $row["project_status"] != $project_status_filter)
		unset($projects[$k]);
	else 
		$projects[$k]['project_status_name'] = $pstatus[$row['project_status']];
}

$show = array(
	'project_priority',
	'project_percent_complete', 
	'company_name', 
	'project_name', 
	'project_start_date', 
	'project_end_date', 
	'project_actual_end_date',
	'project_owner',
	'total_tasks',
	'my_tasks');
if ($show_all_projects)
	$show[] = 'project_status';

$tpl->assign('pstatus', $pstatus);
$tpl->assign('editProjects', true);

$tpl->displayList('projects', $projects, $show);
?>
