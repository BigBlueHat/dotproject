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

// pull data
$usql = "select users.*,company_name from users left join companies on user_company = companies.company_id where user_id = $user_id";
$urc  = mysql_query( $usql );
$urow = mysql_fetch_array( $urc, MYSQL_ASSOC );
?>

<TABLE width="95%" border=0 cellpadding="1" cellspacing=1>
<TR>
	<TD><img src="./images/icons/admin.gif" alt="" border="0"></td>
	<TD nowrap><span class="title">View User</span></td>
	<TD nowrap> <img src="./images/shim.gif" width="16" height="16" alt="" border="0"></td>
	<TD align="right" width="100%">
<?php if (!$denyEdit) { ?>
		<input type="button" class=button value="new user" onClick="javascript:window.location='./index.php?m=admin&a=addedituser';">
<?php } ?>
	</td>
</tr>
</TABLE>

<table border="0" cellpadding="4" cellspacing="0" width="95%">
<TR>
	<TD width="50%" nowrap>
	<a href="./index.php?m=admin">User List</a>
<?php if (!$denyEdit) { ?>
	<b>:</b> <a href="./index.php?m=admin&a=addedituser&user_id=<?php echo $user_id;?>">Edit this User</a>
<?php } ?>
	</td>
	<TD width="50%" align="right"><?php include ("./includes/create_new_menu.php");?></td>
</TR>
</table>

<table border="0" cellpadding="6" cellspacing="0" width="95%" bgcolor="#eeeeee">
<tr valign="top">
	<td width="50%">
		<TABLE width="100%">
		<TR>
			<TD><b>Login Name:</b></TD>
			<td><?php echo $urow["user_username"];?></td>
		</TR>
		<TR>
			<TD><b>Full Name:</b></TD>
			<td><?php echo $urow["user_first_name"].' '.$urow["user_last_name"];?></td>
		</TR>
		<TR>
			<TD><b>Company:</b></TD>
			<td><?php echo $urow["company_name"];?></td>
		</TR>
		<tr>
			<td><b>Phone:</b></td>
			<td><?php echo @$urow["user_phone"];?></td>
		</tr>
		<tr>
			<td><b>Home Phone:</b></td>
			<td><?php echo @$urow["user_home_phone"];?></td>
		</tr>
		<tr>
			<td><b>Mobile:</b></td>
			<td><?php echo @$urow["user_mobile"];?></td>
		</tr>
		<tr valign=top>
			<td><b>Address:</b></td>
			<td><?php
				echo @$urow["user_address1"]
					.( ($urow["user_address2"]) ? '<br>'.$urow["user_city"] : '' )
					.'<br>'.$urow["user_city"]
					.'&nbsp;&nbsp;'.$urow["user_state"]
					.'&nbsp;&nbsp;'.$urow["user_zip"]
					.'<br>'.$urow["user_coutnry"]
					;
			?></td>
		</tr>
		<tr>
			<td><b>Birthday:</b></td>
			<td><?php echo @$urow["user_birthday"];?></td>
		</tr>
		</TABLE>

	</TD>
	<td width="50%">
		<TABLE width="100%">
		<tr>
			<td><b>ICQ#:</b></td>
			<td><?php echo @$urow["user_icq"];?></td>
		</tr>
		<tr>
			<td><b>AOL Nick:</b></td>
			<td><?php echo @$urow["user_aol"];?></td>
		</tr>
		<tr>
			<td><b>E-Mail:</b></td>
			<td><?php echo '<a href="mailto:'.@$urow["user_email"].'">'.@$urow["user_email"].'</a>';?></td>
		</tr>
		<tr>
			<td colspan=2>
				<b>Signature:</b><br>
				<?php
				$newstr = str_replace( chr(10), "<BR>", $urow["signature"]);
				echo $newstr;
				?>
			</td>
		</tr>
		</TABLE>
	</td>
</TR>
</table>

<?php //------Begin Permissions Include--------?>
<?php
//Pull User perms
$usql = "
SELECT u.user_id, u.user_username,
	p.permission_item, p.permission_id, p.permission_grant_on, p.permission_value,
	c.company_id, c.company_name,
	pj.project_id, pj.project_name,
	f.file_id, f.file_name,
	u2.user_id, u2.user_username
FROM users u, permissions p
LEFT JOIN companies c ON c.company_id = p.permission_item and p.permission_grant_on = 'companies'
LEFT JOIN projects pj ON pj.project_id = p.permission_item and p.permission_grant_on = 'projects'
LEFT JOIN files f ON f.file_id = p.permission_item and p.permission_grant_on = 'files'
LEFT JOIN users u2 ON u2.user_id = p.permission_item and p.permission_grant_on = 'users'
WHERE u.user_id = p.permission_user
	AND u.user_id = $user_id
";

$urc = mysql_query($usql);
$nums = mysql_num_rows($urc);

//pull the projects into an temp array
$tarr = array();
for($x=0;$x<$nums;$x++){
	$tarr[$x] = mysql_fetch_array($urc);
}
?>

<TABLE width="95%" border=0 cellpadding="2" cellspacing=1>
<TR>
	<TD width=50% valign=top><strong>Permissions:</strong><br>

		<TABLE width="100%" border=0 cellpadding="2" cellspacing=1>
			<TR style="border: outset #eeeeee 2px;">
				<TD class="mboxhdr">Module</td>
				<TD class="mboxhdr">Item</td>
				<TD class="mboxhdr">Permission Type</td>
			</tr>

	<?php
		for ($x =0; $x < $nums; $x++){
			echo '<tr><td bgcolor="#f4efe3">' . $tarr[$x]['permission_grant_on'] . "</td>";

			if ($tarr[$x]['permission_grant_on'] =="files" && $tarr[$x]['permission_item'] > 0) {
				$item = $tarr[$x]['file_name'];
			} else if ($tarr[$x]['permission_grant_on'] =="users" && $tarr[$x]['permission_item'] > 0) {
				$item = $tarr[$x]['user_username'];
			} else if ($tarr[$x]['permission_grant_on'] =="projects" && $tarr[$x]['permission_item'] > 0) {
				$item = $tarr[$x]['project_name'];
			} else if ($tarr[$x]['permission_grant_on'] =="companies" && $tarr[$x]['permission_item'] > 0) {
				$item = $tarr[$x]['company_name'];
			} else {
				$item = $tarr[$x]['permission_item'];
			}

			if ($item == "-1") {
				$item = "all";
			}
			if ($tarr[$x]['permission_value'] == -1) {
				$value = "read-write";
			} else if ($tarr[$x]['permission_value'] == 1) {
				$value = "read-only";
			} else {
				$value = "deny";
			}
			echo '<td bgcolor="#f4efe3">' . $item . '</td><td bgcolor="#f4efe3">' . $value . "</td></tr>";
		}
	?>
		</TABLE>
	</TD>
	<TD width=50% valign=top><strong>Owned Projects:</strong><br>
<?php
$psql = "
SELECT projects.*
FROM projects
WHERE project_owner = $user_id
ORDER BY project_name
";
$prc = mysql_query($psql);
$nums = mysql_num_rows($prc);

//pull the projects into an temp array
$tarr = array();
for($x=0;$x<$nums;$x++){
	$tarr[$x] = mysql_fetch_array($prc);
}

$pstatus = array(
	'Not Defined',
	'Proposed',
	'In planning',
	'In progress',
	'On hold',
	'Complete'
);
?>
		<TABLE width="100%" border=0 cellpadding="2" cellspacing=1>
		<TR style="border: outset #eeeeee 2px;">
			<TD class="mboxhdr">Name</td>
			<TD class="mboxhdr">Status</td>
		</tr>

	<?php
		for ($x =0; $x < $nums; $x++){
			if ($tarr[$x]["project_active"] <> 0) {
			?>
		<TR bgcolor="#f4efe3">
			<TD>
				<A href="./index.php?m=projects&a=view&project_id=<?php echo $tarr[$x]["project_id"];?>">
					<?php echo $tarr[$x]["project_name"];?>
				</a>
			<td><?php echo $pstatus[$tarr[$x]["project_status"]]; ?></td>
		</tr>
	<?php
			}
		}
	?>
		</TABLE>
	</TD>
</TR>
</TABLE>

