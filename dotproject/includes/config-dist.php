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

// Enable or disable the overallocation checkup
$dPconfig['check_overallocation'] = false;

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
$dPconfig['company_name'] = "My Company";
$dPconfig['page_title'] = "dotProject";
$dPconfig['base_url'] = "http://localhost/dotproject";
$dPconfig['site_domain'] = "dotproject.net";

//Text to be inserted at the beginning of emails sent from dp
$dPconfig['email_prefix'] = '[dotProject]';
//Username for the admin user.
$dPconfig['admin_username'] = 'admin';
//set minimal user id/password length when creating new users, this can NOT be longer than the length of the column in DB
$dPconfig['username_min_len'] = "4";
$dPconfig['password_min_len'] = "4";

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

// Set to true to display debug messages as well as log them.
// WARNING: Setting to true can cause dotproject to fail on
// warnings if the debug level is set greater than 1.
// Normally errors will be displayed so this should only be
// set to track warnings and debug messages if you do not
// have access to the PHP log files.
$dPconfig['display_debug'] = false;

// set to true if you need to be able to relink tickets to
// an arbitrary parent.  Useful for email-generated tickets,
// but the interface is a bit clunky.
$dPconfig['link_tickets_kludge'] = false;

//set to true if you want the task lists to show all assignees names
//set to false if you only want to display the first assignee and then a count of the rest
//the full list is still available on a mouse over.
$dPconfig['show_all_task_assignees'] = false;

//set to true if you want to be able to directly edit task assignment in the big tasks lists
//set to false if you only want to have 'clean' staks lists
$dPconfig['direct_edit_assignment'] = false;

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
$dPconfig['cal_day_view_show_minical'] = true; // defines whether in day view the minicalendars are shown or not

// If you want only to enable task owner, project owner or sysadmin
// to edit already created task time related information, just
// set to "true" the restrict_task_time_editing flag
$dPconfig["restrict_task_time_editing"] = false;


// If you want to define your own start/default page
// set the following variables.
// If the value of default_view_m is '' then the
// first listed module becomes the default view.
// default_view_m specifies the module,
// _a specifies a subview like shown in the url of dP,
// _tab specifies a tabbed subview.
$dPconfig['default_view_m'] = 'calendar';
$dPconfig['default_view_a'] = 'day_view';
$dPconfig['default_view_tab'] = '1';

/* File Indexing for Searching:
** Large Files may cause timeout problems during exhausting indexing process.
** Specify an upper filesize limit for indexing in KiloBytes.
** Have in mind that files greater than the specified value are not indexed!
** A negative value defines the absence of a limit (index all files).
*/
$dPconfig['index_max_file_size'] = -1;

/* Session Management.  This extends the session handling of PHP to
 * beyond browser-based sessions and stores the session information in the
 * database.  This allows for prolonged sessions with lower overheads.
 * Values are in seconds unless followed by a letter:
 * h = hours
 * d = days
 * m = months
 * y = years
 *
 * You can only have 1 character within a string, so 2d4h is NOT valid,
 * but 28h is.
 */
// Which session handling to use, should be either 'php' for traditional
// PHP session management, or 'app' for the dotProject application to
// manage its own sessions via the database.
$dPconfig['session_handling'] = 'app';
// The maximum time a session can have no activity before it is declared dead.
$dPconfig['session_idle_time'] = '2d';
// The maximim time a session can exist before it is trashed, regardless of
// how active it is
$dPconfig['session_max_lifetime'] = '1m';

//File parsers to return indexing information about uploaded files
$ft["default"] = "/usr/bin/strings";
$ft["application/msword"] = "/usr/bin/strings";
$ft["text/html"] = "/usr/bin/strings";
$ft["application/pdf"] = "/usr/bin/pdftotext";

/***************** Configuration for DEVELOPERS use only! ******/

// set debug > 0 to provide more debug information in
// analysing errors.  Set this to 1 to provide the best
// compromise between normal operation and error tracking
// information. Set to 10 to get complete debugging information.
$dPconfig['debug'] = 1;

// When set to true, if dp makes a query, which ends with
// and error, it automatically inserts everything in 
// db/upgrade_latest.sql to try to repair db problems.
$dPconfig['auto_fields_creation'] = false;

// Root directory is now automatically set to avoid 
// getting it wrong. It is also deprecated as $baseDir
// is now set in top-level files index.php and fileviewer.php.
// All code should start to use $baseDir instead of root_dir.
$dPconfig['root_dir'] = $baseDir;

?>
