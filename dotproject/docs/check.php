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

<table cellspacing="0" cellpadding="4" border="1" class="tbl" width="100%">
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
		echo "<tr><td>MySQL Server Version</td><td>" . mysql_get_server_info() . "</td></tr>";
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

$sapi = php_sapi_name();
echo "<tr><td>Server API</td><td>$sapi</td>";
if ($sapi == "cgi") {
  echo "<td class=error>CGI mode is likely to have problems</td></tr>";
} else {
  echo "</tr>";
}
echo "<tr><td>Web Server</td><td>$_SERVER[SERVER_SOFTWARE]</td></tr>";

echo "<tr><td>User Agent</td><td>".$_SERVER['HTTP_USER_AGENT']."</td></tr>";

echo "<tr><td>default locale</td><td>";
$lc_list = explode(";", setlocale( LC_ALL, 0 ));
foreach ($lc_list as $lc) {
  echo "$lc<br>";
}
echo "</td></tr>";

$msg = get_cfg_var( 'session.auto_start' ) > 0 ? "<td class=warning>Try setting to 0 if you are having problems with WSOD</td>" : "<td>OK</td>";
echo "<tr><td>session.auto_start</td><td>".get_cfg_var( 'session.auto_start' )."</td>$msg</tr>";

echo "<tr><td>session.save_handler</td><td>".get_cfg_var( 'session.save_handler' )."</td></tr>";

$msg = is_dir( get_cfg_var( 'session.save_path' ) ) ? "<td>OK</td>" : "<td class=error>Fatal: Save path does not exist</td>";
echo "<tr><td>session.save_path</td><td>".get_cfg_var( 'session.save_path' )."</td>$msg</tr>";

echo "<tr><td>session.serialize_handler</td><td>".get_cfg_var( 'session.serialize_handler' )."</td></tr>";

$cookies = intval( get_cfg_var( 'session.use_cookies' ) );
$msg = $cookies ? "<td>OK</td>" : "<td class=warning>Try setting to 0 if you are having problems logging in</td>";
echo "<tr><td>session.use_cookies</td><td>$cookies</td>$msg</tr>";

$sid = intval( get_cfg_var( 'session.use_trans_sid' ) );
$msg = $sid ? "<td class=warning>There are security risks with this turned on</td>" : "<td>OK</td>";
echo "<tr><td>session.use_trans_sid</td><td>$sid</td>$msg</tr>";

$fup = get_cfg_var( 'file_uploads' );
$msg = $fup ? "<td>OK</td>" : "<td class=warning>You won't be able to upload files</td>";
echo "<tr><td>file_uploads</td><td>$fup</td>$msg</tr>";

$iw = is_writable( "{$dPconfig['root_dir']}/locales/en" );
$msg = $iw ? '<td>OK</td>' : '<td class=warning>Warning: you will not be able to save translation files.  Check the directory permissions.</td>';
echo "<tr><td>/locales/en directory writable</td><td>$iw</td>$msg</tr>";

$iw = is_writable( "{$dPconfig['root_dir']}/files" );
$msg = $iw ? '<td>OK</td>' : '<td class=warning>Warning: you will not be able to upload files.  Check the directory permissions.</td>';

echo "<tr><td>/files directory writable</td><td>$iw</td>$msg</tr>";

$iw = is_writable( "{$dPconfig['root_dir']}/files/temp" );
$msg = $iw ? '<td>OK</td>' : '<td class=warning>Warning: you will not be able to make PDF\'s.  Check the directory permissions.</td>';

echo "<tr><td>/files/temp directory writable</td><td>$iw</td>$msg</tr>";

// Now check to see if the supplied root_dir is the same as the called URL.
$url = strtr(dirname(dirname(__FILE__)), '\\', '/'); // Gets around problems with IIS, Yahoo
echo "<tr><td>root_dir</td><td>$dPconfig[root_dir]</td>";
$dproot = strtr($dPconfig['root_dir'], '\\', '/');
if ($url != $dproot) {
  echo "<td class=error>root_dir seems to be incorrect, probably should be $url</td></tr>";
} else {
  echo "<td>OK</td></tr>";
}

$burl = preg_replace('/\/docs\/.*$/', '', $_SERVER['SCRIPT_NAME']);
preg_match('_^(https?://)([^/]+)(:[0-9]+)?(/.*)?$_i', $dPconfig['base_url'], $url_parts);
echo "<tr><td>base_url</td><td>$dPconfig[base_url]</td>";
$server_port = $_SERVER['SERVER_PORT'];
$https = isset($_SERVER['HTTPS']);
if ($https)
	$https = $_SERVER['HTTPS'] != 'off';

$real_base =  ($https ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'];
if ( $server_port != 80 && $server_port != 443 )
	$real_base .= ":$server_port";
$real_base .= $burl;
if ($url_parts[2] != $_SERVER['SERVER_NAME']
|| $url_parts[4] != $burl ) {
	echo "<td class=error>base_dir seems to be incorrect, probably should be $real_base</td></tr>";
} else {
	echo "<td>OK</td></tr>";
}

echo "</table>";




?>
</table>
<body>
</html>
