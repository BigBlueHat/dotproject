<?php /* PROJECTS $Id$ */
global $projects;
global $AppUI, $company_id, $priority, $tpl, $pstatus;

$perms =& $AppUI->acl();
$df = $AppUI->getPref('SHDATEFORMAT');
foreach ($projects as $k => $row) {
	if (! $perms->checkModuleItem('projects', 'view', $row['project_id']))
		continue;
		
	if ($row['project_active'] < 1)
		unset($projects[$k]);
	else 
		$projects[$k]['project_status_name'] = $pstatus[$row['project_status']];
}

$show = array(
	'project_priority',
	'project_percent_complete', 
	'project_name', 
	'company_name', 
	'project_owner',
	'total_tasks',
	'project_end_date');

$tpl->assign('pstatus', $pstatus);

$tpl->displayList('projects', $projects, $show);
?>
