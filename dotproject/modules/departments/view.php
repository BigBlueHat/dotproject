<?php
$dept_id = isset($_GET['dept_id']) ? $_GET['dept_id'] : 0;
$company_id = isset($_GET['company_id']) ? $_GET['company_id'] : 0;

// check permissions
$denyRead = getDenyRead( $m, $dept_id );
$denyEdit = getDenyEdit( $m, $dept_id );

if ($denyRead) {
	echo '<script language="javascript">
	window.location="./index.php?m=help&a=access_denied";
	</script>
';
}

// pull data
$sql = "
SELECT departments.*,company_name, user_first_name, user_last_name
FROM departments, companies
LEFT JOIN users ON user_id = dept_owner
WHERE dept_id = $dept_id
	AND dept_company = company_id
";
$rc = mysql_query( $sql );
$row = mysql_fetch_array( $rc, MYSQL_ASSOC );

?>

<table border=0 cellpadding="1" cellspacing=1>
<tr>
	<td><img src="./images/icons/users.gif" alt="" border="0"></td>
	<td nowrap><span class="title">View Department</span></td>
	<td nowrap> <img src="./images/shim.gif" width="16" height="16" alt="" border="0"></td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%">
	<tr>
		<td width="50%" nowrap>
		<a href="./index.php?m=companies">companies list</a>
<?php if (!$denyEdit) { ?>
		<b>:</b> <a href="./index.php?m=companies&a=view&company_id=<?php echo $row['dept_company'];?>">view this company</a>
		<b>:</b> <a href="./index.php?m=departments&a=addedit&dept_id=<?php echo $dept_id;?>">edit this department</a>
<?php } ?>
		</td>
		<td align="right" width="100%">
		<?php if (!$denyEdit) { ?>
			<input type="button" class=button value="new department" onClick="javascript:window.location='./index.php?m=departments&a=addedit&company_id=<?php echo $row['dept_company'];?>';">
		<?php } ?>
		</td>
	</tr>
</table>

<table border="0" cellpadding="6" cellspacing="0" width="98%" class="std">
<tr valign="top">
	<td width="50%">
		<b>Details</b>
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" nowrap>Company:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo $row["company_name"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap>Department:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo $row["dept_name"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap>Owner:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo @$row["user_first_name"].' '.@$row["user_last_name"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap>Phone:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo @$row["dept_phone"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap>Fax:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo @$row["dept_fax"];?></td>
		</tr>
		<tr valign=top>
			<td align="right" nowrap>Address:</td>
			<td bgcolor="#ffffff"><?php
				echo @$row["dept_address1"]
					.( ($row["dept_address2"]) ? '<br>'.$row["dept_address2"] : '' )
					.'<br>'.$row["dept_city"]
					.'&nbsp;&nbsp;'.$row["dept_state"]
					.'&nbsp;&nbsp;'.$row["dept_zip"]
					;
			?></td>
		</tr>
		</table>
	</td>
	<td width="50%">
		<b>Description</b>
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td bgcolor="#ffffff" width="100%"><?php echo str_replace( chr(10), "<BR>", $row["dept_desc"]);?>&nbsp;</td>
		</tr>
		</table>
		
	</td>
</tr>
</table>


<p>

<?php
/*
$tabs = array(
	'depts' => 'Departments',
	'active' => 'Active Projects',
	'archived' => 'Archived Projects',
	'users' => 'Users'
);

$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'depts';
drawTabBox( $tabs, $tab, "./index.php?m=companies&a=view&dept_id=$dept_id", "./modules/companies" );
*/
?>

