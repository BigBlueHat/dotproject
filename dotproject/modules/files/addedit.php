<?php 
if(empty($file_id))$file_id =0;
if(empty($file_task))$file_task =0;
if(isset($task_id))$file_task=$task_id;
if(empty($file_parent))$file_parent =0;
$fsql ="Select 
file_name, 
user_username, 
file_date, 
file_size, 
file_description, 
file_type, 
file_owner, 
file_parent, 
file_project, 
file_task, 
file_version, 
file_id 
from files left join users on file_owner = user_id 
where file_id = $file_id";
$frc = mysql_query($fsql);
echo mysql_error();
$frow = mysql_fetch_array($frc);
if($frow) $file_task = $frow["file_task"];
$psql = "select project_name, project_id from projects order by project_name";
$prc =mysql_query($psql);

$f2sql = "select file_project, file_id, file_name from files order by file_project";


?>
<SCRIPT LANGUAGE="javascript">

function uploadFile(){
	var form=document.uploadfile;
	form.submit();
	
	
}



</SCRIPT>


<TABLE width="95%" border=0 cellpadding="0" cellspacing=1>
	<TR>
	<TD><img src="./images/icons/folder.gif" alt="" border="0" width=42 height=42></td>
		<TD nowrap><span class="title">File Management</span></td>
		<TD align="right" width="100%"></td>
	</tr>
</TABLE>

<TABLE width="95%" border=0 cellpadding="3" cellspacing=3  bgcolor="#f4efe3">
<form name="uploadfile" action="./index.php?m=files" enctype="multipart/form-data" method="post">
	<INPUT TYPE="hidden" name="MAX_FILE_SIZE" value="109605000">
	<input type="hidden" name="file_task" value="<?php echo intval($file_task);?>">
	<input type="hidden" name="dosql" value="addfile">
	<input type="hidden" name="file_id" value="<?php echo intval($frow["file_id"]);?>">
	
	<TR style="border: outset #eeeeee 2px;">
		<TD colspan=2 class="mboxhdr"><?php if($file_id ==0){?>Adding a new file<?php }else{?>Updating a file<?php }?></TD>
	</TR>
	<TR>
		<TD width="50%" valign="top">
			<TABLE border=0>
			  <TR>
			    <TD valign=top align=right>File Name:</TD>
			    <TD><?php if(strlen($frow["file_name"])== 0){echo "n/a";}else{ echo $frow["file_name"];}?></TD>
				</TR>
			  <TR>
			   <TD valign=top align=right>Project:</TD>
			    <TD>
					<select name="file_project" style="width:270px">
					<?php while($row = mysql_fetch_row($prc)){ 
						if($frow["file_project"] ==  $row[1]){
							echo "<option selected value=" . $row[1] . ">". $row[0]  ;	
						}
						else{
							echo "<option value=" . $row[1] . ">". $row[0]  ;
						}
					}?>
					</select>
					
					</TD>
				</TR>
				  <TR>
			    <TD valign=top align=right>File Description:</TD>
			    <TD><textarea name="file_description" rows=4 style="width:270px"><?php echo $frow["file_description"];?></textarea>
					</TD>
				</TR>
				<TR>
					<TD  valign=top align=right>upload a file:</TD><TD><input type="File" name="formfile" style="width:270px"></TD>
				</TR>
			</TABLE>
		</TD>

		<TD valign="top" align="right" nowrap>delete this file:	<a href="javascript: 
		if(confirm('Are you sure you would like to delete this file?'))
			{
			document.uploadfile.dosql.value='delfile';
			document.uploadfile.submit();
			}"><img src="./images/trash_small.gif" alt="Trash this file" border="0" width="22" height="22"></A><br>
		
			<table width="100%" border=0>
				<tr>
					<td valign=top align=right>Uploaded by:</td>
					<td width="50%"><?php echo $frow["user_username"];?></td>
				</tr>
				<tr valign=top>
					<td align=right>File Type:</td>
					<td><?php echo $frow["file_type"];?></td>
				</tr>
				<tr>
					<td valign=top align=right>File Size:</td>
					<td><?php echo $frow["file_size"];?></td>
				</tr>
				<tr>
					<td valign=top align=right>File Version:</td>
					<td><input type="text" name="file_version" value="<?php if(strlen($frow["file_version"])>0){ echo $frow["file_version"]; }else{ echo "1";}?>" maxlength=10 size=5></td>
				</tr>
				<tr>
					<td><BR></td>
					<td></td>
				</tr>
				<tr>
					<td>Click here to download</td>
					<td><A href="./fileviewer.php?file_id=<?php echo $frow["file_id"];?>"><?php echo $frow["file_name"];?></a></td>
				</tr>
			</table>
		</TD>
	</TR>
	<TR><TD colspan=2 align="center"><input type="button" value="<?php if($file_id ==0){?>Add file<?php }else{?>Update file<?php }?>" onclick="uploadFile()"></TD></TR>
</form>
</TABLE>
