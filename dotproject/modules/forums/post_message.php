<?php /* FORUMS $Id$ */
// Add / Edit forum
$message_id = isset( $_GET['message_id'] ) ? $_GET['message_id'] : 0;
$message_parent = isset( $_GET['message_parent'] ) ? $_GET['message_parent'] : -1;

//Pull forum information
$sql = "
SELECT forum_name, forum_owner, forum_moderated,
	project_name, project_id
FROM projects, forums
WHERE forums.forum_id = $forum_id
	AND forums.forum_project = projects.project_id
";

$res = db_exec( $sql );
$forum_info = db_fetch_assoc( $res );
echo db_error();

//pull message information
$sql = "
SELECT forum_messages.*
FROM forum_messages
WHERE message_id = ";
$sql .= $message_id ? $message_id : $message_parent;
$res = db_exec( $sql );
echo db_error();
$message_info = db_fetch_assoc($res);

$crumbs = array();
$crumbs["?m=forums"] = "forums list";
$crumbs["?m=forums&a=viewer&forum_id=$forum_id"] = "topics for this forum";
if ($message_parent > -1) {
	$crumbs["?m=forums&a=viewer&forum_id=$forum_id&message_id=$message_parent"] = "this topic";
}
?>
<script language="javascript">
function submitIt(){
	var form = document.changeforum;
	if (form.message_title.value.length < 1) {
		alert("Please enter a valid message subject");
		form.message_title.focus();
	} else if (form.message_body.value.length < 1) {
		alert("Please type a message before posting");
		form.message_body.focus();
	} else {
		form.submit();
	}
}

function delIt(){
	var form = document.changeforum;
	if (confirm( "Are you sure you would like\nto delete this post?" )) {
		form.del.value="<?php echo $message_id;?>";
		form.submit();
	}
}

function orderByName(x){
	var form = document.changeforum;
	if (x == "name") {
		form.forum_order_by.value = form.forum_last_name.value + ", " + form.forum_name.value;
	} else {
		form.forum_order_by.value = form.forum_project.value;
	}
}
</script>

<table cellspacing="1" cellpadding="2" border="0" width="98%">
<tr>
	<td><?php echo breadCrumbs( $crumbs );?></td>
	<td align="right"></td>
</tr>
</table>

<table cellspacing="0" cellpadding="3" border="0" width="98%" class="std">

<!-- <form name="changeforum" action="?m=forums&a=viewposts&forum_id=<?php echo $forum_id;?>" method="post"> -->
<form name="changeforum" action="?m=forums&forum_id=<?php echo $forum_id;?>" method="post">
	<input type="hidden" name="dosql" value="do_post_aed" />
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="message_forum" value="<?php echo $forum_id;?>" />
	<input type="hidden" name="message_parent" value="<?php echo $message_parent;?>" />
	<input type="hidden" name="message_published" value="<?php echo $forum_info["forum_moderated"] ? '1' : '0';?>" />
	<input type="hidden" name="message_author" value="<?php echo $AppUI->user_id;?>" />
	<input type="hidden" name="message_id" value="<?php echo $message_id;?>" />

<tr>
	<th valign="top" colspan="2"><strong><?php
		echo $AppUI->_( $message_id ? 'Edit Message' : 'Add Message' );
	?></strong></th>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Subject' );?>:</td>
	<td>
		<input type="text" name="message_title" value="<?php echo ($message_id || $message_parent < 0 ? '' : 'Re: ') .$message_info['message_title'];?>" size=50 maxlength=250>
	</td>
</tr>
<tr>
	<td align="right" valign="top"><?php echo $AppUI->_( 'Message' );?>:</td>
	<td align="left" valign="top">
		<textarea cols="50" name="message_body" style="height:200px"><?php echo $message_id ? @$message_info["message_body"] : '';?></textarea>
	</td>
</tr>
<tr>
	<td>
		<input type="button" value="<?php echo $AppUI->_('back');?>" class=button onclick="javascript:window.location='./index.php?m=forums';">
	</td>
	<td align="right"><?php
		if ($AppUI->user_id == $forum_info["forum_owner"] || $message_id ==0) {
			echo '<input type="button" value="'.$AppUI->_('submit').'" class=button onclick="submitIt()">';
		}
	?></td>
</tr>
</form>
</table>
