<?php

//todo: interface: right row more to the left
//todo: try to set the perms for the directories or files, message management
//todo: script to read subdirectories for styles and langs (delete empty/superfluous directories in the core distro???)
//todo: enhanced guiding texts
//todo  db backup, drop tables!
//todo: tooltips everywhere
//todo: implement built-in automatic chmod in check.php

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
?>
<html>
<head>
	<title>dotProject Installer</title>
	<meta name="Author" content="Gregor Erhardt: gregor at dotproject dot orangrey dot org">
	<meta name="Description" content="Automated Installer Routine for dotProject">
	<link rel="stylesheet" type="text/css" href="./install.css">
</head>
<body>

<h1><img src="dp.png" align="middle" alt="dotProject Logo"/>&nbsp;Installer for dotProject <?php echo dPgetVersion(); ?>: Introduction</h1>

<table cellspacing="0" cellpadding="3" border="0" class="tbl" width="90%" align="center">
<tr>
        <td class="item" colspan="2">Welcome to the dotProject Installer that guides you through the complete Installation
        Process. Normally all major configuration settings are generated automatically - verified by you! However, depending on your
        System Environment, errors or information lacks may occur. In some cases a manual installation cannot be avoided.
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
