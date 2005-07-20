<?php /* COMPANIES $Id$ */
##
##	Companies: View Projects sub-table
##
GLOBAL $AppUI, $company_id, $pstatus, $dPconfig, $smarty, $theme;

$sort = dPgetParam($_GET, 'sort', 'project_name');
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
$q->addWhere('projects.project_active <> 0');
$q->addOrder($sort);

$smarty->assign('rows', $q->loadList());
$smarty->assign('pstatus', $pstatus);
$smarty->assign('show', array('project_name', 'project_owner', 'project_start_date', 'project_status', 'project_target_budget'));

$smarty->display($theme . '/companies/list.html');
?>