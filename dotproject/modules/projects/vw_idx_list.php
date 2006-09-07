<?php /* PROJECTS $Id: vw_idx_list.php,v 1.0 caseydk Exp $ */
global $projects;
global $AppUI, $company_id, $priority, $tpl, $pstatus;

$check = $AppUI->getState( 'ProjIdxTab' );
$show_all_projects = false;
if ($check == 0)
	$show_all_projects = true;

$perms =& $AppUI->acl();
$df = $AppUI->getPref('SHDATEFORMAT');
foreach ($projects as $k => $row) {
	if (! $perms->checkModuleItem('projects', 'view', $row['project_id']))
		continue;
	if ($perms->checkModuleItem('projects', 'edit', $row['project_id']))
		$projects[$k]['edit'] = true;

	if (!$show_all_projects && $row["project_status"] != $AppUI->getState( 'ProjIdxTab' ))
		unset($projects[$k]);
	else 
		$projects[$k]['project_status_name'] = $pstatus[$row['project_status']];
}

$show = array(
	'project_priority',
	'project_percent_complete', 
	'project_name', 
	'company_name', 
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

$tpl->displayList('projects', $projects, 0, $show);
?>