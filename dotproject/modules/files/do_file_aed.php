<?php
//addfile sql
$file_id = dPgetParam( $_POST, 'file_id', 0 );
$del = dPgetParam( $_POST, 'del', 0 );
$file = new CFile();

if (($msg = $file->bind( $_POST ))) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
	$AppUI->redirect();
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'File' );
// delete the file
if ($del) {
	$file->load( $file_id );
	if (($msg = $file->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
		$AppUI->redirect( "m=files" );
	}
}

set_time_limit( 600 );
ignore_user_abort( 1 );

//echo "<pre>";print_r($_POST);echo "</pre>";die;

$upload = null;
if (isset( $_FILES['formfile'] )) {
	$upload = $_FILES['formfile'];

	if ($upload['size'] < 1) {
		if (!$file_id) {
			$AppUI->setMsg( 'Upload file size is zero.  Process aborted.', UI_MSG_ERROR );
			$AppUI->redirect();
		}
	} else {

	// store file with a unique name
		$file->file_name = $upload['name'];
		$file->file_type = $upload['type'];
		$file->file_size = $upload['size'];
		$file->file_date = db_unix2dateTime( time() );
		$file->file_real_filename = uniqid( rand() );

		$file->moveTemp( $upload );
		$file->indexStrings();
	}
}

if (!$file_id) {
	$file->file_owner = $AppUI->user_id;
}

if (($msg = $file->store())) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
} else {
	$AppUI->setMsg( $file_id ? 'updated' : 'added', UI_MSG_OK, true );
}
$AppUI->redirect();
?>