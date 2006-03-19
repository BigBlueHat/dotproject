<?php /* COMPANIES $Id$ */
##
##	Companies: View Archived Projects sub-table
##
GLOBAL $AppUI, $company_id, $tpl; 

$sort = dPgetParam($_GET, 'orderby', 'project_name');

$page = isset($_REQUEST['page'])?$_REQUEST['page']:1;

$q  = new DBQuery;
$q->addTable('projects');
$q->addQuery('project_id, project_name, project_start_date, project_status, project_target_budget,
	project_start_date,
        project_priority,
	contact_first_name, contact_last_name');
$q->addJoin('users', 'u', 'u.user_id = projects.project_owner');
$q->addJoin('contacts', 'con', 'u.user_contact = con.contact_id');
$q->addWhere('projects.project_company = '.$company_id);
$q->addWhere('projects.project_status = 7');
$q->addOrder($sort);
$q->setPageLimit();
$rows = $q->loadList();

$q->addTable('projects');
$q->addQuery('count(*)');
$q->addWhere('projects.project_company = '.$company_id);
$q->addWhere('projects.project_status = 7');
$q->addOrder($sort);
$count_rows = $q->loadResult();

$tpl->assign('msg', $AppUI->getMsg());
$tpl->assign('current_url', 'index.php?m=companies&a=view&company_id=' . $company_id);

//$smarty->assign('pstatus', $pstatus);
$show = array('project_priority', 'project_name', 'project_owner');

$tpl->displayList('projects', $rows, $count_rows, $show);
?>