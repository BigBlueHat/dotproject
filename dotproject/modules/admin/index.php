<?php
//User Managagement
// view mode = 0 tabbed, 1 flat
$vm = isset($_GET['vm']) ? $_GET['vm'] : 0;
$f = isset($_GET['z']) ? $_GET['z'] : '%';

// check permissions
$denyRead = getDenyRead( $m );
$denyEdit = getDenyEdit( $m );

if ($denyRead) {
	echo '<script language="javascript">
	window.location="./index.php?m=help&a=access_denied";
	</script>
';
}
// Pull First Letters
$let = ":";
$sql = "SELECT DISTINCT LOWER(SUBSTRING(user_username, 1, 1)) FROM users";
$rc = mysql_query( $sql );
echo mysql_error();
while ($row = mysql_fetch_row( $rc )) {
	$let .= $row[0];
}
?>

<script language="javascript">
function delMe( x, y ) {
	if (confirm( "Are you sure you want\nto delete user " + y + "?" )) {
		top.location="./index.php?m=admin&a=dosql&del=1&user_id=" + x;
	}
}
</script>

<table cellpadding="0" cellspacing="1" border="0" width="98%">
<tr>
	<td valign="top"><img src="./images/icons/admin.gif" alt="" border="0" width=42 height=42></td>
	<td nowrap><span class="title">User Management</span></td>

	<td align="right">
		<table cellpadding="2" cellspacing="1" border="0">
		<tr>
			<td width="100%" align="right">filter: </td>
			<td align="center" bgcolor="#cccccc"><a href="./index.php?m=admin">all</a></td>
<?php
	for ($a=65; $a < 91; $a++) {
		$cu = chr( $a );
		$cl = chr( $a+32 );
		$bg = strpos($let, "$cl") > 0 ? "bgcolor=\"#cccccc\"><a href=./index.php?m=admin&z=$cu" : '';
		echo "<TD align=\"center\" $bg>$cu</A></TD>\n";
	}
?>
		</tr>
		</table>
	</td>
</tr>
<tr>
	<td colspan="2">
		<a href="./index.php?m=admin&vm=0">tabbed</a> :
		<a href="./index.php?m=admin&vm=1">flat</a>
	</td>
	<td align="right" width="100%">
		<!-- <input type="button" class=button value="add group" onClick="javascript:window.location='./index.php?m=admin&a=addeditgroup';">-->
		<input type="button" class=button value="add user" onClick="javascript:window.location='./index.php?m=admin&a=addedituser';">
	</td>
</tr>
</table>

<table cellpadding="2" cellspacing="1" border="0" width="98%">
<tr>
</table>

<?php	
$tabs = array(
	'active_usr' => 'Active Users',
	'inactive_usr' => 'In-Active Users'
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

	$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'active_usr';
	drawTabBox( $tabs, $tab, "./index.php?m=admin", "./modules/admin" );
}

?>
