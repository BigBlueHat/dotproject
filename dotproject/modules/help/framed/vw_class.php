<?
// View OOP Class

$sql = "
SELECT *
FROM {$AppUI->project_dbprefix}xclasses
WHERE class_entry = $entry_id
	AND class_lang = '$AppUI->user_locale'
";
$rc = db_exec($sql);
if(!$rc) {
	echo '<font color=red>SQL Error:</font> '.db_errno() . ": " . db_error() . "\n";
}
##echo "<pre>$sql</pre>";##
$rows = db_fetch_assoc( $rc );
?>

<table width="100%" height="100%" border=0 cellpadding="2" cellspacing=0 >
<tr valign=top>
	<td bgcolor=#ffffff>
		<?echo '<h1>'.$rows["class_title"].'</h1><br>' .$rows["class_content"];?>
	</td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
</table>

&nbsp;<br>&nbsp;<br>&nbsp;
