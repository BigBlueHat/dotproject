<?php // $Id$

/*
* dotProject Installer
* @package dotProject
* @Copyright (c) 2004, The dotProject Development Team sf.net/projects/dotproject
* @ All rights reserved
* @ dotProject is Free Software, released under BSD License
* @subpackage Installer
* @ This Installer is released under GNU/GPL License : http://www.gnu.org/copyleft/gpl.html
* @ Major Parts are based on Code from Mambo Open Source www.mamboserver.com
* @version $Revision$
*/

require_once("commonlib.php");



echo $chmsg;
// define image sources
$failedImg = '<img src="../images/icons/stock_cancel-16.png" width="16" height="16" align="middle" alt="Failed"/>';
$okImg = '<img src="../images/icons/stock_ok-16.png" width="16" height="16" align="middle" alt="OK"/>';

?>
<html>
<head>
	<title>dotProject Installer</title>
	<meta name="Author" content="Gregor Erhardt: gregor at dotproject dot orangrey dot org">
	<meta name="Description" content="Automated Installer Routine for dotProject">
	<link rel="stylesheet" type="text/css" href="./install.css">
</head>
<body>

<h1><img src="dp.png" align="middle" alt="dotProject Logo"/>&nbsp;Installer for dotProject <?php echo dPgetVersion(); ?>: Step 1</h1>

<table cellspacing="0" cellpadding="3" border="0" class="tbl" width="90%" align="center">
<tr>
            <td class="title" colspan="2">Check for Requirements</td>
</tr>
<tr>
	<td class="item">PHP Version >= 4.1</td>
	<td align="left"><?php echo phpversion() < '4.1' ? '<b class="error">'.$failedImg.' ('.phpversion().'): dotProject may not work. Please upgrade!</b>' : '<b class="ok">'.$okImg.'</b><span class="item"> ('.phpversion().')</span>';?></td>
</tr>
<tr>
	<td class="item"><li>Server API</li></td>
 	<td align="left"><?php echo (php_sapi_name() != "cgi") ? '<b class="ok">'.$okImg.'</b><span class="item"> ('.php_sapi_name().')</span>' : '<b class="error">'.$failedImg.' CGI mode is likely to have problems</b>';?></td>
</tr>
<tr>
	<td class="item"><li>MYSQL Support</li></td>
 	<td align="left"><?php echo function_exists( 'mysql_pconnect' ) ? '<b class="ok">'.$okImg.'</b><span class="item"> ('.mysql_get_server_info().')</span>' : '<b class="error">'.$failedImg.' Fatal: Check MySQL support is compiled with PHP</b>';?></td>
</tr>
<tr>
	<td class="item"><li>GD Support</li></td>
 	<td align="left"><?php echo extension_loaded('gd') ? '<b class="ok">'.$okImg.'</b>' : '<b class="error">'.$failedImg.'</b> GANTT Chart functionality may not work correctly.';?></td>
</tr>
<tr>
	<td class="item"><li>Zlib compression Support</li></td>
 	<td align="left"><?php echo extension_loaded('zlib') ? '<b class="ok">'.$okImg.'</b>' : '<b class="error">'.$failedImg.'</b> Non-core Backup module is working with some minor restrictions.';?></td>
</tr>
<tr>
	<td class="item"><li>File Uploads</li></td>
 	<td align="left"><?php echo get_cfg_var('file_uploads') ? '<b class="ok">'.$okImg.'</b>' : '<b class="error">'.$failedImg.'</b><span class="warning"> Upload functionality will not be available</span>';?></td>
</tr>
<tr>
            <td class="item">Session Save Path</td>
            <td align="left"><?php echo (is_dir( get_cfg_var( 'session.save_path' )) && is_writable( get_cfg_var( 'session.save_path' )) ) ? '<b class="ok">'.$okImg.'</b>' : '<b class="error">'.$failedImg.' Fatal:</b> <b class="item">'.get_cfg_var( "session.save_path" ).'</b><b class="error"> not existing or not writable</b>';?></td>
</tr>
<tr>
            <td class="title" colspan="2"><br/>Check for Directory and File Permissions</td>
</tr>
<?php
$okMessage="";
if (is_writable( "../includes/config.php" )) {

        changeMode( "../includes/config.php", 777 );
        $okMessage="Permissions for this File have been set to 777 (world-writable) for write purposes. Please consider that there are Security issues with 777 in a productive area.";

 }
?>
<tr>
            <td class="item">./includes/config.php writable</td>
            <td align="left"><?php echo is_writable( "../includes/config.php" ) ? '<b class="ok">'.$okImg.'</b>'.$okMessage : '<span class="warning"><a href="chmod.php?object=config">'.$failedImg.'</a> Configuration process can still be continued. Configuration file will be displayed at the end, just copy & paste this and upload.</span>';?></td>
</tr>
<?php
$okMessage="";
if (is_writable( "../includes/config.php" )) {

        changeMode( "../includes/config.php", 777 );
        $okMessage="Permissions for this File have been set to 777 (world-writable) for write purposes. Please consider that there are Security issues with 777 in a productive area.";

 }
?>
<tr>
            <td class="item">./files writable</td>
            <td align="left"><?php echo is_writable( "../files" ) ? '<b class="ok">'.$okImg.'</b>' : '<b class="error">'.$failedImg.'</b><span class="warning"> File upload functionality will be disabled</span>';?></td>
</tr>
<tr>
            <td class="item">./files/temp writable</td>
            <td align="left"><?php echo is_writable( "../files/temp" ) ? '<b class="ok">'.$okImg.'</b>' : '<b class="error">'.$failedImg.'</b><span class="warning"> PDF report generation will be disabled</span>';?></td>
</tr>
<tr>
            <td class="item">./locales/en writable</td>
            <td align="left"><?php echo is_writable( "../locales/en" ) ? '<b class="ok">'.$okImg.'</b>' : '<b class="error">'.$failedImg.'</b><span class="warning"> Translation files cannot be saved. Check /locales and subdirectories for permissions.</span>';?></td>
</tr>
<tr>
            <td class="title" colspan="2"><br/>Recommended PHP Settings</td>
</tr>
<tr>
            <td class="item">Safe Mode = OFF?</td>
            <td align="left"><?php echo !get_cfg_var('safe_mode') ? '<b class="ok">'.$okImg.'</b>' : '<b class="error">'.$failedImg.'</b><span class="warning"></span>';?></td>
</tr>
<tr>
            <td class="item">Register Globals = OFF?</td>
            <td align="left"><?php echo !get_cfg_var('register_globals') ? '<b class="ok">'.$okImg.'</b>' : '<b class="error">'.$failedImg.'</b><span class="warning"></span>';?></td>
</tr>
<tr>
            <td class="item">Session AutoStart = ON?</td>
            <td align="left"><?php echo get_cfg_var('session.auto_start') ? '<b class="ok">'.$okImg.'</b>' : '<b class="error">'.$failedImg.'</b><span class="warning"> Try setting to ON if you are experiencing a WhiteScreenOfDeath</span>';?></td>
</tr>
<tr>
            <td class="item">Session Use Cookies = ON?</td>
            <td align="left"><?php echo get_cfg_var('session.use_cookies') ? '<b class="ok">'.$okImg.'</b>' : '<b class="error">'.$failedImg.'</b><span class="warning"> Try setting to ON if you are experiencing problems to log in</span>';?></td>
</tr>
<tr>
            <td class="item">Session Use Trans Sid = OFF?</td>
            <td align="left"><?php echo !get_cfg_var('session.use_cookies') ? '<b class="ok">'.$okImg.'</b>' : '<b class="error">'.$failedImg.'</b><span class="warning"> There are security risks with this turned ON</span>';?></td>
</tr>
<tr>
            <td colspan="2" align="right"><br /><form action="db.php" method="post" name="form" id="form"><input class="button" type="submit" name="next" value="Continue" /></form></td>
          </tr>
<?php
/*
echo "<tr><td>Web Server</td><td colspan='2'>$_SERVER[SERVER_SOFTWARE]</td></tr>";
echo "<tr><td>Web Server</td><td colspan='2'>$_SERVER[SERVER_SIGNATURE]</td></tr>";

echo "<tr><td>User Agent</td><td>".$_SERVER['HTTP_USER_AGENT']."</td></tr>";

echo "<tr><td>default locale</td><td>";
$lc_list = explode(";", setlocale( LC_ALL, 0 ));
foreach ($lc_list as $lc) {
  echo "$lc<br>";
}
echo "</td></tr>";


// Now check to see if the supplied root_dir is the same as the called URL.
$url = preg_replace('/\/docs\/.*$/', '', $_SERVER['PATH_TRANSLATED']);
echo "<tr><td>root_dir</td><td>$dPconfig[root_dir]</td>";
if ($url != $dPconfig['root_dir']) {
  echo "<td class=error>root_dir seems to be incorrect, probably should be $url</td></tr>";
} else {
  echo "<td>OK</td></tr>";
}
echo "<tr><td>Operating System</td><td>".php_uname()."</td></tr>";
echo "</table>";


*/

?>
</table>
</body>
</html>
