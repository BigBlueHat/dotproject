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
* @file do_backup.php
* @ backup functionality is based on the backup module for dotproject from daniel vijge, http://sf.net/projects/dotmods/
* @ based on the work of the phpmyadmin project, www.phpmyadmin.net
* @version $Revision$
*/


require_once("commonlib.php");

// propose some Values
$propRoot = realpath("../");
$propBaseUrl = "http://".$_SERVER["SERVER_NAME"].str_replace("install/pref.php", "", $_SERVER["PHP_SELF"]);
if ($_SERVER["SERVER_NAME"] == "127.0.0.1" || $_SERVER["SERVER_NAME"] == "localhost") {
        $propCompanyName = "My Organization";
} else {
        $propCompanyName = $_SERVER["SERVER_NAME"];
}

$cfgmsg                 = trim( dPgetParam( $_POST, 'cfgmsg', '' ) );
$dbmsg                  = trim( dPgetParam( $_POST, 'dbmsg', '' ) );
$root_dir               = trim( dPgetParam( $_POST, 'root_dir', $propRoot ) );
$dbhost                 = trim( dPgetParam( $_POST, 'dbhost', '' ) );
$dbname                 = trim( dPgetParam( $_POST, 'dbname', '' ) );
$dbuser                 = trim( dPgetParam( $_POST, 'dbuser', '' ) );
$dbpass                 = trim( dPgetParam( $_POST, 'dbpass', '' ) );
$dbport                 = trim( dPgetParam( $_POST, 'dbport', '' ) );
$dbpersist              = trim( dPgetParam( $_POST, 'dbpersist', false ) );
$dbdrop                 = trim( dPgetParam( $_POST, 'dbdrop', false ) );
$backupdrop             = trim( dPgetParam( $_POST, 'backupdrop', false ) );
$dbbackup               = trim( dPgetParam( $_POST, 'dbbackup', true ) );
$dobackup               = trim( dPgetParam( $_POST, 'dobackup', true ) );
$dbcreation             = trim( dPgetParam( $_POST, 'dbcreation', false ) );
$host_locale            = trim( dPgetParam( $_POST, 'host_locale', 'en' ) );
$host_style             = trim( dPgetParam( $_POST, 'host_style', 'default' ) );
$jpLocale               = trim( dPgetParam( $_POST, 'jpLocale', '' ) );
$currency_symbol        = trim( dPgetParam( $_POST, 'currency_symbol', '$' ) );
$base_url               = trim( dPgetParam( $_POST, 'base_url', $propBaseUrl ) );
$site_domain            = trim( dPgetParam( $_POST, 'site_domain', $_SERVER["SERVER_NAME"] ) );
$page_title             = trim( dPgetParam( $_POST, 'page_title', 'dotProject' ) );
$company_name           = trim( dPgetParam( $_POST, 'company_name', $propCompanyName ) );
$daily_working_hours    = trim( dPgetParam( $_POST, 'daily_working_hours', '8.0' ) );
$cal_day_start          = trim( dPgetParam( $_POST, 'cal_day_start', 8 ) );
$cal_day_end            = trim( dPgetParam( $_POST, 'cal_day_end', 17 ) );
$cal_day_increment      = trim( dPgetParam( $_POST, 'cal_day_increment', '0.25' ) );
$cal_working_days       = trim( dPgetParam( $_POST, 'cal_working_days', '1,2,3,4,5' ) );
$check_legacy_passwords = trim( dPgetParam( $_POST, 'check_legacy_passwords', false ) );
$show_all_tasks         = trim( dPgetParam( $_POST, 'show_all_tasks', false ) );
$enable_gantt_charts    = trim( dPgetParam( $_POST, 'enable_gantt_charts', true ) );
$log_changes            = trim( dPgetParam( $_POST, 'log_changes', false ) );
$check_tasks_dates      = trim( dPgetParam( $_POST, 'check_tasks_dates', true ) );
$locale_warn            = trim( dPgetParam( $_POST, 'locale_warn', false ) );
$locale_alert           = trim( dPgetParam( $_POST, 'locale_alert', '^' ) );
$debug                  = trim( dPgetParam( $_POST, 'debug', false ) );
$relink_tickets_kludge  = trim( dPgetParam( $_POST, 'relink_tickets_kludge', false ) );
$restrict_task_time_editing = trim( dPgetParam( $_POST, 'restrict_task_time_editing', false ) );
$ft_default             = trim( dPgetParam( $_POST, 'ft_default', '/usr/bin/strings' ) );
$ft_application_msword  = trim( dPgetParam( $_POST, 'ft_application_msword', '/usr/bin/strings' ) );
$ft_text_html           = trim( dPgetParam( $_POST, 'ft_text_html', '/usr/bin/strings' ) );
$ft_application_pdf     = trim( dPgetParam( $_POST, 'ft_application_pdf', '/usr/bin/pdftotext' ) );



// keep existing information and go back to last site
function stepBack($dbmsg) {
global $dbhost,$dbname,$dbuser,$dbpass,$dbport,$dbpersist,$dbdrop,$dbbackup,$dbmsg,$cfgmsg;

        echo "<form name=\"stepBack\" method=\"post\" action=\"db.php\">
                <input type=\"hidden\" name=\"dbhost\" value=\"$dbhost\">
                <input type=\"hidden\" name=\"dbname\" value=\"$dbname\">
                <input type=\"hidden\" name=\"dbuser\" value=\"$dbuser\">
                <input type=\"hidden\" name=\"dbpass\" value=\"$dbpass\">
                <input type=\"hidden\" name=\"dbport\" value=\"$dbport\">
                <input type=\"hidden\" name=\"dbbackup\" value=\"$dbbackup\">
                <input type=\"hidden\" name=\"dbmsg\" value=\"$dbmsg\">
		</form>";
	echo "<SCRIPT>document.stepBack.submit(); </SCRIPT>";
}

if ($dbbackup == true && $dobackup == "Backup" ) {

        if (!$dbhost || !$dbname || !$dbuser || !$dbport) {

                $dbmsg .= "The database details provided are incorrect and/or empty!\n";
                stepBack($dbmsg);
        }

        # hard code for mysql here

        if (!($dbConnect = @mysql_connect($dbhost, $dbuser, $dbpass))) {

                // provide some error info
                $dbmsg .= "Connection to the Database failed: Hostname, Username and/or Password are incorrect and/or empty!\n";
                stepBack($dbmsg);
        }

        mysql_select_db($dbname);
        $alltables = mysql_list_tables($dbname);

        // generate dbScriptHeader
        $output  = '';
        $output .= '# Backup of database \'' . $dbname . '\'' . "\r\n";
        $output .= '# Generated on ' . date('j F Y, H:i:s') . "\r\n";
        $output .= "# Generator : dotProject Installer \r\n";
        $output .= '# OS: ' . PHP_OS . "\r\n";
        $output .= '# PHP version: ' . PHP_VERSION . "\r\n";
        $output .= '# MySQL version: ' . mysql_get_server_info() . "\r\n";
        $output .= "\r\n";
        $output .= "\r\n";


        // fetch all tables one by one
        while ($row = mysql_fetch_row($alltables))
        {
                // introtext for this table
                $output .= '# TABLE: ' . $row[0] . "\r\n";
                $output .= '# --------------------------' . "\r\n";
                $output .= '#' . "\r\n";
                $output .= "\r\n";


                if ($backupdrop == true)
                {
                        // drop table
                        $output .= 'DROP TABLE IF EXISTS `' . $row[0] . '`;' . "\r\n";
                        $output .= "\r\n";
                }




                // structure of the table
                $table = mysql_query('SHOW CREATE TABLE ' . $row[0]);
                $create = mysql_fetch_array($table);

                // replace UNIX enter by Windows Enter for readability in Windows
                $output .= str_replace("\n","\r\n",$create[1]).';';
                $output .= "\r\n";
                $output .= "\r\n";


                $fields = mysql_list_fields($dbname, $row[0]);
                $columns = mysql_num_fields($fields);

                // all data from table
                $result = mysql_query('SELECT * FROM '.$row[0]);
                while($tablerow = mysql_fetch_array($result))
                        {
                        $output .= 'INSERT INTO `'.$row[0].'` (';
                        for ($i = 0; $i < $columns; $i++)
                        {
                                $output .= '`'.mysql_field_name($fields,$i).'`,';
                        }
                        $output = substr($output,0,-1); // remove last comma
                        $output .= ') VALUES (';
                        for ($i = 0; $i < $columns; $i++)
                        {
                                // remove all enters from the field-string. MySql statement must be on one line
                                $value = str_replace("\r\n",'\n',$tablerow[$i]);
                                // replace ' by \'
                                $value = str_replace('\'',"\'",$value);
                                $output .= '\''.$value.'\',';
                        }
                        $output = substr($output,0,-1); // remove last comma
                        $output .= ');' . "\r\n";
                        } // while
                $output .= "\r\n";
                $output .= "\r\n";

        } //end of while clause

        $file = 'backup.sql';
        $mime_type = 'text/sql';
        header('Content-Disposition: inline; filename="' . $file . '"');
        header('Content-Type: ' . $mime_type);
        echo $output;
} else {

        echo "<form name=\"gopref\" method=\"post\" action=\"pref.php\">
                <input type=\"hidden\" name=\"root_dir\" value=\"$root_dir\">
                <input type=\"hidden\" name=\"dbhost\" value=\"$dbhost\">
                <input type=\"hidden\" name=\"dbname\" value=\"$dbname\">
                <input type=\"hidden\" name=\"dbuser\" value=\"$dbuser\">
                <input type=\"hidden\" name=\"dbpass\" value=\"$dbpass\">
                <input type=\"hidden\" name=\"dbport\" value=\"$dbport\">
                <input type=\"hidden\" name=\"dbcreation\" value=\"$dbcreation\">
                <input type=\"hidden\" name=\"dbpersist\" value=\"$dbpersist\">
                <input type=\"hidden\" name=\"dbdrop\" value=\"$dbdrop\">
                <input type=\"hidden\" name=\"dbbackup\" value=\"$dbbackup\">
                <input type=\"hidden\" name=\"dbmsg\" value=\"$dbmsg\">
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
                </form>";
        echo "<SCRIPT>document.gopref.submit(); </SCRIPT>";
}
?>