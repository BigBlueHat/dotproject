<?php /* $Id$ */
require_once( "./includes/config.php" );
require_once( "$root_dir/includes/db_connect.php" );
require_once( "$root_dir/misc/debug.php" );
require_once( "$root_dir/classdefs/ui.php" );
session_name( 'dotproject' );
session_start();
session_register('AppUI');

$username = isset($_POST['username']) ? $_POST['username'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

$_SESSION['AppUI'] = new CAppUI;
$AppUI =& $_SESSION['AppUI'];

$uistyle = $AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : $host_style;

$ok = $AppUI->login( $username, $password );
if (!$ok) {
	$AppUI->setMsg( 'Login Failed' );
	include "$root_dir/style/$uistyle/login.php";
	die;
}
echo '<script language="javascript">window.location = "./index.php";</script>';
?>
