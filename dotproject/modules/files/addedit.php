<?php
##
## Files modules: add new/edit existing file
##

$file_id = isset($_GET['file_id']) ? $_GET['file_id'] : 0;
 
// check permissions
$denyEdit = getDenyEdit( $m, $file_id );

if ($denyEdit) {
	$AppUI->redirect( "m=help&a=access_denied" );
}

$file_task = isset($_GET['file_task']) ? $_GET['file_task'] : 0;
$file_parent = isset($_GET['file_parent']) ? $_GET['file_parent'] : 0;

$sql = "
SELECT files.*, user_username
FROM files
LEFT JOIN users ON file_owner = user_id
WHERE file_id = $file_id
";

db_loadHash( $sql, $file );

$file_task = $file["file_task"];

$sql = "SELECT project_id, project_name  FROM projects ORDER BY project_name";
$projects = arrayMerge( array( '0'=>'- ALL PROJECTS -'), db_loadHashList( $sql ) );

$crumbs = array();
$crumbs["?m=files"] = "files list";
?>

<script language="javascript">
function submitIt() {
	var f = document.uploadfile;
	f.submit();
}
function delIt() {
	if (confirm( "<?php echo $AppUI->_('filesDelete');?>" )) {
		var f = document.uploadfile;
		f.del.value='1';
		f.submit();
	}
}
</script>

<table width="98%" border="0" cellpadding="0" cellspacing="1">
<tr>
	<td><img src="./images/icons/folder.gif" alt="" border="0" width=42 height=42></td>
	<td nowrap><h1>*</h1></td>
	<td align="right" width="100%"></td>
	<td nowrap="nowrap" width="20" align="right"><?php echo contextHelp( '<img src="./images/obj/help.gif" width="14" height="16" border="0" alt="'.$AppUI->_( 'Help', 'ID_HELP_FILE_EDIT' ).'">' );?></td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%">
	<tr>
		<td width="50%" nowrap><?php echo breadCrumbs( $crumbs );?></td>
		<td width="50%" align="right">
	<?php if ($file_id) { ?>
			<a href="javascript:delIt()">
				<img align="absmiddle" src="./images/icons/trash.gif" width="16" height="16" alt="Delete this file" border="0"><?php echo $AppUI->_( 'delete file' );?>
			</a>
	<?php } ?>
		</td>
	</tr>
</table>

<table width="98%" border="0" cellpadding="3" cellspacing="3" class="std">

<form name="uploadfile" action="?m=files" enctype="multipart/form-data" method="post">
<input type="hidden" name="max_file_size" value="109605000">
<input type="hidden" name="dosql" value="file_aed">
<input type="hidden" name="del" value="0">
<input type="hidden" name="file_id" value="<?php echo intval( $file_id );?>">
<input type="hidden" name="file_task" value="<?php echo intval( $file_task );?>">

<tr>
	<td width="100%" valign="top" align="center">
		<table cellspacing="1" cellpadding="2" width="60%">
	<?php if ($file_id) { ?>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'File Name' );?>:</td>
			<td class="hilite"><?php if(strlen($file["file_name"])== 0){echo "n/a";}else{ echo $file["file_name"];}?></td>
			<td><a href="./fileviewer.php?file_id=<?php echo $file["file_id"];?>"><?php echo $AppUI->_( 'download' );?></a></td>
		</tr>
		<tr valign=top>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Type' );?>:</td>
			<td class="hilite"><?php echo $file["file_type"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Size' );?>:</td>
			<td class="hilite"><?php echo $file["file_size"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Uploaded By' );?>:</td>
			<td class="hilite"><?php echo $file["user_username"];?></td>
		</tr>
	<?php } ?>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Version' );?>:</td>
			<td>
				<input type="text" name="file_version" value="<?php echo strlen( $file["file_version"] ) > 0 ? $file["file_version"] : "1";?>" maxlength="10" size="5">
			</td>
		</tr>

		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Project' );?>:</td>
			<td>
			<?php
				echo arraySelect( $projects, 'file_project', 'size="1" class="text" style="width:270px"',
					$file['file_project'] ? $file['file_project'] : -1  );
			?>
			</td>
		</tr>

		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Description' );?>:</td>
			<td>
				<textarea name="file_description" class="textarea" rows="4" style="width:270px"><?php echo $file["file_description"];?></textarea>
			</td>
		</tr>

		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Upload File' );?>:</td>
			<td><input type="File" class="button" name="formfile" style="width:270px"></td>
		</tr>

		</table>
	</td>
</tr>
<tr>
	<td align="right">
		<input type="button" class="button" value="<?php echo $AppUI->_( 'submit' );?>" onclick="submitIt()">
	</td>
</tr>
</form>
</table>
