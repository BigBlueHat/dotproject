<?php
$company_id = isset($_GET['company_id']) ? $_GET['company_id'] : 0;

// check permissions
$denyRead = getDenyRead( $m, $company_id );
$denyEdit = getDenyEdit( $m, $company_id );

if ($denyRead) {
	$AppUI->redirect( "m=help&a=access_denied" );
}

$AppUI->savePlace();

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

db_loadHash( $sql, $row );

$pstatus = array(
	'Not Defined',
	'Proposed',
	'In planning',
	'In progress',
	'On hold',
	'Complete'
);


$crumbs = array();
$crumbs["?m=companies"] = "company list";
if (!$denyEdit) {
	$crumbs["?m=companies&a=addedit&company_id=$company_id"] = "edit this company";
}
?>

<table border=0 cellpadding="1" cellspacing=1>
<tr>
	<td><img src="./images/icons/money.gif" alt="" border="0"></td>
	<td nowrap><span class="title"><?php echo $AppUI->_('View Company/Client');?></span></td>
	<td nowrap> <img src="./images/shim.gif" width="16" height="16" alt="" border="0"></td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%">
<tr>
	<td width="50%" nowrap><?php echo breadCrumbs( $crumbs );?></td>
	<form action="?m=companies&a=addedit" method="post">
	<td align="right" width="100%">
	<?php echo !$denyEdit ? '<input type="submit" class="button" value="'.$AppUI->_('new company').'">' : '';?>
	</td>
	</form>
	<td nowrap="nowrap" width="20" align="right"><?php echo contextHelp( '<img src="./images/obj/help.gif" width="14" height="16" border="0" alt="'.$AppUI->_( 'Help' ).'">', 'ID_HELP_COMP_VIEW' );?></td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%" class="std">
<tr>
	<td valign="top" width="50%">
		<b><?php echo $AppUI->_('Details');?></b>
		<table cellspacing="1" cellpadding="2" width="100%">
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Company');?>:</td>
			<td class="hilite" width="100%"><?php echo $row["company_name"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Phone');?>:</td>
			<td class="hilite"><?php echo @$row["company_phone1"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Phone');?>2:</td>
			<td class="hilite"><?php echo @$row["company_phone2"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Fax');?>:</td>
			<td class="hilite"><?php echo @$row["company_fax"];?></td>
		</tr>
		<tr valign=top>
			<td align="right" nowrap><?php echo $AppUI->_('Address');?>:</td>
			<td class="hilite"><?php
				echo @$row["company_address1"]
					.( ($row["company_address2"]) ? '<br>'.$row["company_address2"] : '' )
					.'<br>'.$row["company_city"]
					.'&nbsp;&nbsp;'.$row["company_state"]
					.'&nbsp;&nbsp;'.$row["company_zip"]
					;
			?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('URL');?>:</td>
			<td class="hilite">
				<a href="http://<?php echo @$row["company_primary_url"];?>" target="Company"><?php echo @$row["company_primary_url"];?></a>
			</td>
		</tr>
		</table>

	</td>
	<td width="50%" valign="top">
		<b><?php echo $AppUI->_('Description');?></b>
		<table cellspacing="0" cellpadding="2" border="0" width="100%">
		<tr>
			<td class="hilite">
				<?php echo str_replace( chr(10), "<BR>", $row["company_description"]);?>&nbsp;
			</td>
		</tr>
		</table>

	</td>
</tr>
</table>

<?php	
// tabbed information boxes
$tabBox = new CTabBox( "?m=companies&a=view&company_id=$company_id", "./modules/companies", $tab );
$tabBox->add( 'vw_depts', 'Departments' );
$tabBox->add( 'vw_active', 'Active Projects' );
$tabBox->add( 'vw_archived', 'Archived Projects' );
$tabBox->add( 'vw_users', 'Users' );
$tabBox->show();
?>
