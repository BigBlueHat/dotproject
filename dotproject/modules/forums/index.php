<?php
// check permissions
$denyRead = getDenyRead( $m );
$denyEdit = getDenyEdit( $m );

if ($denyRead) {
	echo '<script language="javascript">
	window.location="./index.php?m=help&a=access_denied";
	</script>
';
}

$f = isset( $_GET['f'] ) ? $_GET['f'] : 0;

/*
	DATE_FORMAT(forum_create_date, '%d %b %Y') forum_create_date,
	l.message_id, DATE_FORMAT(MAX(l.message_date),'%d %b %Y %h:%m %p') last_date,
	TO_DAYS(now())-TO_DAYS(MAX(l.message_date)) last_days,
	COUNT(distinct t.message_id) forum_topics, COUNT(distinct r.message_id) forum_replies,
	user_username,
	project_name, project_color_identifier, project_id
FROM forums, users, projects
LEFT JOIN forum_messages t ON t.message_forum = forum_id AND t.message_parent = -1
LEFT JOIN forum_messages r ON r.message_forum = forum_id AND r.message_parent > -1
LEFT JOIN forum_messages l ON l.message_forum = forum_id
*/

//Forum index.php
$max_msg_length = 30;
$sql = "
SELECT forum_id, forum_project, forum_description, forum_owner, forum_name, forum_moderated,
	DATE_FORMAT(forum_create_date, '%d %b %Y') forum_create_date,
	COUNT(distinct t.message_id) forum_topics, COUNT(distinct r.message_id) forum_replies,
	user_username,
	project_name, project_color_identifier, project_id,
	DATE_FORMAT(l.message_date, '%d %b %Y %h:%i %p') message_date, 
	SUBSTRING(l.message_body,1,$max_msg_length) message_body,
	LENGTH(l.message_body) message_length,
	UNIX_TIMESTAMP(now())-UNIX_TIMESTAMP(MAX(l.message_date)) message_since,
	watch_user,
	l.message_parent
FROM forums, users, projects
LEFT JOIN forum_messages t ON t.message_forum = forum_id AND t.message_parent = -1
LEFT JOIN forum_messages r ON r.message_forum = forum_id AND r.message_parent > -1
LEFT JOIN forum_messages l ON l.message_id = forum_last_id
LEFT JOIN forum_watch ON watch_user = $AppUI->user_id AND watch_forum = forum_id
WHERE user_id = forum_owner";
if (isset($project_id)) {
	$sql.= " AND forum_project = $project_id";
}
switch ($f) {
	case 1:
		$sql.= " AND forum_owner = $AppUI->user_id";
		break;
	case 2:
		$sql.= " AND watch_user IS NOT NULL";
		break;
	case 3:
		$sql.= " AND project_owner = $AppUI->user_id";
		break;
	case 4:
		$sql.= " AND project_company = $AppUI->user_company";
		break;
}
$sql .= " AND  project_id = forum_project
GROUP BY forum_id
ORDER BY forum_project, forum_name
";
$rc= mysql_query($sql);
##echo "<pre>$sql</pre>".mysql_error();##
?>
<img src="images/shim.gif" width="1" height="5" alt="" border="0"><br>
<TABLE width="95%" border=0 cellpadding="0" cellspacing=1>
<TR>
	<TD><img src="./images/icons/communicate.gif" alt="" border="0" width=42 height=42></td>
	<TD nowrap width="100%"><span class="title">Project Forums</span></td>
<form name="forum_filter" method=GET action="./index.php">
<input type=hidden name=m value=forums>
	<TD nowrap>
<?php
	echo arraySelect( $filters, 'f', 'size=1 class=text onChange="document.forum_filter.submit();"', $f );
?>
	</td>
</form>
	<TD><img src="images/shim.gif" width=5 height=5></td>
<form name="searcher" action="./index.php?m=files&a=search" method="post">
<input type=hidden name=dosql value=searchfiles>
	<TD align="right"><input class=button type=text name=s maxlength=30 size=20 value="Not implemented" disabled></TD>
	<TD><img src="images/shim.gif" width=5 height=5></td>
	<TD><input class=button type="submit" value="search" disabled></td>
</form>
	<TD><img src="images/shim.gif" width=5 height=5></td>
	<TD align="right">
	<?php if (!$denyEdit) { ?>
		<input type="button" class=button value="add new forum" onClick="javascript:window.location='./index.php?m=forums&a=addedit';">
	<?php } ?>
	</td>
</tr>
</TABLE>

<TABLE width="95%" border=0 cellpadding=2 cellspacing=1 class=tbl>
<form name="watcher" action="./index.php?m=forums&f=<?php echo $f;?>" method="post">
<TR style="border: outset #eeeeee 2px;">
	<th nowrap>&nbsp;</th>
	<th nowrap width=25>Watch</th>
	<th nowrap><A href="#"><font color="white">Forum Name</font></a></th>
	<th nowrap width=50 align=center><A href="#"><font color="white">Topics</font></a></th>
	<th nowrap width=50 align=center><A href="#"><font color="white">Replies</font></a></th>
	<th nowrap width=200><A href="#"><font color="white">Last Post Info</font></a></th>
</tr>
<?php
$p ="";
while($row = mysql_fetch_array($rc)){
	if($p != $row["project_id"]){
?>
<TR>
	<TD colspan=6 style="background-color: #<?php echo $row["project_color_identifier"];?>">
		<A href="./index.php?m=projects&a=view&project_id=<?php echo $row["project_id"];?>"><font color=<?php echo bestColor( $row["project_color_identifier"] );?>><B><?php echo $row["project_name"];?></b></font></a>
	</td>
</tr>
	<?php
		$p = $row["project_id"];
	}?>
<TR>
	<TD nowrap align=center>
	<?php if($row["forum_owner"] == $user_cookie){?>
		<A href="./index.php?m=forums&a=addedit&forum_id=<?php echo $row["forum_id"];?>"><img src="./images/icons/pencil.gif" alt="expand forum" border="0" width=12 height=12></a>
	<?php }?>
	</td>

	<TD nowrap align=center>
		<input type="checkbox" name="forum_<?php echo $row['forum_id'];?>" <?php echo $row['watch_user'] ? 'checked' : '';?>>
	</td>

	<TD>
		<span style="font-size:10pt;font-weight:bold"><A href="./index.php?m=forums&a=viewer&forum_id=<?php echo $row["forum_id"];?>"><?php echo $row["forum_name"];?></a></span>
		<br><?php echo $row["forum_description"];?>
		<br><font color=#777777>Forum Owned by: <?php echo $row["user_username"];?>,
		started <?php echo $row["forum_create_date"];?></font>
	</td>
	<TD nowrap align=center><?php echo $row["forum_topics"];?></td>
	<TD nowrap align=center><?php echo $row["forum_replies"];?></td>
	<TD width=200>
<?php if ($row["message_date"]) {
		echo $row["message_date"].'<br><font color=#999966>(';
		if ($row["message_since"] < 3600) {
			printf( "%d minutes", $row["message_since"]/60 );
		} else if ($row["message_since"] < 48*3600) {
			printf( "%d hours", $row["message_since"]/3600 );
		} else {
			printf( "%d days", $row["message_since"]/(24*3600) );
		}
		echo ' ago)</font><br>&gt;&nbsp;<a href="./index.php?m=forums&a=viewer&forum_id='.$row['forum_id'].'&message_id='.$row['message_parent'].'"><font color=#777777>'.$row['message_body'];
		echo $row['message_length'] > $max_msg_length ? '...' : '';
		echo '</font></a>';
	} else {
		echo "No posts";
	}
?>
	</td>
</tr>

<?php }?>
</TABLE>

<TABLE width="95%" border=0 cellpadding="0" cellspacing=1>
<input type=hidden name=dosql value=watch_forum>
<input type=hidden name=watch value=forum>
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
