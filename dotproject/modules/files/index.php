<?php 
//Projects

//Set up defaults
if(!isset($project_id)){$project_id=0;}

$fsql = "select 
file_name, 
file_project, 
file_task, 
file_owner,
file_id, 
file_type, 
file_parent, 
file_date, 
file_size ,
project_name, 
project_color_identifier, 
project_active
from files left join projects on file_project = project_id 
order by project_id , file_name

";


$frc =mysql_query($fsql);
echo mysql_error();

$usql = "Select user_first_name, user_last_name from users where user_id = $user_cookie";
$urc = mysql_query($usql);
$urow = mysql_fetch_array($urc);
?>



<img src="images/shim.gif" width="1" height="5" alt="" border="0"><br>
<TABLE width="95%" border=0 cellpadding="0" cellspacing=1>
<form name="searcher" action="./index.php?m=files&a=search" method="post">
<input type=hidden name=dosql value=searchfiles>
	<TR>
	<TD><img src="./images/icons/folder.gif" alt="" border="0" width=42 height=42></td>
		<TD nowrap><span class="title">File Management</span></td>
		<TD width="100%" align="right"><input class=button type=text name=s maxlength=30 size=20></TD>
		<TD><img src="./images/shim.gif" width=5 height=5></td>
		<TD><input class=button type="submit" value="search"></td>
		<TD><img src="./images/shim.gif" width=5 height=5></td>
		<TD align="right"><input type="button" class=button value="add new file" onClick="javascript:window.location='./index.php?m=files&a=addedit';"></td>
	</tr></form>
</TABLE>

<TABLE width="95%" border=0 cellpadding="2" cellspacing=1>
	<TR style="border: outset #eeeeee 2px;">
		<TD nowrap class="mboxhdr"></td>
		<TD nowrap class="mboxhdr"><A href="#"><font color="white">File Name</font></a></td>
		<TD nowrap class="mboxhdr"><A href="#"><font color="white">File Owner</font></a></td>
		<TD nowrap class="mboxhdr"><A href="#"><font color="white">File Date</font></a></td>
		<TD nowrap class="mboxhdr"><A href="#"><font color="white">File Type</font></a></td>
		<TD nowrap class="mboxhdr"><A href="#"><font color="white">File Size:</font></a></td>
	</tr>
	<?php  
	$fp=0;
	while($row = mysql_fetch_array($frc)){
		if($fp != $row["file_project"]){?>
		<TR bgcolor="#f4efe3"><TD colspan="6" bgcolor="#<?php echo $row["project_color_identifier"];?>" style="border: outset 2px #eeeeee">
			<?php 
					$r = hexdec(substr($row["project_color_identifier"], 0, 2)); 
					$g = hexdec(substr($row["project_color_identifier"], 2, 2)); 
					$b = hexdec(substr($row["project_color_identifier"], 4, 2)); 
					
					if($r < 128 && $g < 128 || $r < 128 && $b < 128 || $b < 128 && $g < 128) 
					{
					echo "<font color='white'>";
					};
					?><?php echo $row["project_name"];?></TD></TR>
		<?php }
		$fp = $row["file_project"];
		?>
				<TR bgcolor="#f4efe3">
						<TD nowrap><A href="./index.php?m=files&a=addedit&file_id=<?php echo $row["file_id"];?>"><img src="./images/icons/pencil.gif" alt="edit file" border="0" width=12 height=12></a></td>				
					<TD nowrap><A href="./fileviewer.php?file_id=<?php echo $row["file_id"];?>"><?php echo $row["file_name"];?></a></td>
					<TD nowrap><?php echo $row["file_owner"];?></td>
					<TD nowrap><?php echo $row["file_date"];?></td>
					<TD nowrap><?php echo $row["file_type"];?></td>
					<TD nowrap><?php echo intval($row["file_size"] / 1024);?>k</td>
				</tr>
	<?php }?>
</Table>
