<?php // $Id$
?>
<html>
<head>
	<title>dotProject Installer</title>
	<meta name="Description" content="dotProject Installer">
 	<link rel="stylesheet" type="text/css" href="../style/default/main.css">
</head>
<body>
<h1><img src="dp.png" align="middle" alt="dotProject Logo"/>&nbsp;dotProject Installer</h1>

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
        <td class="title" colspan="2">There is an initial Check for Requirements appended down below for troubleshooting.</td>
</tr>
<tr>
        <td class="title" colspan="2">You will have to log in with an administrators login/password combination soon.</td>
</tr>
<tr>
        <td colspan="2" align="center"><br /><form action="../?m=install" method="post" name="form" id="form"><input class="button" type="submit" name="next" value="Start Installation" /></form></td>
</tr>
</table>
<br />
<?php
// define some necessary variables for check inclusion
$failedImg = '<img src="../images/icons/stock_cancel-16.png" width="16" height="16" align="middle" alt="Failed"/>';
$okImg = '<img src="../images/icons/stock_ok-16.png" width="16" height="16" align="middle" alt="OK"/>';
$tblwidth = '90%';
$cfgDir = "../includes";
$cfgFile = "../includes/config.php";
$filesDir = "../files";
$locEnDir = "../locales/en";
$tmpDir = "../files/temp";
include_once("../modules/install/vw_idx_check.php");
?>
</body>
</html>
