<html>
<body>
<b>dotproject diagnostics</b>
<table cellspacing="0" cellpadding="4" border="1">
<?php

echo "<tr><td>Operating System</td><td>".php_uname()."</td></tr>";
echo "<tr><td>PHP Version</td><td>".phpversion()."</td></tr>";
echo "<tr><td>Server API</td><td>".php_sapi_name()."</td></tr>";

echo "<tr><td>User Agent</td><td>".$_SERVER['HTTP_USER_AGENT']."</td></tr>";

require "../includes/config.php";
require "../includes/db_connect.php";

echo "<tr><td>DB Type</td><td>".$dbtype."</td></tr>";
echo "<tr><td>DB Version</td><td>".db_version()."</td></tr>";

echo "<tr><td>session.auto_start</td><td>".get_cfg_var( 'session.auto_start' )."</td></tr>";
echo "<tr><td>session.save_handler</td><td>".get_cfg_var( 'session.save_handler' )."</td></tr>";
echo "<tr><td>session.save_path</td><td>".get_cfg_var( 'session.save_path' )."</td></tr>";
echo "<tr><td>session.serialize_handler</td><td>".get_cfg_var( 'session.serialize_handler' )."</td></tr>";
echo "<tr><td>session.use_cookies</td><td>".get_cfg_var( 'session.use_cookies' )."</td></tr>";
echo "<tr><td>session.use_trans_sid</td><td>".get_cfg_var( 'session.use_trans_sid' )."</td></tr>";

?>
</table>
<body>
</html>