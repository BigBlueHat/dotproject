<?php
error_reporting( E_PARSE | E_CORE_ERROR | E_WARNING);
error_reporting( E_ALL );

require_once( "./includes/config.php" );
require_once( "$root_dir/includes/db_connect.php" );
require_once( "$root_dir/misc/debug.php" );
require_once( "$root_dir/classdefs/ui.php" );


session_start();
session_register( 'AppUI' );

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                                                      // always modified
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");                          // HTTP/1.0

if (!isset($_SESSION['AppUI']) || isset($_GET['logout'])) {
	$_SESSION['AppUI'] = new CAppUI;
}
$AppUI =& $_SESSION['AppUI'];
$template = "$root_dir/templates/" . ($AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : DEFAULT_TEMPLATE);
$template_html = "templates/" . ($AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : DEFAULT_TEMPLATE);

// supported since PHP 4.2
// writeDebug( var_export( $AppUI, true ), 'AppUI', __FILE__, __LINE__ );

@include_once( "$root_dir/locales/$AppUI->user_locale/locales.php" );
header("Content-type: text/html;charset=$locale_char_set");

if ($AppUI->doLogin()) {
	session_unset();
	session_destroy();
	include "templates/" . DEFAULT_TEMPLATE . "/login.php";
	exit;
}


$m = isset( $_GET['m'] ) ? $_GET['m'] :
	(isset( $_COOKIE['m'] ) ? $_COOKIE['m'] : 'companies');
$a = isset( $_GET['a'] )? $_GET['a'] : 'index';

setcookie("m", $m, time()+234234532523);

require_once( "$root_dir/includes/main_functions.php" );
require_once( "$root_dir/includes/permissions.php" );
@include_once( "$root_dir/functions/" . $m . "_func.php" );
@include_once( "$root_dir/classdefs/$m.php" );
@include_once( "$root_dir/locales/core.php" );
setlocale( LC_TIME, $AppUI->user_locale );

//do some db work if dosql is set
if (isset( $_POST["dosql"]) ) {
	require("$root_dir/dosql/" . $_POST["dosql"] . ".php");
}
if (isset( $return )) {
	header("Location: ./index.php?" . $return);
}
require "$template/header.php";
require "$root_dir/modules/" . $m . "/" . $a . ".php";
require "$template/footer.php";
?>
