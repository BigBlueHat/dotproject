<?php
##
## Files modules: add new/edit existing file
##

$file_id = isset($HTTP_GET_VARS['file_id']) ? $HTTP_GET_VARS['file_id'] : 0;
$project_id = isset($HTTP_GET_VARS['project_id']) ? $HTTP_GET_VARS['project_id'] : 0;
 
// check permissions
$denyEdit = getDenyEdit( $m, $file_id );

if ($denyEdit) {
	echo '<script language="javascript">
	window.location="./index.php?m=help&a=access_denied";
	</script>
';
}

if (empty( $file_task )) {
	$file_task = 0;
}
if (empty( $file_parent )) {
	$file_parent =0;
}

$fsql = "
SELECT files.*, user_username
FROM files
LEFT JOIN users ON file_owner = user_id
WHERE file_id = $file_id
";

$frc = mysql_query( $fsql );
echo mysql_error();
$frow = mysql_fetch_array( $frc, MYSQL_ASSOC );
if ($frow) {
	$file_task = $frow["file_task"];
}

$projects = array( '0'=>'- ALL PROJECTS -');
$psql = "SELECT project_id, project_name  FROM projects ORDER BY project_name";
$prc = mysql_query( $psql );
echo mysql_error();
while($row = mysql_fetch_row( $prc )){
	$projects[$row[0]] = $row[1];
}
?>

<SCRIPT LANGUAGE="javascript">
function uploadFile() {
	var form=document.uploadfile;
	form.submit();
}
function delIt() {
	if (confirm( "Are you sure you would like to delete this file?" )) {
		var f = document.uploadfile;
		f.dosql.value='delfile';
		f.submit();
	}
}
</SCRIPT>

<TABLE width="95%" border=0 cellpadding="0" cellspacing="1">
<TR>
	<TD><img src="./images/icons/folder.gif" alt="" border="0" width=42 height=42></td>
	<TD nowrap><span class="title">File Management</span></td>
	<TD align="right" width="100%"></td>
</tr>
</TABLE>

<table border="0" cellpadding="4" cellspacing="0" width="95%">
	<TR>
		<TD width="50%" nowrap><a href="./index.php?m=files">Files List</a></td>
		<TD width="50%" align="right">
			<A href="javascript:delIt()"><img align="absmiddle" src="./images/icons/trash.gif" width="16" height="16" alt="Delete this file" border="0">delete file</a>
		</td>
	</TR>
</table>

<TABLE width="95%" border=0 cellpadding="3" cellspacing="3" class="std">

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
			<?php
				echo arraySelect( $projects, 'file_project', 'size="1" class="text" style="width:270px"',
					$frow['file_project'] ? $frow['file_project'] : -1  );
			?>
			</TD>
		</TR>
		<TR>
			<TD valign=top align=right>File Description:</TD>
			<TD>
				<textarea name="file_description" rows=4 style="width:270px"><?php echo $frow["file_description"];?></textarea>
			</TD>
		</TR>
		<TR>
			<TD  valign=top align=right>upload a file:</TD><TD><input type="File" name="formfile" style="width:270px"></TD>
		</TR>
		</TABLE>
	</TD>

	<TD valign="top" align="right" nowrap>
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
<TR>
	<TD colspan=2 align="center">
		<input type="button" value="<?php if($file_id ==0){?>Add file<?php }else{?>Update file<?php }?>" onclick="uploadFile()">
	</TD>
</TR>
</form>
</TABLE>
