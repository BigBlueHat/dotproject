<?php /* COMPANIES $Id$ */
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

/**
 * Companies: View User sub-table
 */
global $AppUI, $company_id, $tpl;

$q  = new DBQuery;
$q->addQuery('user_id, user_username');
$q->addQuery('contact_first_name, contact_last_name');
$q->addTable('users');
$q->addJoin('contacts', 'c', 'users.user_contact = contact_id');
$q->addWhere('contact_company = '.$company_id);
$q->addOrder('contact_last_name'); 

$tpl->assign('list', $q->loadList());
$tpl->assign('msg', $AppUI->getMsg());

$tpl->displayFile('simple_list', 'admin');
?>