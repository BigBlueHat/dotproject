<?php /* $Id$ */
//Config File

//db access information
$dPconfig['dbtype'] = "mysql";
$dPconfig['dbhost'] = "localhost";
$dPconfig['dbname'] = "dp_24jan03";
$dPconfig['dbuser'] = "dp_user";
$dPconfig['dbpass'] = "dp_pass";

/*
 Localisation of the host for this dotproject,
 that is, what language will the login screen be in.
*/
$dPconfig['host_locale'] = "en";

// default user interface style
$dPconfig['host_style'] = "default";

$dPconfig['root_dir'] = "c:/sandbox_sourceforge/dotproject";
$dPconfig['company_name'] = "dotProject WAMP";
$dPconfig['page_title'] = "dotProject Development";
$dPconfig['base_url'] = "http://dp.druid.ca";
$dPconfig['site_domain'] = "druid.ca";

// enable if you want to be able to see other users's tasks
$dPconfig['show_all_tasks'] = false;
// enable if you want to support gantt charts
$dPconfig['enable_gantt_charts'] = true;

$dPconfig['daily_working_hours'] = 8.0;

// set debug = true to help analyse errors
$dPconfig['debug'] = false;

//File parsers to return indexing information about uploaded files
$ft["default"] = "/usr/bin/strings";
$ft["application/msword"] = "/usr/bin/strings";
$ft["text/html"] = "/usr/bin/strings";
$ft["application/pdf"] = "/usr/bin/pdftotext";
?>