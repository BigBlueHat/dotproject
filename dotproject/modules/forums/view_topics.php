<?php /* FORUMS $Id$ */
$AppUI->savePlace();

//Pull All Messages
$sql = "
SELECT fm1.*,
	COUNT(fm2.message_id) AS replies,
	MAX(fm2.message_date) AS latest_reply,
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
		$sql.= " AND (NOW() < DATE_ADD(fm2.message_date, INTERVAL 30 DAY) OR NOW() < DATE_ADD(fm1.message_date, INTERVAL 30 DAY))";
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
	fm1.message_published" .
  ( @$dPconfig['forum_descendent_order'] ? " ORDER BY latest_reply DESC" : "" );

$topics = db_loadList( $sql );
##echo "<pre>$sql</pre>".db_error();

$crumbs = array();
$crumbs["?m=forums"] = "forums list";
?>
<table width="100%" cellspacing="1" cellpadding="2" border="0">
<tr>
	<td><?php echo breadCrumbs( $crumbs );?></td>
	<td align="right">
	<?php if ($canEdit) { ?>
		<input type="button" class=button style="width:120;" value="<?php echo $AppUI->_( 'start a new topic' );?>" onClick="javascript:window.location='./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id;?>&post_message=1';">
	<?php } ?>
	</td>
</tr>
</table>

<table width="100%" cellspacing="1" cellpadding="2" border="0" class="tbl">
<form name="watcher" action="?m=forums&a=viewer&forum_id=<?php echo $forum_id;?>&f=<?php echo $f;?>" method="post">
<tr>
	<th><?php echo $AppUI->_('Watch');?></th>
	<th><?php echo $AppUI->_('Topics');?></th>
	<th><?php echo $AppUI->_('Author');?></th>
	<th><?php echo $AppUI->_('Replies');?></th>
	<th><?php echo $AppUI->_('Last Post');?></th>
</tr>
<?php

$now = new CDate();

foreach ($topics as $row) {
	$last = intval( $row["latest_reply"] ) ? new CDate( $row["latest_reply"] ) : null;
	
//JBF limit displayed messages to first-in-thread
	if ($row["message_parent"] < 0) { ?>
<tr>
	<td nowrap="nowrap" align="center" width="1%">
		<input type="checkbox" name="forum_<?php echo $row['message_id'];?>" <?php echo $row['watch_user'] ? 'checked' : '';?> />
	</td>
	<td>
		<span style="font-size:10pt;">
		<a href="?m=forums&a=viewer&forum_id=<?php echo $forum_id . "&message_id=" . $row["message_id"];?>"><?php echo $row["message_title"];?></a>
		</span>
	</td>
	<td bgcolor="#dddddd" width="10%"><?php echo $row["user_username"];?></td>
	<td align="center" width="10%"><?php echo  $row["replies"];?></td>
	<td bgcolor="#dddddd" width="150" nowrap="nowrap">
<?php if ($row["latest_reply"]) {
		echo $last->format( "$df $tf" ).'<br /><font color=#999966>(';

		$span = new Date_Span();
		$span->setFromDateDiff( $now, $last );

		printf( "%.1f", $span->format( "%d" ) );
		echo ' '.$AppUI->_('days ago');

		echo ')</font>';
	} else {
		echo $AppUI->_("No replies");
	}
?>
	</td>
</tr>
<?php
//JBF
	}
}?>
</table>

<table width="100%" border="0" cellpadding="0" cellspacing="1">
<input type="hidden" name="dosql" value="do_watch_forum" />
<input type="hidden" name="watch" value="topic" />
<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td align="left">
		<input type="submit" class="button" value="<?php echo $AppUI->_( 'update watches' );?>" />
	</td>
</tr>
</form>
</table>
