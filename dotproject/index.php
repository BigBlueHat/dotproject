<?php /* $Id$ */
error_reporting( E_PARSE | E_CORE_ERROR | E_WARNING);
error_reporting( E_ALL );	// this only for development testing

// required includes for start-up
$dPconfig = array();
require_once( "./classdefs/ui.php" );

// don't output anything. Usefull for fileviewer.php, gantt.php, etc.
$suppressHeaders = @$_GET['suppressHeaders'];

// manage the session variable(s)
session_name( 'dotproject' );
session_start();
session_register( 'AppUI' ); 
  
// write the HTML headers
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");	// Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");	// always modified
header ("Cache-Control: no-cache, must-revalidate");	// HTTP/1.1
header ("Pragma: no-cache");	// HTTP/1.0

require_once( "./includes/config.php" );

// check if session has previously been initialised
if (!isset( $_SESSION['AppUI'] ) || isset($_GET['logout'])) {
    $_SESSION['AppUI'] = new CAppUI();
}
$AppUI =& $_SESSION['AppUI'];
$AppUI->setConfig( $dPconfig );
$AppUI->checkStyle();
 
// load the db handler
require_once( "./includes/db_connect.php" );
require_once( "./misc/debug.php" );

// load default preferences if not logged in
if ($AppUI->doLogin()) {
    $AppUI->loadPrefs( 0 );
}

// check if the user is trying to log in
if (isset($_POST['login'])) {
	$username = isset($_POST['username']) ? $_POST['username'] : '';
	$password = isset($_POST['password']) ? $_POST['password'] : '';
	$ok = $AppUI->login( $username, $password );
	if (!$ok) {
		@include_once( "./locales/core.php" );
		$AppUI->setMsg( 'Login Failed' );
		$AppUI->redirect();
	}
}

// supported since PHP 4.2
// writeDebug( var_export( $AppUI, true ), 'AppUI', __FILE__, __LINE__ );

// set the default ui style
$uistyle = $AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : $AppUI->cfg['host_style'];

// clear out main url parameters
$m = '';
$a = '';
$u = '';

// check if we are logged in
if ($AppUI->doLogin()) {
	// load basic locale settings
	@include_once( "./locales/$AppUI->user_locale/locales.php" );
	@include_once( "./locales/core.php" );
	setlocale( LC_TIME, $AppUI->user_locale );

	// output the character set header
	if (isset( $locale_char_set )) {
		 header("Content-type: text/html;charset=$locale_char_set");
	}

	require "./style/$uistyle/login.php";
	// destroy the current session and output login page
	session_unset();
	session_destroy();
	exit;
}

// see if a project id has been passed in the url
if (isset( $_REQUEST['project_id'] )) {
    $AppUI->setProject( $_REQUEST['project_id'] );
}

// see if a unix timestamp has been passed in the url;
if (isset( $_REQUEST['uts'] )) {
    $AppUI->setDaySelected( $_REQUEST['uts'] );
}

// bring in the rest of the support and localisation files
require_once( "./includes/main_functions.php" );
require_once( "./includes/permissions.php" );

// set the module and action from the url
$m = isset( $_GET['m'] ) ? $_GET['m'] : getReadableModule();
$u = isset( $_GET['u'] ) ? $_GET['u'] : '';
$a = isset( $_GET['a'] )? $_GET['a'] : 'index';

@include_once( "./functions/" . $m . "_func.php" );
// check overall module permissions
// these can be further modified by the included action files
$canRead = !getDenyRead( $m );
$canEdit = !getDenyEdit( $m );
$canAuthor = $canEdit;
$canDelete = $canEdit;
	// legacy support
	$denyRead = !$canRead;
	$denyEdit = !$canEdit;

// load module based locale settings
@include_once( "./locales/$AppUI->user_locale/locales.php" );
@include_once( "./locales/core.php" );
setlocale( LC_TIME, $AppUI->user_locale );

if ( !$suppressHeaders ) {
	// output the character set header
	if (isset( $locale_char_set )) {
		header("Content-type: text/html;charset=$locale_char_set");
	}
}

// bounce the user if they don't have at least read access
// however, the public module is accessible by anyone
if (!$canRead && $m != 'public') {
	$AppUI->redirect( "m=public&a=access_denied" );
}
// include the module classes (check in two places)
@include_once( "./classdefs/$m.php" );
@include_once( "./modules/$m/$m.class.php" );
@include_once( "./modules/$m/" . ($u ? "$u/" : "") . "$u.class.php" );

// do some db work if dosql is set
// TODO - MUST MOVE THESE INTO THE MODULE DIRECTORY
if (isset( $_REQUEST["dosql"]) ) {
    require("./dosql/" . $_REQUEST["dosql"] . ".php");
}

// start output proper
include "./style/$uistyle/overrides.php";
if(!$suppressHeaders) {
	require "./style/$uistyle/header.php";
}
require "./modules/$m/" . ($u ? "$u/" : "") . "$a.php";
if(!$suppressHeaders) {
	require "./style/$uistyle/footer.php";
}
?>
