<?php
//view posts
if(empty($forum_id))$forum_id=0;
if(empty($message_id))$message_id=0;
if(empty($post_message))$post_message=0;
$parr = array();

$sql = "select 
forum_id,
forum_project,
project_name,
forum_description,forum_owner,user_username,forum_name,forum_create_date,forum_last_date,forum_message_count,forum_moderated 
from forums, users, projects where user_id = forum_owner and forum_id = $forum_id and forum_project = project_id";
$rc= mysql_query($sql);
$row = mysql_fetch_array($rc);
$forum_name = $row["forum_name"];
echo mysql_error();
?>


<img src="images/shim.gif" width="1" height="5" alt="" border="0"><br>
<TABLE width="95%" border=0 cellpadding="1" cellspacing=1>
<form name="searcher" action="./index.php?m=files&a=search" method="post">
<input type=hidden name=dosql value=searchfiles>
	<TR>
	<TD><img src="./images/icons/communicate.gif" alt="" border="0" width=42 height=42></td>
		<TD nowrap><span class="title">User Forums</span></td>
		<TD width="100%" align="right"><input class=button type=text name=s maxlength=30 size=20></TD>
		<TD><img src="images/shim.gif" width=5 height=5></td>
		<TD><input class=button type="submit" value="search"></td>
		<TD><img src="images/shim.gif" width=5 height=5></td>
	</tr></form>
</TABLE>

<TABLE cellpadding="2" cellspacing=0 width="95%" bgcolor="#eeeeee">
<tr bgcolor="#eeeeee" height=20 colspan=2>
	<TD class="mboxhdr" colspan=2> <font size=2><b><?php echo @$row["forum_name"];?></b></font></td>
</tr>
<TR>
	<TD valign="top">
		<TABLE border=0 cellpadding=1 cellspacing=1 width="100%" bgcolor="silver">
			<tr bgcolor="#eeeeee" height=20 width="100">
				<TD align="left" nowrap>Related Project:</td>
				<TD nowrap><b><?php echo $row["project_name"];?></b></td>
			</tr>
			<tr bgcolor="#eeeeee" height=20 width="100">
				<TD align="left">Forum Owner:</td>
				<TD nowrap><?php echo  $row["user_username"];?> <?php if(intval($row["forum_id"])<>0){echo " (moderated) ";}?></td>
			</tr>
			<tr bgcolor="#eeeeee" height=20>
				<TD align="left">Created On:</td>
				<TD nowrap><?php echo fromDate(@$row["forum_create_date"]);?></td>
			</tr>
			<tr bgcolor="#eeeeee" height=20>
				<TD align="left">Last Post:</td>
				<TD nowrap><?php echo fromDate(@$row["forum_last_date"]);?></td>
			</tr>
			<tr bgcolor="#eeeeee" height=20>
				<TD align="left" nowrap>Message Count:</td>
				<TD nowrap><?php echo @$row["forum_message_count"];?></td>
			</tr>
		</table>
	</TD>
	<TD valign="top" bgcolor="#eeeeee" width="100%"><b>Description:</b><br><?php echo @str_replace(chr(13), "&nbsp;<BR>",$row["forum_description"]);?></TD>
</TR>
</TABLE>
<?php if($post_message){
	include("./modules/forums/post_message.php");
}
else if($message_id ==0){
	include("./modules/forums/view_topics.php");
}
else{
	include("./modules/forums/view_messages.php");
}?>
