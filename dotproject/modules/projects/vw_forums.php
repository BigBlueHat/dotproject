<b>Forums:</b>
<?php
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

<TABLE width="100%" border=0 cellpadding="2" cellspacing=1 bgcolor="white">
<TR style="border: outset #eeeeee 2px;">
	<TD nowrap class="mboxhdr">&nbsp;</td>
	<TD nowrap class="mboxhdr" width="100%"><A href="#"><font color="white">Forum Name</font></a></td>
	<TD nowrap class="mboxhdr"><A href="#"><font color="white">Messages</font></a></td>
	<TD nowrap class="mboxhdr"><A href="#"><font color="white">Last Post</font></a></td>
</tr>
<?php
while ($row = mysql_fetch_array( $rc )) { ?>
<TR bgcolor="#f4efe3">
	<TD nowrap align=center>
<?php
	if ($row["forum_owner"] == $user_cookie) { ?>
		<A href="./index.php?m=forums&a=addedit&forum_id=<?php echo $row["forum_id"];?>"><img src="./images/icons/pencil.gif" alt="expand forum" border="0" width=12 height=12></a>
<?php } ?>
	</td>
	<TD nowrap><A href="./index.php?m=forums&a=viewer&forum_id=<?php echo $row["forum_id"];?>"><?php echo $row["forum_name"];?></a></td>
	<TD nowrap><?php echo $row["forum_message_count"];?></td>
	<TD nowrap>
		<?php echo (intval( $row["forum_last_date"] ) > 0) ? $row["forum_last_date"] : 'n/a'; ?>
	</td>
</tr>
<TR>
	<TD></td>
	<TD colspan=3><?php echo $row["forum_description"];?></td>
</tr>
<?php }?>
</TABLE>
