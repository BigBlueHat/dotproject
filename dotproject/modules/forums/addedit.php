<?php 
// Add / Edit forum
if(empty($forum_id))$forum_id = 0;

//Pull forum information
$csql = "Select * from forums where forums.forum_id = $forum_id";
$crc = mysql_query($csql);
$crow = mysql_fetch_array($crc);
if(empty($crow["forum_status"])){
	$status = -1;
}
else{
	$status = $crow["forum_status"];
}
if($x = mysql_error())		echo $x;
//Pull project Information
$sql = "select project_name, project_id from projects where project_active <>0 order by project_name";
$rc = mysql_query($sql);
echo mysql_error();
//Pull user Information
$sql = "select user_username, user_id from users order by user_username";
$rcu = mysql_query($sql);


if($x = mysql_error())		echo $x;
?>


<SCRIPT language="javascript">
function submitIt(){
	var form = document.changeforum;
	if(form.forum_name.value.length < 1)
	{
		alert("Please enter a valid forum name");
		form.forum_name.focus();
	}
	else if(form.forum_project.selectedIndex < 1)
	{
		alert("Please select the project to associate this forum with");
		form.forum_project.focus();
	}
	else if(form.forum_owner.selectedIndex < 1)
	{
		alert("Please select the owner of this forum");
		form.forum_owner.focus();
	}
	else
	{
		form.submit();
	}
}


function delIt(){
var form = document.changeforum;
if(confirm("Are you sure you would like to delete this forum?\n\nNote: This will also delete all posts in this forum."))
	{
	form.del.value="<?php echo $forum_id;?>";
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


<TABLE border=0 cellpadding="0" cellspacing=1 width="600">
	<TR>
	<TD valign="top">This page allows you to add or edit a discussion forum</td>
	<TD align="right"><?php if($user_cookie == $crow["forum_owner"]){?><A href="javascript:delIt()">delete forum <img align="absmiddle" src="./images/icons/trash.gif" width="16" height="16" alt="Delete this forum" border="0"></a><?php }?></td>
	</tr>
</TABLE>
<TABLE border=0 bgcolor="#f4efe3" cellpadding="3" cellspacing=0 width="600">

<form name="changeforum" action="?m=forums" method="post">
<input type="hidden" name="dosql" value="addeditdelforum">
<input type="hidden" name="del" value="0">
<input type="hidden" name="forum_unique_update" value="<?php echo uniqid("");?>">
<input type="hidden" name="forum_id" value="<?php echo $forum_id;?>">
<TR bgcolor="#878676" height="20" style="border: outset #eeeeee 2px;">
	<TD valign="top" colspan=2><b><i><?php if($forum_id == 0){echo "Add";}else{echo "Edit";}?> forum </i></b></td>
	<TD align="right" colspan=2></td>
</tr>
<TR>
	<TD rowspan=100><img src="./images/shim.gif" width=10 height=10"></td>
	<TD colspan=2></td>
	<TD rowspan=100><img src="./images/shim.gif" width=10 height=10"></td>
	</tr>
<tr>
	<TD colspan=2>
			<TABLE border=0 cellpadding=1 cellspacing=1 bgcolor="black" width="100%">
					<tr bgcolor="#f4efe3"><TD align="right" width="100">Forum Name: </td><TD><input type="text" class="text" size=25 name="forum_name" value="<?php echo @$crow["forum_name"];?>" maxlength="50" style="width:200px;"></td></tr>
					<tr bgcolor="#f4efe3"><TD align="right" width="100">Forum Project</td><TD><select name="forum_project" style="width:200px;">
					<option value=0>N/A
					<?php  while($row = mysql_fetch_array($rc)){
						echo "<option value=". $row["project_id"];
						if($row["project_id"] == $crow["forum_project"]) echo " selected ";
						echo ">" . $row["project_name"];
					}?>
					
					</select></td></tr>
			</table>
	</TD>
</TR>
	<TD valign="top">
			<TABLE border=0 cellpadding=1 cellspacing=1 bgcolor="silver">
					<tr bgcolor="#eeeeee" height=20><TD align="right">Forum Owner:</td><TD>
					<select name="forum_owner" style="width:150px;">
					<option value=0 <?php if(intval($crow["forum_owner"])==0) echo " selected ";?>>
					<?php  while($row = mysql_fetch_array($rcu)){
						echo "<option value=". $row["user_id"];
						if($row["user_id"] == $crow["forum_owner"]) echo " selected ";
						echo ">" . $row["user_username"];
					}?>
					</select>
					</td></tr>
				  <tr bgcolor="#eeeeee" height=20><TD align="right" nowrap valign="top">Forum Status:</td>
					<TD valign="top">
					<input type="radio" value="-1" <?php if($status ==-1)echo " checked";?> name="forum_status">open for posting<br>
					<input type="radio" value="1" <?php if($status ==1)echo " checked";?> name="forum_status">read-only<br>
					<input type="radio" value="0" <?php if($status ==0)echo " checked";?> name="forum_status">closed </td></tr>
					
				  <tr bgcolor="#eeeeee" height=20><TD align="right" nowrap>Forum Moderated:</td><TD><input type="checkbox" name="forum_moderated" value=1 <?php if(intval($crow["forum_moderated"])<>0)echo " checked "?>></td></tr>
					
					<tr bgcolor="#eeeeee" height=20><TD align="right">Created On</td><TD><?php echo @$crow["forum_create_date"];?></td></tr>
					<tr bgcolor="#eeeeee" height=20><TD align="right">Last Post:</td><TD><?php echo @$crow["forum_last_date"];?></td></tr>
					<tr bgcolor="#eeeeee" height=20><TD align="right" nowrap>Message Count:</td><TD><?php echo @$crow["forum_message_count"];?></td></tr>
			</table>
		
	</td>
	<TD valign="top">
		<TABLE border=0 cellpadding=1 cellspacing=1 bgcolor="silver">
			<tr bgcolor="#eeeeee" height=170>
				<TD><b>Description</b><br>
				<textarea class="textarea" name="forum_description" style="height:150px;"><?php echo @$crow["forum_description"];?></textarea></TD>
			</TR>
		</TABLE>
	</td>
</tr>

<TR>
<TD><input type="button" value="back" class=button onClick="javascript:window.location='./index.php?m=forums';"></td>
<TD align="right"><?php if($user_cookie == $crow["forum_owner"] || $forum_id ==0){?><input type="button" value="submit" class=button onClick="submitIt()"><?php }?></td></tr>
</form>
</TABLE>


