<?php
error_reporting( E_PARSE | E_CORE_ERROR | E_WARNING);
//error_reporting(E_ALL );

require_once( "./includes/config.php" );
require_once( "./includes/db_connect.php" );
require_once( "./classdefs/ui.php" );

session_start();
session_register( 'AppUI' );

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                                                      // always modified
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");                          // HTTP/1.0

if (!isset($HTTP_SESSION_VARS['AppUI']) || isset($HTTP_GET_VARS['logout'])) {
	$HTTP_SESSION_VARS['AppUI'] = new CAppUI;
}
$AppUI =& $HTTP_SESSION_VARS['AppUI'];
if ($AppUI->doLogin()) {
	session_unset();
	session_destroy();
	include "./includes/login.php";
	exit;
}

$m = isset( $HTTP_GET_VARS['m'] ) ? $HTTP_GET_VARS['m'] :
	(isset( $HTTP_COOKIE_VARS['m'] ) ? $HTTP_COOKIE_VARS['m'] : 'companies');
$a = isset( $HTTP_GET_VARS['a'] )? $HTTP_GET_VARS['a'] : 'index';

setcookie("m", $m, time()+234234532523);

// legacy cookies
$user_cookie = isset($HTTP_COOKIE_VARS['user_cookie']) ? $HTTP_COOKIE_VARS['user_cookie'] : 0;
$thisuser = isset($HTTP_COOKIE_VARS['thisuser']) ? $HTTP_COOKIE_VARS['thisuser'] : 0;
list($thisuser_id, $thisuser_first_name, $thisuser_last_name, $thisuser_company, $thisuser_dept, $hash) = explode( '|', $thisuser );

require_once( "./includes/main_functions.php" );
require_once( "./includes/permissions.php" );
@include_once( "./functions/" . $m . "_func.php" );
@include_once( "$root_dir/classdefs/$m.php" );

//do some db work if dosql is set
if (isset( $HTTP_POST_VARS["dosql"]) ) {
	require("./dosql/" . $HTTP_POST_VARS["dosql"] . ".php");
}
if (isset( $return )) {
	header("Location: ./index.php?" . $return);
}

require "./includes/header.php";
require "./modules/" . $m . "/" . $a . ".php";
require "./includes/footer.php";

?>
