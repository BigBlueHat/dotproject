<?php
//User Managagement
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
$sql = "SELECT DISTINCT UPPER(SUBSTRING(user_username, 1, 1)) FROM users";
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
			<td><a href="./index.php?m=admin">all</a></td>
<?php
	for ($a=65; $a < 91; $a++) {
		$cu = chr( $a );
		$cell = strpos($let, "$cu") > 0 ?
			"<a href=\"?m=admin&z=$cu\">$cu</a>" :
			"<font color=\"#999999\">$cu</font>";
		echo "<td>$cell</td>";
	}
?>
		</tr>
		</table>
	</td>
</tr>
</table>

<?php
$extra = '<td align="right" width="100%"><input type="button" class=button value="add user" onClick="javascript:window.location=\'./index.php?m=admin&a=addedituser\';"></td>';

// tabbed information boxes
$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 0;
$tabBox = new CTabBox( "?m=admin", "./modules/admin", $tab );
$tabBox->add( 'vw_active_usr', 'Active Users' );
$tabBox->add( 'vw_inactive_usr', 'In-Active Users' );
$tabBox->show( $extra );
?>
