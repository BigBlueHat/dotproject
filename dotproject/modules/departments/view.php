<?php
$dept_id = isset($_GET['dept_id']) ? $_GET['dept_id'] : 0;

// check permissions
$denyRead = getDenyRead( $m, $dept_id );
$denyEdit = getDenyEdit( $m, $dept_id );

if ($denyRead) {
	$AppUI->redirect( "m=help&a=access_denied" );
}
$AppUI->savePlace();

// pull data
$sql = "
SELECT departments.*,company_name, user_first_name, user_last_name
FROM departments, companies
LEFT JOIN users ON user_id = dept_owner
WHERE dept_id = $dept_id
	AND dept_company = company_id
";
db_loadHash( $sql, $dept );
$company_id = $dept['dept_company'];

$crumbs = array();
$crumbs["?m=companies"] = "company list";
if (!$denyEdit) {
	$crumbs["?m=companies&a=view&company_id=$company_id"] = "view this company";
	$crumbs["?m=departments&a=addedit&dept_id=$dept_id"] = "edit this department";
}
?>

<table border="0" cellpadding="1" cellspacing="1" width="98%">
<tr>
	<td><img src="./images/icons/money.gif" alt="" border="0"></td>
	<td nowrap="nowrap"><h1><?php echo $AppUI->_('View Department');?></h1></td>
	<td width="100%" nowrap="nowrap"> <img src="./images/shim.gif" width="16" height="16" alt="" border="0"></td>
	<form action="?m=departments&a=addedit" method="post">
	<td align="right" width="100%">
	<?php echo !$denyEdit ? '<input type="submit" class="button" value="'.$AppUI->_('new department').'">' : '';?>
	</td>
	</form>
	<td nowrap="nowrap" width="20" align="right"><?php echo contextHelp( '<img src="./images/obj/help.gif" width="14" height="16" border="0" alt="'.$AppUI->_( 'Help' ).'">', 'ID_HELP_DEPT_VIEW' );?></td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%">
<tr>
	<td width="50%" nowrap><?php echo breadCrumbs( $crumbs );?></td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%" class="std">
<tr valign="top">
	<td width="50%">
		<strong>Details</strong>
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" nowrap>Company:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo $dept["company_name"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap>Department:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo $dept["dept_name"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap>Owner:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo @$dept["user_first_name"].' '.@$dept["user_last_name"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap>Phone:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo @$dept["dept_phone"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap>Fax:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo @$dept["dept_fax"];?></td>
		</tr>
		<tr valign=top>
			<td align="right" nowrap>Address:</td>
			<td bgcolor="#ffffff"><?php
				echo @$dept["dept_address1"]
					.( ($dept["dept_address2"]) ? '<br />'.$dept["dept_address2"] : '' )
					.'<br />'.$dept["dept_city"]
					.'&nbsp;&nbsp;'.$dept["dept_state"]
					.'&nbsp;&nbsp;'.$dept["dept_zip"]
					;
			?></td>
		</tr>
		</table>
	</td>
	<td width="50%">
		<strong>Description</strong>
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td bgcolor="#ffffff" width="100%"><?php echo str_replace( chr(10), "<br />", $dept["dept_desc"]);?>&nbsp;</td>
		</tr>
		</table>
		
	</td>
</tr>
</table>

