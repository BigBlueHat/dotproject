<?php /* $Id$*/
// check permissions
$denyRead = getDenyRead( $m );
$denyEdit = getDenyEdit( $m );

if ($denyRead) {
	$AppUI->redirect( "m=help&a=access_denied" );
}
$AppUI->savePlace();

// temporary until tranlated!
$AppUI->setWarning( false );

$sql = "SELECT * FROM modules ORDER BY mod_ui_order";
$modules = db_loadList( $sql );

// get the modules actually installed on the fiel system
$modFiles = $AppUI->readDirs( "modules" );

$crumbs = array();
$crumbs["?m=system"] = "System Admin";
?>

<script language="javascript">
</script>

<table cellspacing="1" cellpadding="1" border="0" width="98%">
<tr>
	<td><img src="<?php echo dPfindImage( 'modules.gif', $m );?>" alt="" border="0" /></td>
	<td nowrap="nowrap"><h1><?php echo $AppUI->_('Modules');?></h1></td>
	<td nowrap="nowrap"><img src="./images/shim.gif" width="16" height="16" alt="" border="0" /></td>
	<td valign="top" align="right" width="100%"></td>
	<td nowrap="nowrap" width="20" align="right"><?php echo contextHelp( '<img src="./images/obj/help.gif" width="14" height="16" border="0" alt="'.$AppUI->_( 'Help' ).'" />', 'ID_HELP_MODS_VIEW' );?></td>
</tr>
</table>

<table cellspacing="0" cellpadding="4" border="0" width="98%">
<tr>
	<td width="50%" nowrap="nowrap"><?php echo breadCrumbs( $crumbs );?></td>
</tr>
</table>

<span style="color:red"><strong>Note this function is still in development and testing phase</strong></span>

<table border="0" cellpadding="2" cellspacing="1" width="98%" class="tbl">
<tr>
	<th colspan="2"><?php echo $AppUI->_('Module');?></th>
	<th><?php echo $AppUI->_('Status');?></th>
	<th><?php echo $AppUI->_('Type');?></th>
	<th><?php echo $AppUI->_('Version');?></th>
	<th><?php echo $AppUI->_('Menu Text');?></th>
	<th><?php echo $AppUI->_('Menu Icon');?></th>
	<th><?php echo $AppUI->_('Menu Status');?></th>
</tr>
<?php
// do the modules that are installed on the system
foreach ($modules as $row) {
	// clear the file system entry
	if (isset( $modFiles[$row['mod_directory']] )) {
		$modFiles[$row['mod_directory']] = '';
	}
	$query_string = "?m=$m&a=domodsql&mod_id={$row['mod_id']}";
	$s = '';
	// arrows
	// TODO: sweep this block of code and add line returns to improve View Source readability [kobudo 14 Feb 2003]
	// Line returns after </td> tags would be a good start [as well as <tr> and </tr> tags]
	$s .= '<td>';
	$s .= '<img src="./images/icons/updown.gif" width="10" height="15" border=0 usemap="#arrow'.$row["mod_id"].'" />';
	$s .= '<map name="arrow'.$row["mod_id"].'">';
	$s .= '<area coords="0,0,10,7" href="' . $query_string . '&cmd=moveup">';
	$s .= '<area coords="0,8,10,14" href="'.$query_string . '&cmd=movedn">';
	$s .= '</map>';
	$s .= '</td>';

	$s .= '<td width="1%" nowrap="nowrap">'.$row['mod_name'].'</td>';
	$s .= '<td>';
	$s .= '<img src="./images/obj/dot'.($row['mod_active'] ? 'green' : 'yellowanim').'.gif" width="12" height="12" />&nbsp;';
	$s .= '<a href="'.$query_string . '&cmd=toggle&">'.($row['mod_active'] ? $AppUI->_('deactivate') : $AppUI->_('activate')).'</a>';
	if ($row['mod_type'] != 'core') {
		$s .= ' | <a href="'.$query_string . '&cmd=remove">'.$AppUI->_('remove').'</a>';
	}
	$s .= '</td>';
	$s .= '<td>'.$row['mod_type'].'</td>';
	$s .= '<td>'.$row['mod_version'].'</td>';
	$s .= '<td>'.$row['mod_ui_name'].'</td>';
	$s .= '<td>'.$row['mod_ui_icon'].'</td>';

	$s .= '<td>';
	$s .= '<img src="./images/obj/'.($row['mod_ui_active'] ? 'dotgreen.gif' : 'dotredanim.gif').'" width="12" height="12" />&nbsp;';
//	$s .= $row['mod_ui_active'] ? '<span style="color:green">'.$AppUI->_('on') : '<span style="color:red">'.$AppUI->_('off');
	$s .= '<a href="'.$query_string . '&cmd=toggleMenu">'.($row['mod_ui_active'] ? $AppUI->_('hide') : $AppUI->_('show')).'</a></td>';

	$s .= '<td>'.$row['mod_ui_order'].'</td>';

	echo "<tr>$s</tr>";
}

foreach ($modFiles as $v) {
	// clear the file system entry
	if ($v) {
		$s = '';
		$s .= '<td></td>';
		$s .= '<td>'.$v.'</td>';
		$s .= '<td>';
		$s .= '<img src="./images/obj/dotgrey.gif" width="12" height="12" />&nbsp;';

		$s .= '<a href="?m=' . $m . '&a=domodsql&cmd=install&mod_directory=' . $v . '">' . $AppUI->_('install') . '</a></td>';
		echo "<tr>$s</tr>";
	}

}
?>
</table>

</body>
</html>

