<?php /* $Id$ */

/**  LICENSE  **

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

// Do not change version for support reasons
$dPconfig['version'] = "dotProject v1.0 alpha 2 [9-Apr-2003]";

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
$dPconfig['root_dir'] = "C:/apache/htdocs/dotproject";  // No trailing slash
$dPconfig['company_name'] = "My Company";
$dPconfig['page_title'] = "DotProject V1.0 pre-alpha";
$dPconfig['base_url'] = "http://localhost/dotproject";
$dPconfig['site_domain'] = "dotproject.net";

// enable if you want to be able to see other users's tasks
$dPconfig['show_all_tasks'] = false;

// enable if you want to support gantt charts
$dPconfig['enable_gantt_charts'] = true;

// enable if you want to log changes using the history module
$dPconfig['log_changes'] = false;

// warn when a translation is not found (for developers and tranlators)
$dPconfig['locale_warn'] = false;

// the string appended to untranslated string or unfound keys
$dPconfig['locale_alert'] = '^';

// the number of 'working' hours in a day
$dPconfig['daily_working_hours'] = 8.0;

// set debug = true to help analyse errors
$dPconfig['debug'] = false;

//File parsers to return indexing information about uploaded files
$ft["default"] = "/usr/bin/strings";
$ft["application/msword"] = "/usr/bin/strings";
$ft["text/html"] = "/usr/bin/strings";
$ft["application/pdf"] = "/usr/bin/pdftotext";
?>