<?php /* CONTACTS $Id$ */
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

require_once(DP_BASE_DIR . '/classes/CustomFields.class.php');

$contact_id = intval( dPgetParam( $_GET, 'contact_id', 0 ) );
$AppUI->savePlace();

// check permissions for this record
//$canEdit = !getDenyEdit( $m, $contact_id );
//if (!$canEdit) {
//	$AppUI->redirect( "m=public&a=access_denied" );
//}

// load the record data
$msg = '';
$row = new CContact();
$row->contact_id = $contact_id;
$row->load();
$canDelete = $row->canDelete( $msg, $contact_id );
// Don't allow to delete contacts, that have a user associated to them.
$q  = new DBQuery;
$q->addTable('users');
$q->addQuery('user_id');
$q->addWhere('user_contact = ' . $row->contact_id);
$sql = $q->prepare();
$q->clear();
$tmp_user = db_loadResult($sql);
if (!empty($tmp_user))
	$canDelete = false; 

$canEdit = $perms->checkModuleItem($m, "edit", $contact_id);

if (!$row->load( $contact_id ) && $contact_id > 0) {
	$AppUI->setMsg( 'Contact' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
} else if ($row->contact_private && $row->contact_owner != $AppUI->user_id
	&& $row->contact_owner && $contact_id != 0) {
// check only owner can edit
	$AppUI->redirect( "m=public&a=access_denied" );
}

// Get the contact details for company and department
$company_detail = $row->getCompanyDetails();
$row->company_name = $company_detail['company_name'];
$dept_detail = $row->getDepartmentDetails();
$row->dept_name = $dept_detail['dept_name'];


// setup the title block
$ttl = "View Contact";
$titleBlock = new CTitleBlock( $ttl, 'monkeychat-48.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=contacts", "contacts list" );
if ($canEdit && $contact_id)
	$titleBlock->addCrumb( "?m=contacts&amp;a=addedit&amp;contact_id=$contact_id", 'edit' );
	$titleBlock->addCell(
		'
<form action="?m=projects&amp;a=addedit&amp;company_id='.$row->contact_company.'&amp;contact_id='.$contact_id.'" method="post">
	<input type="submit" class="button" value="'.$AppUI->_('new project').'" />
</form>', '', '', '');
if ($canDelete && $contact_id) {
	$titleBlock->addCrumbDelete( 'delete contact', $canDelete, $msg );
}
$titleBlock->show();

if (!$row->contact_owner)
	$row->contact_owner = $AppUI->user_id;


$tpl->assign('contact_id', $contact_id);
$tpl->assign('view_company', $perms->checkModuleItem( 'companies', 'access', $obj->contact_company ));
$tpl->assign('view_dept', isset($_SESSION['all_tabs']['departments']));
$custom_fields = New CustomFields( $m, $a, $contact_id, "view" );
$tpl->assign('customFields', $custom_fields->getHTML());
$tpl->displayView($row);
?>

<script type="text/javascript" language="javascript">
<!--
function delIt()
{
	var form = document.changecontact;
	if (confirm( "<?php echo $AppUI->_('contactsDelete', UI_OUTPUT_JS);?>" ))
	{
		form.del.value = "<?php echo $contact_id;?>";
		form.submit();
	}
}
-->
</script>
