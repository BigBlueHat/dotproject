<html>
<head>
<style type="text/css">
body,th,td {
	font-family:verdana,helvetica,arial,sans-serif;
	font-size:10pt;
}
</style>
</head>
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

echo "<tr><td>default locale</td><td>".setlocale( LC_ALL, 0 )."</td></tr>";
echo "<tr><td>cs prefered time setting</td><td>".setlocale( LC_TIME, 'Czech' )."</td></tr>";
echo "<tr><td>session.auto_start</td><td>".get_cfg_var( 'session.auto_start' )."</td></tr>";
echo "<tr><td>session.save_handler</td><td>".get_cfg_var( 'session.save_handler' )."</td></tr>";
echo "<tr><td>session.save_path</td><td>".get_cfg_var( 'session.save_path' )."</td></tr>";
echo "<tr><td>session.serialize_handler</td><td>".get_cfg_var( 'session.serialize_handler' )."</td></tr>";
echo "<tr><td>session.use_cookies</td><td>".get_cfg_var( 'session.use_cookies' )."</td></tr>";
echo "<tr><td>session.use_trans_sid</td><td>".get_cfg_var( 'session.use_trans_sid' )."</td></tr>";
echo "</table>";

$locs = array();
$locs['Czech'] = array( 'czech', 'cs', 'cs_CS', 'cz' );
$locs['Dutch'] = array( 'dutch', 'nl', 'nl_NL', 'du' );
$locs['English'] = array( 'english', 'en', 'en_US', 'en_UK', 'english united kingdom' );
$locs['French'] = array( 'french', 'fr', 'fr_FR' );
$locs['German'] = array( 'german', 'de_DE@euro', 'de_DE', 'de', 'ge' );
$locs['Spanish'] = array( 'spanish', 'es', 'es_ES', 'sp' );

echo '<b>Time locales</b>';
echo '<table cellspacing="0" cellpadding="4" border="1">';

foreach ($locs as $k => $v) {
	echo "<tr><td width='10%'>$k</td><td><table cellspacing=0 cellpadding=2 border=1 width='100%'>";
	foreach ($v as $l) {
		echo "<tr><td align='right' width='20%'>$l</td><td width='50%'>".( ($r = setLocale( "LC_TIME", $l )) ? $r : 'failed')."</td></tr>";
	}
	echo "</table></td></tr>";
}

?>
</table>
<body>
</html>