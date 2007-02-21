<?php /* COMPANIES $Id$ */
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

/**	
 * Companies: View User sub-table
 */
global $AppUI, $company_id, $obj, $tpl;

require_once $AppUI->getModuleClass('contacts');
$q  = new DBQuery;
$q->addTable('contacts');
$q->addWhere("contact_company = '" . addslashes($obj->company_name) . "' OR contact_company = '$obj->company_id'");
$q->addOrder('contact_last_name'); 
$rows = $q->loadList();

foreach ($rows as $key => $row)
{
	$contact =& new CContact;
	$contact->bind($row);
	$dept_detail = $contact->getDepartmentDetails();
	$rows[$key]['dept_name'] = $dept_detail['dept_name'];
}

$tpl->assign('company_id', $company_id);
$tpl->assign('company_name', $obj->company_name);
$tpl->assign('msg', $AppUI->getMsg());

$tpl->displayList('contacts', $rows, count($rows), array('contact_order_by', 'contact_email', 'contact_phone', 'company_name'));
?>