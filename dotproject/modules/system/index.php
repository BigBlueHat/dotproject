<?php
// System Administration

// check permissions
$denyRead = getDenyRead( $m );
$denyEdit = getDenyEdit( $m );

if ($denyRead) {
	$AppUI->redirect( "m=help&a=access_denied" );
}

?>

<img src="images/shim.gif" width="1" height="5" alt="" border="0"><br>
<table width="98%" border=0 cellpadding=0 cellspacing=1>
<tr>
	<td><img src="./images/icons/system.gif" alt="" border="0"></td>
	<td nowrap><span class="title">System Administration</span></td>
	<td align="right" width="100%">
	</td>
</tr>
</table>

<img src="images/shim.gif" width="1" height="10" alt="" border="0"><br>

<table width="50%" border="0" cellpadding="0" cellspacing="5" align="left">
<tr>
	<td width="34">
		<img src="./images/icons/world.gif" width="34" height="34" border="0" alt="">
	</td>
	<td align="left" class="subtitle">
		Language Support
	</td>
</tr>

<tr>
	<td>&nbsp;</td>
	<td align="left">
		<a href="?m=system&a=translate">Translation Management</a>
		<br /><a href="#">Date and Time</a>
	</td>
</tr>
</table>

