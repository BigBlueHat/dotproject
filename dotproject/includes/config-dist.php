<?php /* $Id$ */
//Config File
// INSTALLATION INSTRUCTIONS:
// You must customize "config-dist.php" to your local system:
// 1) Copy config-dist.php to "config.php" [if it doesn't exist]
// 2) Edit "config.php" to include your database connection and other local settings.

//db access information [DEFAULT example]
$dPconfig['dbtype'] = "mysql";
$dPconfig['dbhost'] = "localhost";
$dPconfig['dbname'] = "dotproject";
$dPconfig['dbuser'] = "root";
$dPconfig['dbpass'] = "";


/*
 Localisation of the host for this dotproject,
 that is, what language will the login screen be in.
*/
$dPconfig['host_locale'] = "en";

// default user interface style
$dPconfig['host_style'] = "default";

// local settings [DEFAULT example WINDOWS]
$dPconfig['root_dir'] = "C:/apache/htdocs/dotproject";
$dPconfig['company_name'] = "My Company";
$dPconfig['page_title'] = "DotProject";
$dPconfig['base_url'] = "http://localhost/dotproject";
$dPconfig['site_domain'] = "dotproject.net";

// enable if you want to be able to see other users's tasks
$dPconfig['show_all_tasks'] = false;
// enable if you want to support gantt charts
$dPconfig['enable_gantt_charts'] = true;
// enable if you want to log changes using the history module
$dPconfig['log_changes'] = false;

$dPconfig['daily_working_hours'] = 8.0;

// set debug = true to help analyse errors
$dPconfig['debug'] = false;

//File parsers to return indexing information about uploaded files
$ft["default"] = "/usr/bin/strings";
$ft["application/msword"] = "/usr/bin/strings";
$ft["text/html"] = "/usr/bin/strings";
$ft["application/pdf"] = "/usr/bin/pdftotext";
?>
