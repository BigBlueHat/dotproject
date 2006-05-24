<?php /* COMPANIES $Id$ */
$company_id = intval( dPgetParam( $_GET, "company_id", 0 ) );

// check permissions for this record
$perms =& $AppUI->acl();
$canRead = $perms->checkModuleItem( $m, 'view', $company_id );
$canEdit = $perms->checkModuleItem( $m, 'edit', $company_id );


if (!$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

// retrieve any state parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'CompVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'CompVwTab' ) !== NULL ? $AppUI->getState( 'CompVwTab' ) : 0;

// check if this record has dependencies to prevent deletion
$msg = '';
$obj = new CCompany();
$canDelete = $obj->canDelete( $msg, $company_id );

// load the record data
$q  = new DBQuery;
$q->addTable('companies');
$q->addQuery('companies.*');
$q->addQuery('con.contact_first_name');
$q->addQuery('con.contact_last_name');
$q->addJoin('users', 'u', 'u.user_id = companies.company_owner');
$q->addJoin('contacts', 'con', 'u.user_contact = con.contact_id');
$q->addWhere('companies.company_id = '.$company_id);
$sql = $q->prepare();
$q->clear();

$obj = null;
if (!db_loadObject( $sql, $obj )) {
	$AppUI->setMsg( 'Company' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}

// load the list of project statii and company types
$pstatus = dPgetSysVal( 'ProjectStatus' );
$types = dPgetSysVal( 'CompanyType' );

// setup the title block
$titleBlock = new CTitleBlock( 'View Company', 'handshake.png', $m, "$m.$a" );
if ($canEdit) {
	$titleBlock->addCell();
	$titleBlock->addCell(
		'
<form action="?m=companies&amp;a=addedit" method="post">
	<input type="submit" class="button" value="'.$AppUI->_('new company').'" />
</form>', '', '', ''
	);
	$titleBlock->addCell(
		'
<form action="?m=projects&amp;a=addedit&amp;company_id='.$company_id.'" method="post">
	<input type="submit" class="button" value="'.$AppUI->_('new project').'" />
</form>', '', '', '');
}
$titleBlock->addCrumb( "?m=companies", "company list" );
if ($canEdit) {
	$titleBlock->addCrumb( "?m=companies&amp;a=addedit&amp;company_id=$company_id", 'edit this company' );
	
	if ($canDelete)
		$titleBlock->addCrumbDelete( 'delete company', $canDelete, $msg );
}
$titleBlock->show();

$obj->company_description = str_replace( chr(10), '<br />', $obj->company_description );


require_once($baseDir . '/classes/CustomFields.class.php');
$custom_fields = New CustomFields( $m, $a, $obj->company_id, "view" );
$tpl->assign('customFields', $custom_fields->getHTML());

$tpl->assign('delete', $canDelete);
$tpl->assign('company_id', $company_id);
$tpl->assign('type', $types[@$obj->company_type]);

$tpl->displayView($obj);
?>
<script type="text/javascript" language="javascript">
<!--
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canDelete) {
?>
function delIt() 
{
	if (confirm( "<?php echo $AppUI->_('doDelete').' '.$AppUI->_('Company').'?';?>" ))
		document.frmDelete.submit();
}
<?php } ?>
-->
</script>

<?php
// tabbed information boxes
$moddir = $dPconfig['root_dir'] . '/modules/companies/';
$tabBox = new CTabBox( "?m=companies&amp;a=view&amp;company_id=$company_id", '', $tab );
//$tabBox->add( $moddir . 'vw_active', 'Active Projects' );
//$tabBox->add( $moddir . 'vw_archived', 'Archived Projects' );
$tabBox->add( $moddir . 'vw_depts', 'Departments' );
$tabBox->loadExtras($m, $a);
$tabBox->show();
?>
