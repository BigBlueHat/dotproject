<?php /* $Id$ */
/*
	* * * INSTALLATION INSTRUCTIONS * * *

	YOU MUST customise "config-dist.php" to your local system:

	1) COPY config-dist.php to "config.php" [if it doesn't exist]

	2) EDIT "config.php" to include your database connection and other local settings.
*/

// DATABASE ACCESS INFORMATION [DEFAULT example]
// Modify these values to suit your local settings

$dPconfig['dbtype'] = "mysql";      // ONLY MySQL is supported at present
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
$dPconfig['page_title'] = "DotProject V1.0 pre-alpha";
$dPconfig['base_url'] = "http://localhost/dotproject";
$dPconfig['site_domain'] = "dotproject.net";
$dPconfig['daily_working_hours'] = 8.0;

// enable if you want to be able to see other users's tasks
$dPconfig['show_all_tasks'] = false;

// enable if you want to support gantt charts
$dPconfig['enable_gantt_charts'] = true;

// enable if you want to log changes using the history module
$dPconfig['log_changes'] = false;

// set debug = true to help analyse errors
$dPconfig['debug'] = false;

// DotProject Version for debugging and support
$dPconfig['version'] = "dotProject v1.0 alpha 2 [20-Mar-2003]";

//File parsers to return indexing information about uploaded files
$ft["default"] = "/usr/bin/strings";
$ft["application/msword"] = "/usr/bin/strings";
$ft["text/html"] = "/usr/bin/strings";
$ft["application/pdf"] = "/usr/bin/pdftotext";
?>