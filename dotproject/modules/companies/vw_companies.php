<?php /* COMPANIES $Id$ */

global $search_string;
global $owner_filter_id;
global $currentTabId;
global $currentTabName;
global $tabbed;
global $type_filter;
global $natab;
global $orderby;
global $orderdir;

global $tpl;

// load the company types

$types = dPgetSysVal( 'CompanyType' );
// get any records denied from viewing

$obj = new CCompany();
$allowedCompanies = $obj->getAllowedRecords($AppUI->user_id, 'company_id, company_name');

$company_type_filter = $currentTabId;

//Listing all types if ALL (-1) is selected
$companiesType = ($currentTabId != 0);

// natab keeps track of which tab stores companies with no type set.
if ($currentTabId == $natab)
	$company_type_filter = 0;

// retrieve list of records
$q  = new DBQuery;
$q->addTable('companies', 'c');
$q->addQuery('c.company_id, c.company_name, c.company_type');
$q->addQuery('c.company_description');
$q->addQuery('count(distinct p.project_id) as company_projects_active');
$q->addQuery('count(distinct p2.project_id) as company_projects_inactive');
$q->addQuery('con.contact_first_name, con.contact_last_name');
$q->addJoin('projects', 'p', 'c.company_id = p.project_company AND p.project_status != 7');
$q->addJoin('users', 'u', 'c.company_owner = u.user_id');
$q->addJoin('contacts', 'con', 'u.user_contact = con.contact_id');
$q->addJoin('projects', 'p2', 'c.company_id = p2.project_company AND p2.project_status = 7');
if (count($allowedCompanies) > 0) { $q->addWhere('c.company_id IN (' . implode(',', array_keys($allowedCompanies)) . ')'); }
if ($companiesType) { $q->addWhere('c.company_type = '.$company_type_filter); }
if ($search_string != "") { $q->addWhere("c.company_name LIKE '%$search_string%'"); }
if ($owner_filter_id > 0) { $q->addWhere("c.company_owner = $owner_filter_id "); }
$q->addGroup('c.company_id');
$q->addOrder($orderby.' '.$orderdir);
$rows = $q->loadList();

foreach($rows as $key => $value)
	$rows[$key]['company_type_name'] = $types[$rows[$key]['company_type']];

//$smarty->assign('msg', $AppUI->getMsg());
//$smarty->assign('current_url', 'index.php?m=companies');
$show = array('company_name', 'company_projects_active', 'company_projects_inactive', 'company_type');
$tpl->displayList('companies', $rows, $show);
?>