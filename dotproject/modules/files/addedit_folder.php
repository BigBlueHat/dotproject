<?php /* FILES $Id$ */
$file_folder_parent = intval( dPgetParam( $_GET, 'file_folder_parent', 0 ) );
$folder = intval( dPgetParam( $_GET, 'folder', 0 ) );

require ("functions.php");

// add to allow for returning to other modules besides Files
$referrerArray = parse_url($_SERVER['HTTP_REFERER']);
$referrer = $referrerArray['query'] . $referrerArray['fragment'];

// check permissions for this record
if ($folder == 0) {
	$canEdit = true;
} else {
	$canEdit = !getDenyEdit( $m, $folder);
}
if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}
$q = new DBQuery();
$q->addTable('file_folders');
$q->addQuery('file_folders.*');
$q->addWhere("file_folder_id=$folder");
$sql = $q->prepare();

// check if this record has dependancies to prevent deletion
$msg = '';
$obj = new CFileFolder();
if ($folder > 0) {
	$canDelete = $obj->canDelete( $msg, $folder );
}

// load the record data
$obj = null;
if (!db_loadObject( $sql, $obj ) && $folder > 0) {
	$AppUI->setMsg( 'File Folder' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

$folders = getFolderSelectList();
// setup the title block
$ttl = $folder ? "Edit File Folder" : "Add File Folder";
$titleBlock = new CTitleBlock( $ttl, 'folder5.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=files", "files list" );
if ($canEdit && $folder > 0) {
	$titleBlock->addCrumbDelete( 'delete file folder', $canDelete, $msg );
}
$titleBlock->show();

$tpl->assign('folder', $folder);
$tpl->assign('folders', $folders);
$tpl->assign('referrer', $referrer);
$tpl->assign('obj', $obj);
$tpl->assign('file_folder_parent', $file_folder_parent);

$tpl->displayFile('addedit_folder');

?>


