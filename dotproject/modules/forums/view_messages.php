<?php  /* FORUMS $Id$ */
$AppUI->savePlace();

$sql = "
SELECT forum_messages.*,
	contact_first_name, contact_last_name, contact_email, user_username,
	forum_moderated
FROM forum_messages, forums
LEFT JOIN users ON message_author = users.user_id
LEFT JOIN contacts ON contact_id = user_contact
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
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canEdit) {
?>
function delIt(id){
	var form = document.messageForm;
	if (confirm( "<?php echo $AppUI->_('forumsDelete');?>" )) {
		form.del.value = 1;
		form.message_id.value = id;
		form.submit();
	}
}
<?php } ?>
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

foreach ($messages as $row) {
	$sql = "
	SELECT DISTINCT contact_email, contact_first_name, contact_last_name, user_username
	FROM users, forum_messages
        LEFT JOIN contacts ON contact_id = user_contact
	WHERE users.user_id = ".$row["message_editor"];

	$editor = db_loadList( $sql );

	$date = intval( $row["message_date"] ) ? new CDate( $row["message_date"] ) : null;

	$s = '';
	$style = $x ? 'background-color:#eeeeee' : '';

	$s .= "<tr>";

	$s .= '<td valign="top" style="'.$style.'" nowrap="nowrap">';
	$s .= '<a href="mailto:'.$row["contact_email"].'">';
	$s .= '<font size="2">'.$row['contact_first_name'].' '.$row['contact_last_name'].'</font></a>';
	if (sizeof($editor)>0) {
		$s .= '<br/>&nbsp;<br/>'.$AppUI->_('last edited by');
		$s .= ':<br/><a href="mailto:'.$editor[0]["contact_email"].'">';
		$s .= '<font size="1">'.$editor[0]['contact_first_name'].' '.$editor[0]['contact_last_name'].'</font></a>';
	}
	$s .= '</td>';
	$s .= '<td valign="top" style="'.$style.'">';
	$s .= '<font size="2"><strong>'.$row["message_title"].'</strong><hr size=1>';
	$s .= str_replace( chr(13), "&nbsp;<br />", $row["message_body"] );
	$s .= '</font></td>';

	$s .= '</tr><tr>';

	$s .= '<td valign="top" style="'.$style.'" nowrap="nowrap">';
	$s .= '<img src="./images/icons/posticon.gif" alt="date posted" border="0" width="14" height="11">'.$date->format( "$df $tf" ).'</td>';
	$s .= '<td valign="top" align="right" style="'.$style.'">';

	//the following users are allowed to edit/delete a forum message: 1. the forum creator  2. a superuser with read-write access to 'all'
	if ( ($canEdit && $AppUI->user_id == $row['forum_moderated']) || (!empty($perms['all']) && !getDenyEdit('all')) ) {
		$s .= '<table cellspacing="0" cellpadding="0" border="0"><tr>';
	// edit message
		$s .= '<td><a href="./index.php?m=forums&a=viewer&post_message=1&forum_id='.$row["message_forum"].'&message_parent='.$row["message_parent"].'&message_id='.$row["message_id"].'" title="'.$AppUI->_( 'Edit' ).' '.$AppUI->_( 'Message' ).'">';
		$s .= dPshowImage( './images/icons/stock_edit-16.png', '16', '16' );
		$s .= '</td><td>';
	// delete message
		$s .= '<a href="javascript:delIt('.$row["message_id"].')" title="'.$AppUI->_( 'delete' ).'">';
		$s .= dPshowImage( './images/icons/stock_delete-16.png', '16', '16' );
		$s .= '</a>';
		$s .= '</td></tr></table>';

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
		<input type="button" class="button" value="<?php echo $AppUI->_('Post Reply');?>" onclick="javascript:window.location='./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id;?>&message_parent=<?php echo $message_id;?>&post_message=1';" />
		<input type="button" class="button" value="<?php echo $AppUI->_('New Topic');?>" onclick="javascript:window.location='./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id;?>&message_id=0&post_message=1';" />
	<?php } ?>
	</td>
</tr>
</table>
