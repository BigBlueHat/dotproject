<?php /* $Id$ */
$nav = dPgetMenuModules();
?>
<table cellspacing=0 cellpadding=2 border=0 height="600">
<tr>
	<td><img src="images/shim.gif" width="70" height="3"></td>
	<td rowspan="100"><img src="images/shim.gif" width="10" height="100"></td>
</tr>
<?php
$s = '';
foreach ($nav as $module) {
	if (!getDenyRead( $module['mod_directory'] )) {
		$s .= '<tr><td align="center" valign="middle" class="nav">'
			.'<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff">'
			.'<a href="?m='.$module['mod_directory'].'">'
			.'<img src="'.dPfindImage( $module['mod_ui_icon'], $m ).'" onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" alt="" border="0" width="30" height="30"></a></td></tr></table>'
			.$AppUI->_($module['mod_ui_name'])
			."</td></tr>\n";
	}
}
echo $s;
?>
<tr height="100%">
	<td>&nbsp;<img src="images/shim.gif" width="7" height="10"></td>
</tr>
</table>