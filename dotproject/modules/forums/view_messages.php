<?php  /* FORUMS $Id$ */
$AppUI->savePlace();

$sql = "
SELECT forum_messages.*,
	user_first_name, user_last_name, user_email, user_username,
	forum_moderated
FROM forum_messages, forums
LEFT JOIN users ON message_author = users.user_id
WHERE forum_id = message_forum
	AND (message_id = $message_id OR message_parent = $message_id)" .
  ( @$dPconfig['forum_descendent_order'] ? " ORDER BY message_date DESC" : "" );

//echo "<pre>$sql</pre>";
$messages = db_loadList( $sql );

$crumbs = array();
$crumbs["?m=forums"] = "forums list";
$crumbs["?m=forums&a=viewer&forum_id=$forum_id"] = "topics for this forum";
?>
<script language="javascript">
function delIt(id){
	var form = document.messageForm;
	if (confirm( "<?php echo $AppUI->_('forumsDelete');?>" )) {
		form.del.value = 1;
		form.message_id.value = id;
		form.submit();
	}
}
</script>

<table width="98%" cellspacing="1" cellpadding="2" border="0">
<tr>
	<td><?php echo breadCrumbs( $crumbs );?></td>
	<td align="right">
	<?php if ($canEdit) { ?>
		<input type="button" class=button value="<?php echo $AppUI->_('Post Reply');?>" onClick="javascript:window.location='./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id;?>&message_parent=<?php echo $message_id;?>&post_message=1';" />
		<input type="button" class=button value="<?php echo $AppUI->_('New Topic');?>" onClick="javascript:window.location='./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id;?>&message_id=0&post_message=1';" />
	<?php } ?>
	</td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="1" width="98%" class="tbl">
<!-- <form name="messageForm" method="POST" action="?m=forums&a=viewposts&forum_id=<?php echo $row['message_forum'];?>"> -->
<form name="messageForm" method="POST" action="?m=forums&forum_id=<?php echo $row['message_forum'];?>">
	<input type="hidden" name="dosql" value="do_post_aed" />
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="message_id" value="0" />
</form>
<tr>
	<th nowrap><?php echo $AppUI->_('Author');?>:</th>
	<th width="100%"><?php echo $AppUI->_('Message');?>:</th>
</tr>

<?php 
$x = false;

$date = new CDate();
$date->setFormat( "$df $tf" );

foreach ($messages as $row){
	$date->setTimestamp( db_dateTime2unix( $row["message_date"] ) );
	$s = '';
	$style = $x ? 'background-color:#eeeeee' : '';

	$s .= "<tr>";

	$s .= '<td valign="top" style="'.$style.'" nowrap="nowrap">';
	$s .= '<a href="mailto:"'.$row["user_email"].'">';
	$s .= '<font size="2">'.$row["user_first_name"].' '.$row["user_last_name"].'</font></a></td>';
	$s .= '<td valign="top" style="'.$style.'">';
	$s .= '<font size="2"><strong>'.$row["message_title"].'</strong><hr size=1>';
	$s .= str_replace( chr(13), "&nbsp;<br />", $row["message_body"] );
	$s .= '</font></td>';

	$s .= '</tr><tr>';

	$s .= '<td valign="top" style="'.$style.'" nowrap="nowrap">';
	$s .= '<img src="./images/icons/posticon.gif" alt="date posted" border="0" width="14" height="11">'.$date->toString().'</td>';
	$s .= '<td valign="top" align="right" style="'.$style.'">';
	
	if ($canEdit && $AppUI->user_id == $row['forum_moderated']) {
	// edit message
		$s .= '<a href="./index.php?m=forums&a=viewer&post_message=1&forum_id='.$row["message_forum"].'&message_parent='.$row["message_parent"].'&message_id='.$row["message_id"].'">';
		$s .= '<img src="images/icons/pencil.gif" width="12" height="12" border="0" alt="'.$AppUI->_( 'Edit' ).' '.$AppUI->_( 'Message' ).'"></a>';
	// delete message
		$s .= '&nbsp;<a href="javascript:delIt('.$row["message_id"].')"><img src="images/icons/trash.gif" width="16" height="16" border="0" alt="'.$AppUI->_( 'delete' ).'"></a>';

	}
	$s .= '</td>';

	$s .= '</tr>';

	echo $s;
	$x = !$x;
}
?>
</table>
<table border=0 cellpadding=2 cellspacing=1 width="98%" >
<tr>
	<td><?php echo breadCrumbs( $crumbs );?></td>
	<td align="right">
	<?php if ($canEdit) { ?>
		<input type="button" class=button value="<?php echo $AppUI->_('Post Reply');?>" onclick="javascript:window.location='./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id;?>&message_parent=<?php echo $message_id;?>&post_message=1';" />
		<input type="button" class=button value="<?php echo $AppUI->_('New Topic');?>" onclick="javascript:window.location='./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id;?>&message_id=0&post_message=1';" />
	<?php } ?>
	</td>
</tr>
</table>
