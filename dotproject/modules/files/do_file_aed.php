<?php /* FILES $Id$ */
//addfile sql
$file_id = intval( dPgetParam( $_POST, 'file_id', 0 ) );
$del = intval( dPgetParam( $_POST, 'del', 0 ) );
$obj = new CFile();

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'File' );
// delete the file
if ($del) {
	$obj->load( $file_id );
	if (($msg = $obj->delete())) {
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
			$AppUI->setMsg( 'Upload file size is zero. Process aborted.', UI_MSG_ERROR );
			$AppUI->redirect();
		}
	} else {

	// store file with a unique name
		$obj->file_name = $upload['name'];
		$obj->file_type = $upload['type'];
		$obj->file_size = $upload['size'];
		$obj->file_date = db_unix2dateTime( time() );
		$obj->file_real_filename = uniqid( rand() );

		$res = $obj->moveTemp( $upload );
		if (!$res) {
		    $AppUI->setMsg( 'File could not be written', UI_MSG_ERROR );
		    $AppUI->redirect();
		}
		$obj->indexStrings();
	}
}

if (!$file_id) {
	$obj->file_owner = $AppUI->user_id;
}

if (($msg = $obj->store())) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
} else {
	$AppUI->setMsg( $file_id ? 'updated' : 'added', UI_MSG_OK, true );
}
$AppUI->redirect();
?>
