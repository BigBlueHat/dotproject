<?php
GLOBAL $AppUI, $project_id;
// Forums mini-table in project view action

$sql = "
SELECT forum_id, forum_project, forum_description, forum_owner, forum_name, forum_message_count,
	DATE_FORMAT(forum_last_date, '%d-%b-%Y %H:%i' ) forum_last_date,
	project_name, project_color_identifier, project_id
FROM forums
LEFT JOIN projects ON project_id = forum_project
WHERE forum_project = $project_id
ORDER BY forum_project, forum_name
";
//echo "<pre>$sql</pre>";
$rc= mysql_query($sql);
?>

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th nowrap>&nbsp;</th>
	<th nowrap width="100%">Forum Name</th>
	<th nowrap>Messages</th>
	<th nowrap>Last Post</th>
</tr>
<?php
while ($row = mysql_fetch_array( $rc )) { ?>
<tr>
	<td nowrap align=center>
<?php
	if ($row["forum_owner"] == $AppUI->user_id) { ?>
		<A href="./index.php?m=forums&a=addedit&forum_id=<?php echo $row["forum_id"];?>"><img src="./images/icons/pencil.gif" alt="expand forum" border="0" width=12 height=12></a>
<?php } ?>
	</td>
	<td nowrap><A href="./index.php?m=forums&a=viewer&forum_id=<?php echo $row["forum_id"];?>"><?php echo $row["forum_name"];?></a></td>
	<td nowrap><?php echo $row["forum_message_count"];?></td>
	<td nowrap>
		<?php echo (intval( $row["forum_last_date"] ) > 0) ? $row["forum_last_date"] : 'n/a'; ?>
	</td>
</tr>
<tr>
	<td></td>
	<td colspan=3><?php echo $row["forum_description"];?></td>
</tr>
<?php }?>
</table>
