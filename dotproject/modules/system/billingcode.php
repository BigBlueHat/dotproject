<?php /* SYSTEM $Id$ */
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

/** 
 * add or edit a user preferences
 */

$company_id=0;
$company_id = isset($_REQUEST['company_id']) ? $_REQUEST['company_id'] : 0;
// Check permissions
if (!$canEdit) {
  $AppUI->redirect('m=public&a=access_denied' );
}

$q  = new DBQuery;
$q->addTable('billingcode','bc');
$q->addQuery('billingcode_id, billingcode_name, billingcode_value, billingcode_desc, billingcode_status');
$q->addOrder('billingcode_name ASC');
// $q->addWhere('bc.billingcode_status = 0');
$q->addWhere('company_id = ' . $company_id);
$billingcodes = $q->loadList();
$q->clear();

$q  = new DBQuery;
$q->addTable('companies','c');
$q->addQuery('company_id, company_name');
$q->addOrder('company_name ASC');
$company_list = $q->loadHashList();
$company_list[0] = $AppUI->_('Select Company');
$q->clear();

$company_name = $company_list[$company_id];

$titleBlock = new CTitleBlock( 'Edit Billing Codes', 'myevo-weather.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=system", "system admin" );
$titleBlock->show();

if (isset($_GET['billingcode_id'])) {
	$q->addQuery('*');
	$q->addTable('billingcode');
	$q->addWhere('billingcode_id = ' . $_GET['billingcode_id']);
	list($obj) = $q->loadList();

	$tpl->assign('billingcode_id', $_GET['billingcode_id']);
	$tpl->assign('obj', $obj);
}	
$tpl->assign('billingcodes', $billingcodes);
$tpl->assign('company_id', $company_id);
$tpl->assign('company_list', $company_list);

$tpl->displayFile('billingcode', 'system');
?>