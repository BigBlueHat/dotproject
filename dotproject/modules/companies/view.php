<?php
$company_id = isset($HTTP_GET_VARS['company_id']) ? $HTTP_GET_VARS['company_id'] : 0;
// view mode = 0 tabbed, 1 flat
$vm = isset($HTTP_GET_VARS['vm']) ? $HTTP_GET_VARS['vm'] : 0;

// check permissions
$denyRead = getDenyRead( $m, $company_id );
$denyEdit = getDenyEdit( $m, $company_id );

if ($denyRead) {
	echo '<script language="javascript">
	window.location="./index.php?m=help&a=access_denied";
	</script>
';
}

// pull data
$csql = "
SELECT companies.*,users.user_first_name,users.user_last_name
FROM companies
LEFT JOIN users ON users.user_id = companies.company_owner
WHERE companies.company_id = $company_id
";
$rc = mysql_query( $csql );
$row = mysql_fetch_array( $rc, MYSQL_ASSOC );

$pstatus = array(
	'Not Defined',
	'Proposed',
	'In planning',
	'In progress',
	'On hold',
	'Complete'
);
?>

<TABLE border=0 cellpadding="1" cellspacing=1>
	<TR>
		<TD><img src="./images/icons/money.gif" alt="" border="0"></td>
		<TD nowrap><span class="title">View Company/Client</span></td>
		<TD nowrap> <img src="./images/shim.gif" width="16" height="16" alt="" border="0"></td>
	</tr>
</TABLE>

<table border="0" cellpadding="4" cellspacing="0" width="95%">
	<TR>
		<TD width="50%" nowrap>
		<a href="./index.php?m=companies">Companies List</a>
<?php if (!$denyEdit) { ?>
		<b>:</b> <a href="./index.php?m=companies&a=addedit&company_id=<?php echo $company_id;?>">Edit this Company</a>
<?php } ?>
		</td>
		<TD align="right" width="100%">
		<?php if (!$denyEdit) { ?>
			<input type="button" class=button value="new company" onClick="javascript:window.location='./index.php?m=companies&a=addedit';">
		<?php } ?>
		</td>
	</TR>
</table>

<table border="0" cellpadding="6" cellspacing="0" width="95%" class=std>
<tr valign="top">
	<td width="50%">
		<TABLE width="100%">
		<TR>
			<TD><b>Company:</b></TD>
			<td><?php echo $row["company_name"];?></td>
		</TR>
		<tr>
			<td><b>Phone:</b></td>
			<td><?php echo @$row["company_phone1"];?></td>
		</tr>
		<tr>
			<td><b>Phone2:</b></td>
			<td><?php echo @$row["company_phone2"];?></td>
		</tr>
		<tr>
			<td><b>Fax:</b></td>
			<td><?php echo @$row["company_fax"];?></td>
		</tr>
		<tr valign=top>
			<td><b>Address:</b></td>
			<td><?php
				echo @$row["company_address1"]
					.( ($row["company_address2"]) ? '<br>'.$row["company_address2"] : '' )
					.'<br>'.$row["company_city"]
					.'&nbsp;&nbsp;'.$row["company_state"]
					.'&nbsp;&nbsp;'.$row["company_zip"]
					;
			?></td>
		</tr>
		<tr>
			<td><b>URL:</b></td>
			<td>
				<a href="http://<?php echo @$row["company_primary_url"];?>" target="Company"><?php echo @$row["company_primary_url"];?></a>
			</td>
		</tr>
		</TABLE>

	</TD>
	<td width="50%">
		<b>Description</b><br>
		<?php
		$newstr = str_replace( chr(10), "<BR>", $row["company_description"]);
		echo $newstr;
		?>
	</td>
</TR>
</table>

<table border="0" cellpadding="2" cellspacing="0" width="95%">
<tr>
	<td>
		<a href="./index.php?m=companies&a=view&company_id=<?php echo $company_id;?>&vm=0">tabbed</a> :
		<a href="./index.php?m=companies&a=view&company_id=<?php echo $company_id;?>&vm=1">flat</a>
	</td>
</tr>
</table>

<?php	
$tabs = array(
	'depts' => 'Departments',
	'active' => 'Active Projects',
	'archived' => 'Archived Projects',
	'users' => 'Users'
);

if ($vm == 1) { ?>
<table border="0" cellpadding="2" cellspacing="0" width="95%">
<?php
	foreach ($tabs as $k => $v) {
		echo "<tr><td><b>$v</b></td></tr>";
		echo "<tr><td>";
		include "vw_$k.php";
		echo "</td></tr>";
	}
?>
</table>
<?php 
} else {

	$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'depts';
	drawTabBox( $tabs, $tab, "./index.php?m=companies&a=view&company_id=$company_id", "./modules/companies" );
}

?>

