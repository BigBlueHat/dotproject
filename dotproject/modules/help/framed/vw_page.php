<?php
// Add / Edit Control Key
$sql = "SELECT * FROM {$AppUI->project_dbprefix}xpages WHERE page_entry=$entry_id AND page_lang='$AppUI->user_locale'";
##echo "<pre>$sql</pre>";##
if(!db_loadHash( $sql, $page )) {
	echo '<font color=red>SQL Error:</font> '.db_errno() . ": " . db_error() . "\n";
}
##echo "<pre>$sql</pre>";##
?>

<table width="100%" height="100%" border=0 cellpadding="2" cellspacing=0 >
<tr valign=top>
	<td bgcolor=#ffffff>
		<?php echo ($page["page_show_title"] ? '<h1>'.$page["page_title"].'</h1><br />' : '') .$page["page_content"];?>
	</td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
</table>

&nbsp;<br />&nbsp;<br />&nbsp;
