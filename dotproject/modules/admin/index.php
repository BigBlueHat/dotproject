<?php
//User Managagement
// view mode = 0 tabbed, 1 flat
$vm = isset($_GET['vm']) ? $_GET['vm'] : 0;

// check permissions
$denyRead = getDenyRead( $m );
$denyEdit = getDenyEdit( $m );

if ($denyRead) {
	echo '<script language="javascript">
	window.location="./index.php?m=help&a=access_denied";
	</script>
';
}

?>

<script language="javascript">
function delMe( x, y ) {
	if (confirm( "Are you sure you want\nto delete user " + y + "?" )) {
		top.location="./index.php?m=admin&a=dosql&del=1&user_id=" + x;
	}
}
</script>

<table cellpadding="0" cellspacing="1" border="0" width="95%">
<tr>
	<td valign="top"><img src="./images/icons/admin.gif" alt="" border="0" width=42 height=42></td>
	<td nowrap><span class="title">User Management</span></td>
</tr>
<tr>
	<td colspan="3">
		<a href="./index.php?m=admin&vm=0">tabbed</a> :
		<a href="./index.php?m=admin&vm=1">flat</a>
	</td>
	<td align="right" width="100%">
		<!-- <input type="button" class=button value="add group" onClick="javascript:window.location='./index.php?m=admin&a=addeditgroup';">-->
		<input type="button" class=button value="add user" onClick="javascript:window.location='./index.php?m=admin&a=addedituser';">
	</td>
</tr>
</table>

<?php	
$tabs = array(
	'active_usr' => 'Active Users',
	'inactive_usr' => 'In-Active Users'
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

	$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'active_usr';
	drawTabBox( $tabs, $tab, "./index.php?m=admin", "./modules/admin" );
}

?>
