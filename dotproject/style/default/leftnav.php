<?php
$leftnav = array(
	array( 'companies', 'money.gif', 'Clients & Companies' ),
	//array( 'plans', 'companies.gif', 'Plans' ),
	//array( 'reports', 'graph.gif', 'Reports' ),
	array( 'projects', 'projects.gif', 'Projects' ),
	array( 'tasks', 'tasks.gif', 'Tasks' ),
	array( 'calendar', 'calendar.gif', 'Calendar' ),
	array( 'files', 'folder.gif', 'Files' ),
	array( 'contacts', 'contacts.gif', 'Contacts' ),
	array( 'forums', 'communicate.gif', 'Forums' ),
	array( 'ticketsmith', 'ticketsmith.gif', 'Tickets' ),
	array( 'admin', 'admin.gif', 'User Admin' ),
	array( 'system', 'system.gif', 'System Admin' )
);
?>
<table cellspacing=0 cellpadding=2 border=0 height="600">
<tr>
	<td><img src=images/shim.gif width=70 height=3></td>
	<td rowspan="100"><img src=images/shim.gif width=10 height=100></td>
</tr>
<?php
$s = '';
foreach ($leftnav as $module) {
	if (isset( $perms['all'] ) || isset( $perms[$module[0]] )) {
		$s .= '<td align="center" valign="middle" class="nav">'
			.'<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff">'
			.'<a href="?m='.$module[0].'">'
			.'<img src="./images/icons/'.$module[1].'" onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" alt="" border="0" width="30" height="30"></a></td></tr></table>'
			.$AppUI->_($module[2])
			.'</td></tr>';
	}
}
echo $s;
?>

<tr height="100%">
	<td>&nbsp;<img src=images/shim.gif width=7 height=10></td>
</tr>
</table>