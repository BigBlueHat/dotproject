<?php
error_reporting( E_PARSE | E_CORE_ERROR | E_WARNING);
error_reporting( E_ALL );

// required includes for start-up
require_once( "./includes/config.php" );
require_once( "$root_dir/includes/db_connect.php" );
require_once( "$root_dir/misc/debug.php" );
require_once( "$root_dir/classdefs/ui.php" );

// manage the session variable(s)
session_start();
session_register( 'AppUI' );

// write the HTML headers
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                                                      // always modified
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");                          // HTTP/1.0

// initialise/retrieve the session variable(s)
if (!isset($_SESSION['AppUI']) || isset($_GET['logout'])) {
	$_SESSION['AppUI'] = new CAppUI;
}
$AppUI =& $_SESSION['AppUI'];


// supported since PHP 4.2
// writeDebug( var_export( $AppUI, true ), 'AppUI', __FILE__, __LINE__ );

// load some local settings
@include_once( "$root_dir/locales/$AppUI->user_locale/locales.php" );
header("Content-type: text/html;charset=$locale_char_set");

$uistyle = $AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : $host_style;

// check if we are logged in
if ($AppUI->doLogin()) {
	// nope, destroy the current session and output login page
	session_unset();
	session_destroy();
	include "$root_dir/style/$uistyle/login.php";
	exit;
}

// set the module and action from the url
$m = isset( $_GET['m'] ) ? $_GET['m'] : 'companies';
$a = isset( $_GET['a'] )? $_GET['a'] : 'index';

// see if a project id has been passed in the url;
if (isset( $_REQUEST['project_id'] )) {
	$AppUI->setProject( $_REQUEST['project_id'] );
}

// bring in the rest of the support and localisation files
require_once( "$root_dir/includes/main_functions.php" );
require_once( "$root_dir/includes/permissions.php" );
@include_once( "$root_dir/functions/" . $m . "_func.php" );
@include_once( "$root_dir/classdefs/$m.php" );
@include_once( "$root_dir/locales/core.php" );
setlocale( LC_TIME, $AppUI->user_locale );

// do some db work if dosql is set
if (isset( $_POST["dosql"]) ) {
	require("$root_dir/dosql/" . $_POST["dosql"] . ".php");
}
if (isset( $return )) {
	header("Location: ./index.php?" . $return);
}

// start outputting proper
require "$root_dir/style/$uistyle/header.php";
require "$root_dir/modules/" . $m . "/" . $a . ".php";
require "$root_dir/style/$uistyle/footer.php";
?>