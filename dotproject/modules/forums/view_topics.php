<?php

//Pull All Messages
$sql = "
SELECT fm1.message_id, fm1.message_parent, fm1.message_author, fm1.message_title,
	fm1.message_date, fm1.message_published,
	COUNT(fm2.message_id) AS replies,
	DATE_FORMAT(MAX(fm2.message_date), '%d %b %Y %h:%i %p') AS latest_reply,
	UNIX_TIMESTAMP(now())-UNIX_TIMESTAMP(MAX(fm2.message_date)) message_since,
	user_username, user_first_name,
	watch_user
FROM forum_messages fm1
LEFT JOIN users ON fm1.message_author = users.user_id
LEFT JOIN forum_messages fm2 ON fm1.message_id = fm2.message_parent
LEFT JOIN forum_watch ON watch_user = $AppUI->user_id AND watch_topic = fm1.message_id
WHERE fm1.message_forum = $forum_id
";
switch ($f) {
	case 1:
		$sql.= " AND watch_user IS NOT NULL";
		break;
	case 2:
		$sql.= " AND NOW() < DATE_ADD(fm2.message_date, INTERVAL 30 DAY)";
		break;
}
$sql .= "
GROUP BY
	fm1.message_id,
	fm1.message_parent,
	fm1.message_author,
	fm1.message_title,
	fm1.message_date,
	fm1.message_body,
	fm1.message_published
";
$prc = mysql_query($sql);
##echo "<pre>$sql</pre>".mysql_error();
?>
<TABLE border=0 cellpadding=2 cellspacing=1 width="95%" >
<TR>
	<TD><A href="./index.php?m=forums">All forums</a></td>
	<TD align="right">
	<?php if (!$denyEdit) { ?>
		<input type="button" class=button style="width:120;" value="start a new topic" onClick="javascript:window.location='./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id;?>&post_message=1';">
	<?php } ?>
	</td>
</TR>
</TABLE>

<TABLE border=0 cellpadding=2 cellspacing=1 width="95%" class=tbl>
<form name="watcher" action="./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id;?>&f=<?php echo $f;?>" method="post">
<TR>
	<th>Watch</th>
	<th>Topics</th>
	<th>Author</th>
	<th>Replies</th>
	<th>Last Post</th>
</tr>
<?php 
$n = mysql_num_rows($prc);
for ($x=0; $x<$n; $x++) {
	$row = mysql_fetch_array($prc);
//JBF limit displayed messages to first-in-thread
	if ($row["message_parent"] < 0) { ?>
<TR>
	<TD nowrap align=center>
		<input type="checkbox" name="forum_<?php echo $row['message_id'];?>" <?php echo $row['watch_user'] ? 'checked' : '';?>>
	</td>
	<TD>
		<span style="font-size:10pt;">
		<A href="./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id . "&message_id=" . $row["message_id"];?>"><?php echo $row["message_title"];?></a>
		</span>
	</td>
	<TD bgcolor=#dddddd><?php echo $row["user_username"];?></td>
	<TD align=center><?php echo  $row["replies"];?></td>
	<TD bgcolor=#dddddd>
<?php if ($row["latest_reply"]) {
		echo $row["latest_reply"].'<br><font color=#999966>(';
		if ($row["message_since"] < 3600) {
			printf( "%d minutes", $row["message_since"]/60 );
		} else if ($row["message_since"] < 48*3600) {
			printf( "%d hours", $row["message_since"]/3600 );
		} else {
			printf( "%d days", $row["message_since"]/(24*3600) );
		}
		echo ' ago)</font>';
	} else {
		echo "No replies";
	}
?>
	</td>
</tr>
<?php
//JBF
	}
}?>
</table>

<TABLE width="95%" border=0 cellpadding="0" cellspacing=1>
<input type=hidden name=dosql value=watch_forum>
<input type=hidden name=watch value=topic>
<TR>
	<TD>&nbsp;</td>
</tr>
<TR>
	<TD align="left">
		<input type="submit" class=button value="update watches">
	</td>
</tr>
</form>
</TABLE>
