<?php
error_reporting( E_PARSE | E_CORE_ERROR | E_WARNING);
//error_reporting(E_ALL );

if (isset( $HTTP_POST_VARS["cookie_project"] )) {
	setcookie("cookie_project", $HTTP_POST_VARS["cookie_project"]);
}

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                                                      // always modified
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");                          // HTTP/1.0


$m = isset( $HTTP_GET_VARS['m'] ) ? $HTTP_GET_VARS['m'] :
	(isset( $HTTP_COOKIE_VARS['m'] ) ? $HTTP_COOKIE_VARS['m'] : 'companies');
$a = isset( $HTTP_GET_VARS['a'] )? $HTTP_GET_VARS['a'] : 'index';

setcookie("m", $m, time()+234234532523);

if (isset( $mmodule )) {
	$m="mail";
}

require "./includes/config.php";
require "./includes/db_connect.php";
require "./includes/main_functions.php";
require "./includes/permissions.php";
@include "./functions/" . $m . "_func.php";

//do some db work if dosql is set
if (isset( $HTTP_POST_VARS["dosql"]) ) {
	require("./dosql/" . $HTTP_POST_VARS["dosql"] . ".php");
}
if (isset( $return )) {
	header("Location: /" . $return);
}

require "./includes/header.php";
require "./modules/" . $m . "/" . $a . ".php";
require "./includes/footer.php";
?>
