<?php
//User Managagement

// check permissions
$denyRead = getDenyRead( $m );
$denyEdit = getDenyEdit( $m );

if ($denyRead) {
	echo '<script language="javascript">
	window.location="./index.php?m=help&a=access_denied";
	</script>
';
}

//set defaults
$message = isset( $HTTP_GET_VARS['message'] ) ? $HTTP_GET_VARS['message'] : '';
$orderby = isset( $HTTP_GET_VARS['orderby'] ) ? $HTTP_GET_VARS['orderby'] : 'user_username';

$usql = "
SELECT distinct(user_id), user_username, user_last_name, user_first_name, permission_user, user_email
FROM users
LEFT JOIN permissions ON users.user_id = permissions.permission_user and permission_value <> 0
ORDER by $orderby
";
$urow = mysql_query( $usql );
?>

<SCRIPT language="javascript">
function delMe( x, y ) {
	if (confirm( "Are you sure you want\nto delete user " + y + "?" )) {
		top.location="./index.php?m=admin&a=dosql&del=1&user_id=" + x;
	}
}
</script>

<TABLE width="95%" border=0 cellpadding="0" cellspacing=1>
<TR>
	<TD valign="top"><img src="./images/icons/admin.gif" alt="" border="0" width=42 height=42></td>
		<TD nowrap><span class="title">User Management</span></td>
		<TD valign="top" align="right" width="100%">
			<!-- <input type="button" class=button value="add group" onClick="javascript:window.location='./index.php?m=admin&a=addeditgroup';">-->
			<input type="button" class=button value="add user" onClick="javascript:window.location='./index.php?m=admin&a=addedituser';">
		</td>
</tr>
</TABLE>

<?php echo $message;?>

<TABLE width="95%" border=0 height="400">
<TR>
	<TD valign="top" colspan=2>
		<TABLE width="100%" cellpadding="1" cellspacing=1 >
		<TR bgcolor="gray" height=20>
			<TD width="60" bgcolor="#eeeeee" align="right" class="smallNorm">
				&nbsp; sort by:&nbsp;
			</td>
			<TD class="mboxhdr">
				<A href="./index.php?m=admin&a=index&orderby=user_username"><font color="white">Login Name</font></a>
			</td>
			<TD class="mboxhdr">
				<A href="./index.php?m=admin&a=index&orderby=user_last_name"><font color="white">Real Name</font></a>
			</td>
			<TD class="mboxhdr">
				<A href="./index.php?m=admin&a=index&orderby=user_email"><font color="white">Email</font></a>
			</td>
			<TD class="mboxhdr">Active?</td>
<?php if (!$denyEdit) { ?>
			<TD class="mboxhdr">Permissions</td>
<?php } ?>
		</tr>
<?php 
	while ($row = mysql_fetch_array( $urow, MYSQL_ASSOC )) {
		if (intval(@$row["permission_user"]) !=0 ) {
			echo "<TR bgcolor=#f4efe3>";
		} else {
			echo "<TR bgcolor=#eeeeee>";
		}?>
			<TD width="60" class="smallNorm" align="right">
<?php if (!$denyEdit) { ?>
				<span  class="smallNorm">
				<A href="./index.php?m=admin&a=addedituser&user_id=<?php echo $row["user_id"];?>">edit</a> | 
				<A href="javascript:delMe(<?php echo $row["user_id"];?>, '<?php echo $row["user_first_name"] . " " . $row["user_last_name"];?>')">del</a>
				</span>
<?php } ?>
				&nbsp;
			</td>
			<TD>
				<A href="./index.php?m=admin&a=viewuser&user_id=<?php echo $row["user_id"];?>"><?php echo $row["user_username"];?></a>
			</td>
			<TD>
				<?php echo $row["user_last_name"].', '.$row["user_first_name"];?>
			</td>
			<TD>
				<A href="mailto:<?php echo $row["user_email"];?>"><?php echo $row["user_email"];?></a>
			</td>
			<TD>
				<?php if(intval(@$row["permission_user"]) !=0){echo "Yes";} else { echo "No";}?>
			</td>
<?php if (!$denyEdit) { ?>
			<TD>
				<input type="button" class=button value="permissions" onClick="javascript:window.location= './index.php?m=admin&a=permissions&user_id=<?php echo $row["user_id"];?>';">
			</td>
<?php } ?>

		</tr>
<?php }?>
		</table>
	</TD>
</TR>
</table>

</body>
</html>
