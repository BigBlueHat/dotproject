<?php
//file viewer
require "./includes/config.php";
require "$root_dir/includes/db_connect.php";
require "$root_dir/classdefs/ui.php";

session_start();
$AppUI =& $_SESSION['AppUI'];

include "$root_dir/includes/main_functions.php";
include "$root_dir/includes/permissions.php";

$denyRead = getDenyRead( 'files' );
if ($denyRead) {
	$AppUI->redirect( "m=help&a=access_denied" );
}

$file_id = isset($_GET['file_id']) ? $_GET['file_id'] : 0;

if ($file_id) {
    $sql = "SELECT * FROM files WHERE file_id=$file_id";
	db_loadHash( $sql, $file );
	header( "Content-length: {$file['file_size']}" );
	header( "Content-type: {$file['file_type']}" );
	header( "Content-disposition: attachment; filename={$file['file_name']}" );
	readfile( "$root_dir/files/{$file['file_project']}/{$file['file_real_filename']}" );
} else {
	$AppUI->setMsg( "fileIdError", UI_MSG_ERROR );
	$AppUI->redirect();
}
?>