<?php /* FILES $Id$ */
$file_id = isset($_GET['file_id']) ? $_GET['file_id'] : 0;
 
// check permissions for this file
$canEdit = !getDenyEdit( $m, $file_id );
if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

$file_task = dPgetParam( $_GET, 'file_task', 0 );
$file_parent = dPgetParam( $_GET, 'file_parent', 0 );
$file_project = dPgetParam( $_GET, 'project_id', 0 );

$sql = "
SELECT files.*,
user_username,
project_id,
task_id, task_name
FROM files
LEFT JOIN users ON file_owner = user_id
LEFT JOIN projects ON project_id = file_project
LEFT JOIN tasks ON task_id = file_task
WHERE file_id = $file_id
";

if (!db_loadHash( $sql, $file ) && $file_id > 0) {
	$titleBlock = new CTitleBlock( 'Invalid File ID', 'folder.gif', $m, 'ID_HELP_FILE_EDIT' );
	$titleBlock->addCrumb( "?m=files", "files list" );
	$titleBlock->show();
} else {
// setup the title block
	$ttl = $file_id > 0 ? "Edit File" : "Add File";
	$titleBlock = new CTitleBlock( $ttl, 'folder.gif', $m, 'ID_HELP_COMP_EDIT' );
	$titleBlock->addCrumb( "?m=files", "files list" );
	if ($canDelete) {
		$titleBlock->addCrumbRight(
			'<a href="javascript:delIt()">'
				. '<img align="absmiddle" src="' . dPfindImage( 'trash.gif', $m ) . '" width="16" height="16" alt="" border="0" />&nbsp;'
				. $AppUI->_('delete file') . '</a>'
		);
	}
	$titleBlock->show();

	if (isset($file["file_project"])) {
		$file_project = $file["file_project"];
	}
	if (isset($file["file_task"])) {
		$file_task = $file["file_task"];
		$task_name = @$file["task_name"];
	} else if ($file_task) {
		$sql = "SELECT task_name FROM tasks WHERE task_id=$file_task";
		$task_name = db_loadResult( $sql );
	} else {
		$task_name = '';
	}

	$sql = "SELECT project_id, project_name  FROM projects ORDER BY project_name";
	$projects = arrayMerge( array( '0'=>'- ALL PROJECTS -'), db_loadHashList( $sql ) );
?>
<script language="javascript">
function submitIt() {
	var f = document.uploadFrm;
	f.submit();
}
function delIt() {
	if (confirm( "<?php echo $AppUI->_('filesDelete');?>" )) {
		var f = document.uploadFrm;
		f.del.value='1';
		f.submit();
	}
}
function popTask() {
    var f = document.uploadFrm;
    if (f.file_project.selectedIndex == 0) {
        alert( 'Please select a project first!' );
    } else {
        window.open('./index.php?m=public&a=selector&dialog=1&callback=setTask&table=tasks&task_project='
            + f.file_project.options[f.file_project.selectedIndex].value, 'task','left=50,top=50,height=250,width=400,resizable')
    }
}

// Callback function for the generic selector
function setTask( key, val ) {
    var f = document.uploadFrm;
    if (val != '') {
        f.file_task.value = key;
        f.task_name.value = val;
    } else {
        f.fiel_task.value = '0';
        f.task_name.value = '';
    }
}
</script>

<table width="100%" border="0" cellpadding="3" cellspacing="3" class="std">

<form name="uploadFrm" action="?m=files" enctype="multipart/form-data" method="post">
	<input type="hidden" name="max_file_size" value="109605000" />
	<input type="hidden" name="dosql" value="file_aed" />
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="file_id" value="<?php echo $file_id;?>" />
	<input type="hidden" name="file_task" value="<?php echo $file_task;?>" />

<tr>
	<td width="100%" valign="top" align="center">
		<table cellspacing="1" cellpadding="2" width="60%">
	<?php if ($file_id) { ?>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'File Name' );?>:</td>
			<td align="left" class="hilite"><?php if(strlen($file["file_name"])== 0){echo "n/a";}else{ echo $file["file_name"];}?></td>
			<td>
				<a href="./fileviewer.php?file_id=<?php echo $file["file_id"];?>"><?php echo $AppUI->_( 'download' );?></a>
			</td>
		</tr>
		<tr valign=top>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Type' );?>:</td>
			<td align="left" class="hilite"><?php echo $file["file_type"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Size' );?>:</td>
			<td align="left" class="hilite"><?php echo $file["file_size"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Uploaded By' );?>:</td>
			<td align="left" class="hilite"><?php echo $file["user_username"];?></td>
		</tr>
	<?php } ?>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Version' );?>:</td>
			<td align="left">
				<input type="text" name="file_version" value="<?php echo strlen( $file["file_version"] ) > 0 ? $file["file_version"] : "1";?>" maxlength="10" size="5" />
			</td>
		</tr>

		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Project' );?>:</td>
			<td align="left">
			<?php
				echo arraySelect( $projects, 'file_project', 'size="1" class="text" style="width:270px"',
					$file_project  );
			?>
			</td>
		</tr>

		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Task' );?>:</td>
			<td align="left" colspan="2" valign="top">
				<input type="hidden" name="file_task" value="<?php echo $file_task;?>" />
				<input type="text" class="text" name="task_name" value="<?php echo $task_name;?>" size="40" disabled />
				<input type="button" class="button" value="select task..." onclick="popTask()" />
			</td>
		</tr>

		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Description' );?>:</td>
			<td align="left">
				<textarea name="file_description" class="textarea" rows="4" style="width:270px"><?php echo $file["file_description"];?></textarea>
			</td>
		</tr>

		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Upload File' );?>:</td>
			<td align="left"><input type="File" class="button" name="formfile" style="width:270px"></td>
		</tr>

		</table>
	</td>
</tr>
<tr>
	<td align="right">
		<input type="button" class="button" value="<?php echo $AppUI->_( 'submit' );?>" onclick="submitIt()" />
	</td>
</tr>
</form>
</table>
<?php } ?>