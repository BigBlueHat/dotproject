<?php // $Id$
$mode = 'install';
if (is_file( "../includes/config.php" )) {
	$mode = 'upgrade';
}
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
        <td class="item" colspan="2">Welcome to the dotProject Installer! It will setup the database for dotProject and create an appropriate config file.
	In some cases a manual installation cannot be avoided.
        </td>
</tr>
<tr>
        <td colspan="2">&nbsp;</td>
</tr>
<tr>
        <td class="title" colspan="2">There is an initial Check for (minimal) Requirements appended down below for troubleshooting. At least a database connection
	must be available and ../includes/config.php must be writable for the webserver!</td>
</tr>
<?php
	if ($mode == 'upgrade') {
?>
<tr>
	<td class='title' colspan='2'><p class='error'>It would appear that you already have a dotProject installation. The installar will attempt to upgrade your system, however it is a good idea to take a full backup first!</p></td>
<?php
	}
?>
<tr>
        <td colspan="2" align="center"><br /><form action="db.php" method="post" name="form" id="form">
	<input class="button" type="submit" name="next" value="Start <?php echo $mode == 'install' ? "Installation" : "Upgrade" ?>" />
	<input type="hidden" name="mode" value="<?php echo $mode; ?>" /></form></td>
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
include_once("vw_idx_check.php");
?>
</body>
</html>
