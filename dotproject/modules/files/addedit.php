<?php /* FILES $Id$ */
$file_id = intval( dPgetParam( $_GET, 'file_id', 0 ) );
 
// check permissions for this record
$canEdit = !getDenyEdit( $m, $file_id );
if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

// load the companies class to retrieved denied companies
require_once( $AppUI->getModuleClass( 'projects' ) );

$file_task = intval( dPgetParam( $_GET, 'file_task', 0 ) );
$file_parent = intval( dPgetParam( $_GET, 'file_parent', 0 ) );
$file_project = intval( dPgetParam( $_GET, 'project_id', 0 ) );

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

// check if this record has dependancies to prevent deletion
$msg = '';
$obj = new CFile();
$canDelete = $obj->canDelete( $msg, $file_id );

// load the record data
$obj = null;
if (!db_loadObject( $sql, $obj ) && $file_id > 0) {
	$AppUI->setMsg( 'File' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

// setup the title block
$ttl = $file_id ? "Edit File" : "Add File";
$titleBlock = new CTitleBlock( $ttl, 'folder5.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=files", "files list" );
if ($canEdit && $file_id > 0) {
	$titleBlock->addCrumbDelete( 'delete file', $canDelete, $msg );
}
$titleBlock->show();

if ($obj->file_project) {
	$file_project = $obj->file_project;
}
if ($obj->file_task) {
	$file_task = $obj->file_task;
	$task_name = @$obj->task_name;
} else if ($file_task) {
	$sql = "SELECT task_name FROM tasks WHERE task_id=$file_task";
	$task_name = db_loadResult( $sql );
} else {
	$task_name = '';
}

$extra = array(
	'where'=>'AND project_active <> 0'
);
$project = new CProject();
$projects = $project->getAllowedRecords( $AppUI->user_id, 'project_id,project_name', 'project_name', null, $extra );
$projects = arrayMerge( array( '0'=>'All' ), $projects );

//$sql = "SELECT project_id, project_name  FROM projects ORDER BY project_name";
//$projects = arrayMerge( array( '0'=>'- ALL PROJECTS -'), db_loadHashList( $sql ) );
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
        f.file_task.value = '0';
        f.task_name.value = '';
    }
}
</script>

<table width="100%" border="0" cellpadding="3" cellspacing="3" class="std">

<form name="uploadFrm" action="?m=files" enctype="multipart/form-data" method="post">
	<input type="hidden" name="max_file_size" value="109605000" />
	<input type="hidden" name="dosql" value="do_file_aed" />
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="file_id" value="<?php echo $file_id;?>" />

<tr>
	<td width="100%" valign="top" align="center">
		<table cellspacing="1" cellpadding="2" width="60%">
	<?php if ($file_id) { ?>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'File Name' );?>:</td>
			<td align="left" class="hilite"><?php echo strlen($obj->file_name)== 0 ? "n/a" : $obj->file_name;?></td>
			<td>
				<a href="./fileviewer.php?file_id=<?php echo $obj->file_id;?>"><?php echo $AppUI->_( 'download' );?></a>
			</td>
		</tr>
		<tr valign="top">
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Type' );?>:</td>
			<td align="left" class="hilite"><?php echo $obj->file_type;?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Size' );?>:</td>
			<td align="left" class="hilite"><?php echo $obj->file_size;?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Uploaded By' );?>:</td>
			<td align="left" class="hilite"><?php echo $obj->user_username;?></td>
		</tr>
	<?php } ?>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Version' );?>:</td>
			<td align="left">
				<input type="text" name="file_version" value="<?php echo strlen( $obj->file_version ) > 0 ? $obj->file_version : "1";?>" maxlength="10" size="5" />
			</td>
		</tr>

		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Project' );?>:</td>
			<td align="left">
			<?php
				echo arraySelect( $projects, 'file_project', 'size="1" class="text" style="width:270px"', $file_project  );
			?>
			</td>
		</tr>

		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Task' );?>:</td>
			<td align="left" colspan="2" valign="top">
				<input type="hidden" name="file_task" value="<?php echo $file_task;?>" />
				<input type="text" class="text" name="task_name" value="<?php echo $task_name;?>" size="40" disabled />
				<input type="button" class="button" value="<?php echo $AppUI->_('select task');?>..." onclick="popTask()" />
			</td>
		</tr>

		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Description' );?>:</td>
			<td align="left">
				<textarea name="file_description" class="textarea" rows="4" style="width:270px"><?php echo $obj->file_description;?></textarea>
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