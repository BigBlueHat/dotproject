<?php /* FILES $Id$ */
$file_id = intval( dPgetParam( $_GET, 'file_id', 0 ) );
$ci = dPgetParam($_GET, 'ci', 0) == 1 ? true : false;
$preserve = $dPconfig['files_ci_preserve_attr'];


// check permissions for this record
$perms =& $AppUI->acl();
$canEdit = $perms->checkModuleItem( $m, 'edit', $file_id );
if (!$canEdit)
	$AppUI->redirect('m=public&a=access_denied');

$canAdmin = $perms->checkModule('system', 'edit');

// load the companies class to retrieved denied companies
require_once ($AppUI->getModuleClass('projects'));
require_once ($AppUI->getModuleClass('tasks'));

$file_task 		= intval( dPgetParam( $_GET, 'file_task', 0 ) );
$file_parent 	= intval( dPgetParam( $_GET, 'file_parent', 0 ) );
$file_project = intval( dPgetParam( $_GET, 'project_id', 0 ) );

$q =& new DBQuery;

// check if this record has dependencies to prevent deletion
$msg = '';
$obj = new CFile();
$canDelete = $obj->canDelete( $msg, $file_id );

// load the record data
// $obj = null;
if ($file_id > 0 && ! $obj->load($file_id)) {
	$AppUI->setMsg( 'File' );
	$AppUI->setMsg( 'invalidID', UI_MSG_ERROR, true );
	$AppUI->redirect();
}
if ($file_id > 0) {
	// Check to see if the task or the project is also allowed.
	if ($obj->file_task) {
		if (! $perms->checkModuleItem('tasks', 'view', $obj->file_task))
			$AppUI->redirect('m=public&a=access_denied');
	}
	if ($obj->file_project) {
		if (! $perms->checkModuleItem('projects', 'view', $obj->file_project))
			$AppUI->redirect('m=public&a=access_denied');
	}
}

if ($obj->file_checkout != $AppUI->user_id)
	$ci = false;

if (! $canAdmin)
	$canAdmin = $obj->canAdmin();

if ($obj->file_checkout == 'final' && ! $canAdmin) {
	$AppUI->redirect('m=public&a=access_denied');
}
// setup the title block
$ttl = $file_id ? 'Edit File' : 'Add File';
$ttl = $ci ? 'Checking in' : $ttl;
$titleBlock = new CTitleBlock( $ttl, 'folder5.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=files", "files list" );
if ($canDelete && $file_id > 0 && !$ci) {
	$titleBlock->addCrumbDelete( 'delete file', $canDelete, $msg );
}
$titleBlock->show();

//Clear the file id if checking out so a new version is created.
//if ($ci)
//        $file_id = 0;

if ($obj->file_project) {
	$file_project = $obj->file_project;
}
if ($obj->file_task) {
	$file_task = $obj->file_task;
	$task_name = $obj->getTaskName();
} else if ($file_task) {
	$q  = new DBQuery;
	$q->addQuery('task_name');
	$q->addTable('tasks');
	$q->addWhere('task_id = ' . $file_task);
	$task_name = $q->loadResult();
} else {
	$task_name = '';
}

$extra = array('where'=>'project_status != 7');
$project = new CProject();
$projects = $project->getAllowedRecords( $AppUI->user_id, 'project_id,project_name', 'project_name', null, $extra );
$projects = arrayMerge( array( '0'=>$AppUI->_('All', UI_OUTPUT_RAW) ), $projects );

$tpl->assign('file_id', $file_id);
$tpl->assign('file_owner', $obj->getOwner());
$tpl->assign('ci', $ci);
$tpl->assign('preserve', $preserve); 
$tpl->assign('canAdmin', $canAdmin);
$tpl->assign('file_project', $file_project);

$select_disabled = ( $ci && $preserve ) ? ' disabled ' : ' ';
$filetype = dPgetSysVal('FileType');
$select_file_category = arraySelect($filetype, 'file_category', ''.$select_disabled, $obj->file_category, true);
$tpl->assign('select_file_category', $select_file_category);

$select_file_project = arraySelect($projects, 'file_project', 'size="1" class="text" style="width:270px"' . $select_disabled, $file_project);
$tpl->assign('select_file_project', $select_file_project);

$tpl->assign('file_task', $file_task);
$tpl->assign('task_name', $task_name);

$tpl->displayAddEdit($obj);
?>
