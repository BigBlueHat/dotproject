<?php
// Add / Edit forum
if(empty($message_id))$message_id = 0;
if(empty($message_parent))$message_parent = -1;
//Pull forum information
$csql = "Select 
forum_name, 
forum_owner,
forum_moderated, 
project_name, 
project_id 
from projects,forums 
where forums.forum_id = $forum_id and
forums.forum_project = projects.project_id";
$crc = mysql_query($csql);
$crow = mysql_fetch_array($crc);
if($x = mysql_error())		echo $x;

//Pull user Information
$sql = "select user_username, user_id from users where user_id = $user_cookie";
$rcu = mysql_query($sql);
$username = mysql_result($rcu, 0, "user_username");

//pull message information
$sql = "select message_id, message_parent, message_author, 
message_title, message_date, message_body, message_published,  user_username, user_id 
from users , forum_messages   
where 
message_id = $message_id and 
user_id = message_author or message_id = $message_parent";
$mrc = mysql_query($sql);
$mrow = mysql_fetch_array($mrc);

if(isset($message_parent)){
	$mp = $message_parent;
}
else{
	$mp = intval($mrow["message_parent"]);
}
?>


<SCRIPT language="javascript">
function submitIt(){
	var form = document.changeforum;
	if(form.message_title.value.length < 1)
	{
		alert("Please enter a valid message subject");
		form.message_title.focus();
	}
	else if(form.message_body.value.length < 1)
	{
		alert("Please type a message before posting");
		form.message_body.focus();
	}
	else
	{
		form.submit();
	}
}


function delIt(){
var form = document.changeforum;
if(confirm("Are you sure you would like\nto delete this post?"))
	{
	form.del.value="<?php echo $message_id;?>";
	form.submit();
	}
}


function orderByName(x){
	var form = document.changeforum;
	if(x == "name"){
		form.forum_order_by.value = form.forum_last_name.value + ", " + form.forum_name.value;
	}
	else{
		form.forum_order_by.value = form.forum_project.value;
	}

}


</script>
	<TABLE border=0 cellpadding=2 cellspacing=1 width="95%" >
		<TR>
		<TD><A href="./index.php?m=forums">All forums</a>::<A href="./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id;?>"><?php echo $forum_name;?></a></td>
			<TD align="right"></TD></TR>
		</TABLE>
<TABLE border=0 bgcolor="#f4efe3" cellpadding="3" cellspacing=0 width="95%">

<form name="changeforum" action="?m=forums&a=viewposts&forum_id=<?php echo $forum_id;?>" method="post">
<input type="hidden" name="dosql" value="aed_post">
<input type="hidden" name="del" value="0">
<input type="hidden" name="message_forum" value="<?php echo $forum_id;?>">
<input type="hidden" name="message_parent" value="<?php echo $mp;?>">
<?php if($crow["forum_moderated"]){?>
<input type="hidden" name="message_published" value="1">
<?php }else{?>
<input type="hidden" name="message_published" value="0">
<?php }?>
<input type="hidden" name="message_author" value="<?php if(intval($mrow["message_author"]) ==0){echo $user_cookie;}else{echo $mrow["message_author"];}?>">
<input type="hidden" name="message_id" value="<?php echo $message_id;?>">
<TR bgcolor="silver">
	<TD valign="top" colspan=2><b><i><?php if($message_id == 0){echo "Add";}else{echo "Edit";}?> Topic </i></b></td>
	<TD align="right" colspan=2></td>
</tr>
<TR>
	<TD rowspan=100><img src="./images/shim.gif" width=10 height=10"></td>
	<TD colspan=2></td>
	<TD rowspan=100 width="100%"><img src="./images/shim.gif" width=10 height=10"></td>
	</tr>
<tr>
	<TD colspan=2>
			<TABLE border=0 cellpadding=1 cellspacing=1 bgcolor="black" width="100%">
					<tr bgcolor="#f4efe3">
						<TD align="right" width="100" bgcolor=silver>Forum Name: </td>
						<TD bgcolor="#eeeeee">&nbsp; <b><?php echo @$crow["forum_name"];?></b></td>
					</tr>
					<tr bgcolor="#f4efe3">
						<TD align="right" width="100" bgcolor=silver>Forum Project: </td>
						<TD bgcolor="#eeeeee">&nbsp; <?php echo $crow["project_name"];?></td>
					</tr>
			</table>
	</TD>
</TR>
<TR>
	<TD valign="top" colspan=2>
			<TABLE border=0 cellpadding=1 cellspacing=1 bgcolor="silver" width="100%">
				  <tr bgcolor="#eeeeee" height=20>
						<TD align="right">Subject:</td>
						<TD>
						<?php if($message_parent > 0){?>
							<input type="hidden" name="message_title" value="<?php echo $mrow["message_title"];?>">
							<B>Re: <?php echo $mrow["message_title"];?> </B>
						<?php }else{?>
						<input type="text" name="message_title" value="" size=50 maxlength=250>
						<?php }?>
						</td>
					</tr>
					<tr bgcolor="#eeeeee" height=20>
						<TD align="right">Message Author:</td>
						<TD><?php echo @$username;?></td>
					</tr>
					<tr bgcolor="#eeeeee" height=20>
						<TD align="right">Posting Date:</td>
						<TD><?php if(intval($mrow["message_date"])>0)	{
							echo $mrow["message_date"];
							}
							else{
							echo date("Y-m-d h:i:s",time());
							}?></td>
					</tr>
					<tr bgcolor="#eeeeee">
						<TD colspan=2 align="center"><b>Message</b></TD>
					</TR>
					<TR bgcolor="#eeeeee">
						<TD colspan=2 align="center"><textarea class="textarea" name="message_body" style="height:200px;width:400;"><?php echo @$crow["forum_description"];?></textarea></TD>
					</TR>
					<tr bgcolor="#eeeeee" height=20>
						<TD><input type="button" value="back" class=button onClick="javascript:window.location='./index.php?m=forums';"></td>
						<TD align="right"><?php if($user_cookie == $crow["forum_owner"] || $message_id ==0){?><input type="button" value="submit" class=button onClick="submitIt()"><?php }?></td>
					</tr>
			</TABLE>
	</td>
</tr>
</form>
</TABLE>


