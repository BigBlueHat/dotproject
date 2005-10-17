<?php /* COMPANIES $Id$ */
##
##	Companies: View User sub-table
##
global $AppUI, $company_id, $tpl;

$q  = new DBQuery;
$q->addTable('users');
$q->addQuery('user_id, user_username, contact_first_name, contact_last_name');
$q->addJoin('contacts', 'c', 'users.user_contact = contact_id');
$q->addWhere('contact_company = '.$company_id);
$q->addOrder('contact_last_name'); 

$tpl->assign('msg', $AppUI->getMsg());

$tpl->assign('list', $q->loadList());
$tpl->displayFile('simple_list', 'admin');
?>