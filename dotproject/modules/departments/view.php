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
SELECT *
FROM departments
WHERE dept_id = $dept_id
";
$rc = mysql_query( $sql );
$row = mysql_fetch_array( $rc, MYSQL_ASSOC );

?>

<TABLE border=0 cellpadding="1" cellspacing=1>
<TR>
	<TD><img src="./images/icons/users.gif" alt="" border="0"></td>
	<TD nowrap><span class="title">View Department</span></td>
	<TD nowrap> <img src="./images/shim.gif" width="16" height="16" alt="" border="0"></td>
</tr>
</TABLE>

<table border="0" cellpadding="4" cellspacing="0" width="95%">
	<TR>
		<TD width="50%" nowrap>
		<a href="./index.php?m=companies">Companies List</a>
<?php if (!$denyEdit) { ?>
		<b>:</b> <a href="./index.php?m=companies&a=view&company_id=<?php echo $row['dept_company'];?>">View this Company</a>
		<b>:</b> <a href="./index.php?m=departments&a=addedit&dept_id=<?php echo $dept_id;?>">Edit this Department</a>
<?php } ?>
		</td>
		<TD align="right" width="100%">
		<?php if (!$denyEdit) { ?>
			<input type="button" class=button value="new department" onClick="javascript:window.location='./index.php?m=departments&a=addedit&company_id=<?php echo $row['dept_company'];?>';">
		<?php } ?>
		</td>
	</TR>
</table>

<table border="0" cellpadding="6" cellspacing="0" width="95%" class=std>
<tr valign="top">
	<td width="50%">
		<TABLE width="100%">
		<TR>
			<TD><b>Department:</b></TD>
			<td><?php echo $row["dept_name"];?></td>
		</TR>
		<tr>
			<td><b>Phone:</b></td>
			<td><?php echo @$row["dept_phone"];?></td>
		</tr>
		<tr>
			<td><b>Fax:</b></td>
			<td><?php echo @$row["dept_fax"];?></td>
		</tr>
		</TABLE>

	</TD>
	<td width="50%">
		<b>Description</b><br>
		<?php
		$newstr = str_replace( chr(10), "<BR>", $row["dept_desc"]);
		echo $newstr;
		?>
	</td>
</TR>
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

