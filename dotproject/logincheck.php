<?php
require_once( "./includes/config.php" );
require_once( "./includes/db_connect.php" );
require_once( "./classdefs/ui.php" );
session_start();
session_register('AppUI');

##
## set debug = true to help analyse persistent login errors
##
$debug = false;

$HTTP_SESSION_VARS['AppUI'] = new CAppUI;
$AppUI =& $HTTP_SESSION_VARS['AppUI'];

$ok = $AppUI->login( $username, $password );
if (!$ok) {
	$message = 'Login Failed';
	include "./includes/login.php";
	die;
}
echo '<script language="JavaScript">window.location = "./index.php";</script>';
?>
