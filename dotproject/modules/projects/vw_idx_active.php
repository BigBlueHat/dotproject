<?php /* PROJECTS $Id$ */
global $projects;
global $AppUI, $company_id, $priority, $tpl, $pstatus;

$perms =& $AppUI->acl();
$df = $AppUI->getPref('SHDATEFORMAT');
foreach ($projects as $k => $row) {
	if (! $perms->checkModuleItem('projects', 'view', $row['project_id']))
		continue;
		
	if ($row['project_status'] != 3)
		unset($projects[$k]);
	else 
		$projects[$k]['project_status_name'] = $pstatus[$row['project_status']];
}

$show = array(
	'project_priority',
	'project_percent_complete', 
	'project_name', 
	'project_start_date', 
	'project_end_date', 
	'project_actual_end_date',
	'project_owner',
	'total_tasks',
	'my_tasks');

$tpl->assign('pstatus', $pstatus);

$tpl->displayList('projects', $projects, $show);
?>