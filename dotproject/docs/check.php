<?php /* $Id$ */ ?>
<html>
<head>
	<title>dotProject Roadmap</title>
	<meta name="Generator" content="EditPlus">
	<meta name="Author" content="Andrew Eddie">
	<meta name="Description" content="Troubleshooting dotProject">

	<link rel="stylesheet" type="text/css" href="./main.css">
</head>
<body>
<h1>dotProject System Checks</h1>

<table cellspacing="0" cellpadding="4" border="1" class="tbl">
<?php
error_reporting( E_ALL );

require "../includes/config.php";

if ($dbok = function_exists( 'mysql_pconnect' )) {
	echo "<tr><td>MySQL</td><td>Available</td><td>OK</td></tr>";

	$host = $dPconfig['dbhost'];
	$port = 3306;
	$user = $dPconfig['dbuser'];
	$passwd = $dPconfig['dbpass'];
	$dbname = $dPconfig['dbname'];

	if (mysql_pconnect( "$host:$port", $user, $passwd )) {
		echo "<tr><td>MySQL Database Connection</td><td>Connected</td><td>OK</td></tr>";

		if ($dbname) {
			if (mysql_select_db( $dbname )) {
				echo "<tr><td>MySQL Database Select</td><td>Selected</td><td>OK</td></tr>";
			} else {
				echo "<tr><td>MySQL Database Select</td><td class=error>Failed</td><td class=error>Fatal: could not connect to $dbname</td></tr>";
			}
		} else {
			echo "<tr><td>MySQL Database Select</td><td class=error>Failed</td><td class=error>Fatal: no database name supplied</td></tr>";
		}
	} else {
		echo "<tr><td>MySQL Database Connection</td><td class=error>Failed</td><td class=error>Fatal: Check host, username and password</td></tr>";
	}
} else {
	echo "<tr><td>MySQL</td><td>Not Available</td><td>Fatal: Check MySQL support is compiled with PHP</td></tr>";
}

echo "<tr><td>Operating System</td><td>".php_uname()."</td></tr>";

$msg = phpversion() < '4.1' ? "<td class=error>To old, upgrade</td>" : "<td>OK</td>";
echo "<tr><td>PHP Version</td><td>".phpversion()."</td>$msg</tr>";

echo "<tr><td>Server API</td><td>".php_sapi_name()."</td></tr>";

echo "<tr><td>User Agent</td><td>".$_SERVER['HTTP_USER_AGENT']."</td></tr>";

echo "<tr><td>default locale</td><td>".setlocale( LC_ALL, 0 )."</td></tr>";

$msg = get_cfg_var( 'session.auto_start' ) > 0 ? "<td class=warning>Try setting to 0 if you are having problems with WSOD</td>" : "<td>OK</td>";
echo "<tr><td>session.auto_start</td><td>".get_cfg_var( 'session.auto_start' )."</td>$msg</tr>";

echo "<tr><td>session.save_handler</td><td>".get_cfg_var( 'session.save_handler' )."</td></tr>";

$msg = is_dir( get_cfg_var( 'session.save_path' ) ) ? "<td>OK</td>" : "<td class=error>Fatal: Save path does not exist</td>";
echo "<tr><td>session.save_path</td><td>".get_cfg_var( 'session.save_path' )."</td>$msg</tr>";

echo "<tr><td>session.serialize_handler</td><td>".get_cfg_var( 'session.serialize_handler' )."</td></tr>";

echo "<tr><td>session.use_cookies</td><td>".get_cfg_var( 'session.use_cookies' )."</td></tr>";

echo "<tr><td>session.use_trans_sid</td><td>".get_cfg_var( 'session.use_trans_sid' )."</td></tr>";

echo "</table>";

?>
</table>
<body>
</html>