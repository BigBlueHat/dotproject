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

// propose some values
$propDbPort = ( ini_get('mysql.default_port') == null) ? $defDbPort : ini_get('mysql.default_port');


$dbmsg          = trim( dPgetParam( $_POST, 'dbmsg', '' ) );
$dbhost         = trim( dPgetParam( $_POST, 'dbhost', $defDbHost ) );
$dbname         = trim( dPgetParam( $_POST, 'dbname', $defDbName ) );
$dbuser         = trim( dPgetParam( $_POST, 'dbuser', '' ) );
$dbpass         = trim( dPgetParam( $_POST, 'dbpass', '' ) );
$dbport         = trim( dPgetParam( $_POST, 'dbport', $propDbPort ) );
$dbpersist      = trim( dPgetParam( $_POST, 'dbpersist', false ) );
$dbdrop         = trim( dPgetParam( $_POST, 'dbdrop', false ) );
$dbbackup       = trim( dPgetParam( $_POST, 'dbbackup', true ) );

?>
<html>
<head>
	<title>dotProject Installer</title>
	<meta name="Author" content="Gregor Erhardt: gregor at dotproject dot orangrey dot org">
	<meta name="Description" content="Automated Installer Routine for dotProject">
	<link rel="stylesheet" type="text/css" href="./install.css">
</head>
<body>
<span class="error"><?php echo $dbmsg; ?></span>
<h1><img src="dp.png" align="middle" alt="dotProject Logo"/>&nbsp;Installer for dotProject <?php echo dPgetVersion();?>: Step 2</h1>
<form action="do_backup.php" method="post" name="form" id="form">
        <table cellspacing="0" cellpadding="3" border="0" class="tbl" width="90%" align="center">
        <tr>
            <td class="title" colspan="2">Database Settings</td>
        </tr>
         <tr>
            <td class="item">Database Host Name</td>
            <td align="left"><input class="button" type="text" name="dbhost" value="<?php echo $dbhost; ?>" /></td>
          </tr>
           <tr>
            <td class="item">Database Name</td>
            <td align="left"><input class="button" type="text" name="dbname" value="<?php echo $dbname; ?>" /></td>
          </tr>
          <tr>
            <td class="item">Database User Name</td>
            <td align="left"><input class="button" type="text" name="dbuser" value="<?php echo "$dbuser"; ?>" /></td>
            <td colspan="2">&nbsp;</td>
          </tr>
          <tr>
            <td class="item">Database User Password</td>
            <td align="left"><input class="button" type="text" name="dbpass" value="<?php echo "$dbpass"; ?>" /></td>
          </tr>

          <tr>
            <td class="item">Database Port Name</td>
            <td align="left"><input class="button" type="text" name="dbport" value="<?php echo $dbport; ?>" /></td>
          </tr>
           <tr>
            <td class="item">Use Persistent Connection?</td>
            <td align="left"><input type="checkbox" name="dbpersist" value="true" <?php echo ($dbpersist==true) ? 'checked="checked"' : ''; ?> /></td>
          </tr>
          <tr>
            <td class="item">Drop Existing Database?</td>
            <td align="left"><input type="checkbox" name="dbdrop" value="true" <?php echo ($dbdrop==true) ? 'checked="checked"' : ''; ?> /></td>
            <td class="item">If checked, existing Data will be lost!</td>
        </tr>
          <tr>
            <td class="title" colspan="2">&nbsp;</td>
        </tr>
          <tr>
            <td class="title" colspan="2">Backup existing Database (Recommended)</td>
        </tr>
        <tr>
            <td class="item" colspan="2">Receive a Backup SQL File containing all Tables for the database entered above
            by clicking on the Button labeled 'Backup' down below.</td>
        </tr>
        <tr>
            <td class="item">Add 'Drop Tables'-Command in SQL-Script?</td>
            <td align="left"><input type="checkbox" name="backupdrop" value="false" <?php echo ($backupdrop==true) ? 'checked="checked"' : ''; ?> /></td>
        </tr>
        <tr>
            <td class="item">Receive SQL File</td>
            <td align="left"><input class="button" type="submit" name="dobackup" value="Backup" /></td>
        </tr>
          <tr>
            <td colspan="3" align="right"><br /> <input class="button" type="submit" name="next" value="Next" /></td>
          </tr>
        </table>
        <?php if ($dbmsg > "") {
                echo "<input type=\"hidden\" name=\"root_dir\" value=\"$root_dir\">
                <input type=\"hidden\" name=\"host_locale\" value=\"$host_locale\">
                <input type=\"hidden\" name=\"host_style\" value=\"$host_style\">
                <input type=\"hidden\" name=\"jpLocale\" value=\"$jpLocale\">
                <input type=\"hidden\" name=\"currency_symbol\" value=\"$currency_symbol\">
                <input type=\"hidden\" name=\"base_url\" value=\"$base_url\">
                <input type=\"hidden\" name=\"site_domain\" value=\"$site_domain\">
                <input type=\"hidden\" name=\"page_title\" value=\"$page_title\">
                <input type=\"hidden\" name=\"company_name\" value=\"$company_name\">
                <input type=\"hidden\" name=\"daily_working_hours\" value=\"$daily_working_hours\">
                <input type=\"hidden\" name=\"cal_day_start\" value=\"$cal_day_start\">
                <input type=\"hidden\" name=\"cal_day_end\" value=\"$cal_day_end\">
                <input type=\"hidden\" name=\"cal_day_increment\" value=\"$cal_day_increment\">
                <input type=\"hidden\" name=\"cal_working_days\" value=\"$cal_working_days\">
                <input type=\"hidden\" name=\"check_legacy_passwords\" value=\"$check_legacy_passwords\">
                <input type=\"hidden\" name=\"show_all_tasks\" value=\"$show_all_tasks\">
                <input type=\"hidden\" name=\"enable_gantt_charts\" value=\"$enable_gantt_charts\">
                <input type=\"hidden\" name=\"log_changes\" value=\"$log_changes\">
                <input type=\"hidden\" name=\"check_tasks_dates\" value=\"$check_tasks_dates\">
                <input type=\"hidden\" name=\"locale_warn\" value=\"$locale_warn\">
                <input type=\"hidden\" name=\"locale_alert\" value=\"$locale_alert\">
                <input type=\"hidden\" name=\"debug\" value=\"$debug\">
                <input type=\"hidden\" name=\"relink_tickets_kludge\" value=\"$relink_tickets_kludge\">
                <input type=\"hidden\" name=\"restrict_task_time_editing\" value=\"$restrict_task_time_editing\">
                <input type=\"hidden\" name=\"ft_default\" value=\"$ft_default\">
                <input type=\"hidden\" name=\"ft_application_msword\" value=\"$ft_application_msword\">
                <input type=\"hidden\" name=\"ft_text_html\" value=\"$ft_text_html\">
                <input type=\"hidden\" name=\"ft_application_pdf\" value=\"$ft_application_pdf\">
                <input type=\"hidden\" name=\"cfgmsg\" value=\"$cfgmsg\">
                ";
        }?>
</form>
</body>
</html>