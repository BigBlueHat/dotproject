<?php /* $Id$ */
//file viewer
require_once "./includes/config.php";
require_once "./classes/ui.class.php";

session_name( 'dotproject' );
$cookie_dir = dirname($_SERVER['SCRIPT_NAME']);
if (substr($cookie_dir, -1) != '/')
	$cookie_dir .= '/';
session_set_cookie_params(0, $cookie_dir);
if (get_cfg_var( 'session.auto_start' ) > 0) {
	session_write_close();
}
session_start();

// check if session has previously been initialised
// if no ask for logging and do redirect
if (!isset( $_SESSION['AppUI'] ) || isset($_GET['logout'])) {
    $_SESSION['AppUI'] = new CAppUI();
	$AppUI =& $_SESSION['AppUI'];
	$AppUI->setConfig( $dPconfig );
	$AppUI->checkStyle();
	 
	require_once( $AppUI->getSystemClass( 'dp' ) );
	require_once( "./misc/debug.php" );

	if ($AppUI->doLogin()) $AppUI->loadPrefs( 0 );
	// check if the user is trying to log in
	if (isset($_POST['login'])) {
		$username = dPgetParam( $_POST, 'username', '' );
		$password = dPgetParam( $_POST, 'password', '' );
		$redirect = dPgetParam( $_REQUEST, 'redirect', '' );
		$ok = $AppUI->login( $username, $password );
		if (!$ok) {
			//display login failed message 
			$uistyle = $AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : $dPconfig['host_style'];
			$AppUI->setMsg( 'Login Failed' );
			require "./style/$uistyle/login.php";
			session_unset();
			exit;
		}
		header ( "Location: fileviewer.php?$redirect" );
		exit;
	}	

	$uistyle = $AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : $dPconfig['host_style'];
	// check if we are logged in
	if ($AppUI->doLogin()) {
	    $AppUI->setUserLocale();
		@include_once( "./locales/$AppUI->user_locale/locales.php" );
		@include_once( "./locales/core.php" );
		setlocale( LC_TIME, $AppUI->user_locale );
		
		$redirect = @$_SERVER['QUERY_STRING'];
		if (strpos( $redirect, 'logout' ) !== false) $redirect = '';	
		if (isset( $locale_char_set )) header("Content-type: text/html;charset=$locale_char_set");
		require "./style/$uistyle/login.php";
		session_unset();
		session_destroy();
		exit;
	}	
}
$AppUI =& $_SESSION['AppUI'];

require_once "{$dPconfig['root_dir']}/includes/main_functions.php";
require_once "{$dPconfig['root_dir']}/includes/db_adodb.php";
require_once "{$dPconfig['root_dir']}/includes/db_connect.php";
require_once "{$dPconfig['root_dir']}/includes/permissions.php";

$perms =& $AppUI->acl();

$canRead = $perms->checkModule( 'files' , 'view' );
if (!$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

$file_id = isset($_GET['file_id']) ? $_GET['file_id'] : 0;

if ($file_id) {
	// projects tat are denied access
	require_once($AppUI->getModuleClass("projects"));
	require_once($AppUI->getModuleClass("files"));
	$project =& new CProject;
	$allowedProjects = $project->getAllowedRecords($AppUI->user_id, 'project_id, project_name');
	$fileclass =& new CFile;
	$allowedFiles = $fileclass->getAllowedRecords($AppUI->user_id, 'file_id, file_name');
	
	if (count($allowedFiles) && ! array_key_exists($file_id, $allowedFiles)) {
		$AppUI->redirect( 'm=public&a=access_denied' );
	}

	if (count($allowedProjects)) {
		$allowedProjects[0] = 'All Projects';
	}

	$sql = "SELECT *
	FROM files
	WHERE file_id=$file_id"
	  . (count( $allowedProjects ) > 0 ? "\nAND file_project IN (" . implode(',', array_keys($allowedProjects) ) . ')' : '');

	if (!db_loadHash( $sql, $file )) {
		$AppUI->redirect( "m=public&a=access_denied" );
	};

	/*
	 * DISABLED LINES TO FIX A NEWER BUG 914075 WITH IE 6 (GREGORERHARDT 20040612)

	// BEGIN extra headers to resolve IE caching bug (JRP 9 Feb 2003)
	// [http://bugs.php.net/bug.php?id=16173]
	header("Pragma: ");
	header("Cache-Control: ");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");  //HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	// END extra headers to resolve IE caching bug
	*/

	header("MIME-Version: 1.0");
	header( "Content-length: {$file['file_size']}" );
	header( "Content-type: {$file['file_type']}" );
	header( "Content-disposition: inline; filename=\"{$file['file_name']}\"" );
	readfile( "{$dPconfig['root_dir']}/files/{$file['file_project']}/{$file['file_real_filename']}" );
} else {
	$AppUI->setMsg( "fileIdError", UI_MSG_ERROR );
	$AppUI->redirect();
}
?>
