<?php

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
$dbbackup               = trim( dPgetParam( $_POST, 'dbbackup', true ) );
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



#
# Some config definition needed for the dbConnection
#

require_once("../includes/db_mysql.php");

if (! $dbcreation == true){

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

        #
        # IMPLEMENT DROP TABLES FUNCTIONALITY
        #

        if ($dbdrop == true){
                $sql = "DROP DATABASE IF EXISTS $dbname";
                $dbExec = db_exec($sql);
        }

        // create the database now
        $sql = "CREATE DATABASE $dbname";
        $dbExec = db_exec($sql);
        $dbError = db_errno();

        if ($dbError <> 0 && $dbError <> 1007){

        //provide some error info
        $dbmsg .= "A Database Error occurred. Database has not been created!\n".mysql_error();
        stepBack($dbmsg);
        }

        $db = mysql_select_db($dbname);


        populate_db($dbname, $defSqlFilePath);


}

function stepBack($dbmsg) {
global $dbhost,$dbname,$dbuser,$dbpass,$dbport,$dbpersist,$dbdrop,$dbbackup,$dbmsg,$cfgmsg;

        echo "<form name=\"stepBack\" method=\"post\" action=\"db.php\">
                <input type=\"hidden\" name=\"dbhost\" value=\"$dbhost\">
                <input type=\"hidden\" name=\"dbname\" value=\"$dbname\">
                <input type=\"hidden\" name=\"dbuser\" value=\"$dbuser\">
                <input type=\"hidden\" name=\"dbpass\" value=\"$dbpass\">
                <input type=\"hidden\" name=\"dbport\" value=\"$dbport\">
                <input type=\"hidden\" name=\"dbpersist\" value=\"$dbpersist\">
                <input type=\"hidden\" name=\"dbdrop\" value=\"$dbdrop\">
                <input type=\"hidden\" name=\"dbbackup\" value=\"$dbbackup\">
                <input type=\"hidden\" name=\"dbmsg\" value=\"$dbmsg\">
                <input type=\"hidden\" name=\"cfgmsg\" value=\"$cfgmsg\">
		</form>";
	echo "<SCRIPT>document.stepBack.submit(); </SCRIPT>";
}

function populate_db($DBname, $sqlfile) {
	mysql_select_db($DBname);
	$mqr = @get_magic_quotes_runtime();
	@set_magic_quotes_runtime(0);
	$query = fread(fopen($sqlfile, "r"), filesize($sqlfile));
	@set_magic_quotes_runtime($mqr);
	$pieces  = split_sql($query);
	$errors = array();
	for ($i=0; $i<count($pieces); $i++) {
		$pieces[$i] = trim($pieces[$i]);
		if(!empty($pieces[$i]) && $pieces[$i] != "#") {
			if (!$result = mysql_query ($pieces[$i])) {
				$errors[] = array ( mysql_error(), $pieces[$i] );
			}
		}
	}
}

function split_sql($sql) {
	$sql = trim($sql);
	$sql = ereg_replace("\n#[^\n]*\n", "\n", $sql);

	$buffer = array();
	$ret = array();
	$in_string = false;

	for($i=0; $i<strlen($sql)-1; $i++) {
		if($sql[$i] == ";" && !$in_string) {
			$ret[] = substr($sql, 0, $i);
			$sql = substr($sql, $i + 1);
			$i = 0;
		}

		if($in_string && ($sql[$i] == $in_string) && $buffer[1] != "\\") {
			$in_string = false;
		}
		elseif(!$in_string && ($sql[$i] == '"' || $sql[$i] == "'") && (!isset($buffer[0]) || $buffer[0] != "\\")) {
			$in_string = $sql[$i];
		}
		if(isset($buffer[1])) {
			$buffer[0] = $buffer[1];
		}
		$buffer[1] = $sql[$i];
	}

	if(!empty($sql)) {
		$ret[] = $sql;
	}
	return($ret);
}
?>
<html>
<head>
	<title>dotProject Installer</title>
	<meta name="Author" content="Gregor Erhardt: gregor at dotproject dot orangrey dot org">
	<meta name="Description" content="Automated Installer Routine for dotProject">
	<link rel="stylesheet" type="text/css" href="./install.css">
</head>
<body>
<span class="error"><?php echo $cfgmsg; ?></span>
<h1><img src="dp.png" align="middle" alt="dotProject Logo"/>&nbsp;Installer for dotProject <?php echo dPgetVersion();?>: Step 3</h1>
<form action="config.php" method="post" name="form" id="form">
        <table cellspacing="0" cellpadding="3" border="0" class="tbl" width="90%" align="center">
        <tr>
            <td class="title" colspan="2">Database Setup Results</td>
        </tr>
        <tr>
            <td class="item" colspan="2">The database for dotProject was created successfully with the following values:</td>
        </tr>
        <tr>
            <td class="item">Database Host</td>
            <td align="left"><input disabled class="button" type="text"  name="dbhost" value="<?php echo $dbhost;?>" /></td>
        </tr>
         <tr>
            <td class="item">Database Name</td>
            <td align="left"><input disabled class="button" type="text"  name="dbname" value="<?php echo $dbname;?>" /></td>
        </tr>
        <tr>
            <td class="item">Database User</td>
            <td align="left"><input disabled class="button" type="text"  name="dbuser" value="<?php echo $dbuser;?>" /></td>
        </tr>
        <tr>
            <td class="item">Database User Password</td>
            <td align="left"><input disabled class="button" type="text"  name="dbpass" value="<?php echo $dbpass;?>" /></td>
        </tr>
        <tr>
            <td class="item">Database Port</td>
            <td align="left"><input disabled class="button" type="text"  name="dbport" value="<?php echo $dbport;?>" /></td>
        </tr>
        <tr>
            <td class="item">Database Persistent Connection</td>
            <td align="left"><input disabled type="checkbox" name="dbpersist" value="true" <?php echo ($dbpersist==true) ? 'checked="checked"' : ''; ?>" /></td>
        </tr>
        <tr>
            <td class="title" colspan="2">&nbsp;</td>
        </tr>
        <tr>
            <td class="title" colspan="2">Important Settings</td>
        </tr>
        <tr>
            <td class="item">Root Directory</td>
            <td align="left"><input class="button" type="text" name="root_dir" value="<?php echo $root_dir; ?>" title="The Root Directory for dotProject" /></td>
        </tr>
        <tr>
            <td class="item">Base URL</td>
            <td align="left"><input class="button" type="text" name="base_url" value="<?php echo $base_url; ?>" title="" /></td>
        </tr>
        <tr>
            <td class="item">Site Domain</td>
            <td align="left"><input class="button" type="text" name="site_domain" value="<?php echo $site_domain; ?>" title="" /></td>
        </tr>
        <tr>
            <td class="item">Page Title</td>
            <td align="left"><input class="button" type="text" name="page_title" value="<?php echo $page_title; ?>" title="The Title shown in your Browser and in the dotProject Head" /></td>
        </tr>
        <tr>
            <td class="item">Organization Name</td>
            <td align="left"><input class="button" type="text" name="company_name" value="<?php echo $company_name; ?>" title="The Name of your Organization. It is also the Title for the Login Screen." /></td>
        </tr>
         <tr>
            <td class="title" colspan="2">&nbsp;</td>
        </tr>
        <tr>
            <td class="title" colspan="2">Additional Settings</td>
        </tr>
        <tr>
            <td class="item">Host Locale</td>
            <td align="left"><input class="button" type="text" name="host_locale" value="<?php echo $host_locale; ?>" title="The Language the Login Screen will be in" /></td>
        </tr>
        <tr>
            <td class="item">Currency Symbol</td>
            <td align="left"><input class="button" type="text" name="currency_symbol" value="<?php echo $currency_symbol; ?>" title="Define your localized Currency Symbol. Use '#8364;' preceded by '&' for the EURO sign. Check http://www.w3.org/TR/html401/sgml/entities.html for more info." /></td>
        </tr>
        <tr>
            <td class="item">Daily Working Hours</td>
            <td align="left"><input class="button" type="text" name="daily_working_hours" value="<?php echo $daily_working_hours; ?>" title="Sets the number of 'working' hours in a day." /></td>
        </tr>
        <tr>
            <td class="item">Start Hour of Day</td>
            <td align="left"><input class="button" type="text" name="cal_day_start" value="<?php echo $cal_day_start; ?>" title="Sets the Start Hour of Day View in Calendar." /></td>
        </tr>
        <tr>
            <td class="item">End Hour of Day</td>
            <td align="left"><input class="button" type="text" name="cal_day_end" value="<?php echo $cal_day_end; ?>" title="Sets the End Hour of Day View in Calendar." /></td>
        </tr>
        <tr>
            <td class="item">Hour Incremention</td>
            <td align="left"><input class="button" type="text" name="cal_day_increment" value="<?php echo $cal_day_increment; ?>" title="Sets the Subdivision Fineness in Day View in Calendar. Data mus be entered in the Format 0.xyz" /></td>
        </tr>
        <tr>
            <td class="item">Working Days</td>
            <td align="left"><input class="button" type="text" name="cal_working_days" value="<?php echo $cal_working_days; ?>" title="Sets the Days of Week the Organization is working. 0 = Sunday." /></td>
        </tr>
        <tr>
            <td class="title" colspan="2">&nbsp;</td>
        </tr>
        <tr>
            <td class="title" colspan="2">Settings for Advanced Users</td>
        </tr>
        <tr>
            <td class="item">User Interface Style</td>
            <td align="left"><input class="button" type="text" name="host_style" value="<?php echo $host_style; ?>" title="Sets the default User Interface Style. Available is 'default' and 'classic'." /></td>
        </tr>
        <tr>
            <td class="item">JPGraphLibrary Locale</td>
            <td align="left"><input class="button" type="text" name="jpLocale" value="<?php echo $jpLocale; ?>" title="Sets the locale for the jpGraph library (used for GANTT Charts). Leave blank if you experience problems." /></td>
        </tr>

          <tr>
            <td class="item">Check Legacy Passwords?</td>
            <td align="left"><input type="checkbox" name="check_legacy_passwords" value="true" <?php echo ($check_legacy_passwords==true) ? 'checked="checked"' : ''; ?> title="ONLY REQUIRED FOR UPGRADES prior to and including version 1.0 alpha 2!" /></td>
          </tr>
             <tr>
            <td class="item">Show Other Users' Tasks?</td>
            <td align="left"><input type="checkbox" name="show_all_tasks" value="true" <?php echo ($show_all_tasks==true) ? 'checked="checked"' : ''; ?> title="Enable if you want to be able to see other users' tasks." /></td>
          </tr>
             <tr>
            <td class="item">Enable GANTT Charts?</td>
            <td align="left"><input type="checkbox" name="enable_gantt_charts" value="true" <?php echo ($enable_gantt_charts==true) ? 'checked="checked"' : ''; ?> title="Enable if you want to support GANTT Charts." /></td>
          </tr>
             <tr>
            <td class="item">Log Changes?</td>
            <td align="left"><input type="checkbox" name="log_changes" value="true" <?php echo ($log_changes==true) ? 'checked="checked"' : ''; ?> title="Enable if you want to log changes using the history module." /></td>
          </tr>
           </tr>
             <tr>
            <td class="item">Check Task Dates?</td>
            <td align="left"><input type="checkbox" name="check_tasks_dates" value="true" <?php echo ($check_tasks_dates==true) ? 'checked="checked"' : ''; ?> title="Enable if you want to check task's start and end dates. Disable if you want to be able to leave start or end dates empty." /></td>
          </tr>
          </tr>
          <tr>
            <td class="item">Enable Relink of Tickets?</td>
            <td align="left"><input type="checkbox" name="relink_tickets_kludge" value="true" <?php echo ($relink_tickets_kludge==true) ? 'checked="checked"' : ''; ?> title="Set to true if you need to be able to relink tickets to an arbitrary parent." /></td>
          </tr>
          </tr>
          <tr>
            <td class="item">Restrict Task Time Editing?</td>
            <td align="left"><input type="checkbox" name="restrict_task_time_editing" value="true" <?php echo ($restrict_task_time_editing==true) ? 'checked="checked"' : ''; ?> title="Set to true if you want only to enable task owner, project owner or sysadmin to edit already created task time related information." /></td>
          </tr>
          </tr>
          <tr>
            <td class="item">Warn if Translation Unavailable?</td>
            <td align="left"><input type="checkbox" name="locale_warn" value="true" <?php echo ($locale_warn==true) ? 'checked="checked"' : ''; ?> title="Warn when a translation is not found (for developers and tranlators)!" /></td>
          </tr>
           <tr>
            <td class="item">Locale Warning String</td>
            <td align="left"><input class="button" type="text" name="locale_alert" value="<?php echo $locale_alert; ?>" size="true" title="The string appended to untranslated string or unfound keys." /></td>
          </tr>
           </tr>
             <tr>
            <td class="item">Debug?</td>
            <td align="left"><input type="checkbox" name="debug" value="true" <?php echo ($debug==true) ? 'checked="checked"' : ''; ?> title="Set debug to 'true' to help analyse errors." /></td>
          </tr>
          <tr>
            <td class="title" colspan="2">&nbsp;</td>
        </tr>
        <tr>
            <td class="title" colspan="2">File Parsers for Indexing Information (Advanced Users)</td>
        </tr>
         <tr>
            <td class="item">Default</td>
            <td align="left"><input class="button" type="text" name="ft_default" value="<?php echo $ft_default; ?>" title="" /></td>
          </tr>
          <tr>
            <td class="item">M$ Word</td>
            <td align="left"><input class="button" type="text" name="ft_application_msword" value="<?php echo $ft_application_msword; ?>" title="" /></td>
          </tr>
          <tr>
            <td class="item">Text/HTML</td>
            <td align="left"><input class="button" type="text" name="ft_text_html" value="<?php echo $ft_text_html; ?>" /></td>
          </tr>
          <tr>
            <td class="item">PDF</td>
            <td align="left"><input class="button" type="text" name="ft_application_pdf" value="<?php echo $ft_application_pdf; ?>" title="" /></td>
          </tr>
          <tr>
          <tr>
            <td colspan="2" align="right"><br /> <input class="button" type="submit" name="next" value="Next" /></td>
          </tr>
        </table>
        <input type="hidden" name="dbhost" value="<?php echo $dbhost;?>" />
        <input type="hidden" name="dbname" value="<?php echo $dbname;?>" />
        <input type="hidden" name="dbuser" value="<?php echo $dbuser;?>" />
        <input type="hidden" name="dbpass" value="<?php echo $dbpass;?>" />
        <input type="hidden" name="dbport" value="<?php echo $dbport;?>" />
        <input type="hidden" name="dbpersist" value="<?php echo $dbpersist;?>" />
</form>
</body>
</html>