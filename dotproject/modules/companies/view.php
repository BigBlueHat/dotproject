<?php /* COMPANIES $Id$ */
$company_id = intval( dPgetParam( $_GET, "company_id", 0 ) );

// check permissions for this company
$canRead = !getDenyRead( $m, $company_id );
$canEdit = !getDenyEdit( $m, $company_id );

if (!$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'CompVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'CompVwTab' ) !== NULL ? $AppUI->getState( 'CompVwTab' ) : 0;

// pull data
$sql = "
SELECT companies.*,users.user_first_name,users.user_last_name
FROM companies
LEFT JOIN users ON users.user_id = companies.company_owner
WHERE companies.company_id = $company_id
";

if (!db_loadHash( $sql, $row )) {
	$AppUI->setMsg( 'Company' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}

$pstatus = dPgetSysVal( 'ProjectStatus' );
$types = dPgetSysVal( 'CompanyType' );

// load the module object
$obj = new CCompany();

$msg = '';
$canDelete = $obj->canDelete( $msg, $company_id );

// setup the title block
$titleBlock = new CTitleBlock( 'View Company', 'handshake.png', $m, "$m.$a" );
if ($canEdit) {
	$titleBlock->addCell();
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new company').'" />', '',
		'<form action="?m=companies&a=addedit" method="post">', '</form>'
	);
}
$titleBlock->addCrumb( "?m=companies", "company list" );
if ($canEdit) {
	$titleBlock->addCrumb( "?m=companies&a=addedit&company_id=$company_id", "edit this company" );
	
	if ($canEdit) {
		$titleBlock->addCrumbRight(
			'<table cellspacing="0" cellpadding="0" border="0"?<tr><td>'
			. dPshowImage( './images/icons/'.($canDelete?'stock_delete-16.png':'stock_trash_full-16.png'), '16', '16',  '' )
			. '</td><td>&nbsp;'
			. '<a href="javascript:delIt()" title="'.($canDelete?'':$msg).'">' . $AppUI->_('delete company') . '</a>'
			. '</td></tr></table>'
		);
	}
}
$titleBlock->show();
?>
<script language="javascript">
function delIt() {
	if (confirm( "<?php echo $AppUI->_('doDelete').' '.$AppUI->_('Company').'?';?>" )) {
		document.frmDelete.submit();
	}
}
</script>

<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">

<form name="frmDelete" action="./index.php?m=companies" method="post">
	<input type="hidden" name="dosql" value="do_company_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="company_id" value="<?php echo $company_id;?>" />
</form>

<tr>
	<td valign="top" width="50%">
		<strong><?php echo $AppUI->_('Details');?></strong>
		<table cellspacing="1" cellpadding="2" width="100%">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Company');?>:</td>
			<td class="hilite" width="100%"><?php echo $row["company_name"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Phone');?>:</td>
			<td class="hilite"><?php echo @$row["company_phone1"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Phone');?>2:</td>
			<td class="hilite"><?php echo @$row["company_phone2"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Fax');?>:</td>
			<td class="hilite"><?php echo @$row["company_fax"];?></td>
		</tr>
		<tr valign=top>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Address');?>:</td>
			<td class="hilite"><?php
				echo @$row["company_address1"]
					.( ($row["company_address2"]) ? '<br />'.$row["company_address2"] : '' )
					.'<br />'.$row["company_city"]
					.'&nbsp;&nbsp;'.$row["company_state"]
					.'&nbsp;&nbsp;'.$row["company_zip"]
					;
			?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('URL');?>:</td>
			<td class="hilite">
				<a href="http://<?php echo @$row["company_primary_url"];?>" target="Company"><?php echo @$row["company_primary_url"];?></a>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Type');?>:</td>
			<td class="hilite"><?php echo $types[@$row["company_type"]];?></td>
		</tr>
		</table>

	</td>
	<td width="50%" valign="top">
		<strong><?php echo $AppUI->_('Description');?></strong>
		<table cellspacing="0" cellpadding="2" border="0" width="100%">
		<tr>
			<td class="hilite">
				<?php echo str_replace( chr(10), "<br />", $row["company_description"]);?>&nbsp;
			</td>
		</tr>
		</table>

	</td>
</tr>
</table>

<?php
// tabbed information boxes
$tabBox = new CTabBox( "?m=companies&a=view&company_id=$company_id", "{$AppUI->cfg['root_dir']}/modules/companies/", $tab );
$tabBox->add( 'vw_depts', 'Departments' );
$tabBox->add( 'vw_active', 'Active Projects' );
$tabBox->add( 'vw_archived', 'Archived Projects' );
$tabBox->add( 'vw_users', 'Users' );
$tabBox->show();
?>