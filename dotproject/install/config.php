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

//reset the goback bool for tracing errors
$goback = false;

//reset the message containers
$dbmsg = false;
$cfgmsg = false;


$root_dir               = trim( dPgetParam( $_POST, 'root_dir', '' ) );
$dbhost                 = trim( dPgetParam( $_POST, 'dbhost', '' ) );
$dbname                 = trim( dPgetParam( $_POST, 'dbname', '' ) );
$dbuser                 = trim( dPgetParam( $_POST, 'dbuser', '' ) );
$dbpass                 = trim( dPgetParam( $_POST, 'dbpass', '' ) );
$dbport                 = trim( dPgetParam( $_POST, 'dbport', '' ) );
$dbpersist              = trim( dPgetParam( $_POST, 'dbpersist', false ) );
$host_locale            = trim( dPgetParam( $_POST, 'host_locale', 'en' ) );
$host_style             = trim( dPgetParam( $_POST, 'host_style', 'default' ) );
$jpLocale               = trim( dPgetParam( $_POST, 'jpLocale', '' ) );
$currency_symbol        = trim( dPgetParam( $_POST, 'currency_symbol', '$' ) );
$base_url               = trim( dPgetParam( $_POST, 'base_url', '' ) );
$site_domain            = trim( dPgetParam( $_POST, 'site_domain', '' ) );
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


//convert values of null to 'false'
$dbpersist              = defVal($dbpersist, "false");
$check_legacy_passwords = defVal($check_legacy_passwords, "false");
$show_all_tasks         = defVal($show_all_tasks, "false");
$enable_gantt_charts    = defVal($enable_gantt_charts, "false");
$log_changes            = defVal($log_changes, "false");
$check_tasks_dates      = defVal($check_tasks_dates, "false");
$locale_warn            = defVal($locale_warn, "false");
$debug                  = defVal($debug, "false");
$relink_tickets_kludge  = defVal($relink_tickets_kludge, "false");
$restrict_task_time_editing = defVal($restrict_task_time_editing, "false");

if(!$root_dir) {
        $goback = "pref.php";
        $cfgmsg .= "The Root Directory provided is incorrect and/or empty!\n";
}

if(!$base_url) {
        $goback = "pref.php";
        $cfgmsg .= "The Base URL provided is incorrect and/or empty!\n";
}

if(!$site_domain) {
        $goback = "pref.php";
        $cfgmsg .= "The Site Domain provided is incorrect and/or empty!\n";
}


if(!$dbhost || !$dbname || !$dbuser || !$dbport) {
        $goback = "db.php";
        $dbmsg .= "The database details provided are incorrect and/or empty!\n";
}

if ($goback){
	echo "<form name=\"stepBack\" method=\"post\" action=\"$goback\">
		<input type=\"hidden\" name=\"root_dir\" value=\"$root_dir\">
                <input type=\"hidden\" name=\"dbhost\" value=\"$dbhost\">
                <input type=\"hidden\" name=\"dbname\" value=\"$dbname\">
                <input type=\"hidden\" name=\"dbuser\" value=\"$dbuser\">
                <input type=\"hidden\" name=\"dbpass\" value=\"$dbpass\">
                <input type=\"hidden\" name=\"dbport\" value=\"$dbport\">
                <input type=\"hidden\" name=\"dbpersist\" value=\"$dbpersist\">
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
                <input type=\"hidden\" name=\"dbmsg\" value=\"$dbmsg\">
                <input type=\"hidden\" name=\"cfgmsg\" value=\"$cfgmsg\">
		</form>";
	echo "<SCRIPT>document.stepBack.submit(); </SCRIPT>";
}

if (file_exists( '../includes/config.php' )) {
	$canWrite = is_writable( '../includes/config.php' );
} else {
	$canWrite = is_writable( '../includes' );
}


if (!$goback){
	$config = "<?php\n";
        $config .= "### Copyright (c) 2004, The dotProject Development Team sf.net/projects/dotproject ###\n";
        $config .= "### All rights reserved. Released under BSD License. For further Information see config-dist.php ###\n";
        $config .= "\n";
        $config .= "### CONFIGURATION FILE AUTOMATICALLY GENERATED BY THE DOTPROJECT INSTALLER ###\n";
        $config .= "### FOR INFORMATION ON MANUAL CONFIGURATION AND FOR DOCUMENTATION SEE config-dist.php ###\n";
        $config .= "\n";
        $config .= "\$dPconfig['dbtype'] = 'mysql';\n";
        $config .= "\$dPconfig['dbhost'] = '{$dbhost}';\n";
        $config .= "\$dPconfig['dbname'] = '{$dbname}';\n";
        $config .= "\$dPconfig['dbuser'] = '{$dbuser}';\n";
        $config .= "\$dPconfig['dbpass'] = '{$dbpass}';\n";
        $config .= "\$dPconfig['dbport'] = '{$dbport}';\n";
        $config .= "\$dPconfig['dbpersist'] = {$dbpersist};\n";
        $config .= "\$dPconfig['check_legacy_passwords'] = {$check_legacy_passwords};\n";
        $config .= "\$dPconfig['host_locale'] = '{$host_locale}';\n";
        $config .= "\$dPconfig['currency_symbol'] = '{$currency_symbol}';\n";
        $config .= "\$dPconfig['host_style'] = '{$host_style}';\n";
        $config .= "\$dPconfig['root_dir'] = '{$root_dir}';\n";
        $config .= "\$dPconfig['company_name'] = '{$company_name}';\n";
        $config .= "\$dPconfig['page_title'] = '{$page_title}';\n";
        $config .= "\$dPconfig['base_url'] = '{$base_url}';\n";
        $config .= "\$dPconfig['site_domain'] = '{$site_domain}';\n";
        $config .= "\$dPconfig['show_all_tasks'] = {$show_all_tasks};\n";
        $config .= "\$dPconfig['enable_gantt_charts'] = {$enable_gantt_charts};\n";
        $config .= "\$dPconfig['jpLocale'] = '{$jpLocale}';\n";
        $config .= "\$dPconfig['log_changes'] = {$log_changes};\n";
        $config .= "\$dPconfig['check_tasks_dates'] = {$check_tasks_dates};\n";
        $config .= "\$dPconfig['locale_warn'] = {$locale_warn};\n";
        $config .= "\$dPconfig['locale_alert'] = '{$locale_alert}';\n";
        $config .= "\$dPconfig['daily_working_hours'] = '{$daily_working_hours}';\n";
        $config .= "\$dPconfig['debug'] = {$debug};\n";
        $config .= "\$dPconfig['relink_tickets_kludge'] = {$relink_tickets_kludge};\n";
        $config .= "\$dPconfig['cal_day_start'] = '{$cal_day_start}';\n";
        $config .= "\$dPconfig['cal_day_end'] = '{$cal_day_end}';\n";
        $config .= "\$dPconfig['cal_day_increment'] = '{$cal_day_increment}';\n";
        $config .= "\$dPconfig['cal_working_days'] = '{$cal_working_days}';\n";
        $config .= "\$dPconfig['restrict_task_time_editing'] = {$restrict_task_time_editing};\n";
        $config .= "\$ft['default'] = '{$ft_default}';\n";
        $config .= "\$ft['application/msword'] = '{$ft_application_msword}';\n";
        $config .= "\$ft['text/html'] = '{$ft_text_html}';\n";
        $config .= "\$ft['application/pdf'] = '{$ft_application_pdf}'\n";
	$config .= "?>";

	if ($canWrite && ($fp = fopen("../includes/config.php", "w"))) {
		fputs( $fp, $config, strlen( $config ) );
		fclose( $fp );
	} else {
		$canWrite = false;
	}
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

<h1><img src="dp.png" align="middle" alt="dotProject Logo"/>&nbsp;Installer for dotProject <?php echo dPgetVersion();?>: Step 3</h1>
<form action="db.php" method="post" name="form" id="form">
        <table cellspacing="0" cellpadding="3" border="0" class="tbl" width="90%" align="center">
        <tr>
            <td class="title" colspan="2">Congratulations: The Installation of dotProject is complete</td>
        </tr>
        <tr>
            <td class="title" colspan="2">&nbsp;</td>
        </tr>
        <tr>
            <td class="item" colspan="2">Please <span class="warning">remove the ./install directory</span> completely for security issues.</td>
        </tr>
        <tr>
            <td class="title" colspan="2">&nbsp;</td>
        </tr>
        <tr>
            <td class="item" colspan="2">Please consider that during Installation Process some
            <span class="warning">file or directory permissions</span> have been set to a possibly <span class="warning">dangerous level</span>. It would be worth to do some effort to verify this.</td>
        </tr>
        <tr>
            <td class="title" colspan="2">&nbsp;</td>
        </tr>
        <tr>
            <td class="item" colspan="2">Now please <u><a href="<?php echo $base_url; ?>">run dotProject</a></u> as admin
            (use 'admin' as username and 'passwd' as password) and
            <span class="warning">change the admin password</span> and fill in an <span class="warning">admin email</span>!</td>
        </tr>
<?php if (!$canWrite) { ?>
        <tr>

                <td colspan="2" class="item"> Your configuration file or directory is not writeable,
                or there was a problem creating the configuration file. You'll have to
                create or edit the file ./includes/config.php and fill in (paste & copy) the following code by hand: </td>
        </tr>
        <tr>
                <td colspan="2" align="center">
                        <textarea rows="5" cols="80" name="configcode" ><?php echo htmlspecialchars( $config );?></textarea>
                </td>
        </tr>
<?php } ?>

        </table>
</form>
</body>
</html>