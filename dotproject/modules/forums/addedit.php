<?php /* FORUMS $Id$ */
// Add / Edit forum
$forum_id = isset( $_GET['forum_id'] ) ? $_GET['forum_id'] : 0;

//Pull forum information
$sql = "SELECT * FROM forums WHERE forums.forum_id = $forum_id";
$res = db_exec( $sql );
echo db_error();
$forum_info = db_fetch_assoc( $res );

$status = isset( $forum_info["forum_status"] ) ? $forum_info["forum_status"] : -1;

//Pull project Information
$sql = "SELECT project_id, project_name FROM projects WHERE project_active <> 0 ORDER BY project_name";
$projects = array( '0' => '' ) + db_loadHashList( $sql );
echo db_error();

//Pull user Information
$sql = "SELECT user_id, user_username FROM users ORDER BY user_username";
$users = array( '0' => '' ) + db_loadHashList( $sql );
echo db_error();

$crumbs = array();
$crumbs["?m=forums"] = "forums list";
?>
<script language="javascript">
function submitIt(){
	var form = document.changeforum;
	if(form.forum_name.value.length < 1) {
		alert("Please enter a valid forum name");
		form.forum_name.focus();
	} else if(form.forum_project.selectedIndex < 1) {
		alert("Please select the project to associate this forum with");
		form.forum_project.focus();
	} else if(form.forum_owner.selectedIndex < 1) {
		alert("Please select the owner of this forum");
		form.forum_owner.focus();
	} else {
		form.submit();
	}
}

function delIt(){
	var form = document.changeforum;
	if (confirm( "Are you sure you would like to delete this forum?\n\nNote: This will also delete all posts in this forum." )) {
		form.del.value="<?php echo $forum_id;?>";
		form.submit();
	}
}
</script>

<table width="98%" cellspacing="1" cellpadding="1" border="0">
<tr>
	<td><img src="./images/icons/communicate.gif" alt="" border="0" width="42" height="42"></td>
	<td nowrap width="100%"><h1><?php
		echo $AppUI->_( 'Project' ).' '.$AppUI->_( 'Forums' );
	?></h1></td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%">
<tr>
	<td width="50%" nowrap><?php echo breadCrumbs( $crumbs );?></td>
	<td width="50%" align="right">
		<a href="javascript:delIt()"><img align="absmiddle" src="./images/icons/trash.gif" width="16" height="16" alt="" border="0"><?php echo $AppUI->_( 'delete forum' );?></a>
	</td>
</tr>
</table>

<table cellspacing="0" cellpadding="4" border="0" width="98%" class="std">
<form name="changeforum" action="?m=forums" method="post">
	<input type="hidden" name="dosql" value="do_forum_aed" />
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="forum_unique_update" value="<?php echo uniqid("");?>" />
	<input type="hidden" name="forum_id" value="<?php echo $forum_id;?>" />

<tr height="20">
	<th valign="top" colspan="3">
		<strong><?php
		echo $AppUI->_( $forum_id ? 'Edit' : 'Add' ).' '.$AppUI->_( 'Forum' );
		?></strong>
	</th>
</tr>
<tr>
	<td valign="top" width="50%">
		<strong><?php echo $AppUI->_('Details');?></strong>
		<table cellspacing="1" cellpadding="2" width="100%">
		<tr>
			<td align="right" width="100">Forum Name:</td>
			<td>
				<input type="text" class="text" size=25 name="forum_name" value="<?php echo @$forum_info["forum_name"];?>" maxlength="50" style="width:200px;">
			</td>
		</tr>
		<tr>
			<td align="right">Related Project</td>
			<td>
		<?php
			echo arraySelect( $projects, 'forum_project', 'size="1" class="text"', $forum_info['forum_project'] );
		?>
			</td>
		</tr>
		<tr>
			<td align="right">Owner:</td>
			<td>
		<?php
			echo arraySelect( $users, 'forum_owner', 'size="1" class="text"', $forum_info['forum_owner'] ? $forum_info['forum_owner'] : $AppUI->user_id );
		?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap valign="top">Status:</td>
			<td valign="top">
				<input type="radio" value="-1" <?php if($status ==-1)echo " checked";?> name="forum_status">open for posting<br />
				<input type="radio" value="1" <?php if($status ==1)echo " checked";?> name="forum_status">read-only<br />
				<input type="radio" value="0" <?php if($status ==0)echo " checked";?> name="forum_status">closed
			</td>
		</tr>
		<tr>
			<td align="right" nowrap>Moderator:</td>
			<td>
		<?php
			echo arraySelect( $users, 'forum_moderated', 'size="1" class="text"', $forum_info['forum_moderated'] );
		?>
			</td>
		</tr>
		<?php if ($forum_id) { ?>
		<tr>
			<td align="right">Created On</td>
			<td bgcolor="#ffffff"><?php echo @$forum_info["forum_create_date"];?></td>
		</tr>
		<tr>
			<td align="right">Last Post:</td>
			<td bgcolor="#ffffff"><?php echo @$forum_info["forum_last_date"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap>Message Count:</td>
			<td bgcolor="#ffffff"><?php echo @$forum_info["forum_message_count"];?></td>
		</tr>
		<?php } ?>
		</table>
	</td>
	<td valign="top" width="50%">
		<strong>Description</strong><br />
		<textarea class="textarea" name="forum_description" style="height:150px;"><?php echo @$forum_info["forum_description"];?></textarea>
	</td>
</tr>


<tr>
	<td align="left">
		<input type="button" value="back" class=button onClick="javascript:window.location='./index.php?m=forums';">
	</td>
	<td align="right" colspan="2"><?php
		if ($AppUI->user_id == $forum_info["forum_owner"] || $forum_id ==0) {
			echo '<input type="button" value="submit" class=button onclick="submitIt()">';
		}?></td>
</tr>
</form>
</table>


