<?php // $Id$
//todo: enable dbcreated functionality for reposts and so on  (prevent from double-install)
//todo: interface: right row more to the left
//todo: !heavy!: design: have a main site where the steps are linked and where we always come back and do the main work.
//todo: script to read subdirectories for styles and langs (delete empty/superfluous directories in the core distro???)
//todo: and sniff default locale on machine/or browset
//todo: enhanced guiding texts
//todo: read existing config file in case of upgrade
//todo: require then (config file existing) admin passwd for security check
//todo: check in dp main if installer is deleted after successfull install
//todo: change admin passwd?!
//todo: GPL possible?
//todo: better error management with displaying what worked well (has been installed) and what went wrong
//todo: enable db upgrade functionality if db/upgrade.sql is available
//todo: core: store database version (dp version) in dPdatabase for verifying db upgrade scripts!
//todo: ask how advanced the user is, do not show advanced config settings in dummy case
//todo: centralized registration of config vars!
//todo: bug backup db and then there are no preferred values shown


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

### A NOTE FOR DEVELOPERS ###
### HOWTO REGISTER A NEW (NON-DB RELATED) CONFIG VARIABLE WITH THE INSTALLER (2004 05 01) (modified 2004 06 12) ###
# 1) Add an appropriate line to the list of input type hidden fallback items in db.php (somewhere at the end of file)
# 2) Copy the same line to the list of input type hidden fallback items in do_backup.php (somewhere at the end of file)
# 3) Copy the same line to the list of input type hidden fallback items in config.php (somewhere at the middle of file)
# 4) Add an appropriate line to the list of dPgetParam/POST definitions in do_backup.php (somewhere at the top of file)
# 5) Copy the same line to the list of dPgetParam/POST definitions in config.php (somewhere at the top of file)
# 6) Copy the same line to the list of dPgetParam/POST definitions in pref.php (somewhere at the top of file)
# 7) Add an appropriate html form field (tag) to pref.php
# 8) Add an appropriate line to the list of config variables that will be written to the config file in config.php (somewhere more to the end of file)
# 9) If the variable's field is a checkbox (bool variable), add a suitable line to the section where NULL values are converted to FALSE in config.php
### THE REGISTRATION OF THE CONFIG VAR SHOULD NOW BE COMPLETE ###
### IN CASE YOU EXPERIENCE PROBLEMS CONTACT THE AUTHOR/MAINTAINER OF THIS INSTALLER ###

require_once("commonlib.php");
?>
<html>
<head>
	<title>dotProject Installer</title>
	<meta name="Author" content="Gregor Erhardt: gregor at dotproject dot orangrey dot org">
	<meta name="Description" content="Automated Installer Routine for dotProject">
	<link rel="stylesheet" type="text/css" href="./install.css">
</head>
<body>
<h1 class="warning">This Installer is in development. You may try it, but it is likely that it is not properly working!</h1>
<h1><img src="dp.png" align="middle" alt="dotProject Logo"/>&nbsp;Installer for dotProject <?php echo dPgetVersion(); ?>: Introduction</h1>

<table cellspacing="0" cellpadding="3" border="0" class="tbl" width="90%" align="center">
<tr>
        <td class="item" colspan="2">Welcome to the dotProject Installer that guides you through the complete Installation
        Process. Normally all major configuration settings are generated automatically - verified by you! However, depending on your
        System Environment, errors or information lacks may occur. In some cases a manual installation cannot be avoided.<br />&nbsp;<br/>
 	Moving the mouse pointer over a form field will show you a tooltip with information that could be helpful for you!
        </td>
</tr>
<tr>
        <td colspan="2">&nbsp;</td>
</tr>
<tr>
        <td class="title" colspan="2">Step 1: Check for Requirements</td>
</tr>
<tr>
        <td class="title" colspan="2">Step 2: Configuring Database</td>
</tr>
<tr>
        <td class="title" colspan="2">Step 3: Customize and Configure dotProject</td>
</tr>
<tr>
        <td colspan="2" align="right"><br /><form action="check.php" method="post" name="form" id="form"><input class="button" type="submit" name="next" value="Start Installation" /></form></td>
</tr>
<tr>
        <td colspan="2">&nbsp;</td>
</tr>
<tr>
        <td class="item" colspan="2">This Installation Routine will make use of write access to your filesystem and to a database on your system.
        There is no warranty for these actions (For Further Information see the GNU/GPL License: http://www.gnu.org/copyleft/gpl.html).
         </td>
</tr>
</table>
</body>
</html>

