<?php

// check permissions
$denyRead = getDenyRead( $m );
$denyEdit = getDenyEdit( $m );

if ($denyRead) {
	echo '<script language="javascript">
	window.location="./index.php?m=help&a=access_denied";
	</script>
';
}

$user_id = isset( $HTTP_GET_VARS['user_id'] ) ? $HTTP_GET_VARS['user_id'] : 0;
// view mode = 0 tabbed, 1 flat
$vm = isset($HTTP_GET_VARS['vm']) ? $HTTP_GET_VARS['vm'] : 0;

// pull data
$usql = "
SELECT users.*, 
	company_id, company_name, 
	dept_name, dept_id
FROM users
LEFT JOIN companies ON user_company = companies.company_id
LEFT JOIN departments ON dept_id = user_department
WHERE user_id = $user_id
";
$urc  = mysql_query( $usql );
$urow = mysql_fetch_array( $urc, MYSQL_ASSOC );
?>

<table border=0 cellpadding="1" cellspacing=1>
<tr>
	<td><img src="./images/icons/admin.gif" alt="" border="0"></td>
	<td nowrap><span class="title">View User</span></td>
	<td nowrap> <img src="./images/shim.gif" width="16" height="16" alt="" border="0"></td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%">
<tr>
	<td nowrap>
	<a href="./index.php?m=admin">user list</a>
<?php if (!$denyEdit) { ?>
	<b>:</b> <a href="?m=admin&a=addedituser&user_id=<?php echo $user_id;?>">edit this user</a>
	<b>:</b> <a href="?m=admin&a=permissions&user_id=<?php echo $user_id;?>">edit permissions</a>
<?php } ?>
	</td>
	<td align="right" width="100%">
<?php if (!$denyEdit) { ?>
		<input type="button" class=button value="new user" onClick="javascript:window.location='./index.php?m=admin&a=addedituser';">
<?php } ?>
	</td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%" class="std">
<tr valign="top">
	<td width="50%">
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" nowrap>Login Name:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo $urow["user_username"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap>User Type:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo $utypes[$urow["user_type"]];?></td>
		</tr>
		<tr>
			<td align="right" nowrap>Full Name:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo $urow["user_first_name"].' '.$urow["user_last_name"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap>Company:</td>
			<td bgcolor="#ffffff" width="100%">
				<a href="?m=companies&a=view&company_id=<?php echo @$urow["company_id"];?>"><?php echo @$urow["company_name"];?></a>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap>Department:</td>
			<td bgcolor="#ffffff" width="100%">
				<a href="?m=departments&a=view&dept_id=<?php echo @$urow["dept_id"];?>"><?php echo $urow["dept_name"];?></a>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap>Phone:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo @$urow["user_phone"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap>Home Phone:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo @$urow["user_home_phone"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap>Mobile:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo @$urow["user_mobile"];?></td>
		</tr>
		<tr valign=top>
			<td align="right" nowrap>Address:</td>
			<td bgcolor="#ffffff" width="100%"><?php
				echo @$urow["user_address1"]
					.( ($urow["user_address2"]) ? '<br>'.$urow["user_city"] : '' )
					.'<br>'.$urow["user_city"]
					.'&nbsp;&nbsp;'.$urow["user_state"]
					.'&nbsp;&nbsp;'.$urow["user_zip"]
					.'<br>'.$urow["user_coutnry"]
					;
			?></td>
		</tr>
		</table>

	</td>
	<td width="50%">
		<table width="100%">
		<tr>
			<td align="right" nowrap>Birthday:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo @$urow["user_birthday"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap>ICQ#:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo @$urow["user_icq"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap>AOL Nick:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo @$urow["user_aol"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap>E-Mail:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo '<a href="mailto:'.@$urow["user_email"].'">'.@$urow["user_email"].'</a>';?></td>
		</tr>
		<tr>
			<td colspan="2"><b>Signature:</b></td>
		</tr>
		<tr>
			<td bgcolor="#ffffff" width="100%" colspan="2">
				<?php echo str_replace( chr(10), "<BR>", $urow["signature"]);?>&nbsp;
			</td>
		</tr>
		</table>
	</td>
</tr>
</table>

<table border="0" cellpadding="2" cellspacing="0" width="98%">
<tr>
	<td>
		<a href="./index.php?m=admin&a=viewuser&user_id=<?php echo $user_id;?>&vm=0">tabbed</a> :
		<a href="./index.php?m=admin&a=viewuser&user_id=<?php echo $user_id;?>&vm=1">flat</a>
	</td>
</tr>
</table>

<?php	
$tabs = array(
	'usr_proj' => 'Owned Projects',
	'usr_perms' => 'Permissions'
);

if ($vm == 1) { ?>
<table border="0" cellpadding="2" cellspacing="0" width="98%">
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
	$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'usr_proj';
	drawTabBox( $tabs, $tab, "./index.php?m=admin&a=viewuser&user_id=$user_id", "./modules/admin" );
}
?>
