<?php /* FILES $Id$ */
$file_id = intval( dPgetParam( $_GET, 'file_id', 0 ) );
// check permissions for this record
$perms =& $AppUI->acl();

$canEdit = $perms->checkModuleItem( $m, "edit", $file_id );
if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}
$canAdmin = $perms->checkModule('system', 'edit');

// load the companies class to retrieved denied companies
require_once( $AppUI->getModuleClass( 'projects' ) );

$file_parent = intval( dPgetParam( $_GET, 'file_parent', 0 ) );

// check if this record has dependencies to prevent deletion
$msg = '';
$obj = new CFile();

// load the record data
if ( $file_id > 0 && ! $obj->load($file_id) ) {
	$AppUI->setMsg( 'File' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

// setup the title block
$titleBlock = new CTitleBlock( 'Checkout', 'folder5.png', $m, "$m.$a" );
$titleBlock->addCrumb( '?m=files', 'files list' );
$titleBlock->show();

if ($obj->file_project) {
	$file_project = $obj->file_project;
}
if ($obj->file_task) {
	$file_task = $obj->file_task;
	$task_name = $obj->getTaskName();
} else if ($file_task) {
	$q  = new DBQuery;
	$q->addTable('tasks');
	$q->addQuery('task_name');
	$q->addWhere("task_id=$file_task");
	$sql = $q->prepare();
	$q->clear();
	$task_name = db_loadResult( $sql );
} else {
	$task_name = '';
}

$extra = array(
	'where'=>'project_status <> 7'
);
$project = new CProject();
$projects = $project->getAllowedRecords( $AppUI->user_id, 'project_id,project_name', 'project_name', null, $extra );
$projects = arrayMerge( array( '0'=>$AppUI->_('All') ), $projects );

//MerlinYoda: moved to code from do_file_co.php to "submit" button's "onclick" event 
//to prevent "less intelligent" pop-up blockers from blocking file checkout.
//Some "die-hard" pop-up blockers may still block the window despite the direct user interaction.
//Also added menu bar for saving files that don't trigger an open/save dialog.
$params = 'file_id=' . $file_id;
$session_id = SID;
// are the params empty
// Fix to handle cookieless sessions
if ($session_id != '') {
    $params .= '&' . $session_id;
}

$extra_js = "fileloader = window.open('fileviewer.php?{$params}','mywindow','location=1,menubar=1,status=1,width=200px,height=150px,resizable');fileloader.moveTo(0,0);document.coFrm.submit();";

$tpl->assign('file_id', $file_id);
$tpl->assign('user_id', $AppUI->user_id);
$tpl->assign('obj', $obj);
$tpl->assign('file_owner', $obj->getOwner());
$tpl->assign('extra_js', $extra_js);

$tpl->displayFile('co');
?>