<?php
//view posts
$forum_id = isset($HTTP_GET_VARS["forum_id"]) ? $HTTP_GET_VARS["forum_id"] : 0;
$message_id = isset($HTTP_GET_VARS["message_id"]) ? $HTTP_GET_VARS["message_id"] : 0;
$post_message = isset($HTTP_GET_VARS["post_message"]) ? $HTTP_GET_VARS["post_message"] : 0;
$f = isset( $_GET['f'] ) ? $_GET['f'] : 0;

// check permissions
$denyRead = getDenyRead( $m, $forum_id );
$denyEdit = getDenyEdit( $m, $forum_id );

if ($denyRead || ($post_message & $denyEdit)) {
	echo '<script language="javascript">
	window.location="./index.php?m=help&a=access_denied";
	</script>
';
}

$parr = array();

$sql = "
SELECT forum_id, forum_project,	forum_description, forum_owner, forum_name,
	forum_create_date, forum_last_date, forum_message_count, forum_moderated,
	user_username,
	project_name, project_color_identifier
FROM forums, users, projects 
WHERE user_id = forum_owner 
	AND forum_id = $forum_id 
	AND forum_project = project_id
";
$rc = mysql_query($sql);
$row = mysql_fetch_array($rc);
$forum_name = $row["forum_name"];
echo mysql_error();
?>


<img src="images/shim.gif" width="1" height="5" alt="" border="0"><br>
<TABLE width="95%" border=0 cellpadding="1" cellspacing=1>
<input type=hidden name=dosql value=searchfiles>
<TR>
	<TD><img src="./images/icons/communicate.gif" alt="" border="0" width=42 height=42></td>
	<TD nowrap width="100%"><span class="title">Project Forum</span></td>
<form name="forum_filter" method=GET action="./index.php">
<input type=hidden name=m value=forums>
<input type=hidden name=a value=viewer>
<input type=hidden name=forum_id value=<?php echo $forum_id;?>>
	<TD nowrap>
<?php
	echo arraySelect( $filters, 'f', 'size=1 class=text onChange="document.forum_filter.submit();"', $f );
?>
	</td>
</form>
	<TD><img src="images/shim.gif" width=5 height=5></td>
<form name="searcher" action="./index.php?m=files&a=search" method="post">
	<TD width="100%" align="right">
		<input class=button type=text name=s maxlength=30 size=20 value="Not implemented" disabled>
	</TD>
	<TD><img src="images/shim.gif" width=5 height=5></td>
	<TD><input class=button type="submit" value="search" disabled></td>
	<TD><img src="images/shim.gif" width=5 height=5></td>
</form>
</tr>
</TABLE>

<TABLE cellpadding="2" cellspacing=0 width="95%" class=std>
<tr height=20>
	<TD colspan=3 bgcolor="<?php echo $row["project_color_identifier"];?>" style="border: outset #D1D1CD 1px">
		<font size=2 color=<?php echo bestColor( $row["project_color_identifier"] );?>><b><?php echo @$row["forum_name"];?></b></font>
	</td>
</tr>
<tr>
	<TD align="left" nowrap>Related Project:</td>
	<TD nowrap><b><?php echo $row["project_name"];?></b></td>
	<TD valign="top" width="50%" rowspan=99><b>Description:</b><br><?php echo @str_replace(chr(13), "&nbsp;<BR>",$row["forum_description"]);?></TD>
</tr>
<tr>
	<TD align="left">Forum Owner:</td>
	<TD nowrap><?php echo  $row["user_username"];?> <?php if(intval($row["forum_id"])<>0){echo " (moderated) ";}?></td>
</tr>
<tr>
	<TD align="left">Created On:</td>
	<TD nowrap><?php echo fromDate(@$row["forum_create_date"]);?></td>
</tr>
</TABLE>

<?php
if($post_message){
	include("./modules/forums/post_message.php");
} else if($message_id == 0) {
	include("./modules/forums/view_topics.php");
} else {
	include("./modules/forums/view_messages.php");
}?>
