<?php /* $Id$ */
//file viewer
require "./includes/config.php";
require "./classes/ui.class.php";

session_name( 'dotproject' );
session_start();
$AppUI =& $_SESSION['AppUI'];

require "{$AppUI->cfg['root_dir']}/includes/db_connect.php";

include "{$AppUI->cfg['root_dir']}/includes/main_functions.php";
include "{$AppUI->cfg['root_dir']}/includes/permissions.php";

$denyRead = getDenyRead( 'files' );
if ($denyRead) {
	$AppUI->redirect( "m=help&a=access_denied" );
}

$file_id = isset($_GET['file_id']) ? $_GET['file_id'] : 0;

if ($file_id) {
	$sql = "SELECT * FROM files WHERE file_id=$file_id";
	db_loadHash( $sql, $file );

	// BEGIN extra headers to resolve IE caching bug (JRP 9 Feb 2003)
	// [http://bugs.php.net/bug.php?id=16173]
	header("Pragma: ");
	header("Cache-Control: ");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");  //HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	// END extra headers to resolve IE caching bug

	header( "Content-length: {$file['file_size']}" );
	header( "Content-type: {$file['file_type']}" );
	header( "Content-disposition: attachment; filename={$file['file_name']}" );
	readfile( "{$AppUI->cfg['root_dir']}/files/{$file['file_project']}/{$file['file_real_filename']}" );
} else {
	$AppUI->setMsg( "fileIdError", UI_MSG_ERROR );
	$AppUI->redirect();
}
?>
