<?

//Forum index.php
$sql = "select forum_id,forum_project,forum_description,forum_owner,user_username,forum_name,forum_create_date,forum_last_date,forum_message_count,forum_moderated, project_name, project_color_identifier, project_id
from forums, users, projects 
where user_id = forum_owner and ";
if(isset($project_id))$sql.= "forum_project = $project_id and ";
$sql.= " project_id = forum_project order by forum_project, forum_name";
$rc= mysql_query($sql);



?>


<img src="images/shim.gif" width="1" height="5" alt="" border="0"><br>
<TABLE width="95%" border=0 cellpadding="0" cellspacing=1>
<form name="searcher" action="./index.php?m=files&a=search" method="post">
<input type=hidden name=dosql value=searchfiles>
	<TR>
	<TD><img src="./images/icons/communicate.gif" alt="" border="0" width=42 height=42></td>
		<TD nowrap><span class="title">User Forums</span></td>
		<TD width="100%" align="right"><input class=button type=text name=s maxlength=30 size=20></TD>
		<TD><img src="images/shim.gif" width=5 height=5></td>
		<TD><input class=button type="submit" value="search"></td>
		<TD><img src="images/shim.gif" width=5 height=5></td>
		<TD align="right"><input type="button" class=button value="add new forum" onClick="javascript:window.location='./index.php?m=forums&a=addedit';"></td>
	</tr></form>
</TABLE>
<table width="95%"  cellpadding="1" cellspacing=1><tr><td bgcolor="black">
<TABLE width="100%" border=0 cellpadding="2" cellspacing=1 bgcolor="white" height=400>
	<TR style="border: outset #eeeeee 2px;">
		<TD nowrap class="mboxhdr"></td>
		<TD nowrap class="mboxhdr"><A href="#"><font color="white">Forum Name</font></a></td>
		<TD nowrap class="mboxhdr"><A href="#"><font color="white">Forum Owner</font></a></td>
		<TD nowrap class="mboxhdr"><A href="#"><font color="white">Messages</font></a></td>
		<TD nowrap class="mboxhdr"><A href="#"><font color="white">Created</font></a></td>
		<TD nowrap class="mboxhdr"><A href="#"><font color="white">Last Post</font></a></td>
	</tr>
<?
$p ="";
while($row = mysql_fetch_array($rc)){
	if($p != $row["project_id"]){
		
			$r = hexdec(substr($row["project_color_identifier"], 0, 2)); 
			$g = hexdec(substr($row["project_color_identifier"], 2, 2)); 
			$b = hexdec(substr($row["project_color_identifier"], 4, 2)); 
			
			if($r < 128 && $g < 128 || $r < 128 && $b < 128 || $b < 128 && $g < 128){
				$font = "<span style='color:#ffffff;text-decoration:none;' >";
			}
			else{
				$font = "<span style='color:#000000;text-decoration:none;' >";
			}
		?>
		<TR>
			<TD colspan=6 bgcolor="<?echo $row["project_color_identifier"];?>"><A href="./index.php?m=projects&a=view&project_id=<?echo $row["project_id"];?>"><?echo $font;?><B><?echo $row["project_name"];?></b></span></a></td>
		</tr>
		<?
		$p = $row["project_id"];
	}?>
	<TR bgcolor="#f4efe3">
		<TD nowrap align=center>
		<?if($row["forum_owner"] == $user_cookie){?>
		
		<A href="./index.php?m=forums&a=addedit&forum_id=<?echo $row["forum_id"];?>"><img src="./images/icons/pencil.gif" alt="expand forum" border="0" width=12 height=12></a>
		<?}?>
		</td>				
		<TD nowrap width="330"><span style="font-size:10pt;font-weight:bold"><A href="./index.php?m=forums&a=viewer&forum_id=<?echo $row["forum_id"];?>"><?echo $row["forum_name"];?></a></span></td>
		<TD nowrap><?echo $row["user_username"];?></td>
		<TD nowrap><?echo $row["forum_message_count"];?></td>
		<TD nowrap><?echo substr($row["forum_create_date"], 0, 10);?></td>
		<TD nowrap><?if(intval($row["forum_last_date"])>0 )
			{
			echo $row["forum_last_date"];
			}
			else
			{
			echo "n/a";
			}
			?></td>
	</tr>
	<TR>
		<TD></td>				
		<TD><?echo $row["forum_description"];?></td>
		<TD colspan=4></td>
	</tr>
	
<?}?>
<TR><TD colspan=6 height="100%"> &nbsp;</td></tr>
</TABLE></td></tr></table>