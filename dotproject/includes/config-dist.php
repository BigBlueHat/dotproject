<?php /* $Id$ */

### DEVELOPERS: PLEASE REGISTER NEW CONFIG VARS WITH THE INSTALLER, TOO ###
### DEVELOPERS: THEREFORE READ THE MINI-HOWTO IN ../install/index.php ###

/**  BSD LICENSE  **

Copyright (c) 2003, The dotProject Development Team sf.net/projects/dotproject
All rights reserved.

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice,
  this list of conditions and the following disclaimer.
* Redistributions in binary form must reproduce the above copyright notice,
  this list of conditions and the following disclaimer in the documentation
  and/or other materials provided with the distribution.
* Neither the name of the dotproject development team (past or present) nor the
  names of its contributors may be used to endorse or promote products derived
  from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE
FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

**/

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
$dPconfig['dbname'] = "dotproject";  // Change to match your DotProject Database Name
$dPconfig['dbuser'] = "dp_user";  // Change to match your MySQL Username
$dPconfig['dbpass'] = "dp_pass";  // Change to match your MySQL Password
$dPconfig['dbport'] = "";  // Change to match your Db Port or use the standard value of 3306 if string is empty

// set this value to true to use persistent database connections
$dPconfig['dbpersist'] = false;

// check for legacy password
// ONLY REQUIRED FOR UPGRADES prior to and including version 1.0 alpha 2
$dPconfig['check_legacy_password'] = false;

/*
 Localisation of the host for this dotproject,
 that is, what language will the login screen be in.
*/
$dPconfig['host_locale'] = "en";

/*
 Localisation of the currency-symbol.
 For the EURO sign symbol set to ... = "&#8364;";
 Check http://www.w3.org/TR/html401/sgml/entities.html
 for information about html special characters.
*/
$dPconfig['currency_symbol'] = "$";

// default user interface style
$dPconfig['host_style'] = "default";

// local settings [DEFAULT example WINDOWS]
$dPconfig['root_dir'] = "C:/apache/htdocs/dotproject";  // No trailing slash
$dPconfig['company_name'] = "My Company";
$dPconfig['page_title'] = "dotProject";
$dPconfig['base_url'] = "http://localhost/dotproject";
$dPconfig['site_domain'] = "dotproject.net";

// enable if you want to be able to see other users's tasks
$dPconfig['show_all_tasks'] = false;

// enable if you want to support gantt charts
$dPconfig['enable_gantt_charts'] = true;

/** Sets the locale for the jpGraph library.  Leave blank if you experience problems */
$dPconfig['jpLocale'] = '';

// enable if you want to log changes using the history module
$dPconfig['log_changes'] = false;

// enable if you want to check task's start and end dates
// disable if you want to be able to leave start or end dates empty
$dPconfig['check_tasks_dates'] = true;

// warn when a translation is not found (for developers and tranlators)
$dPconfig['locale_warn'] = false;

// the string appended to untranslated string or unfound keys
$dPconfig['locale_alert'] = '^';

// the number of 'working' hours in a day
$dPconfig['daily_working_hours'] = 8.0;

// set debug = true to help analyse errors
$dPconfig['debug'] = false;

// set to true if you need to be able to relink tickets to
// an arbitrary parent.  Useful for email-generated tickets,
// but the interface is a bit clunky.
$dPconfig['link_tickets_kludge'] = false;

//set to true is you want the task lists to show all assignees names
//set to false if you only want to display the first assignee and then a count of the rest
//the full list is still available on a mouse over.
$dPconfig['show_all_task_assignees'] = false;

//set to true will cause the color selection dialog for projects to only provide a text list of 
// selections to choose from, as defined in as sysvalues.
//set to false will provide a color selection palette for selection of arbitrary colors.
//set this to true if you would like to have the project colors correlate with a specific meaning.
$dPconfig['restrict_color_selection'] = false;

// Calendar settings.
// Day view start end and increment
$dPconfig['cal_day_start']     = 8;	  // Start hour, in 24 hour format
$dPconfig['cal_day_end']       = 17;  // End hour in 24 hour format
$dPconfig['cal_day_increment'] = 15;  // Increment, in minutes
$dPconfig["cal_working_days"]  = "1,2,3,4,5"; // days of week that the company works 0=Sunday

// If you want only to enable task owner, project owner or sysadmin
// to edit already created task time related information, just
// set to "true" the restrict_task_time_editing flag
$dPconfig["restrict_task_time_editing"] = false;

//File parsers to return indexing information about uploaded files
$ft["default"] = "/usr/bin/strings";
$ft["application/msword"] = "/usr/bin/strings";
$ft["text/html"] = "/usr/bin/strings";
$ft["application/pdf"] = "/usr/bin/pdftotext";
?>
