<?php // $Id$

global $cfgDir, $cfgFile, $failedImg, $filesDir, $locEnDir, $okImg, $tblwidth, $tmpDir;
$cfgDir = isset($cfgDir) ? $cfgDir : "../includes";
$cfgFile = isset($cfgFile) ? $cfgFile : "../includes/config.php";
$filesDir = isset($filesDir) ? $filesDir : "../files";
$locEnDir = isset($locEnDir) ? $locEnDir : "../locales/en";
$tmpDir = isset($tmpDir) ? $tmpDir : "../files/temp";
$tblwidth = isset($tblwidth) ? $tblwidth :'100%';
$chmod = '0777';
?>

<table cellspacing="0" cellpadding="3" border="0" class="tbl" width="<?php echo $tblwidth; ?>" align="center">
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
	<td class="item"><li>GD Support (for GANTT Charts)</li></td>
 	<td align="left"><?php echo extension_loaded('gd') ? '<b class="ok">'.$okImg.'</b>' : '<b class="error">'.$failedImg.'</b> GANTT Chart functionality may not work correctly.';?></td>
</tr>
<tr>
	<td class="item"><li>Zlib compression Support</li></td>
 	<td align="left"><?php echo extension_loaded('zlib') ? '<b class="ok">'.$okImg.'</b>' : '<b class="error">'.$failedImg.'</b> Non-core Backup module is working with some minor restrictions.';?></td>
</tr>
<tr>
	<td class="item"><li>File Uploads</li></td>
 	<td align="left"><?php echo get_cfg_var('file_uploads') ? '<b class="ok">'.$okImg.'</b><span class="item"> (Max File Upload Size: '. min(ini_get('upload_max_filesize'), ini_get('post_max_size'), ini_get('memory_limit')) .')</span>' : '<b class="error">'.$failedImg.'</b><span class="warning"> Upload functionality will not be available</span>';?></td>
</tr>
<tr>
            <td class="item">Session Save Path writable?</td>
            <td align="left"><?php echo (is_dir( get_cfg_var( 'session.save_path' )) && is_writable( get_cfg_var( 'session.save_path' )) ) ? '<b class="ok">'.$okImg.'</b> <span class="item">('.get_cfg_var( 'session.save_path').')</span>' : '<b class="error">'.$failedImg.' Fatal:</b> <b class="item">'.get_cfg_var( "session.save_path" ).'</b><b class="error"> not existing or not writable</b>';?></td>
</tr>
<tr>
            <td class="title" colspan="2"><br />Database Connectors</td>
</tr>
<tr>
            <td class="item" colspan="2">The next tests check for database support compiled with php. We use the ADODB database abstraction layer which comes with drivers for
	    many databases. Consult the ADODB documentation for details. <br />For non-advanced users: MySQL will probably be the database of your choice - make sure MySQL Support
	    is available.</td>
</tr>
<tr>
	<td class="item"><li>iBase Support</li></td>
 	<td align="left"><?php echo function_exists( 'ibase_connect' ) ? '<b class="ok">'.$okImg.'</b><span class="item"> ('.ibase_server_info().')</span>' : '<span class="warning">'.$failedImg.' Not available</span>';?></td>
</tr>
<tr>
	<td class="item"><li>Informix Support</li></td>
 	<td align="left"><?php echo function_exists( 'ifx_connect' ) ? '<b class="ok">'.$okImg.'</b><span class="item"> </span>' : '<span class="warning">'.$failedImg.' Not available</span>';?></td>
</tr>
<tr>
	<td class="item"><li>LDAP Support</li></td>
 	<td align="left"><?php echo function_exists( 'ldap_connect' ) ? '<b class="ok">'.$okImg.'</b><span class="item"> </span>' : '<span class="warning">'.$failedImg.' Not available</span>';?></td>
</tr>
<tr>
	<td class="item"><li>mSQL Support</li></td>
 	<td align="left"><?php echo function_exists( 'msql_connect' ) ? '<b class="ok">'.$okImg.'</b><span class="item"></span>' : '<span class="warning">'.$failedImg.' Not available</span>';?></td>
</tr>
<tr>
	<td class="item"><li>M$SQL Support</li></td>
 	<td align="left"><?php echo function_exists( 'mssql_connect' ) ? '<b class="ok">'.$okImg.'</b><span class="item"></span>' : '<span class="warning">'.$failedImg.' Not available</span>';?></td>
</tr>
<tr>
	<td class="item"><li>MySQL Support</li></td>
 	<td align="left"><?php echo function_exists( 'mysql_connect' ) ? '<b class="ok">'.$okImg.'</b><span class="item"> ('.mysql_get_server_info().')</span>' : '<span class="warning">'.$failedImg.' Not available</span>';?></td>
</tr>
<tr>
	<td class="item"><li>ODBC Support</li></td>
 	<td align="left"><?php echo function_exists( 'odbc_connect' ) ? '<b class="ok">'.$okImg.'</b><span class="item"></span>' : '<span class="warning">'.$failedImg.' Not available</span>';?></td>
</tr>
<tr>
	<td class="item"><li>Oracle Support</li></td>
 	<td align="left"><?php echo function_exists( 'oci_connect' ) ? '<b class="ok">'.$okImg.'</b><span class="item"> ('.ociserverversion().')</span>' : '<span class="warning">'.$failedImg.' Not available</span>';?></td>
</tr>
<tr>
	<td class="item"><li>PostgreSQL Support</li></td>
 	<td align="left"><?php echo function_exists( 'pg_connect' ) ? '<b class="ok">'.$okImg.'</b><span class="item"></span>' : '<span class="warning">'.$failedImg.' Not available</span>';?></td>
</tr>
<tr>
	<td class="item"><li>SQLite Support</li></td>
 	<td align="left"><?php echo function_exists( 'sqlite_open' ) ? '<b class="ok">'.$okImg.'</b><span class="item"> ('.sqlite_libversion().')</span>' : '<span class="warning">'.$failedImg.' Not available</span>';?></td>
</tr>
<tr>
	<td class="item"><li>Sybase Support</li></td>
 	<td align="left"><?php echo function_exists( 'sybase_connect' ) ? '<b class="ok">'.$okImg.'</b><span class="item"> </span>' : '<span class="warning">'.$failedImg.' Not available</span>';?></td>
</tr>
<tr>
            <td class="title" colspan="2"><br />Check for Directory and File Permissions</td>
</tr>
<tr>
            <td class="item" colspan="2">If there appears a message '777' after a file/directory, then Permissions for this File have been set to 777 (world-writable) for write purposes.
            Consider that there are Security issues with 777 in a productive environment. Manual efforts for fine grained permissions setting are inevitable.</td>
</tr>
<?php
if ( (file_exists( $cfgFile ) && !is_writable( $cfgFile )) || (!file_exists( $cfgFile ) && !(is_writable( $cfgDir ))) ) {

        @chmod( $cfgFile, $chmod );
        @chmod( $cfgDir, $chmod );
	$filemode = @fileperms($cfgFile);
	if ($filemode & 2)
	        $okMessage="<span class='error'> 777</span>";

 }
?>
<tr>
            <td class="item">./includes/config.php writable?</td>
            <td align="left"><?php echo ( is_writable( $cfgFile ) || is_writable( $cfgDir ))  ? '<b class="ok">'.$okImg.'</b>'.$okMessage : '<b class="error">'.$failedImg.'</b><span class="warning"> Configuration process can still be continued. Configuration file will be displayed at the end, just copy & paste this and upload.</span>';?></td>
</tr>
<?php
$okMessage="";
if (is_writable( $filesDir )) {

        @chmod( $filesDir, $chmod );
	$filemode = @fileperms($filesDir);
	if ($filemode & 2)
        	$okMessage="<span class='error'> 777</span>";

 }
?>
<tr>
            <td class="item">./files writable?</td>
            <td align="left"><?php echo is_writable( $filesDir ) ? '<b class="ok">'.$okImg.'</b>'.$okMessage : '<b class="error">'.$failedImg.'</b><span class="warning"> File upload functionality will be disabled</span>';?></td>
</tr>
<?php
$okMessage="";
if (is_writable( $tmpDir )) {

        @chmod( $tmpDir, $chmod );
	$filemode = @fileperms($tmpDir);
	if ($filemode & 2)
        	$okMessage="<span class='error'> 777</span>";

 }
?>
<tr>
            <td class="item">./files/temp writable?</td>
            <td align="left"><?php echo is_writable( $tmpDir ) ? '<b class="ok">'.$okImg.'</b>'.$okMessage : '<b class="error">'.$failedImg.'</b><span class="warning"> PDF report generation will be disabled</span>';?></td>
</tr>
<?php
$okMessage="";
if (is_writable( $locEnDir )) {

        @chmod( $locEnDir, $chmod );
	$filemode = @fileperms($filesDir);
	if ($filemode & 2)
	        $okMessage="<span class='error'> 777</span>";

 }
?>
<tr>
            <td class="item">./locales/en writable?</td>
            <td align="left"><?php echo is_writable( $locEnDir ) ? '<b class="ok">'.$okImg.'</b>'.$okMessage : '<b class="error">'.$failedImg.'</b><span class="warning"> Translation files cannot be saved. Check /locales and subdirectories for permissions.</span>';?></td>
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
            <td class="title" colspan="2"><br/>Other Recommendations</td>
</tr>
<tr>
            <td class="item">Free Operating System?</td>
            <td align="left"><?php echo (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN') ? '<b class="ok">'.$okImg.'</b><span class="item"> ('.php_uname().')</span>' : '<b class="error">'.$failedImg.'</b><span class="warning">
            It seems as if you were using a propietary operating system. For most extent Freedom and for all known security issues with proprietary things
            the author of this installer would strongly encourage you to use free software wherever possible.</span>';?></td>
</tr>
<tr>
            <td class="item">Free Web Server?</td>
            <td align="left"><?php echo (stristr($_SERVER['SERVER_SOFTWARE'], 'iis') == false) ? '<b class="ok">'.$okImg.'</b><span class="item"> ('.$_SERVER['SERVER_SOFTWARE'].')</span>' : '<b class="error">'.$failedImg.'</b><span class="warning">
            It seems as if you were using a propietary web server. For most extent Freedom and for all known security issues with proprietary things
            the author of this installer would strongly encourage you to use free software wherever possible.</span>';?></td>
</tr>
<tr>
            <td class="item">Browser is not MSIE?</td>
            <td align="left"><?php echo (stristr($_SERVER['HTTP_USER_AGENT'], 'msie') == false) ? '<b class="ok">'.$okImg.'</b><span class="item"> ('.$_SERVER['HTTP_USER_AGENT'].')</span>' : '<b class="error">'.$failedImg.'</b><span class="warning">
            It seems as if you were using a propietary browser. For most extent Freedom and for all known security and compatibility issues
            with proprietary things the author of this installer would strongly encourage you to use free software wherever possible.
            There are some known issues for proprietary browsers.</span>';?></td>
</tr>
</table>
