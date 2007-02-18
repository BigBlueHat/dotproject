<?php /* CONTACTS $Id$ */
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

$contact_id = intval( dPgetParam( $_GET, 'contact_id', 0 ) );
$company_id = intval( dPgetParam( $_REQUEST, 'company_id', 0 ) );
$company_name = dPgetParam( $_REQUEST, 'company_name', null );

// check permissions for this record
$perms =& $AppUI->acl();
if (! ($canEdit = $perms->checkModuleItem( 'contacts', 'edit', $contact_id )) ) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

// load the record data
$msg = '';
$row = new CContact();

$canDelete = $row->canDelete( $msg, $contact_id );
if($msg == $AppUI->_('contactsDeleteUserError', UI_OUTPUT_JS)) {
	$userDeleteProtect=true;
}

if (!$row->load( $contact_id ) && $contact_id > 0) {
	$AppUI->setMsg( 'Contact' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
} else if ($row->contact_private && $row->contact_owner != $AppUI->user_id
	&& $row->contact_owner && $contact_id != 0) {
// check only owner can edit
	$AppUI->redirect( "m=public&a=access_denied" );
}

// setup the title block
$ttl = $contact_id > 0 ? "Edit Contact" : "Add Contact";
$titleBlock = new CTitleBlock( $ttl, 'monkeychat-48.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=contacts", "contacts list" );
if ($canDelete && $contact_id) {
	$titleBlock->addCrumbDelete( 'delete contact', $canDelete, $msg );
}
$titleBlock->show();
$company_detail = $row->getCompanyDetails();
$dept_detail = $row->getDepartmentDetails();
if ($contact_id == 0 && $company_id > 0) {
	$company_detail['company_id'] = $company_id;
	$company_detail['company_name'] = $company_name;
	echo $company_name;
}

$contact_owner = $row->contact_owner ? $row->contact_owner : $AppUI->user_id;
$contact_unique_update = uniqid("");

require_once(DP_BASE_DIR . '/classes/CustomFields.class.php');
$custom_fields = New CustomFields( $m, $a, $contact_id, "edit" );
$tpl->assign('customFields', $custom_fields->getHTML());
$tpl->assign('company_detail', $company_detail);
$tpl->assign('contact_id', $contact_id);
$tpl->assign('contact_owner', $contact_owner);
$tpl->assign('contact_unique_update', $contact_unique_update);
$tpl->assign('dept_detail', $dept_detail);
$tpl->assign('userDeleteProtect', $userDeleteProtect);

$tpl->displayAddEdit($row);
?>
