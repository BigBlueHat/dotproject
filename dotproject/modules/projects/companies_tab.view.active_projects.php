<?php /* PROJECTS $Id$ */
GLOBAL $AppUI, $company_id, $pstatus, $dPconfig, $tpl, $m;

$pstatus = dPgetSysVal( 'ProjectStatus' );

$sort = dPgetParam($_GET, 'orderby', 'project_name');
if ($sort == 'project_priority')
        $sort .= ' DESC';

$df = $AppUI->getPref('SHDATEFORMAT');

$q  = new DBQuery;
$q->addTable('projects');
$q->addQuery('project_id, project_name, project_start_date, project_status, project_target_budget,
	project_start_date,
        project_priority,
	contact_first_name, contact_last_name');
$q->addJoin('users', 'u', 'u.user_id = projects.project_owner');
$q->addJoin('contacts', 'con', 'u.user_contact = con.contact_id');
$q->addWhere('projects.project_company = '.$company_id);
$q->addWhere('projects.project_status != 7');
$q->addOrder($sort);
$q->setPageLimit();
$rows = $q->loadList();

$q->addTable('projects');
$q->addQuery('count(*)');
$q->addWhere('projects.project_company = '.$company_id);
$q->addWhere('projects.project_status != 7');
$count_rows = $q->loadResult();

$tpl->assign('current_url', 'index.php?m=companies&a=view&company_id=' . $company_id);
$tpl->assign('pstatus', $pstatus);

$show = array('project_priority', 'project_name', 'project_owner', 'project_start_date', 'project_status', 'project_target_budget');

$tpl->displayList('projects', $rows, $count_rows, $show);
?>